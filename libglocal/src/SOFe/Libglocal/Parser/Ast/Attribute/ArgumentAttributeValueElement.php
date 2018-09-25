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

class ArgumentAttributeValueElement extends AstNode implements AttributeValueElement{
	/** @var string */
	protected $argName;

	protected function accept() : bool{
		$token = $this->acceptToken(Token::IDENTIFIER);
		if($token === null){
			return false;
		}
		$this->argName = $token->getCode();
		return true;
	}

	protected function complete() : void{
		// only one token
	}

	protected static function getNodeName() : string{
		return "argument attribute value";
	}

	public function toJsonArray() : array{
		return [
			"argName" => $this->argName,
		];
	}
}
