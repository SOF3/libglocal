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

use JsonSerializable;
use SOFe\Libglocal\ArgDefault\ArgDefault;
use SOFe\Libglocal\ArgType\ArgType;

class MessageArg implements JsonSerializable{
	/** @var Message */
	protected $message;
	/** @var string */
	protected $name;
	/** @var ArgType */
	protected $type;
	/** @var ArgDefault|null */
	protected $defaultValue;

	public function __construct(Message $message, string $name, ArgType $type, ?ArgDefault $defaultValue){
		$this->message = $message;
		$this->name = $name;
		$this->type = $type;
		$type->setArg($this);
		$this->defaultValue = $defaultValue;
	}

	public function init() : void{
		$this->type->init();
		if($this->defaultValue !== null){
			$this->defaultValue->init();
		}
	}


	public function getName() : string{
		return $this->name;
	}

	public function getMessage() : Message{
		return $this->message;
	}

	public function getType() : ArgType{
		return $this->type;
	}

	public function getDefaultValue() : ?ArgDefault{
		return $this->defaultValue;
	}

	public function resolve(string $lang, array $args) : string{
		return isset($args[$this->name]) ? $this->type->toString($args[$this->name]) : $this->defaultValue->resolve($lang, $args);
	}

	public function __toString() : string{
		return "{$this->message->getId()}({$this->name})";
	}

	public function jsonSerialize() : array{
		return [
			"name" => $this->name,
			"type" => $this->type,
			"default" => $this->defaultValue,
		];
	}
}
