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
use SOFe\Libglocal\Parser\Token;

class VersionBlock extends AstNode{
	/** @var string */
	protected $value;

	protected function accept() : bool{
		return $this->acceptToken(Token::VERSION) !== null;
	}

	protected function complete() : void{
		$this->value = $this->expectToken(Token::IDENTIFIER)->getCode();
	}

	protected static function getNodeName() : string{
		return "<version>";
	}

	public function jsonSerialize() : array{
		return [
			"value" => $this->value,
		];
	}


	public function getValue() : string{
		return $this->value;
	}
}
