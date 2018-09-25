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

namespace SOFe\Libglocal\Parser\Ast;

use SOFe\Libglocal\Parser\Ast\Message\MessageParentBlock;
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;

abstract class AstRoot extends AstNode implements MessageParentBlock{
	public function __construct(LibglocalLexer $lexer){
		parent::__construct($lexer, $this, 1);
		$this->complete();
	}

	protected static function getNodeName() : string{
		return "file";
	}

}
