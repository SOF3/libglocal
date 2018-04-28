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

class Message{
	/** @var string */
	protected $id;
	/** @var MessageParameter[] */
	protected $parameters;
	/** @var Translation */
	protected $base;
	/** @var Translation[] */
	protected $translations = [];

	/**
	 * Message constructor.
	 * @param string             $id
	 * @param MessageParameter[] $parameters
	 */
	public function __construct(string $id, array $parameters){
		$this->id = $id;
		$this->parameters = $parameters;
	}

	public function addTranslation(Translation $translation) : void{
		if(!isset($this->base)){
			$this->base = $translation;
		}
		// allow overriding
		$this->translations[$translation->getLang()] = $translation;
	}

	public function getId() : string{
		return $this->id;
	}

	public function getTranslations() : array{
		return $this->translations;
	}

	public function getTranslation(string $lang) : Translation{
		return $this->translations[$lang] ?? $this->base;
	}

	public function hasParameter(string $name) : bool{
		return isset($this->parameters[$name]);
	}

	/**
	 * @return MessageParameter[]
	 */
	public function getParameters():array{
		return $this->parameters;
	}
}
