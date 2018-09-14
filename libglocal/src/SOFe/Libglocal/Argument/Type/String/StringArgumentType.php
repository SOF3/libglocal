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

use SOFe\Libglocal\Argument\Attribute\ArgumentAttribute;
use SOFe\Libglocal\Argument\Type\ArgumentType;
use SOFe\Libglocal\Context;
use SOFe\Libglocal\Format\FormattedString;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Constraint\LiteralConstraintBlock;

class StringArgumentType extends ArgumentType{
	/** @var StringConstraint[] */
	protected $constraints = [];
	/** @var AttributeValueElement|null */
	protected $default = null;

	public function getType() : string{
		return "string";
	}

	public function setDefault(AttributeValueElement $default) : void{
		$this->default = $default;
	}

	protected function applyConstraintImpl(ConstraintBlock $constraint) : bool{
		if($constraint instanceof LiteralConstraintBlock){
			switch($constraint->getDirective()){
				case "enum":
					$this->constraints[] = new ExactStringConstraint($constraint->getValue()->requireStatic(), false);
					return true;
				case "ienum":
					$this->constraints[] = new ExactStringConstraint($constraint->getValue()->requireStatic(), true);
					return true;
				case "pattern":
					$this->constraints[] = new PatternStringConstraint($constraint->getValue()->requireStatic());
					return true;
			}
		}

		return false;
	}

	/**
	 * @param mixed               $value
	 * @param Context             $context
	 * @param ArgumentAttribute[] $attributes
	 *
	 * @return FormattedString
	 */
	public function toString($value, Context $context, array $attributes) : FormattedString{

	}
}
