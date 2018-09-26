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

namespace SOFe\Libglocal\Math;

use AssertionError;
use SOFe\Libglocal\Parser\Ast\Math\MathPredicateElement;
use SOFe\Libglocal\Parser\Token;

class MathPredicate{
	/** @var int|float|null */
	protected $mod = null;
	/** @var int */
	protected $operator;
	/** @var int|float */
	protected $operand;

	public function __construct(MathPredicateElement $element){
		$this->mod = $element->getMod();
		$this->operator = $element->getComparator();
		$this->operand = $element->getOperand();
	}

	public function test($number) : bool{
		if($this->mod !== null){
			$number %= $this->mod;
			if($number < 0){
				$number += $this->mod;
			}
		}

		switch($this->operator){
			case Token::MATH_EQ:
				/** @noinspection TypeUnsafeComparisonInspection */
				return $number == $this->operand;
			case Token::MATH_NE:
				/** @noinspection TypeUnsafeComparisonInspection */
				return $number != $this->operand;
			case Token::MATH_LE:
				return $number <= $this->operand;
			case Token::MATH_LT:
				return $number < $this->operand;
			case Token::MATH_GE:
				return $number >= $this->operand;
			case Token::MATH_GT:
				return $number > $this->operand;
		}

		throw new AssertionError("Unexpected operator");
	}
}
