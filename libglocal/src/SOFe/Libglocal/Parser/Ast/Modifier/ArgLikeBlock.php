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

namespace SOFe\Libglocal\Parser\Ast\Modifier;

use SOFe\Libglocal\Parser\Ast\Attribute\ArgumentAttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Attribute\LiteralAttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Attribute\MessageAttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Attribute\NumberAttributeValueElement;
use SOFe\Libglocal\Parser\Ast\BlockParentAstNode;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Constraint\FieldConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Math\MathRuleBlock;
use SOFe\Libglocal\Parser\Token;

abstract class ArgLikeBlock extends BlockParentAstNode{
	/** @var string */
	protected $name;
	/** @var Token[] */
	protected $typeFlags = [];
	/** @var string */
	protected $type = "string";
	/** @var bool */
	protected $explicitType = false;
	/** @var AttributeValueElement|null */
	protected $default;
	/** @var ConstraintBlock[] */
	protected $constraints = [];

	protected function accept() : bool{
		return $this->acceptToken(Token::MOD_ARG) !== null;
	}

	protected function initial() : void{
		$this->name = $this->expectToken(Token::IDENTIFIER)->getCode();
		while(($typeFlag = $this->acceptTokenCategory(Token::CATEGORY_FLAGS)) !== null){
			$this->typeFlags[] = $typeFlag;
		}
		if(($type = $this->acceptToken(Token::IDENTIFIER)) !== null){
			$this->explicitType = true;
			$this->type = $type->getCode();
		}
		if($this->acceptToken(Token::EQUALS)){
			$this->default = $this->expectAnyChildren(LiteralAttributeValueElement::class, NumberAttributeValueElement::class,
				ArgumentAttributeValueElement::class, MessageAttributeValueElement::class);
		}
	}

	protected function acceptChild() : void{
		$this->constraints[] = $this->expectAnyChildren(FieldConstraintBlock::class, MathRuleBlock::class);
	}

	public function jsonSerialize() : array{
		return [
			"name" => $this->name,
			"type" => [
				"flags" => $this->typeFlags,
				"name" => $this->name,
			],
			"default" => $this->default,
			"constraints" => $this->constraints,
		];
	}


	public function getName() : string{
		return $this->name;
	}

	public function getType() : string{
		return $this->type;
	}

	public function isExplicitType() : bool{
		return $this->explicitType;
	}

	/**
	 * @return Token[]
	 */
	public function getTypeFlags() : array{
		return $this->typeFlags;
	}

	public function getDefault() : ?AttributeValueElement{
		return $this->default;
	}

	/**
	 * @return ConstraintBlock[]
	 */
	public function getConstraints() : array{
		return $this->constraints;
	}
}
