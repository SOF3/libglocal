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

use function gettype;
use InvalidArgumentException;
use SOFe\Libglocal\MessageParameter;

class EnumConversionParameter implements MessageParameter{
	protected $conversionMap;

	public function __construct(array $conversionMap){
		$this->conversionMap = $conversionMap;
	}
	
	public function acceptValue($value) : string{
		foreach($this->conversionMap as [$match, $result]){
			if($value === $match){
				return $result;
			}
		}
		throw new InvalidArgumentException("\"$value\" (" . gettype($value) . ") is not an acceptable value");
	}
}
