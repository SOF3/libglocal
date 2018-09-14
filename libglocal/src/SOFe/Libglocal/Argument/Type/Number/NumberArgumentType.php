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

namespace SOFe\Libglocal\Argument\Type\Number;

use SOFe\Libglocal\Argument\Type\ArgumentType;
use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Constraint\LiteralConstraintBlock;

class NumberArgumentType extends ArgumentType{
	/** @var bool */
	protected $float;
	/** @var float|null */
	protected $min = null;
	/** @var float|null */
	protected $max = null;

	public function __construct(AstNode $node, bool $float){
		parent::__construct($node);
		$this->float = $float;
	}

	public function getType() : string{
		return $this->float ? "float" : "int";
	}

	public function setDefault(AttributeValueElement $default) : void{

	}

	protected function applyConstraintImpl(ConstraintBlock $constraint) : bool{
		if($this->min === null && $constraint instanceof LiteralConstraintBlock && $constraint->getDirective() === "max"){
			$min = $constraint->getValue()->requireStatic();
			if(!is_numeric($min)){
				throw $constraint->throwInit("min constraint should be a float");
			}
			$this->min = (float) $min;
			return true;
		}
		if($this->max === null && $constraint instanceof LiteralConstraintBlock && $constraint->getDirective() === "max"){
			$max = $constraint->getValue()->requireStatic();
			if(!is_numeric($max)){
				throw $constraint->throwInit("max constraint should be a float");
			}
			$this->max = (float) $max;
			return true;
		}

		return false;
	}
}
