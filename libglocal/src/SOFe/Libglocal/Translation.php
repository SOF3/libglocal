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

use SOFe\Libglocal\Component\ComponentHolder;
use SOFe\Libglocal\Component\TranslationComponent;
use function mb_strtolower;

class Translation implements ComponentHolder{
	public const SPECIAL_ARG_STACK_COLOR = "!!StackSpanColorStack!!";
	public const SPECIAL_ARG_STACK_FONT = "!!StackSpanFontStack!!";

	/** @var Message */
	private $message;
	/** @var string */
	protected $id;
	/** @var string */
	protected $lang;
	/** @var string */
	protected $declaration;

	/** @var TranslationComponent[] */
	protected $components = [];
	/** @var MessageArg[] */
	protected $argOverrides = [];

	/** @var string|null */
	protected $updated = null;


	public function __construct(Message $message, string $id, string $lang, string $declaration){
		$this->message = $message;
		$this->id = $id;
		$this->lang = $lang;
		$this->declaration = $declaration;
	}

	public function init() : void{
		foreach($this->argOverrides as $arg){
			$arg->init();
		}
		foreach($this->components as $component){
			$component->init();
		}
	}


	public function getMessage() : Message{
		return $this->message;
	}

	public function getId() : string{
		return $this->id;
	}

	public function getLang() : string{
		return $this->lang;
	}

	public function getDeclaration() : string{
		return $this->declaration;
	}

	/**
	 * @return TranslationComponent[]
	 */
	public function &getComponents() : array{
		return $this->components;
	}

	/**
	 * @return MessageArg[]
	 */
	public function &getArgOverrides() : array{
		return $this->argOverrides;
	}

	public function getUpdated() : ?string{
		return $this->updated;
	}

	public function setUpdated(?string $updated) : void{
		$this->updated = $updated;

		if($this->message->getUpdatedVersion() === null){
			$this->message->getManager()->getLogger()->warning("[libglocal] The message {$this->message->getId()} does not contain a base version, but the {$this->lang} translation declares a version.");
		}
		if(mb_strtolower($this->message->getUpdatedVersion()) !== mb_strtolower($updated)){
			$this->message->getManager()->getLogger()->warning("[libglocal] The base version of message {$this->message->getId()} is {$this->message->getUpdatedVersion()}, while the {$this->lang} translation targets the version {$updated}. This translation will not be used.");
		}
	}
}
