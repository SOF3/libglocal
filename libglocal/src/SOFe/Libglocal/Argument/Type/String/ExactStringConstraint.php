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

use function mb_strtolower;

class ExactStringConstraint implements StringConstraint{
	/** @var bool */
	protected $noCase;
	/** @var string */
	protected $expected;

	public function __construct(string $expected, bool $noCase){
		$this->noCase = $noCase;
		$this->expected = $noCase ? mb_strtolower($expected) : $expected;
	}

	public function test(string $string) : bool{
		return ($this->noCase ? mb_strtolower($string) : $string) === $this->expected;
	}
}
