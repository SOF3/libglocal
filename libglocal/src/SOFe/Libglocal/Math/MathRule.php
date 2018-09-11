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

namespace SOFe\Libglocal\Math;

class MathRule{
	/** @var null|string */
	private $result;
	/** @var MathPredicate[] */
	protected $predicates;

	public function __construct(?string $result, array $predicates){
		$this->result = $result;
		$this->predicates = $predicates;
	}

	public function test($number) : bool{
		foreach($this->predicates as $predicate){
			if(!$predicate->test($number)){
				return false;
			}
		}
		return true;
	}

	public function getResult() : ?string{
		return $this->result;
	}
}
