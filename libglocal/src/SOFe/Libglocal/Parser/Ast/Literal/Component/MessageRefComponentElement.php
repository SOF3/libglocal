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

namespace SOFe\Libglocal\Parser\Ast\Literal\Component;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Attribute\AttributeElement;
use SOFe\Libglocal\Parser\Token;

class MessageRefComponentElement extends AstNode implements LiteralComponentElement{
	/** @var bool */
	protected $dynamic;
	/** @var string */
	protected $name;
	/** @var AttributeElement[] */
	protected $attributes = [];

	protected function accept() : bool{
		return $this->acceptToken(Token::MESSAGE_REF_START) !== null;
	}

	protected function complete() : void{
		$this->dynamic = $this->acceptToken(Token::MOD_ARG) !== null;
		$this->name = $this->expectToken(Token::IDENTIFIER)->getCode();
		while($this->acceptToken(Token::CLOSE_BRACE) === null){
			$this->attributes[] = $this->expectAnyChildren(AttributeElement::class);
		}
	}

	protected static function getNodeName() : string{
		return "message reference";
	}

	public function toJsonArray() : array{
		return [
			"dynamic" => $this->dynamic,
			"name" => $this->name,
			"attributes" => $this->attributes,
		];
	}
}
