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

namespace SOFe\Libglocal\Argument\Type;

use SOFe\Libglocal\Argument\Attribute\ArgumentAttribute;
use SOFe\Libglocal\Context;
use SOFe\Libglocal\Format\FormattedString;
use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;

abstract class ArgumentType{
	protected $node;

	public function __construct(AstNode $node){
		$this->node = $node;
	}

	public abstract function getType() : string;

	public abstract function setDefault(AttributeValueElement $default) : void;

	public function applyConstraint(ConstraintBlock $constraint) : void{
		if(!$this->applyConstraintImpl($constraint)){
			throw $constraint->throwInit("Incompatible constraint $constraint applied on argument/field of string type");
		}
	}

	protected abstract function applyConstraintImpl(ConstraintBlock $constraint) : bool;

	/**
	 * @param mixed               $value
	 * @param Context             $context
	 * @param ArgumentAttribute[] $attributes
	 *
	 * @return FormattedString
	 */
	public abstract function toString($value, Context $context, array $attributes) : FormattedString;

	public function onPostParse() : void{
	}

	public function resolve() : void{
	}

	/**
	 * @param string[] $fieldPath
	 */
	public function testFieldPath(array $fieldPath) : void{
		$this->node->throwInit("Argument of type " . $this->getType() . " does not have any fields. The . notation is not allowed.");
	}
}
