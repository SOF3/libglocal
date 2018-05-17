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

use SOFe\Libglocal\Arg\MessageArg;
use SOFe\Libglocal\Component\TranslationComponent;

class Translation{
	/** @var string */
	protected $id;

	/** @var string */
	protected $lang;

	/** @var TranslationComponent[] */
	protected $components = [];

	/** @var MessageArg[] */
	protected $argOverrides = [];

	/** @var string */
	protected $updated;


	public function getId() : string{
		return $this->id;
	}

	public function getLang() : string{
		return $this->lang;
	}

	/**
	 * @return TranslationComponent[]
	 */
	public function getComponents() : array{
		return $this->components;
	}

	/**
	 * @return MessageArg[]
	 */
	public function getArgOverrides() : array{
		return $this->argOverrides;
	}

	public function getUpdated() : string{
		return $this->updated;
	}
}
