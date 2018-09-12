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

namespace SOFe\Libglocal\Translation\Component;

use SOFe\Libglocal\Context;
use SOFe\Libglocal\Format\Format;
use SOFe\Libglocal\Format\FormattedString;
use SOFe\Libglocal\Format\ParentFormattedString;
use function array_map;

class SpanResolvedComponent implements ResolvedComponent{
	/** @var Format */
	protected $format;

	/** @var ResolvedComponent[] */
	protected $children = [];

	public function __construct(Format $format, array $children){
		$this->format = $format;
		$this->children = $children;
	}

	public function resolve() : void{
	}

	public function toString(Context $context) : FormattedString{
		return new ParentFormattedString($this->format, array_map(function(ResolvedComponent $component) use ($context): FormattedString{
			return $component->toString($context);
		}, $this->children));
	}
}
