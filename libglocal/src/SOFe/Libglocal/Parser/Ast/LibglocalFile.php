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

use AssertionError;
use SOFe\Libglocal\Parser\Ast\Message\MessagesBlock;
use SOFe\Libglocal\Parser\Ast\Meta\AuthorBlock;
use SOFe\Libglocal\Parser\Ast\Meta\LangBlock;
use SOFe\Libglocal\Parser\Ast\Meta\RequireBlock;
use SOFe\Libglocal\Parser\Ast\Meta\VersionBlock;
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;
use SOFe\Libglocal\Parser\ParseException;

class LibglocalFile extends AstNode{
	/** @var LangBlock */
	protected $lang;
	/** @var AuthorBlock[] */
	protected $authors = [];
	/** @var VersionBlock|null */
	protected $version = null;
	/** @var RequireBlock[] */
	protected $requires = [];
	/** @var MessagesBlock */
	protected $messages;

	// TODO add support for math rules

	public function __construct(LibglocalLexer $lexer){
		parent::__construct($lexer);
		$this->complete();
	}

	protected function complete() : void{
		while(true){
			$child = $this->expectAnyChildren(LangBlock::class, AuthorBlock::class, VersionBlock::class, RequireBlock::class, MessagesBlock::class);
			if($child instanceof LangBlock){
				if($this->lang !== null){
					throw new ParseException("<lang> can only be declared once");
				}
				$this->lang = $child;
			}elseif($child instanceof AuthorBlock){
				$this->authors[] = $child;
			}elseif($child instanceof VersionBlock){
				if($this->version !== null){
					throw new ParseException("<version> can only be declared once");
				}
				$this->version = $child;
			}elseif($child instanceof RequireBlock){
				$this->requires[] = $child;
			}elseif($child instanceof MessagesBlock){
				$this->messages[] = $child;
				break;
			}else{
				throw new AssertionError("Unexpected child");
			}
		}
		if(!$this->lexer->eof()){
			throw new ParseException("<messages> must be the last block");
		}

		if($this->lang === null){
			throw new ParseException("Missing <lang>");
		}
	}

	protected static function getName() : string{
		return "libglocal lang";
	}
}
