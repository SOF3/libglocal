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
use pocketmine\plugin\Plugin;

class LangManager{
	/** @var Plugin */
	protected $plugin;

	/** @var string */
	protected $baseLang;

	/** @var Message[] */
	protected $messages = [];

	public function __construct(Plugin $plugin){
		$this->plugin = $plugin;
	}


	public function getPlugin() : Plugin{
		return $this->plugin;
	}

	public function getBaseLang() : string{
		return $this->baseLang;
	}

	public function &getMessages() : array{
		return $this->messages;
	}


	public function translate(string $lang, string $id, array $args) : string{
		if(!isset($this->messages[$id])){
			throw new InvalidArgumentException("Translation \"{$id}\" not found");
		}

		return $this->messages[$id]->translate($lang, $args);
	}
}
