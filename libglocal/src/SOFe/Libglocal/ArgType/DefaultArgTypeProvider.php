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

use SOFe\Libglocal\ArgTypeProvider;
use SOFe\Libglocal\LangManager;

class DefaultArgTypeProvider implements ArgTypeProvider{
	/** @var LangManager */
	protected $manager;

	public function __construct(LangManager $manager){
		$this->manager = $manager;
	}

	public function createArgType(?string $modifier, string $type) : ?ArgType{
		if($modifier !== null){
			switch($modifier){
				case "list":
					return new ListArgTypeModifier($this->manager->createArgType(null, $type));
			}
			return null;
		}

		if($type === "string"){
			return new StringArgType();
		}

		if($type === "int" || $type === "integer"){
			return new NumericArgType(true);
		}

		if($type === "float" || $type === "double" || $type === "number" || $type === "real"){
			return new NumericArgType(false);
		}

		if($type === "quantity" || $type === "iquantity"){
			return new QuantityArgType(true);
		}
		if($type === "fquantity"){
			return new QuantityArgType(true);
		}

		return null;
	}
}
