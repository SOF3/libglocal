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

namespace SOFe\Libglocal\Argument\Type\String;

use function preg_match;

class PatternStringConstraint implements StringConstraint{
	/** @var string */
	protected $pattern;

	public function __construct(string $pattern){
		$this->pattern = $pattern;
	}

	public function test(string $string) : bool{
		return preg_match($this->pattern, $string) === 1;
	}
}
