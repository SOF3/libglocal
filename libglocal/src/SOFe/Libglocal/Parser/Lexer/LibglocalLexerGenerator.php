<?php

/*
 * libglocal
 *
 * Copyright (C) 2018 SOFe
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

declare(strict_types=1);

namespace SOFe\Libglocal\Parser\Lexer;

use Generator;
use SOFe\Libglocal\Parser\Token;
use function array_pop;
use function assert;
use function count;
use function json_encode;
use function mb_strtolower;
use function str_replace;
use function strpos;

class LibglocalLexerGenerator{
	protected const IDENTIFIER_REGEX = '[A-Za-z0-9_\\.\\-]+';

	protected $afterMessages = false;
	protected $indentStack = [];

	public function lex(StringReader $reader) : Generator{
		while(!$reader->eof()){
			yield from $this->lexLine($reader);
			yield new Token(Token::CHECKSUM, "");
		}
	}

	protected function lexLine(StringReader $reader) : Generator{
		$ret = yield from $this->lineStart($reader);
		if(!$ret){
			return;
		}

		$identifiers = yield from $this->command($reader);

		while(true){
			yield from $this->readWhitespace($reader);
			$lf = $reader->readAny("\r\n", false);
			if(!empty($lf)){
				yield new Token(Token::WHITESPACE, $lf);
				return;
			}
			if($reader->eof()){
				return;
			}

			if($identifiers === 0){
				yield from $this->literal($reader, false);
				if($reader->startsWith("\r\n")){
					yield new Token(Token::WHITESPACE, $reader->readExpected("\r\n"));
					return;
				}
				if($reader->startsWith("\n")){
					yield new Token(Token::WHITESPACE, $reader->readExpected("\n"));
					return;
				}
			}

			if($identifiers === -1 && $reader->startsWith("=")){
				yield new Token(Token::EQUALS, $reader->readExpected("="));
				continue;
			}

			yield from $this->readIdentifier($reader, true);
			if($identifiers > 0){
				$identifiers--;
			}
		}
	}

	protected function lineStart(StringReader $reader) : Generator{
		$lf = $reader->readAny("\r\n", false);
		if(!empty($lf)){
			yield new Token(Token::WHITESPACE, $lf);
		}

		$white = $reader->readAny(" \t", false);

		if($reader->startsWith("//")){
			if(!empty($white)){
				yield new Token(Token::WHITESPACE, $white);
			}
			$comment = $reader->readAny("\r\n", true) . $reader->readAny("\r\n", false);
			yield new Token(Token::COMMENT, $comment);
			return false;
		}

		$lf = $reader->readAny("\r\n", false);
		if(!empty($lf)){
			yield new Token(Token::WHITESPACE, $white . $lf);
			return false;
		}

		if(empty($white)){
			/** @noinspection PhpUnusedLocalVariableInspection */
			foreach($this->indentStack as $_){
				yield new Token(Token::INDENT_DECREASE, "");
			}
			$this->indentStack = [];

			return true;
		}

		if(empty($this->indentStack)){
			yield new Token(Token::INDENT_INCREASE, $white);
			return true;
		}

		$last = $this->indentStack[count($this->indentStack) - 1];
		if(strpos($white, $last) === 0){
			if($white !== $last){ // $white starts with $last
				$this->indentStack[] = $white;
				yield new Token(Token::INDENT_INCREASE, "");
			}
			yield new Token(Token::INDENT, $white);
			return true;
		}

		$ok = false;
		while(!empty($this->indentStack)){
			$last = $this->indentStack[count($this->indentStack) - 1];
			if($last === $white){
				$ok = true;
				break;
			}
			yield new Token(Token::INDENT_DECREASE, "");
			array_pop($this->indentStack);
		}
		if(!$ok){
			throw $reader->throw("Invalid indent \"" . str_replace("\t", "\\t", $white) . "\"");
		}
		yield new Token(Token::INDENT, $white);

		return true;
	}

	protected function command(StringReader $reader) : Generator{
		if(!$this->afterMessages){
			if(($match = $reader->matchRead('base[ \\t]+lang\\b')) !== null){
				yield new Token(Token::BASE_LANG, $match);
				return 1;
			}
			if(($match = $reader->matchRead('lang\\b')) !== null){
				yield new Token(Token::LANG, $match);
				return 1;
			}
			if(($match = $reader->matchRead('author\\b')) !== null){
				yield new Token(Token::AUTHOR, $match);
				return 0;
			}
			if(($match = $reader->matchRead('version\\b')) !== null){
				yield new Token(Token::AUTHOR, $match);
				return 1;
			}
			if(($match = $reader->matchRead('require\\b')) !== null){
				yield new Token(Token::REQUIRE, $match);
				return 1;
			}
			if(($match = $reader->matchRead('messages\\b')) !== null){
				$this->afterMessages = true;
				yield new Token(Token::MESSAGES, $match);
				return 1;
			}

			throw $reader->throw("Unknown command");
		}

		if($reader->startsWith('#')){
			$reader->advance(1);
			yield new Token(Token::INSTRUCTION, '#');
			return -1;
		}

		if($reader->startsWith('$')){
			$reader->advance(1);
			yield new Token(Token::MOD_ARG, '$');
			return 2;
		}

		if($reader->startsWith('*')){
			yield new Token(Token::MOD_DOC, '*');
			$reader->advance(1);
			return 0;
		}

		if($reader->startsWith('~')){
			yield new Token(Token::MOD_VERSION, '~');
			$reader->advance(1);
			return 1;
		}

		yield from $this->readIdentifier($reader, true);
		return 0;
	}

	protected function literal(StringReader $reader, bool $closeable) : Generator{
		while(true){
			$literal = $reader->readAny("\r\n\\#\$%}", true);
			if(!empty($literal)){
				yield new Token(Token::LITERAL, $literal);
			}

			if($reader->eof()){
				return;
			}
			if($reader->startsWith("\r\n") || $reader->startsWith("\n")){
				if($reader->matchRead('[ \\t\\r\\n]+([!|\\\\])', $match) !== null){
					switch($match[1]){
						case '!':
							yield new Token(Token::CONT_NEWLINE, $match[0]);
							break;
						case '|':
							yield new Token(Token::CONT_SPACE, $match[0]);
							break;
						case '\\':
							yield new Token(Token::CONT_CONCAT, $match[0]);
							break;
						default:
							assert(false, "Unexpected match {$match[1]}");
					}
					continue;
				}
				return;
			}
			if($reader->startsWith("\\")){
				yield new Token(Token::ESCAPE, $reader->read(2));
				continue;
			}
			if($reader->startsWith("}")){
				if(!$closeable){
					throw $reader->throw("Unexpected }, must be escaped as \\}");
				}
				return;
			}

			if($reader->getRemainingString(){1} !== "{"){
				yield new Token(Token::LITERAL, $reader->read(1));
				continue;
			}

			if($reader->startsWith('#{')){
				yield from $this->messageRef($reader);
				continue;
			}

			if($reader->startsWith('${')){
				yield from $this->argRef($reader);

				continue;
			}

			assert($reader->startsWith('%{'));
			yield from $this->span($reader);
		}
	}

	protected function messageRef(StringReader $reader) : Generator{
		yield new Token(Token::MESSAGE_REF_START, $reader->readExpected('#{'));
		yield from $this->readWhitespace($reader, " \t\r\n");
		if($reader->startsWith('$')){
			yield new Token(Token::MOD_ARG, $reader->readExpected('$'));
			yield from $this->readWhitespace($reader, " \t\r\n");
		}
		yield from $this->readIdentifier($reader, true, false);

		while(!$reader->startsWith("}")){
			yield from $this->readWhitespace($reader, " \t,\r\n", true);
			if($reader->startsWith("}")){
				break;
			}
			yield from $this->readIdentifier($reader, true, false);
			yield from $this->readWhitespace($reader, " \t\r\n");
			yield new Token(Token::EQUALS, $reader->readExpected("="));
			yield from $this->readWhitespace($reader, " \t\r\n");
			yield from $this->readMessageArgValue($reader);
		}

		yield new Token(Token::WHITESPACE, $reader->readExpected("}"));
		yield new Token(Token::CHECKSUM, "");
	}

	protected function readMessageArgValue(StringReader $reader) : Generator{
		if(($number = $reader->matchRead('-?[0-9]+(\\.[0-9]+)?')) !== null){
			yield new Token(Token::NUMBER, $number);
			return;
		}
		if($reader->startsWith("{")){
			yield new Token(Token::OPEN_BRACE, $reader->read(1));
			yield from $this->literal($reader, true);
			yield new Token(Token::CLOSE_BRACE, $reader->readExpected("}"));
			return;
		}
		$hasIdentifier = yield from $this->readIdentifier($reader, false, false);
		if(!$hasIdentifier){
			throw $reader->throw("Expected identifier, number or {literal}");
		}
	}

	protected function argRef(StringReader $reader) : Generator{
		yield new Token(Token::ARG_REF_START, $reader->read(2));
		yield from $this->readWhitespace($reader);
		yield from $this->readIdentifier($reader, true, false);
		yield from $this->readWhitespace($reader);
		yield new Token(Token::CLOSE_BRACE, $reader->readExpected("}"));
		yield new Token(Token::CHECKSUM, "");
	}

	protected function span(StringReader $reader) : Generator{
		yield new Token(Token::SPAN_START, $reader->read(2));
		yield from $this->readWhitespace($reader);
		yield from $this->readIdentifier($reader, true, true, Token::SPAN_NAME);
		yield from $this->readWhitespace($reader);
		yield from $this->literal($reader, true);
		yield new Token(Token::CLOSE_BRACE, $reader->readExpected("}"));
		yield new Token(Token::CHECKSUM, "");
	}

	protected function readWhitespace(StringReader $reader, string $charset = " \t", bool $must = false) : Generator{
		$white = $reader->readAny($charset, false);
		if(!empty($white)){
			yield new Token(Token::WHITESPACE, $white);
			return true;
		}

		if($must){
			throw $reader->throw("Expected any of " . json_encode($charset));
		}
		return false;
	}

	protected function readIdentifier(StringReader $reader, bool $must, bool $needWhite = true, int $identifierType = Token::IDENTIFIER) : Generator{
		while(true){
			$identifier = $reader->matchRead(self::IDENTIFIER_REGEX);
			if($identifier === null){
				if($must){
					throw $reader->throw("Expected identifier");
				}
				return false;
			}

			if($reader->startsWith(':')){
				$reader->advance(1);
				switch(mb_strtolower($identifier)){
					case "public":
						yield new Token(Token::FLAG_PUBLIC, $identifier . ':');
						continue 2;
					case "lib":
						yield new Token(Token::FLAG_LIB, $identifier . ':');
						continue 2;
					case "local":
						yield new Token(Token::FLAG_LOCAL, $identifier . ':');
						continue 2;
					case "list":
						yield new Token(Token::FLAG_LIST, $identifier . ':');
						continue 2;
				}
				yield new Token(Token::FLAG_UNKNOWN, $identifier . ':');
				continue;
			}

			yield new Token($identifierType, $identifier);
			if($needWhite && !$reader->eof() && $reader->matches('[ \\t\\r\\n]+') === null){
				throw $reader->throw("Expected whitespace behind identifier");
			}
			return true;
		}
	}
}
