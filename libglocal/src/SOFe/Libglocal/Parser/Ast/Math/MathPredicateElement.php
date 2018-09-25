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

namespace SOFe\Libglocal\Parser\Ast\Math;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Token;

class MathPredicateElement extends AstNode{
	/** @var int|float|null */
	protected $mod = null;
	/** @var Token */
	protected $comparator;
	/** @var int|float */
	protected $operand;

	protected function accept() : bool{
		return $this->acceptToken(Token::MATH_SEPARATOR) !== null;
	}

	protected function complete() : void{
		if($this->acceptToken(Token::MATH_MOD) !== null){
			$this->mod = $this->expectToken(Token::NUMBER)->getCodeAsIntFloat();
		}

		$this->comparator = $this->acceptToken(Token::MATH_EQ) ??
			$this->acceptToken(Token::MATH_NE) ??
			$this->acceptToken(Token::MATH_LE) ??
			$this->acceptToken(Token::MATH_LT) ??
			$this->acceptToken(Token::MATH_GE) ??
			$this->acceptToken(Token::MATH_GT);
		if($this->comparator === null){
			$this->lexer->throwExpect("MATH_EQ, MATH_NE, MATH_LE, MATH_LT, MATH_GE, MATH_GT");
		}

		$this->operand = $this->expectToken(Token::NUMBER)->getCodeAsIntFloat();
	}

	protected static function getNodeName() : string{
		return "arithmetic predicate";
	}

	public function toJsonArray() : array{
		return [
			"mod" => $this->mod,
			"comparator" => $this->comparator,
			"operand" => $this->operand,
		];
	}


	public function getMod(){
		return $this->mod;
	}

	public function getComparator() : int{
		return $this->comparator->getType();
	}

	public function getOperand(){
		return $this->operand;
	}
}
