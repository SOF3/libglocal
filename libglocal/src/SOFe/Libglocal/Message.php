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

namespace SOFe\Libglocal;

use SOFe\Libglocal\Argument\Argument;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;
use SOFe\Libglocal\Parser\Token;
use SOFe\Libglocal\Translation\Translation;
use function implode;

class Message{
	public const PUBLIC = 0;
	public const LIB = 1;
	public const LOCAL = 2;

	/** @var int */
	protected $visibility;
	/** @var string[] */
	protected $docs = [];
	/** @var string|null null version implies earliest possible version */
	protected $baseVersion;
	/** @var Argument[] */
	protected $arguments = [];

	/** @var Translation */
	protected $baseTranslation;
	/** @var Translation[] */
	protected $translations = [];


	public function __construct(MessageBlock $block){
		$this->setVisibility($block);
		$this->setDocs($block);
		$this->baseVersion = $block->getVersion() !== null ? $block->getVersion()->getTarget() : null;
	}

	protected function setVisibility(MessageBlock $block) : void{
		foreach($block->getFlags() as $flag){
			switch($flag->getType()){
				case Token::FLAG_LIB:
					$this->visibility = self::LIB;
					break;
				case Token::FLAG_LOCAL:
					$this->visibility = self::LOCAL;
					break;
				case Token::FLAG_PUBLIC:
					$this->visibility = self::PUBLIC;
					break;
				default:
					$block->throwInit("Invalid flag on message: {$flag->getCode()}");
			}
		}
		$this->visibility = $this->visibility ?? self::PUBLIC;
	}

	protected function setDocs(MessageBlock $block) : void{
		$temp = [];
		foreach($block->getDocs() as $doc){
			if($doc->getValue() === null){
				if(!empty($temp)){
					$this->docs[] = implode(" ", $temp);
					$temp = [];
				}
			}else{
				$temp[] = $doc->getValue();
			}
		}
		if(!empty($temp)){
			$this->docs[] = implode(" ", $temp);
		}
	}
}
