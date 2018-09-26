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

namespace SOFe\Libglocal\Message;

use SOFe\Libglocal\Argument\LocalArg;
use SOFe\Libglocal\Literal\PreparedLiteral;
use SOFe\Libglocal\Math\MathRule;
use SOFe\Libglocal\Parser\Ast\Math\MathRuleBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;

class Translation{
	/** @var bool */
	protected $base;
	/** @var Message */
	protected $message;
	/** @var string */
	protected $lang;
	/** @var PreparedLiteral */
	protected $literal;
	/** @var LocalArg[] */
	protected $localArgs = [];

	public function __construct(Message $message, string $lang, MessageBlock $block, bool $base){
		$this->base = $base;
		$this->message = $message;
		$this->lang = $lang;

		$this->literal = PreparedLiteral::fromElement($block->getLiteral());

		foreach($message->getArgs() as $arg){
			$this->localArgs[$arg->getName()] = $arg->getType()->localize();
		}
		foreach($block->getArgs() as $argBlock){
			if(!isset($this->localArgs[$argBlock->getName()])){
				throw $argBlock->throwInit("Argument \${$argBlock->getName()} does not exist");
			}
			$local = $this->localArgs[$argBlock->getName()];
			foreach($argBlock->getConstraints() as $constraint){
				if(($constraint instanceof MathRuleBlock) && !$local->addMathRule(new MathRule($constraint))){
					throw $constraint->throwInit("MathRule is not acceptable in {$message->getArgs()[$argBlock->getName()]->getType()} arguments");
				}
			}
		}
	}
}
