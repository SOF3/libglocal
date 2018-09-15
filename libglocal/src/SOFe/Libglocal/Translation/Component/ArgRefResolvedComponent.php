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

namespace SOFe\Libglocal\Translation\Component;

use SOFe\Libglocal\Argument\Argument;
use SOFe\Libglocal\Argument\Attribute\ArgumentAttribute;
use SOFe\Libglocal\Context;
use SOFe\Libglocal\Format\FormattedString;
use SOFe\Libglocal\LangManager;
use SOFe\Libglocal\Message;
use SOFe\Libglocal\Parser\Ast\Literal\Component\ArgRefComponentElement;
use SOFe\Libglocal\Translation\Translation;
use function array_slice;
use function explode;

class ArgRefResolvedComponent implements ResolvedComponent{
	/** @var Translation */
	protected $translation;
	/** @var Message */
	protected $message;
	/** @var ArgRefComponentElement */
	protected $element;
	/** @var Argument */
	protected $arg;
	/** @var string[] */
	protected $argPath = [];
	/** @var ResolvedComponentGroup[] */
	protected $mathSwitches = [];
	/** @var ArgumentAttribute[] */
	protected $attributes = [];

	public function __construct(Translation $translation, ArgRefComponentElement $element){
		$this->translation = $translation;
		$this->message = $translation->getMessage();
		$this->element = $element;
	}

	public function resolve(LangManager $manager) : void{
		$name = $this->element->getName();
		$argsArray = $this->message->getArguments();

		$paths = explode(".", $name);
		if(!isset($argsArray[$paths[0]])){
			$this->element->throwInit("Undefined argument {$paths[0]}");
		}
		$this->arg = $argsArray[$paths[0]];
		$this->argPath = array_slice($paths, 1);

		foreach($this->element->getAttributes() as $attribute){
			if($attribute->isMath()){
//				$this->mathSwitches[$attribute->getName()] = ;
			}else{
				$this->attributes[] = ArgumentAttribute::fromAst($attribute);
			}
		}
	}

	public function toString(Context $context) : FormattedString{

	}
}
