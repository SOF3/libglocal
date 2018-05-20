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

namespace SOFe\Libglocal\ArgDefault;

use LogicException;
use SOFe\Libglocal\Message;
use function assert;
use function sprintf;

class ArgFallbackDefault extends ArgDefault{
	/** @var Message */
	protected $message;
	/** @var string|null */
	protected $myLang;
	/** @var string */
	protected $argName;

	private $resolving = false; // circular reference detection

	public function __construct(Message $message, ?string $myLang, string $argName){
		$this->message = $message;
		$this->myLang = $myLang;
		$this->argName = $argName;
	}

	public function resolve(string $lang, array $args){
		if($this->resolving){
			throw new LogicException(sprintf("Circular argument reference to argument %s detected for %s in %s", $this->argName, $this->message->getId(), $this->myLang ?? "(base lang)"));
		}

		if(isset($args[$this->argName])){
			return $args[$this->argName];
		}

		$this->resolving = true;
		$arg = $this->message->getArg($lang, $this->argName);
		assert($arg !== null && $arg->getDefaultValue() !== null);
		$result = $arg->getDefaultValue()->resolve($lang, $args);
		$this->resolving = false;

		return $result;
	}
}
