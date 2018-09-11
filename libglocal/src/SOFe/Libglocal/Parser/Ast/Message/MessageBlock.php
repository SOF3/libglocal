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

namespace SOFe\Libglocal\Parser\Ast\Message;

use SOFe\Libglocal\Parser\Ast\BlockParentAstNode;
use SOFe\Libglocal\Parser\Ast\Literal\LiteralElement;
use SOFe\Libglocal\Parser\Ast\Modifier\ArgModifier;
use SOFe\Libglocal\Parser\Ast\Modifier\DocModifier;
use SOFe\Libglocal\Parser\Ast\Modifier\VersionModifier;
use SOFe\Libglocal\Parser\ParseException;
use SOFe\Libglocal\Parser\Token;

class MessageBlock extends BlockParentAstNode{
	/** @var Token[] */
	protected $flags;
	/** @var string */
	protected $id;
	/** @var LiteralElement */
	protected $literal;

	/** @var ArgModifier[] */
	protected $args = [];
	/** @var DocModifier[] */
	protected $docs = [];
	/** @var VersionModifier|null */
	protected $version = null;

	protected function initial() : void{
		while(($flag = $this->acceptTokenCategory(Token::CATEGORY_FLAGS)) !== null){
			$this->flags[] = $flag;
		}
		$this->id = $this->expectToken(Token::IDENTIFIER)->getCode();
		$this->literal = $this->expectAnyChildren(LiteralElement::class);
	}

	protected function acceptChild() : void{
		$child = $this->expectAnyChildren(ArgModifier::class, DocModifier::class, VersionModifier::class);
		if($child instanceof ArgModifier){
			$this->args[] = $child;
		}elseif($child instanceof DocModifier){
			$this->docs[] = $child;
		}elseif($child instanceof VersionModifier){
			if($this->version !== null){
				throw new ParseException("<version> can only be declared once");
			}
			$this->version = $child;
		}
	}

	protected static function getName() : string{
		return "<message>";
	}

	public function jsonSerialize() : array{
		$ret = [
			"id" => $this->id,
			"literal" => $this->literal,
		];
		if(!empty($this->flags)){
			$ret["flags"] = $this->flags;
		}
		if(!empty($this->args)){
			$ret["args"] = $this->args;
		}
		if(!empty($this->docs)){
			$ret["docs"] = $this->docs;
		}
		if($this->version !== null){
			$ret["version"] = $this->version;
		}
		return $ret;
	}
}
