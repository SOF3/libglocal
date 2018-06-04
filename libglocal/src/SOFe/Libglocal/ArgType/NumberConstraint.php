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

namespace SOFe\Libglocal\ArgType;

use AssertionError;
use JsonSerializable;
use SOFe\Libglocal\ParseException;
use function fmod;
use function is_numeric;
use function preg_match;

class NumberConstraint implements JsonSerializable{
	public const OP_EQ = 1; // = == ===
	public const OP_NEQ = 2; // != !== <>
	public const OP_LT = 3; // <
	public const OP_LTE = 4; // <=
	public const OP_GT = 5; // >
	public const OP_GTE = 6; // >=

	public const OP_NAMES = [
		self::OP_EQ => "=",
		self::OP_NEQ => "!=",
		self::OP_LT => "<",
		self::OP_LTE => "<=",
		self::OP_GT => ">",
		self::OP_GTE => ">=",
	];

	/** @var int */
	protected $modulusSize = 0;
	/** @var int */
	protected $operator;
	/** @var float */
	protected $operand;

	public static function parseNumberConstraint(string $string) : NumberConstraint{
		$constraint = new NumberConstraint();

		if(!preg_match('/^(?:%([0-9.]+))([<>=!]+)([0-9.]+)$/', $string, $match)){
			throw new ParseException("$string is not a valid number constraint");
		}

		if(isset($match[1]) && $match[1] !== ""){
			if(!is_numeric($match[1])){
				throw new ParseException("Malformed number {$match[1]}");
			}
			$constraint->modulusSize = (float) $match[1];
		}

		switch($match[2]){
			case "=":
			case "==":
			case "===":
				$constraint->operator = self::OP_EQ;
				break;
			case "!=":
			case "!==":
			case "<>":
				$constraint->operator = self::OP_NEQ;
				break;
			case "<":
				$constraint->operator = self::OP_LT;
				break;
			case "<=":
				$constraint->operator = self::OP_LTE;
				break;
			case ">":
				$constraint->operator = self::OP_GT;
				break;
			case ">=":
				$constraint->operator = self::OP_GTE;
				break;
			default:
				throw new ParseException("{$match[2]} is an unknown operator");
		}

		if(!is_numeric($match[3])){
			throw new ParseException("Malformed number {$match[3]}");
		}
		$constraint->operand = (float) $match[3];

		return $constraint;
	}

	public function matches(float $number) : bool{
		if($this->modulusSize !== 0){
			$number = fmod($number, $this->modulusSize);
		}
		switch($this->operator){
			case self::OP_EQ:
				/** @noinspection TypeUnsafeComparisonInspection */
				return $number == $this->operand;
			case self::OP_NEQ:
				/** @noinspection TypeUnsafeComparisonInspection */
				return $number != $this->operand;
			case self::OP_LT:
				return $number < $this->operand;
			case self::OP_LTE:
				return $number <= $this->operand;
			case self::OP_GT:
				return $number > $this->operand;
			case self::OP_GTE:
				return $number >= $this->operand;
		}
		throw new AssertionError("Unexpected operator");
	}

	public function toString() : string{
		return ($this->modulusSize !== 0 ? "%{$this->modulusSize}" : "") . self::OP_NAMES[$this->operator] . $this->operand;
	}

	public function jsonSerialize() : array{
		return [
			"modulus" => $this->modulusSize !== 0 ? $this->modulusSize : null,
			"operator" => self::OP_NAMES[$this->operator],
			"operand" => $this->operand,
		];
	}
}
