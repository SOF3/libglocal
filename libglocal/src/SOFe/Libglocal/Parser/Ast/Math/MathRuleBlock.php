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

namespace SOFe\Libglocal\Parser\Ast\Math;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Token;

class MathRuleBlock extends AstNode implements ConstraintBlock{
	/** @var bool */
	protected $restriction;
	/** @var string|null */
	protected $name = null;
	/** @var MathPredicateElement[] */
	protected $predicates = [];

	protected function accept() : bool{
		return $this->acceptToken(Token::MATH_AT) !== null;
	}

	protected function complete() : void{
		$this->restriction = $this->acceptToken(Token::MATH_AT) !== null;
		if(!$this->restriction){
			$name = $this->acceptToken(Token::IDENTIFIER);
			if($name !== null){
				$this->name = $name->getCode();
			}
		}

		while(($predicate = $this->acceptAnyChildren(MathPredicateElement::class)) !== null){
			$this->predicates[] = $predicate;
		}
	}

	protected static function getNodeName() : string{
		return "math rule";
	}

	public function toJsonArray() : array{
		return [
			"isRestriction" => $this->restriction,
			"name" => $this->name,
			"predicates" => $this->predicates,
		];
	}


	public function isRestriction() : bool{
		return $this->restriction;
	}

	public function getName() : ?string{
		return $this->name;
	}

	/**
	 * @return MathPredicateElement[]
	 */
	public function getPredicates() : array{
		return $this->predicates;
	}
}
