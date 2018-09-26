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

namespace SOFe\Libglocal\Argument;

use SOFe\Libglocal\Argument\BoolType\BoolArgType;
use SOFe\Libglocal\Argument\ListType\ListArgType;
use SOFe\Libglocal\Argument\NumberType\NumberArgType;
use SOFe\Libglocal\Argument\ObjectType\ObjectArgType;
use SOFe\Libglocal\Argument\StringType\StringArgType;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use function array_slice;

abstract class ArgType{
	public static function create(string $type, array $flags) : ?ArgType{
		if(isset($flags[0]) && $flags[0] === "list"){
			return new ListArgType(ArgType::create($type, array_slice($flags, 1)));
		}

		switch($type){
			case "string":
				return new StringArgType();
			case "int":
				return new NumberArgType(false);
			case "float":
				return new NumberArgType(true);
			case "bool":
				return new BoolArgType();
			case "object":
				return new ObjectArgType();
		}

		return null;
	}

	public function acceptConstraint(ConstraintBlock $block) : bool{
		return false;
	}

	public function setDefault(AttributeValueElement $value) : bool{
		return false;
	}

	abstract public function localize() : LocalArg;
}
