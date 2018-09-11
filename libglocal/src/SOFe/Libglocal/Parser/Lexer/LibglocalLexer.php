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

use AssertionError;
use Generator;
use SOFe\Libglocal\Parser\ParseException;
use SOFe\Libglocal\Parser\Token;
use function array_merge;
use function array_pop;
use function array_push;
use function array_shift;
use function array_unshift;
use function assert;
use function count;
use function json_encode;
use function strlen;
use function substr;

class LibglocalLexer{
	/** @var StringReader */
	protected $reader;
	/** @var Generator */
	protected $generator;

	protected $first = true;

	/** @var string reference used to validate that the lexer and the string reader are synchronized correctly */
	protected $peer;
	/** @var int */
	protected $peerPointer = 0;

	/** @var Token[][] */
	protected $bufferStack = [];
	/** @var Token[] */
	protected $future = [];

	public function __construct(string $data){
		$this->reader = new StringReader($data);
		$this->generator = (new LibglocalLexerGenerator)->lex($this->reader);
		$this->peer = $data;
	}

	public function createStack() : void{
		$this->bufferStack[] = [];
	}

	public function rejectStack() : void{
		$buffer = array_pop($this->bufferStack);
		$this->future = array_merge($buffer, $this->future);
	}

	public function acceptStack() : void{
		$buffer = array_pop($this->bufferStack);
		if(!empty($this->bufferStack)){
			array_push($this->bufferStack[count($this->bufferStack) - 1], ...$buffer);
		}
	}

	public function eof() : bool{
		if(!empty($this->future)){
			return false;
		}
		$next = $this->nextTokenSkipping();
		if($next === null){
			return true;
		}
		$this->future[] = $next;
		return false;
	}

	public function next() : ?Token{
		if(empty($this->future)){
			$token = $this->nextTokenSkipping();
		}else{
			$token = array_shift($this->future);
		}

		if(!empty($this->bufferStack)){
			$this->bufferStack[count($this->bufferStack) - 1][] = $token;
		}
		return $token;
	}

	public function rewind(Token $token) : void{
		array_unshift($this->future, $token);
	}

	protected function nextTokenSkipping() : ?Token{
		while(true){
			$token = $this->nextTokenPeer();

			if($token === null || $token->getType() === Token::CHECKSUM){
				assert($this->reader->getRemainingString() === substr($this->peer, $this->peerPointer),
					json_encode($this->reader->getRemainingString()) . "!==" . json_encode(substr($this->peer, $this->peerPointer)));
			}
			if($token !== null && $token->getTypeCategory() === 0){
				continue;
			}

			return $token;
		}

		throw new AssertionError("Unexpected control flow");
	}

	protected function nextTokenPeer() : ?Token{
		if($this->first){
			$this->generator->rewind();
		}else{
			$this->generator->next();
		}
		$this->first = false;

		if(!$this->generator->valid()){
			return null;
		}
		$ret = $this->generator->current();
		assert($ret instanceof Token);

		assert(substr($this->peer, $this->peerPointer, strlen($ret->getCode())) === $ret->getCode(),
			json_encode(substr($this->peer, $this->peerPointer, strlen($ret->getCode()))) . " !== " . json_encode($ret->getCode()));
		$this->peerPointer += strlen($ret->getCode());

		$ret->setLine($this->reader->getLine());

		return $ret;
	}

	public function throwExpect(string $expect) : ParseException{
		if($this->eof()){
			throw new ParseException("Expecting $expect, end of line reached");
		}
		$next = $this->next();
		assert($next !== null);
		throw $next->throwExpect($expect);
	}
}
