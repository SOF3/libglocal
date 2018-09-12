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

namespace SOFe\Libglocal\Argument\Type\Object;

use function mb_strpos;
use pocketmine\command\CommandSender;
use SOFe\Libglocal\Argument\Argument;
use SOFe\Libglocal\Argument\ArgumentAttribute;
use SOFe\Libglocal\Argument\Type\ArgumentType;
use SOFe\Libglocal\FormattedString;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Constraint\FieldConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Literal\LiteralElement;
use function strpos;

class ObjectArgumentType implements ArgumentType{
	/** @var Argument[] */
	protected $fields = [];

	public function getType() : string{
		return "object";
	}

	public function setDefault(AttributeValueElement $default) : void{
		throw $default->throwInit("Objects cannot have default values. The default values should be placed in the fields");
	}

	public function applyConstraint(ConstraintBlock $constraint) : void{
		if($constraint instanceof FieldConstraintBlock){
			if(mb_strpos($constraint->getName(), ".") !== false){
				throw $constraint->throwParse("Object fields must not contain dots");
			}
			$this->fields[$constraint->getName()] = new Argument($constraint->getName(), Argument::createType($constraint));
			return;
		}
		$constraint->throwInit("Incompatible constraint $constraint applied on argument/field of string type");
	}

	/**
	 * @param mixed               $value
	 * @param CommandSender       $context
	 * @param ArgumentAttribute[] $attributes
	 *
	 * @return FormattedString
	 */
	public function toString($value, CommandSender $context, array $attributes) : FormattedString{

	}

	public function onPostParse() : void{
	}
}
