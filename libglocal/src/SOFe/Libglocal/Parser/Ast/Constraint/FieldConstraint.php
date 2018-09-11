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

namespace SOFe\Libglocal\Parser\Ast\Constraint;

use SOFe\Libglocal\Parser\Ast\BlockParentAstNode;
use SOFe\Libglocal\Parser\Ast\Literal\LiteralElement;
use SOFe\Libglocal\Parser\Token;

class FieldConstraint extends BlockParentAstNode implements ConstraintBlock{
	/** @var string */
	protected $name;
	/** @var Token[] */
	protected $typeFlags = [];
	/** @var string */
	protected $type = "string";
	/** @var LiteralElement|null */
	protected $default;
	/** @var FieldConstraint[] */
	protected $fields = [];

	protected function accept() : bool{
		return $this->acceptToken(Token::MOD_ARG) !== null;
	}

	protected function initial() : void{
		$this->name = $this->expectToken(Token::IDENTIFIER)->getCode();
		while(($typeFlag = $this->acceptTokenCategory(Token::CATEGORY_FLAGS)) !== null){
			$this->typeFlags[] = $typeFlag;
		}
		if(($type = $this->acceptToken(Token::IDENTIFIER)) !== null){
			$this->type = $type->getCode();
			$this->default = $this->acceptAnyChildren(LiteralElement::class);
		}
	}

	protected function acceptChild() : void{
		$this->fields[] = $this->expectAnyChildren(FieldConstraint::class);
	}

	protected static function getNodeName() : string{
		return "field";
	}

	public function jsonSerialize() : array{
		return [
			"name" => $this->name,
			"type" => [
				"flags" => $this->typeFlags,
				"name" => $this->name,
			],
			"default" => $this->default,
			"fields" => $this->fields,
		];
	}


	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return Token[]
	 */
	public function getTypeFlags() : array{
		return $this->typeFlags;
	}

	public function getType() : string{
		return $this->type;
	}

	public function getDefault() : ?LiteralElement{
		return $this->default;
	}

	/**
	 * @return FieldConstraint[]
	 */
	public function getFields() : array{
		return $this->fields;
	}
}
