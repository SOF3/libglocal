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

namespace SOFe\Libglocal\Arg;

use SOFe\Libglocal\Message;

class MessageArg{
	/** @var string */
	protected $name;
	/** @var Message */
	protected $message;
	/** @var MessageArgType */
	protected $type;
	/** @var MessageArgDefault|null */
	protected $defaultValue;

	public function getName() : string{
		return $this->name;
	}

	public function getMessage() : Message{
		return $this->message;
	}

	public function getType() : MessageArgType{
		return $this->type;
	}

	public function getDefaultValue() : ?MessageArgDefault{
		return $this->defaultValue;
	}

	public function resolve(string $lang, array $args) : string{
		return isset($args[$this->name]) ? $this->type->toString($args[$this->name]) : $this->defaultValue->resolve($lang, $args);
	}

	public function __toString() : string{
		return "{$this->message->getId()}({$this->name})";
	}
}
