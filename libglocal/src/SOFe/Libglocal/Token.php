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

namespace SOFe\Libglocal;

class Token{
	// gaps
	public const WHITESPACE = 0x0000;
	public const COMMENT = 0x0001;
	public const INDENT = 0x0002;

	// structural
	public const TERMINATOR = 0x0100;
	public const INDENT_INCREASE = 0x0101;
	public const INDENT_DECREASE = 0x0102;

	// keywords
	public const BASE_LANG = 0x0200;
	public const LANG = 0x0201;
	public const AUTHOR = 0x0202;
	public const VERSION = 0x0203;
	public const REQUIRE = 0x0204;
	public const MESSAGES = 0x0205;

	// modifiers
	public const INSTRUCTION = 0x0300;
	public const MOD_ARG = 0x0301;
	public const MOD_DOC = 0x0302;
	public const MOD_VERSION = 0x0303;

	// flags
	public const FLAG_UNKNOWN = 0x0400;
	public const FLAG_PUBLIC = 0x0401;
	public const FLAG_LIB = 0x0402;
	public const FLAG_LOCAL = 0x0403;
	public const FLAG_LIST = 0x0404;

	// identifiers
	public const IDENTIFIER = 0x0500;
	public const EQUALS = 0x0501;

	// literal
	public const LITERAL = 0x0600;
	public const ESCAPE = 0x0601;
	public const CLOSE_BRACE = 0x0602;

	// arg ref
	public const ARG_REF_START = 0x0700;

	// message ref
	public const MESSAGE_REF_START = 0x0800;
	public const NUMBER = 0x0801;
	public const OPEN_BRACE = 0x0802;

	// span
	public const SPAN_START = 0x0900;
	public const SPAN_NAME = 0x0900;


	/** @var int */
	protected $type;
	/** @var string */
	protected $code;

	public function __construct(int $type, string $code){
		$this->type = $type;
		$this->code = $code;
	}

	public function getType() : int{
		return $this->type;
	}

	public function getCode() : string{
		return $this->code;
	}
}
