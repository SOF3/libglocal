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

use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;

abstract class AstNode{
	/** @var LibglocalLexer */
	protected $lexer;

	/**
	 * @param LibglocalLexer $lexer
	 * @return static
	 */
	public static function try(LibglocalLexer $lexer){
		$instance = new static($lexer);

	}

	private function __construct(LibglocalLexer $lexer){
		$this->lexer = $lexer;
	}

	protected abstract function accept() : bool;

	protected function expect(int $type) : void{
		$token = $this->lexer->next();
	}
}
