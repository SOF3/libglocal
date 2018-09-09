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

use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;
use SOFe\Libglocal\Parser\Token;
use function array_map;
use function count;
use function implode;

abstract class AstNode{
	/** @var LibglocalLexer */
	protected $lexer;
	/** @var AstNode[] */
	protected $children = [];

	private function __construct(LibglocalLexer $lexer){
		$this->lexer = $lexer;
	}

	protected function accept() : bool{
		return true;
	}

	protected abstract function complete() : void;

	protected function expectAnyChildren(string ...$classes) : AstNode{
		$node = $this->acceptAnyChildren(...$classes);
		if($node === null){
			$names = implode(", ", array_map(function(string $class){
				/** @var AstNode $class */
				return $class::getName();
			}, $classes));
			throw $this->lexer->throwExpect($names);
		}
		return $node;
	}

	protected function acceptAnyChildren(string ...$classes) : ?AstNode{
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
				$this->children[] = $node;
				return $node;
			}
		}

		return null;
	}

	protected function expectToken(int $type) : Token{
		if(($token = $this->acceptToken($type)) !== null){
			return $token;
		}
		throw $this->lexer->throwExpect(Token::idToName($type));
	}

	protected function acceptToken(int $type) : ?Token{
		$token = $this->lexer->next();
		if($token !== null && $token->getType() === $type){
			return $token;
		}
		$this->lexer->rewind($token);
		return null;
	}

	protected function acceptTokenCategory(int $category) : ?Token{
		$token = $this->lexer->next();
		if($token !== null && $token->getTypeCategory() === $category){
			return $token;
		}
		$this->lexer->rewind($token);
		return null;
	}

	protected static abstract function getName() : string;

	public function __toString() : string{
		return static::getName();
	}
}
