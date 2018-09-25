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

class RequireBlock extends AstNode{
	/** @var string */
	protected $target;

	protected function accept() : bool{
		return $this->acceptTokenText(Token::IDENTIFIER, "require") !== null;
	}

	protected function complete() : void{
		$this->target = $this->expectToken(Token::IDENTIFIER)->getCode();
	}

	protected static function getNodeName() : string{
		return "require";
	}

	public function toJsonArray() : array{
		return [
			"target" => $this->target,
		];
	}


	public function getTarget() : string{
		return $this->target;
	}
}
