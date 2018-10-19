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
use SOFe\Libglocal\Parser\Ast\Math\MathRuleBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageGroupBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageParentBlock;
use SOFe\Libglocal\Parser\Ast\Message\ModuleBlock;
use SOFe\Libglocal\Parser\Ast\Meta\AuthorBlock;
use SOFe\Libglocal\Parser\Ast\Meta\LangBlock;
use SOFe\Libglocal\Parser\Ast\Meta\RequireBlock;
use SOFe\Libglocal\Parser\Ast\Meta\UseBlock;
use SOFe\Libglocal\Parser\Ast\Meta\VersionBlock;

class LibglocalFile extends AstRoot implements MessageParentBlock{
	/** @var LangBlock */
	protected $lang;
	/** @var AuthorBlock[] */
	protected $authors = [];
	/** @var VersionBlock|null */
	protected $version = null;
	/** @var RequireBlock[] */
	protected $requires = [];
	/** @var UseBlock[] */
	protected $uses = [];
	/** @var MathRuleBlock[] */
	protected $mathRules = [];
	/** @var ModuleBlock */
	protected $module;

	/** @var MessageGroupBlock[] */
	protected $groups = [];
	/** @var MessageBlock[] */
	protected $messages = [];


	protected function complete() : void{
		while(true){
			$child = $this->expectAnyChildren(LangBlock::class, AuthorBlock::class, VersionBlock::class, RequireBlock::class, UseBlock::class, MathRuleBlock::class, ModuleBlock::class);
			if($child instanceof LangBlock){
				if($this->lang !== null){
					throw $this->throwParse("<lang> can only be declared once");
				}
				$this->lang = $child;
			}elseif($child instanceof AuthorBlock){
				$this->authors[] = $child;
			}elseif($child instanceof VersionBlock){
				if($this->version !== null){
					throw $this->throwParse("<version> can only be declared once");
				}
				$this->version = $child;
			}elseif($child instanceof RequireBlock){
				$this->requires[] = $child;
			}elseif($child instanceof UseBlock){
				$this->uses[] = $child;
			}elseif($child instanceof MathRuleBlock){
				$this->mathRules[] = $child;
			}elseif($child instanceof ModuleBlock){
				$this->module = $child;
				break;
			}else{
				throw new AssertionError("Unexpected child");
			}
		}

		if($this->lang === null){
			throw $this->throwParse("Missing <lang>");
		}

		while(!$this->lexer->eof()){
			$this->acceptChild();
		}
	}

	protected function acceptChild() : void{
		$child = $this->expectAnyChildren(MessageGroupBlock::class, MessageBlock::class);
		if($child instanceof MessageGroupBlock){
			$this->groups[] = $child;
		}elseif($child instanceof MessageBlock){
			$this->messages[] = $child;
		}
	}

	public function toJsonArray() : array{
		return [
			"lang" => $this->lang,
			"authors" => $this->authors,
			"version" => $this->version,
			"requires" => $this->requires,
			"module" => $this->module,
			"groups" => $this->groups,
			"messages" => $this->messages,
		];
	}


	public function getLang() : LangBlock{
		return $this->lang;
	}

	/**
	 * @return AuthorBlock[]
	 */
	public function getAuthors() : array{
		return $this->authors;
	}

	public function getVersion() : ?VersionBlock{
		return $this->version;
	}

	/**
	 * @return RequireBlock[]
	 */
	public function getRequires() : array{
		return $this->requires;
	}

	/**
	 * @return UseBlock[]
	 */
	public function getUses() : array{
		return $this->uses;
	}

	/**
	 * @return MathRuleBlock[]
	 */
	public function getMathRules() : array{
		return $this->mathRules;
	}

	public function getModule() : ModuleBlock{
		return $this->module;
	}

	/**
	 * @return MessageGroupBlock[]
	 */
	public function getGroups() : array{
		return $this->groups;
	}

	/**
	 * @return MessageBlock[]
	 */
	public function getMessages() : array{
		return $this->messages;
	}
}
