package io.github.sof3.libglocal.intellij.parser

import com.intellij.psi.TokenType.BAD_CHARACTER
import com.intellij.psi.tree.IElementType
import io.github.sof3.libglocal.intellij.util.NEVER

/*
 * libglocal
 *
 * Copyright 2018 SOFe
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

private typealias Tokens = LgcElements

class LibglocalLexer {
	companion object {
		val CHARSET_WHITESPACE = " \t".toCharArray()
		val CHARSET_NEWLINE = "\r\n".toCharArray()
		val CHARSET_WS_NL = CHARSET_WHITESPACE + CHARSET_NEWLINE
		val CHARSET_IDENTIFIER = (
			"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +
				"abcdefghijklmnopqrstuvwxyz" +
				"1234567890" +
				"-_.").toCharArray()
		val CHARSET_DIGITS = "1234567890".toCharArray()
		val CHARSET_LITERAL_STOP = "\r\n\\#$%}".toCharArray()

		val MATH_TOKENS = mapOf(
			"@" to Tokens.MATH_AT,
			"%" to Tokens.MATH_MOD,
			"=" to Tokens.MATH_EQ,
			"<>" to Tokens.MATH_NE,
			"!=" to Tokens.MATH_NE,
			"<=" to Tokens.MATH_LE,
			"<" to Tokens.MATH_LT,
			">=" to Tokens.MATH_GE,
			">" to Tokens.MATH_GT
		)
	}

	val wrapper = LexerWrapper(this::lex)
	private val reader = LexReader(wrapper)

	private var indentStack = mutableListOf<String>()

	private fun lex(state: Int): Int {
		when(state) {
			LEXER_INITIAL_STATE -> {
				if(!lexLineStart()) return state
				lexLineBody()
				return state
			}
			1 -> {
				for(i in 1..indentStack.size) {
					yield(Tokens.INDENT_DECREASE, "")
				}
				return LEXER_END_STATE
			}
			else -> return state
		}
	}

	private fun lexLineStart(): Boolean {
		val lfInit = reader.readAny(CHARSET_NEWLINE)
		if(lfInit.isNotEmpty()) {
			yield(Tokens.WHITESPACE, lfInit)
		}

		val white = reader.readAny(CHARSET_WHITESPACE)

		if(reader.startsWith("//")) {
			if(white.isNotEmpty()) {
				yield(Tokens.WHITESPACE, white)
			}
			val comment = reader.readAny(CHARSET_NEWLINE, true) + reader.readAny(CHARSET_NEWLINE)
			yield(Tokens.COMMENT, comment)
			return false
		}

		val lf = reader.readAny(CHARSET_NEWLINE)
		if(lf.isNotEmpty()) {
			yield(Tokens.WHITESPACE, white + lf)
			return false
		}

		if(white.isEmpty()) {
			for(i in 1..indentStack.size) {
				yield(Tokens.INDENT_DECREASE, "")
			}
			indentStack.clear()
			return true
		}

		if(indentStack.isEmpty()) {
			indentStack.add(white)
			yield(Tokens.INDENT_INCREASE, white)
			return true
		}

		if(white.startsWith(indentStack.last())) {
			if(white != indentStack.last()) {
				indentStack.add(white)
				yield(Tokens.INDENT_INCREASE, "")
			}
			yield(Tokens.INDENT, white)
			return true
		}

		var ok = false
		while(indentStack.isNotEmpty()) {
			if(white == indentStack.last()) {
				ok = true
				break
			}
			yield(Tokens.INDENT_DECREASE, "")
			indentStack.removeAt(indentStack.lastIndex)
		}
		if(!ok) {
			yield(Tokens.INVALID_INDENT, "")
		}

		yield(Tokens.INDENT, white)
		return true
	}

	private fun lexLineBody() {
		if(reader.startsWith("@")) {
			return lexMathRule()
		}

		var nextFirst = true
		var isArgLine = false
		while(true) {
			val isFirst = nextFirst
			nextFirst = false

			if(reader.startsWithLfe()) return

			if(reader.startsWith("=")) {
				yield(Tokens.EQUALS, reader.readExpect("="))
				readWhitespace()

				if(isArgLine) {
					lexAttributeValue()
					return
				}
				break
			}

			if(isFirst) {
				if(reader.startsWith("*")) {
					yield(Tokens.MOD_DOC, reader.readExpect("*"))
					readWhitespace()
					break
				}

				if(reader.startsWith("~")) {
					yield(Tokens.MOD_VERSION, reader.readExpect("~"))
					readWhitespace()
					continue
				}

				if(reader.startsWith("$")) {
					yield(Tokens.MOD_ARG, reader.readExpect("$"))
					readWhitespace()
					isArgLine = true
					continue
				}
			}

			readIdentifier(needWhite = false)
			readWhitespace()
		}

		lexLiteral(false)
	}

	private fun lexLiteral(closeable: Boolean) {
		while(true) {
			val literal = reader.readAny(CHARSET_LITERAL_STOP, true)
			if(literal.isNotEmpty()) yield(Tokens.LITERAL, literal)

			if(reader.eof()) return
			if(reader.startsWithLf()) {
				val lf = reader.readExpect(if(reader.startsWith("\n")) "\n" else "\r\n")
				val cont = reader.peek(1)[0]
				if(cont == '!' || cont == '|' || cont == '\\') {
					yield(when(cont) {
						'!' -> Tokens.CONT_NEWLINE
						'|' -> Tokens.CONT_SPACE
						'\\' -> Tokens.CONT_CONCAT
						else -> NEVER
					}, lf + reader.readExpect(cont.toString()))
				} else {
					reader.restore(lf)
				}
				return
			}
			if(reader.startsWith("\\")) {
				yield(Tokens.LITERAL_ESCAPE, reader.read(2))
				continue
			}
			if(reader.startsWith("}")) {
				if(!closeable) yield(Tokens.INVALID_CLOSE_BRACE, reader.readExpect("}"))
				return
			}

			if(reader.peek(2)[1] != '{') {
				yield(Tokens.LITERAL, reader.read(1))
				continue
			}

			if(reader.startsWith("#{")) {
				yield(Tokens.MESSAGE_REF_START, reader.readExpect("#{"))
				lexRef()
				continue
			}

			if(reader.startsWith("\${")) {
				yield(Tokens.ARG_REF_START, reader.readExpect("\${"))
				lexRef()
				continue
			}

			assert(reader.startsWith("%{"))
			lexSpan()
		}
	}

	private fun lexRef() {
		readWhitespace(charset = CHARSET_WS_NL)
		if(reader.startsWith("$")) {
			yield(Tokens.MOD_ARG, reader.readExpect("$"))
			readWhitespace(charset = CHARSET_WS_NL)
		}
		readIdentifier(needWhite = false)

		lexAttributeList()

		yield(Tokens.CLOSE_BRACE, reader.readExpect("}"))
	}

	private fun lexAttributeList() {
		while(!reader.startsWith("}")) {
			readWhitespace(charset = CHARSET_WS_NL + charArrayOf(','), must = true)
			if(reader.startsWith("}")) break

			val isMathAttribute = reader.startsWith("@")
			if(isMathAttribute) yield(Tokens.MATH_AT, reader.readExpect("@"))
			readIdentifier(must = !isMathAttribute, needWhite = false)
			readWhitespace(CHARSET_WS_NL)
			if(reader.startsWith("=")) {
				yield(Tokens.EQUALS, reader.readExpect("="))
				readWhitespace(CHARSET_WS_NL)
			}
			lexAttributeValue()
		}
	}

	private fun lexAttributeValue() {
		val number = readNumber()
		if(number != null) {
			yield(Tokens.NUMBER, number)
			return
		}
		if(reader.startsWith("{")) {
			yield(Tokens.OPEN_BRACE, reader.read(1))
			lexLiteral(true)
			yield(Tokens.CLOSE_BRACE, reader.readExpect("}"))
			return
		}
		if(reader.startsWith("#")) {
			yield(Tokens.ATTRIBUTE_SIMPLE_MESSAGE, reader.readExpect("#"))
			readWhitespace()
		}
		readIdentifier(needWhite = false)
	}

	private fun lexSpan() {
		yield(Tokens.SPAN_START, reader.readExpect("%{"))
		readWhitespace()
		readIdentifier(type = Tokens.SPAN_NAME)
		readWhitespace()
		lexLiteral(true)
		yield(Tokens.CLOSE_BRACE, reader.readExpect("}"))
	}

	private fun lexMathRule() {
		while(!reader.startsWithLf()) {
			readWhitespace(tokenType = Tokens.MATH_SEPARATOR)
			for((code, token) in MATH_TOKENS) {
				if(reader.startsWith(code)) {
					yield(token, reader.readExpect(code))
					continue
				}
			}

			val number = readNumber()
			if(number != null) {
				yield(Tokens.NUMBER, number)
				continue
			}
			readIdentifier(false)
		}
	}

	private fun yield(type: IElementType, code: CharSequence) = reader.yield(type, code)

	private fun readWhitespace(charset: CharArray = CHARSET_WHITESPACE, must: Boolean = false, tokenType: IElementType = Tokens.WHITESPACE): Boolean {
		val white = reader.readAny(charset)
		if(white.isNotEmpty()) {
			yield(tokenType, white)
			return true
		}
		if(must) yield(Tokens.MISSING_WHITESPACE, "")
		return false
	}

	private fun readNumber(): String? {
		val ret = StringBuilder()
		if(reader.startsWith("-")) ret.append(reader.readExpect("-"))

		val digits = reader.readAny(CHARSET_DIGITS)
		if(digits.isEmpty()) {
			reader.restore(ret.toString())
			return null
		}
		ret.append(digits)

		if(reader.startsWith(".")) {
			reader.readExpect(".")
			val decimal = reader.readAny(CHARSET_DIGITS)
			if(decimal.isEmpty()) {
				reader.restore(".")
			} else {
				ret.append('.').append(decimal)
			}
		}
		return ret.toString()
	}

	private fun readIdentifier(must: Boolean = true, needWhite: Boolean = true, type: IElementType = Tokens.IDENTIFIER, acceptFlags: Boolean = true): Boolean {
		while(true) {
			val identifier = reader.readAny(CHARSET_IDENTIFIER)
			if(identifier.isEmpty()) {
				if(must) {
					yield(BAD_CHARACTER, reader.read(1))
					continue
				}
				return false
			}
			if(acceptFlags && reader.startsWith(":")) {
				yield(Tokens.FLAG, identifier)
				yield(Tokens.WHITESPACE, reader.readExpect(":"))
				continue
			}

			yield(type, identifier)
			if(needWhite && !reader.eof() && !reader.startsWithAny(CHARSET_WS_NL)) {
				yield(Tokens.MISSING_WHITESPACE, "")
			}
			return true
		}
	}
}
