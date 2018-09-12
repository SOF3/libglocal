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

namespace SOFe\Libglocal\Argument\Type\List_;

use pocketmine\command\CommandSender;
use SOFe\Libglocal\Argument\ArgumentAttribute;
use SOFe\Libglocal\Argument\Type\ArgumentType;
use SOFe\Libglocal\FormattedString;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Literal\LiteralElement;

class ListArgumentType implements ArgumentType{
	/** @var ArgumentType */
	protected $delegate;

	public function __construct(ArgumentType $delegate){
		$this->delegate = $delegate;
	}

	public function getType() : string{
		return "list:" . $this->delegate->getType();
	}

	public function setDefault(AttributeValueElement $default) : void{
		$default->throwInit("List arguments cannot have default values and are always required arguments");
	}

	public function applyConstraint(ConstraintBlock $constraint) : void{
		$this->delegate->applyConstraint($constraint);
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
