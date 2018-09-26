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

use SOFe\Libglocal\Argument\Argument;
use SOFe\Libglocal\Parser\Ast\LibglocalFile;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageGroupBlock;
use function assert;
use function in_array;

class Message{
	/** @var string */
	protected $id;
	/** @var string[] */
	protected $docs = [];
	/** @var string|null */
	protected $version = null;
	/** @var string */
	protected $visibility = MessageVisibility::PUBLIC;
	/** @var Argument[] */
	protected $args = [];
	/** @var Translation */
	protected $baseTranslation;
	/** @var Translation[] */
	protected $translations = [];

	/** @var MessageBlock */
	private $baseBlock;

	public function __construct(MessageBlock $block){
		$this->id = $block->getId();
		for($group = $block->getParent(); $group instanceof MessageGroupBlock; $group = $group->getParent()){
			$this->id = $group->getId() . "." . $this->id;
		}

		$docBuffer = "";
		foreach($block->getDocs() as $doc){
			if($doc->getValue() === null || empty($doc->getValue()->toString())){
				if(!empty($docBuffer)){
					$this->docs[] = $docBuffer;
				}
				$docBuffer = "";
			}else{
				$docBuffer .= $doc->getValue()->toString() . " ";
			}
		}
		if(!empty($docBuffer)){
			$this->docs[] = $docBuffer;
		}

		$this->version = $block->getVersion() !== null ? $block->getVersion()->getTarget() : null;

		$visSet = false;
		foreach($block->getFlags() as $flag){
			if(in_array($flag->getCode(), MessageVisibility::ALL, true)){
				if($visSet){
					throw $block->throwInit("Only one visibility flag is allowed per message");
				}
				$visSet = true;
				$this->visibility = $flag->getCode();
			}
		}

		foreach($block->getArgs() as $argBlock){
			$arg = new Argument($argBlock);
			$this->args[$arg->getName()] = $arg;
		}

		$this->baseBlock = $block;
	}

	public function init() : void{

		foreach($this->args as $arg){
			$arg->init();
		}
		$root = $this->baseBlock->getRoot();
		assert($root instanceof LibglocalFile);
		$baseLang = $root->getLang()->getId();
		$this->baseTranslation = new Translation($this, $baseLang, $this->baseBlock, true);
	}

	public function getId() : string{
		return $this->id;
	}

	/**
	 * @return string[]
	 */
	public function getDocs() : array{
		return $this->docs;
	}

	public function getVersion() : ?string{
		return $this->version;
	}

	/**
	 * @return Argument[]
	 */
	public function getArgs() : array{
		return $this->args;
	}

	public function getBaseTranslation() : Translation{
		return $this->baseTranslation;
	}

	/**
	 * @return Translation[]
	 */
	public function getTranslations() : array{
		return $this->translations;
	}

	public function getTranslation(string $lang) : ?Translation{
		return $this->translations[$lang] ?? null;
	}
}
