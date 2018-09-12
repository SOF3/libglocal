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

namespace SOFe\Libglocal\Parser\Ast\Constraint;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Literal\LiteralElement;
use SOFe\Libglocal\Parser\Token;

class GenericConstraintBlock extends AstNode implements ConstraintBlock{
	/** @var string */
	protected $directive;
	/** @var string[] */
	protected $identifiers = [];
	/** @var LiteralElement|null */
	protected $literal = null;

	protected function accept() : bool{
		return $this->acceptToken(Token::INSTRUCTION) !== null;
	}

	protected function complete() : void{
		$this->directive = $this->expectToken(Token::IDENTIFIER)->getCode();
		while(($token = $this->acceptToken(Token::IDENTIFIER)) !== null){
			$this->identifiers[] = $token->getCode();
		}
		if($this->acceptToken(Token::EQUALS) !== null){
			$this->literal = $this->expectAnyChildren(LiteralElement::class);
		}
	}

	protected static function getNodeName() : string{
		return "<instruction>";
	}

	public function jsonSerialize() : array{
		return [
			"directive" => $this->directive,
			"identifiers" => $this->identifiers,
			"literal" => $this->literal,
		];
	}

	public function inErrorString() : string{
		return "#{$this->directive}";
	}


	public function getDirective() : string{
		return $this->directive;
	}

	/**
	 * @return string[]
	 */
	public function getIdentifiers() : array{
		return $this->identifiers;
	}

	public function getLiteral() : ?LiteralElement{
		return $this->literal;
	}
}
