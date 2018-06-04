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

use SOFe\Libglocal\LangParser;
use SOFe\Libglocal\MultibyteLineReader;
use function array_map;
use function implode;

class ListArgTypeModifier extends ArgTypeModifier{
	protected $delimiter;

	public function __construct(ArgType $child){
		$this->delegate = $child;
	}

	public function parseConstraint(MultibyteLineReader $reader) : bool{
		if($reader->consumeRegex('/^delimiter[ \t]+/iu') !== null){
			$reader->useEscapedComment();
			$this->delimiter = "";
			while(($before = $reader->consumeUntilExact("\\")) !== null){
				$this->delimiter .= $before;
				$reader->consume(1, "assert false");
				$esc = $reader->consume(1, "Incomplete escape sequence");
				$this->delimiter .= LangParser::resolveEscape($esc, $reader);
			}
			$this->delimiter .= $reader->remaining();
			return true;
		}

		return $this->delegate->parseConstraint($reader);
	}

	public function toString($value) : string{
		return implode($this->getDelimiter(), array_map([$this->delegate, "toString"], $value));
	}

	public function getDelimiter() : string{
		return $this->delimiter;
	}

	public function getModifierName() : string{
		return "list";
	}
}
