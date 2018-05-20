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

use InvalidArgumentException;
use LogicException;
use pocketmine\utils\TextFormat;
use function assert;

class Message{
	/** @var LangManager */
	protected $manager;
	/** @var string */
	protected $id;

	/** @var Translation */
	protected $baseTranslation;
	/** @var Translation[] */
	protected $translations = [];
	/** @var MessageArg[] */
	protected $args = [];

	/** @var string|null */
	protected $doc = null;
	/** @var string|null */
	protected $updated = null;


	public function __construct(LangManager $manager, string $id){
		$this->manager = $manager;
		$this->id = $id;
	}

	public function setBaseTranslation(Translation $translation) : void{
		if(isset($this->baseTranslation)){
			throw new LogicException("Attempt to set base translation twice");
		}
		$this->baseTranslation = $translation;
	}


	public function getManager() : LangManager{
		return $this->manager;
	}

	public function getId() : string{
		return $this->id;
	}

	public function &getTranslations() : array{
		return $this->translations;
	}

	public function getTranslation(string $lang) : ?Translation{
		return $this->translations[$lang] ?? null;
	}

	/**
	 * @return MessageArg[]
	 */
	public function &getArgs() : array{
		return $this->args;
	}

	public function getArg(string $lang, string $name) : ?MessageArg{
		if($this->translations[$lang] !== null && isset($this->translations[$lang]->getArgOverrides()[$name])){
			return $this->translations[$lang]->getArgOverrides()[$name];
		}
		if(isset($this->args[$name])){
			return $this->args[$name];
		}
		return null;
	}

	public function getDoc() : ?string{
		return $this->doc;
	}

	public function setDoc(?string $doc) : void{
		$this->doc = $doc;
	}

	public function getUpdatedVersion() : ?string{
		return $this->updated;
	}

	public function setUpdatedVersion(?string $updated) : void{
		$this->updated = $updated;
	}


	public function init() : void{
		foreach($this->args as $arg){
			$arg->init();
		}
		foreach($this->translations as $translation){
			$translation->init();
		}
	}


	public function translate(string $lang, array $args) : string{
		foreach($this->args as $argName => $argDecl){
			if(!isset($args[$argName]) && $argDecl->getDefaultValue() === null){
				throw new InvalidArgumentException("Required argument $argName for $this->id ($lang) is missing");
			}
		}

		$translation = $this->translations[$lang] ?? $this->baseTranslation;
		assert($translation !== null);

		if(!isset($args[Translation::SPECIAL_ARG_STACK_COLOR], $args[Translation::SPECIAL_ARG_STACK_FONT])){
			$args[Translation::SPECIAL_ARG_STACK_COLOR] = [TextFormat::WHITE];
			$args[Translation::SPECIAL_ARG_STACK_FONT] = [];
		}

		$output = "";
		foreach($translation->getComponents() as $component){
			$output .= $component->toString($args);
		}
		return $output;
	}
}
