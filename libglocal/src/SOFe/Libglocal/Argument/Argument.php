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

use SOFe\Libglocal\Argument\Type\ArgumentType;
use SOFe\Libglocal\Argument\Type\Bool\BoolArgumentType;
use SOFe\Libglocal\Argument\Type\List_\ListArgumentType;
use SOFe\Libglocal\Argument\Type\Number\NumberArgumentType;
use SOFe\Libglocal\Argument\Type\Object\ObjectArgumentType;
use SOFe\Libglocal\Argument\Type\String\StringArgumentType;
use SOFe\Libglocal\Parser\Ast\Modifier\ArgLikeBlock;
use SOFe\Libglocal\Parser\Token;
use function assert;

class Argument{
	/** @var string */
	protected $id;

	/** @var ArgumentType */
	protected $type;

	public function __construct(string $id, ArgumentType $type){
		$this->id = $id;
		$this->type = $type;
	}

	public static function createType(ArgLikeBlock $arg, int $listLevels = 0) : ArgumentType{
		$type = self::newType($arg, $listLevels);
		if($arg->getDefault() !== null){
			$type->setDefault($arg->getDefault());
		}
		foreach($arg->getConstraints() as $constraint){
			$type->applyConstraint($constraint);
		}
		$type->onPostParse();
		return $type;
	}

	private static function newType(ArgLikeBlock $arg, int $listLevels) : ArgumentType{
		$remListLevels = $listLevels;
		foreach($arg->getTypeFlags() as $flag){
			if($flag->getType() === Token::FLAG_LIST){
				if($remListLevels === 0){
					return new ListArgumentType(self::createType($arg, $listLevels + 1));
				}
				$remListLevels--;
			}
		}
		assert($remListLevels === 0);
		switch($arg->getType()){
			case "string":
				return new StringArgumentType();
			case "int":
				return new NumberArgumentType(false);
			case "float":
				return new NumberArgumentType(true);
			case "bool":
				return new BoolArgumentType();
			case "object":
				return new ObjectArgumentType();
		}

		throw $arg->throwInit("Unknown argument type " . $arg->getType());
	}
}
