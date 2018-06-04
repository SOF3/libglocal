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
use SOFe\Libglocal\Libglocal;
use SOFe\Libglocal\MultibyteLineReader;
use function array_map;
use function implode;
use function is_float;
use function is_int;
use function sprintf;

class NumericArgType extends ArgType{
	/** @var bool */
	protected $int;
	/** @var NumberConstraint[] */
	protected $ranges = [];

	public function __construct(bool $int){
		$this->int = $int;
	}

	public function parseConstraint(MultibyteLineReader $reader) : bool{
		if(parent::parseConstraint($reader)){
			return true;
		}

		if($reader->consumeRegex('/^range[ \t]+/iu') !== null){
			$reader->useEscapedComment();
			$this->ranges[] = NumberConstraint::parseNumberConstraint($reader->remaining());
			return true;
		}

		return false;
	}

	public function toString($value) : string{
		if(($this->int && !is_int($value)) || (!$this->int && !is_float($value) && !is_int($value))){
			throw new InvalidArgumentException(sprintf("%s expected argument %s to be int/float/numeric string, got %s", $this->arg->getMessage()->getId(), $this->arg->getName(), Libglocal::printVar($value)));
		}

		if($this->ranges !== []){
			$match = false;
			foreach($this->ranges as $range){
				if($range->matches($value)){
					$match = true;
					break;
				}
			}
			if(!$match){
				throw new InvalidArgumentException("$value is out of range");
			}
		}

		return (string) $value;
	}

	public function getName() : string{
		$ret = $this->int ? "int" : "float";
		if(!empty($this->ranges)){
			$ret .= " (" . implode("; ", array_map(function(NumberConstraint $constraint) : string{
					return $constraint->toString();
				}, $this->ranges)) . ")";
		}
		return $ret;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "Numeric",
			"isInteger" => $this->int,
			"ranges" => $this->ranges,
		];
	}
}
