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

namespace SOFe\Libglocal\Parser;

use JsonSerializable;
use ReflectionClass;
use RuntimeException;
use function array_search;
use function strpos;

final class Token implements JsonSerializable{
	protected const BITMASK_TOKEN_CATEGORY = 0xFF00;

	public const CATEGORY_GAPS = 0x0000;
	public const WHITESPACE = 0x0000;
	public const COMMENT = 0x0001;
	public const INDENT = 0x0002;
	public const CHECKSUM = 0x0003;

	public const CATEGORY_STRUCTURAL = 0x0100;
	public const INDENT_INCREASE = 0x0101;
	public const INDENT_DECREASE = 0x0102;
	public const CONT_NEWLINE = 0x0103;
	public const CONT_SPACE = 0x0104;
	public const CONT_CONCAT = 0x0105;

	public const CATEGORY_MODIFIERS = 0x0300;
	public const MOD_ARG = 0x0301;
	public const MOD_DOC = 0x0302;
	public const MOD_VERSION = 0x0303;

	public const CATEGORY_FLAGS = 0x0400;
	public const FLAG_UNKNOWN = 0x0400;
	public const FLAG_PUBLIC = 0x0401;
	public const FLAG_LIB = 0x0402;
	public const FLAG_LOCAL = 0x0403;
	public const FLAG_LIST = 0x0404;

	public const CATEGORY_IDENTIFIERS = 0x0500;
	public const IDENTIFIER = 0x0500;
	public const EQUALS = 0x0501;

	public const CATEGORY_LITERAL = 0x0600;
	public const LITERAL = 0x0600;
	public const ESCAPE = 0x0601;
	public const CLOSE_BRACE = 0x0602;

	public const CATEGORY_ARG_REF = 0x0700;
	public const ARG_REF_START = 0x0700;

	public const CATEGORY_MESSAGE_REF = 0x0800;
	public const MESSAGE_REF_START = 0x0800;
	public const NUMBER = 0x0801;
	public const OPEN_BRACE = 0x0802;
	public const MESSAGE_REF_SIMPLE = 0x0803;

	public const CATEGORY_SPAN = 0x0000;
	public const SPAN_START = 0x0900;
	public const SPAN_NAME = 0x0901;

	public const CATEGORY_MATH = 0x0a00;
	public const MATH_AT = 0x0a00;
	public const MATH_MOD = 0x0a01; // %
	public const MATH_EQ = 0x0a02; // =
	public const MATH_NE = 0x0a03; // <>
	public const MATH_LE = 0x0a04; // <=
	public const MATH_LT = 0x0a05; // <
	public const MATH_GE = 0x0a06; // >=
	public const MATH_GT = 0x0a07; // >
	public const MATH_SEPARATOR = 0x0a08;


	/** @var int */
	protected $type;
	/** @var string */
	protected $code;
	/** @var int */
	protected $line;

	public function __construct(int $type, string $code){
		$this->type = $type;
		$this->code = $code;
	}

	public function getType() : int{
		return $this->type;
	}

	public function getTypeCategory() : int{
		return $this->type & self::BITMASK_TOKEN_CATEGORY;
	}

	public function getTypeName() : string{
		return self::idToName($this->type);
	}

	public static function idToName(int $id) : string{
		static $constantsCache = null;
		if($constantsCache === null){
			$constantsCache = [];
			$class = new ReflectionClass(Token::class);
			foreach($class->getConstants() as $name => $value){
				if(strpos($name, "CATEGORY_") !== 0){
					$constantsCache[$name] = $value;
				}
			}
		}
		return array_search($id, $constantsCache, true) ?: "<unknown>";
	}

	public function getCode() : string{
		return $this->code;
	}

	public function getCodeAsIntFloat() : float{
		if($this->type !== Token::NUMBER){
			throw new RuntimeException("Call is only permitted for NUMBER tokens");
		}

		return strpos($this->code, ".") !== false ? (float) $this->code : (int) $this->code;
	}

	public function getLine() : int{
		return $this->line;
	}

	public function setLine(int $line) : void{
		$this->line = $line;
	}

	public function throwExpect(string $expect, string $fileName) : ParseException{
		throw new ParseException("Expecting $expect, got {$this->getTypeName()} on line {$this->line}", $fileName);
	}

	public function jsonSerialize(){
		return [
			"type" => $this->getTypeName(),
			"code" => $this->code,
		];
	}
}
