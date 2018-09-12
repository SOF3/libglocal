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

namespace SOFe\Libglocal\Format;

use function array_merge;

class ParentFormattedString implements FormattedString{
	/** @var Format */
	protected $format;
	/** @var FormattedString[] */
	protected $children = [];

	public function __construct(Format $format, array $children){
		$this->format = $format;
		$this->children = $children;
	}

	public function tokenize(?Format $parentFormat) : array{
		$format = $parentFormat !== null ? $parentFormat->add($this->format) : $this->format;
		$output = [[]];
		foreach($this->children as $child){
			$output[] = $child->tokenize($format);
		}
		return array_merge(...$output);
	}
}
