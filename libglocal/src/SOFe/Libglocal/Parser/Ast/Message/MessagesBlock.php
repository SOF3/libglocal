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
use SOFe\Libglocal\Parser\Token;

class MessagesBlock extends BlockParentAstNode{
	/** @var string */
	protected $module;

	/** @var MessageGroupBlock */
	protected $groups = [];
	/** @var MessageBlock */
	protected $messages = [];

	protected function accept() : bool{
		return $this->acceptToken(Token::MESSAGES) !== null;
	}

	protected function initial() : void{
		$this->module = $this->expectToken(Token::IDENTIFIER)->getCode();
	}

	protected function acceptChild() : void{
		$child = $this->expectAnyChildren(MessageGroupBlock::class, MessageBlock::class);
		if($child instanceof MessageGroupBlock){
			$this->groups[] = $child;
		}elseif($child instanceof MessageBlock){
			$this->messages[] = $child;
		}
	}

	protected static function getName() : string{
		return "<messages>";
	}
}
