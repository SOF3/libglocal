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
use function strpos;

class NumberAttributeValueElement extends AstNode implements AttributeValueElement{
	/** @var int|float */
	protected $value;

	protected function accept() : bool{
		$token = $this->acceptToken(Token::NUMBER);
		if($token === null){
			return false;
		}
		$value = $token->getCode();
		if(strpos($value, ".") !== false){
			$this->value = (float) $value;
		}else{
			$this->value = (int) $value;
		}
		return true;
	}

	protected function complete() : void{
		// only one token
	}

	protected static function getNodeName() : string{
		return "number attribute value";
	}

	public function jsonSerialize() : array{
		return [
			"value" => $this->value,
		];
	}
}
