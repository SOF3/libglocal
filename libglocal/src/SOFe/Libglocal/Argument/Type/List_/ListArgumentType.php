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

use SOFe\Libglocal\Argument\Type\ArgumentType;
use SOFe\Libglocal\Context;
use SOFe\Libglocal\Format\FormattedString;
use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Constraint\LiteralConstraintBlock;
use function is_numeric;

class ListArgumentType extends ArgumentType{
	/** @var ArgumentType */
	protected $delegate;

	/** @var int|null */
	protected $min = null;
	/** @var int|null */
	protected $max = null;

	public function __construct(AstNode $node, ArgumentType $delegate){
		parent::__construct($node);
		$this->delegate = $delegate;
	}

	public function getType() : string{
		return "list:" . $this->delegate->getType();
	}

	public function setDefault(AttributeValueElement $default) : void{
		$default->throwInit("List arguments cannot have default values and are always required arguments");
	}

	protected function applyConstraintImpl(ConstraintBlock $constraint) : bool{
		if($this->min === null && $constraint instanceof LiteralConstraintBlock && $constraint->getDirective() === "max"){
			$min = $constraint->getValue()->requireStatic();
			if(!is_numeric($min)){
				throw $constraint->throwInit("min constraint should be an integer");
			}
			$this->min = (int) $min;
			return true;
		}
		if($this->max === null && $constraint instanceof LiteralConstraintBlock && $constraint->getDirective() === "max"){
			$max = $constraint->getValue()->requireStatic();
			if(!is_numeric($max)){
				throw $constraint->throwInit("max constraint should be an integer");
			}
			$this->max = (int) $max;
			return true;
		}

		return $this->delegate->applyConstraintImpl($constraint);
	}

	public function toString($value, Context $context, array $attributes) : FormattedString{

	}

	public function onPostParse() : void{
		$this->delegate->onPostParse();
	}

	public function testFieldPath(array $fieldPath) : void{
		$this->delegate->testFieldPath($fieldPath);
	}

	public function getDelegate() : ArgumentType{
		return $this->delegate;
	}
}
