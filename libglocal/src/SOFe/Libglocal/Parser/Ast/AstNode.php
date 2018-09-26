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

namespace SOFe\Libglocal\Parser\Ast;

use JsonSerializable;
use SOFe\Libglocal\InitException;
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;
use SOFe\Libglocal\Parser\ParseException;
use SOFe\Libglocal\Parser\Token;
use function array_map;
use function count;
use function implode;
use function json_encode;

abstract class AstNode implements JsonSerializable, IAstNode{
	/** @var LibglocalLexer */
	protected $lexer;
	/** @var AstRoot */
	protected $root;
	/** @var null|AstNode */
	protected $parent;
	/** @var int */
	protected $line;

	protected function __construct(LibglocalLexer $lexer, AstRoot $root, ?AstNode $parent, int $line){
		$this->lexer = $lexer;
		$this->root = $root;
		$this->parent = $parent;
		$this->line = $line;
	}

	protected function accept() : bool{
		return true;
	}

	abstract protected function complete() : void;

	final protected function expectAnyChildren(string ...$classes) : AstNode{
		return $this->readAnyChildren($classes, true);
	}

	final protected function acceptAnyChildren(string ...$classes) : ?AstNode{
		return $this->readAnyChildren($classes, false);
	}

	private function readAnyChildren(array $classes, bool $throw) : ?AstNode{
		foreach($classes as $class){
			/** @noinspection DisconnectedForeachInstructionInspection */
			if(count($classes) > 1){
				$this->lexer->createStack();
			}
			/** @var AstNode $node */
			$node = new $class($this->lexer, $this->root, $this, $this->lexer->getLine());
			$accepted = $node->accept();

			if(count($classes) > 1){
				if($accepted){
					$this->lexer->acceptStack();
				}else{
					$this->lexer->rejectStack();
				}
			}

			if($accepted){
				$node->complete();
				return $node;
			}
		}

		if($throw){
			$names = implode(", ", array_map(function(string $class){
				/** @var AstNode $class */
				return $class::getNodeName();
			}, $classes));
			throw $this->lexer->throwExpect($names);
		}
		return null;
	}

	final protected function expectToken(int $type) : Token{
		return $this->readToken($type, true);
	}

	final protected function acceptToken(int $type) : ?Token{
		return $this->readToken($type, false);
	}

	private function readToken(int $type, bool $throw) : ?Token{
		$token = $this->lexer->next();
		if($token !== null && $token->getType() === $type){
			return $token;
		}
		if($throw){
			throw $this->lexer->throwExpect(Token::idToName($type), $token);
		}
		if($token !== null){
			$this->lexer->rewind($token);
		}
		return null;
	}

	final protected function expectTokenText(int $type, string $text) : Token{
		return $this->readTokenText($type, $text, true);
	}

	final protected function acceptTokenText(int $type, string $text) : ?Token{
		return $this->readTokenText($type, $text, false);
	}

	private function readTokenText(int $type, string $text, bool $throw) : ?Token{
		$token = $this->lexer->next();
		if($token !== null && $token->getType() === $type && $token->getCode() === $text){
			return $token;
		}
		if($throw){
			throw $this->lexer->throwExpect(json_encode($text), $token);
		}
		if($token !== null){
			$this->lexer->rewind($token);
		}
		return null;
	}

	final protected function acceptTokenCategory(int $category) : ?Token{
		$token = $this->lexer->next();
		if($token !== null && $token->getTypeCategory() === $category){
			return $token;
		}
		if($token !== null){
			$this->lexer->rewind($token);
		}
		return null;
	}

	public function getFileName() : string{
		return $this->lexer->getFileName();
	}

	public function getParent() : ?AstNode{
		return $this->parent;
	}

	public function getLine() : int{
		return $this->line;
	}

	public function getRoot() : AstRoot{
		return $this->root;
	}

	public function throwParse(string $message) : InitException{
		throw new ParseException($message . " on line " . $this->getLine(), $this->getFileName());
	}

	public function throwInit(string $message) : InitException{
		throw new InitException($message . " on line " . $this->getLine(), $this->getFileName());
	}

	abstract protected static function getNodeName() : string;

	public function __toString() : string{
		return static::getNodeName();
	}

	public function jsonSerialize(){
		return ["nodeName" => static::getNodeName()] + $this->toJsonArray();
	}

	abstract protected function toJsonArray() : array;
}
