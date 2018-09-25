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

namespace SOFe\Libglocal\Parser\Ast\Attribute;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Token;

class AttributeElement extends AstNode{
	/** @var bool */
	protected $isMath;
	/** @var string */
	protected $name;
	protected $value;

	protected function accept() : bool{
		$this->isMath = $this->acceptToken(Token::MATH_AT) !== null;
		$name = $this->acceptToken(Token::IDENTIFIER);
		if($name !== null){
			$this->name = $name->getCode();
		}
		return $this->isMath || $name !== null;
	}

	protected function complete() : void{
		$this->acceptToken(Token::EQUALS);
		$this->value = $this->expectAnyChildren(LiteralAttributeValueElement::class, NumberAttributeValueElement::class,
			ArgumentAttributeValueElement::class, MessageAttributeValueElement::class);
	}

	protected static function getNodeName() : string{
		return "attribute";
	}

	public function isMath() : bool{
		return $this->isMath;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getValue(){
		return $this->value;
	}

	public function toJsonArray() : array{
		return [
			"isMath" => $this->isMath,
			"name" => $this->name,
			"value" => $this->value,
		];
	}
}
