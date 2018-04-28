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

namespace SOFe\Libglocal\Parameter;

use InvalidArgumentException;
use SOFe\Libglocal\MessageParameter;
use function get_class;
use function gettype;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;

class ListParameter implements MessageParameter{
	/** @var string */
	protected $delimiter;

	public function __construct(string $delimiter){
		$this->delimiter = $delimiter;
	}

	public function acceptValue($values) : string{
		if(!is_array($values)){
			throw new InvalidArgumentException("Value passed to list parameter must be a string/number/boolean array");
		}

		foreach($values as $n => &$item){
			if(is_string($item)){
				continue;
			}

			if(is_int($item) || is_float($item)){
				$item = (string) $item;
				continue;
			}

			if(is_bool($item)){
				$item = $item ? "true" : "false";
			}

			throw new InvalidArgumentException("Value passed to list parameter must be a string/number/boolean array, got " . (is_object($item) ? (get_class($item) . " object") : gettype($item)));
		}

		return implode($this->delimiter, $values);
	}
}
