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

namespace SOFe\Libglocal;

use Generator;
use function array_pop;
use function assert;
use function count;
use function mb_strtolower;
use function str_replace;
use function strpos;

class LangLexer{
	protected const IDENTIFIER_REGEX = '[A-Za-z0-9_\\.\\-]+';

	protected $afterMessages = false;
	protected $indentStack = [];

	public function lex(StringReader $reader) : Generator{
		while(!$reader->eof()){
			yield from $this->lexLine($reader);
			yield new Token(Token::TERMINATOR, "");
		}
	}

	protected function lexLine(StringReader $reader) : Generator{
		$ret = yield from $this->lineStart($reader);
		if(!$ret){
			return;
		}

		$identifiers = yield from $this->command($reader);
		while($identifiers !== 0){
			$lf = $reader->readAny("\r\n", false);
			if(!empty($lf)){
				yield new Token(Token::WHITESPACE, $lf);
				return;
			}

			yield from $this->readWhitespace($reader);

			if($identifiers === -1 && $reader->startsWith("=")){
				yield new Token(Token::EQUALS, "=");
				continue;
			}

			yield from $this->readIdentifier($reader, true);

			if($identifiers > 0){
				$identifiers--;
			}else{
				assert($identifiers === -1);
			}
		}

		yield from $this->literal($reader, false);
	}

	protected function lineStart(StringReader $reader) : Generator{
		$lf = $reader->readAny("\r\n", false);
		if(!empty($lf)){
			yield new Token(Token::WHITESPACE, $lf);
		}

		$white = $reader->readAny(" \t", false);

		if($reader->startsWith("//")){
			yield new Token(Token::WHITESPACE, $white);
			$comment = $reader->readAny("\r\n", true);
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
		}else{
			$last = $this->indentStack[count($this->indentStack) - 1];
			if(strpos($white, $last) === 0){
				if($white !== $last){
					$this->indentStack[] = $white;
					yield new Token(Token::INDENT_INCREASE, "");
				}
			}else{
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
			}
			yield new Token(Token::INDENT, $white);
		}

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

			if($reader->eof() || $reader->startsWith("\r\n") || $reader->startsWith("\n")){
				// TODO handle cont
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

			if(!$reader->startsWith('#{') || !$reader->startsWith('${') || !$reader->startsWith('%{')){
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
		yield from $this->readIdentifier($reader, true);
		yield from $this->readWhitespace($reader, " \t,\r\n");

		while(!$reader->startsWith("}")){
			yield from $this->readIdentifier($reader, true);
			yield from $this->readWhitespace($reader, " \t\r\n");
			yield new Token(Token::EQUALS, $reader->readExpected("="));
			yield from $this->readWhitespace($reader, " \t\r\n");
			yield from $this->readMessageArgValue($reader);
			yield from $this->readWhitespace($reader, " \t,\r\n");
		}

		yield new Token(Token::WHITESPACE, "}");
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
		$hasIdentifier = yield from $this->readIdentifier($reader, false);
		if(!$hasIdentifier){
			throw $reader->throw("Expected identifier, number or {literal}");
		}
	}

	protected function argRef(StringReader $reader) : Generator{
		yield new Token(Token::ARG_REF_START, $reader->read(2));
		yield from $this->readWhitespace($reader);
		yield from $this->readIdentifier($reader, true);
		yield from $this->readWhitespace($reader);
		yield new Token(Token::CLOSE_BRACE, $reader->readExpected("}"));
	}

	protected function span(StringReader $reader) : Generator{
		yield new Token(Token::SPAN_START, $reader->read(2));
		yield from $this->readWhitespace($reader);
		yield from $this->literal($reader, true);
		yield new Token(Token::CLOSE_BRACE, $reader->readExpected("}"));
	}

	protected function readWhitespace(StringReader $reader, string $charset = " \t") : Generator{
		$white = $reader->readAny($charset, false);
		if(!empty($white)){
			yield new Token(Token::WHITESPACE, $white);
			return true;
		}
		return false;
	}

	protected function readIdentifier(StringReader $reader, bool $must, int $identifierType = Token::IDENTIFIER) : Generator{
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
			if(!$reader->eof() && $reader->matches('[ \\t\\r\\n]+') === null){
				throw $reader->throw("Expected whitespace behind identifier");
			}
			return true;
		}
	}
}
