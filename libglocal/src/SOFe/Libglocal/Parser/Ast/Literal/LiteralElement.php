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
use SOFe\Libglocal\Parser\Ast\Literal\Component\ArgRefComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\LiteralStringComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\MessageRefComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\SpanComponentElement;

class LiteralElement extends AbstractLiteralElement{
	protected function acceptComponent() : ?AstNode{
		return $this->acceptAnyChildren(LiteralStringComponentElement::class, ArgRefComponentElement::class, MessageRefComponentElement::class, SpanComponentElement::class);
	}

	protected static function getNodeName() : string{
		return "literal";
	}

	public function requireStatic() : string{
		$output = "";
		foreach($this->components as $component){
			if(!($component instanceof LiteralStringComponentElement)){
				$this->throwInit("Dynamic resolution is not allowed");
			}
			$output .= $component->toString();
		}
		return $output;
	}
}
