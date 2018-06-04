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

namespace SOFe\Libglocal\Component;

use pocketmine\utils\TextFormat;
use SOFe\Libglocal\Translation;
use function array_map;
use function array_pop;
use function array_unique;
use function count;
use function implode;
use function in_array;

class StackSpanTranslationComponent extends TranslationComponent implements ComponentHolder{
	/** @var bool */
	protected $isColor;
	/** @var string */
	protected $code;
	/** @var string|null */
	protected $fallbackCondition;
	/** @var string|null */
	protected $fallbackCode;

	/** @var TranslationComponent[] */
	protected $childComponents = [];

	public function __construct(Translation $translation, bool $isColor, string $code, ?string $fallbackCondition, ?string $fallbackCode){
		$this->myTranslation = $translation;
		$this->isColor = $isColor;
		$this->code = $code;
		$this->fallbackCondition = $fallbackCondition;
		$this->fallbackCode = $fallbackCode;
	}


	public function &getComponents() : array{
		return $this->childComponents;
	}

	public function init() : void{
		foreach($this->childComponents as $comp){
			$comp->init();
		}
	}

	public function toString(array &$args) : string{
		if($this->isColor){
			$start = $this->fallbackCondition !== null && in_array($this->fallbackCondition, $args[Translation::SPECIAL_ARG_STACK_COLOR], true) ? $this->fallbackCode : $this->code;
			$args[Translation::SPECIAL_ARG_STACK_COLOR][] = $start;
			$output = $start;
			foreach($this->childComponents as $comp){
				$output .= $comp->toString($args);
			}
			array_pop($args[Translation::SPECIAL_ARG_STACK_COLOR]);
			$output .= $args[Translation::SPECIAL_ARG_STACK_COLOR][count($args[Translation::SPECIAL_ARG_STACK_COLOR]) - 1];
			return $output;
		}

		$start = $this->code;
		$args[Translation::SPECIAL_ARG_STACK_FONT] = $start;
		$output = $start;
		foreach($this->childComponents as $comp){
			$output .= $comp->toString($args);
		}
		array_pop($args[Translation::SPECIAL_ARG_STACK_FONT]);
		$output .= TextFormat::RESET;
		$output .= $args[Translation::SPECIAL_ARG_STACK_COLOR][count($args[Translation::SPECIAL_ARG_STACK_COLOR]) - 1];
		$output .= implode("", array_unique($args[Translation::SPECIAL_ARG_STACK_FONT]));
		return $output;
	}

	public function toHtml() : string{
		$tag = null;
		switch($this->code){
			case TextFormat::BOLD:
				$tag = "strong";
				break;
			case TextFormat::ITALIC:
				$tag = "em";
				break;
			case TextFormat::UNDERLINE:
				$tag = "u";
				break;
			case TextFormat::STRIKETHROUGH:
				$tag = "strikethrough";
				break;
		}

		if($tag !== null){
			$prefix = "<{$tag}>";
			$suffix = "</{$tag}>";
		}else{
			$prefix = $suffix = "";
		}

		return '%{' . $this->code . ' ' . $prefix . implode('', array_map(function(TranslationComponent $component) : string{
				return $component->toHtml();
			}, $this->childComponents)) . $suffix . '}';
	}
}
