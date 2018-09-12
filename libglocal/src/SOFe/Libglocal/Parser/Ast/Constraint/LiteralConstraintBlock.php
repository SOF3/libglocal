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

class LiteralConstraintBlock extends AstNode implements ConstraintBlock{
	/** @var string */
	protected $directive;
	/** @var LiteralElement */
	protected $value;

	protected function accept() : bool{
		if(($token = $this->acceptToken(Token::IDENTIFIER)) === null){
			return false;
		}
		$this->directive = $token;
		return true;
	}

	protected function complete() : void{
		$this->value = $this->expectAnyChildren(LiteralElement::class);
	}

	protected static function getNodeName() : string{
		return "<literal constraint>";
	}

	public function jsonSerialize() : array{
		return [
			"directive" => $this->directive,
			"value" => $this->value,
		];
	}

	public function inErrorString() : string{
		return "\"{$this->directive}\"";
	}


	public function getDirective() : string{
		return $this->directive;
	}

	public function getValue() : LiteralElement{
		return $this->value;
	}
}
