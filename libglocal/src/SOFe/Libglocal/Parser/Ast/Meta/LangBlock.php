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

namespace SOFe\Libglocal\Parser\Ast\Meta;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Literal\StaticLiteralElement;
use SOFe\Libglocal\Parser\Token;

class LangBlock extends AstNode{
	/** @var bool */
	protected $base;
	/** @var string */
	protected $id;
	/** @var StaticLiteralElement */
	protected $name;

	protected function accept() : bool{
		if($this->acceptToken(Token::BASE_LANG)){
			$this->base = true;
			return true;
		}

		if($this->acceptToken(Token::LANG)){
			$this->base = false;
			return true;
		}

		return false;
	}

	protected function complete() : void{
		$this->id = $this->expectToken(Token::IDENTIFIER)->getCode();
		$this->name = $this->expectAnyChildren(StaticLiteralElement::class);
	}

	protected static function getNodeName() : string{
		return "<lang>";
	}

	public function jsonSerialize() : array{
		return [
			"base" => $this->base,
			"id" => $this->id,
			"name" => $this->name,
		];
	}


	public function isBase() : bool{
		return $this->base;
	}

	public function getId() : string{
		return $this->id;
	}

	public function getName() : StaticLiteralElement{
		return $this->name;
	}
}
