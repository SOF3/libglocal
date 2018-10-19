package io.github.sof3.libglocal.intellij.parser

import com.intellij.lexer.LexerBase
import com.intellij.psi.TokenType
import com.intellij.psi.tree.IElementType
import java.util.*

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

class Token(
	val start: Int,
	val end: Int,
	val type: IElementType
)

const val LEXER_INITIAL_STATE = 0
const val LEXER_END_STATE = 10

class LexerWrapper(val lexer: (state: Int)->Int) : LexerBase() {
	var myState = 0
	var nextState = 0
	var myTokenType: IElementType? = null
	var requestedStartOffset = 0
	var requestedEndOffset = 0
	var myTokenStart = 0
	var myTokenEnd = 0
	lateinit var myBuffer: CharSequence
	var errored = false

	val nextTokens = LinkedList<Token>()

	override fun start(buffer: CharSequence, startOffset: Int, endOffset: Int, initialState: Int) {
		requestedStartOffset = startOffset
		myTokenEnd = startOffset
		requestedEndOffset = endOffset

		myState = -1
		myBuffer = buffer
		myTokenType = null

		nextState = myState
	}

	override fun advance() {
		locateToken()
		myTokenType = null
	}

	fun locateToken() {
		while(true) {
			locateTokenImpl()
			if(myTokenStart > requestedEndOffset) {
				myTokenType = null
			}
			if(myTokenEnd > requestedEndOffset) {
				myTokenEnd = requestedEndOffset
				// cut the token into two parts, just because the IDE didn't pss everything
			}
			if(myTokenEnd < requestedStartOffset) {
				continue
			}
			if(myTokenStart < requestedStartOffset) {
				myTokenStart = requestedStartOffset
				// cut the token into two parts, just because the IDE asked us to :(
			}
			return
		}
	}

	fun locateTokenImpl() {
		if(myState != 0) return

		myTokenStart = myTokenEnd
		if(errored) return

		myState = nextState

		while(nextTokens.isEmpty() && nextState != LEXER_END_STATE) {
			if(myTokenStart == requestedEndOffset) return
			nextState = lexer(nextState)
		}

		if(nextTokens.isNotEmpty()) {
			val token = nextTokens.removeFirst()!!
			myTokenType = token.type
			assert(myTokenStart == token.start)
			myTokenEnd = token.end
			return
		}
	}

	override fun getState(): Int {
		locateToken()
		return myState
	}

	override fun getTokenStart(): Int {
		locateToken()
		return myTokenStart
	}

	override fun getTokenEnd(): Int {
		locateToken()
		return myTokenEnd
	}

	override fun getBufferEnd(): Int = requestedEndOffset

	override fun getBufferSequence(): CharSequence = myBuffer

	override fun getTokenType(): IElementType? {
		locateToken()
		return myTokenType
	}
}

class LexReader(val wrapper: LexerWrapper) {
	private val buffer get() = wrapper.myBuffer
	private var readPointer = 0
	private var tokenPointer = 0


	fun startsWith(string: String) = compareCharSequence(buffer, readPointer, string)

	fun startsWithAny(charset: CharArray) = charset.contains(buffer[readPointer])

	fun startsWithLf() = startsWith("\n") || startsWith("\r\n")
	fun startsWithLfe() = eof() || startsWithLf()

	fun readAny(charset: CharArray, invert: Boolean = false): String {
		val ret = StringBuilder()
		while(readPointer < buffer.length) {
			if(charset.contains(buffer[readPointer]) == invert) break
			ret.append(buffer[readPointer++])
		}

		return ret.toString()
	}

	fun read(size: Int): String {
		val ret = buffer.substring(readPointer, readPointer + size)
		readPointer += size
		return ret
	}

	fun peek(size: Int) = buffer.substring(readPointer, readPointer + size)

	fun readExpect(string: String): String {
		while(!startsWith(string)) {
			yield(TokenType.BAD_CHARACTER, read(1))
		}
		return read(string.length)
	}

	fun restore(prev: String) {
		assert(compareCharSequence(buffer, readPointer - prev.length, prev))
		readPointer -= prev.length
	}

	fun eof() = readPointer >= buffer.length


	fun yield(type: IElementType, code: CharSequence) {
		assert(tokenPointer + code.length <= readPointer)
		assert(compareCharSequence(buffer, tokenPointer, code))
		wrapper.nextTokens.add(Token(tokenPointer, tokenPointer + code.length, type))
		tokenPointer += code.length
	}
}

fun compareCharSequence(c1: CharSequence, c1Start: Int, c2: CharSequence, c2Start: Int = 0, length: Int = c2.length): Boolean {
	if(c1.length < c1Start + length || c2.length < c2Start + length) {
		return false
	}
	for(i in 0 until length) {
		if(c1[c1Start + i] != c2[c2Start + i]) return false
	}
	return true
}
