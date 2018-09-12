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

namespace SOFe\Libglocal\Parser\Ast\Literal;

use SOFe\Libglocal\Parser\Ast\AstNode;
use SOFe\Libglocal\Parser\Ast\Literal\Component\LiteralComponentElement;

abstract class AbstractLiteralElement extends AstNode{
	/** @var LiteralComponentElement[] */
	protected $components = [];

	protected function accept() : bool{
		return $this->nextComponent();
	}

	protected function complete() : void{
		/** @noinspection LoopWhichDoesNotLoopInspection */
		while($this->nextComponent()){
			// continue to next component
		}
	}

	protected function nextComponent() : bool{
		$component = $this->acceptComponent();
		if($component !== null){
			$this->components[] = $component;
			return true;
		}
		return false;
	}

	public function jsonSerialize() : array{
		return [
			"components" => $this->components,
		];
	}

	protected abstract function acceptComponent() : ?AstNode;

	/**
	 * @return LiteralComponentElement[]
	 */
	public function getComponents() : array{
		return $this->components;
	}
}
