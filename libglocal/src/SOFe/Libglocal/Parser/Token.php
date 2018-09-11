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

use ReflectionClass;
use function array_search;

final class Token{
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

	public const CATEGORY_KEYWORDS = 0x0200;
	public const BASE_LANG = 0x0200;
	public const LANG = 0x0201;
	public const AUTHOR = 0x0202;
	public const VERSION = 0x0203;
	public const REQUIRE = 0x0204;
	public const MESSAGES = 0x0205;

	public const CATEGORY_MODIFIERS = 0x0300;
	public const INSTRUCTION = 0x0300;
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
	public const SPAN_NAME = 0x0900;


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
		$class = new ReflectionClass(Token::class);
		return array_search($id, $class->getConstants(), true) ?: "<unknown>";
	}

	public function getCode() : string{
		return $this->code;
	}

	public function getLine() : int{
		return $this->line;
	}

	public function setLine(int $line) : void{
		$this->line = $line;
	}

	public function throwExpect(string $expect) : ParseException{
		throw new ParseException("Expecting $expect, got {$this->getTypeName()} on line {$this->line}");
	}

	public function throwUnexpected() : ParseException{
		throw new ParseException("Unexpected {$this->getTypeName()} on line {$this->line}");
	}
}
