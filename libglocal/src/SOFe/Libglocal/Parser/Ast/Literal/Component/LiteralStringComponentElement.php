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
use SOFe\Libglocal\Parser\Token;

class LiteralStringComponentElement extends AstNode implements LiteralComponentElement{
	/** @var Token */
	protected $token;

	protected function accept() : bool{
		return ($this->token = $this->acceptToken(Token::LITERAL)) !== null ||
			($this->token = $this->acceptToken(Token::ESCAPE)) !== null ||
			($this->token = $this->acceptToken(Token::CONT_NEWLINE)) !== null ||
			($this->token = $this->acceptToken(Token::CONT_SPACE)) !== null ||
			($this->token = $this->acceptToken(Token::CONT_CONCAT)) !== null;

	}

	protected function complete() : void{
		// we only read one token.
	}

	protected static function getName() : string{
		return "literal component";
	}

	public function jsonSerialize() : array{
		return [
			"token" => $this->token,
		];
	}
}
