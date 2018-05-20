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

use InvalidArgumentException;
use SOFe\Libglocal\MultibyteLineReader;
use function mb_split;
use function sprintf;

class QuantityArgType extends NumericArgType{
	/** @var QuantityCase[] */
	protected $cases = [];
	/** @var string|null */
	protected $default = null;

	public function parseConstraint(MultibyteLineReader $reader) : bool{
		if(parent::parseConstraint($reader)){
			return true;
		}

		if($reader->consumeRegex('/^when[ \t]+/iu') !== null){
			$constraintString = $reader->consumeUntilExact(",", "Number constraint expected, unexpected end of line");
			$constraints = [];
			foreach(mb_split('\s+', $constraintString) as $constraint){
				$constraints[] = NumberConstraint::parseNumberConstraint(MultibyteLineReader::trim($constraint, true, true));
			}
			$reader->readWhitespace();
			$reader->useEscapedComment();
			$case = new QuantityCase($constraints, $reader->remaining());
			$this->cases[] = $case;
			return true;
		}

		if($reader->consumeRegex('/^default[ \t]+/iu') !== null){
			$reader->useEscapedComment();
			$this->default = $reader->remaining();
			return true;
		}

		return false;
	}

	public function toString($value) : string{
		parent::toString($value); // throw exceptions

		foreach($this->cases as $case){
			if(($string = $case->resolve($value)) !== null){
				return $string;
			}
		}

		if($this->default === null){
			throw new InvalidArgumentException("$value is out of range");
		}

		return sprintf($this->default, $value);
	}
}
