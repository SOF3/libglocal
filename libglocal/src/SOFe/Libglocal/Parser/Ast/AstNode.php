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
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;
use SOFe\Libglocal\Parser\Token;
use function array_map;
use function count;
use function implode;

abstract class AstNode implements JsonSerializable{
	/** @var LibglocalLexer */
	protected $lexer;

	protected function __construct(LibglocalLexer $lexer){
		$this->lexer = $lexer;
	}

	protected function accept() : bool{
		return true;
	}

	protected abstract function complete() : void;

	protected final function expectAnyChildren(string ...$classes) : AstNode{
		return $this->readAnyChildren($classes, true);
	}

	protected final function acceptAnyChildren(string ...$classes) : ?AstNode{
		return $this->readAnyChildren($classes, false);
	}

	private function readAnyChildren(array $classes, bool $throw) : ?AstNode{
		foreach($classes as $class){
			if(count($classes) > 1){
				$this->lexer->createStack();
			}
			/** @var AstNode $node */
			$node = new $class($this->lexer);
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

	protected final function expectToken(int $type) : Token{
		return $this->readToken($type, true);
	}

	protected final function acceptToken(int $type) : ?Token{
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

	protected final function acceptTokenCategory(int $category) : ?Token{
		$token = $this->lexer->next();
		if($token !== null && $token->getTypeCategory() === $category){
			return $token;
		}
		if($token !== null){
			$this->lexer->rewind($token);
		}
		return null;
	}

	protected static abstract function getNodeName() : string;

	public function __toString() : string{
		return static::getNodeName();
	}
}
