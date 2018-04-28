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

use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use ReflectionClass;
use function array_pop;
use function array_search;
use function count;
use function strlen;
use function strpos;
use function substr;

class Translation{
	protected const ESCAPE_MAP = [
		"\\" => "\\",
		"%" => "%",
		"\$" => "\$",
		"{" => "{",
		"}" => "}",
//		"n" => "\n",
	];
	protected const BACKGROUND_COLORS = [
		"error" => TextFormat::RED,
		"err" => TextFormat::RED,
		"warn" => TextFormat::YELLOW,
		"notice" => TextFormat::AQUA,
		"success" => TextFormat::GREEN,
		"info" => TextFormat::WHITE,
	];
	protected const STACK_COLORS = [
		// if bg is [1], use [2], otherwise use [0]
		"hl1" => [TextFormat::LIGHT_PURPLE, TextFormat::LIGHT_PURPLE, TextFormat::LIGHT_PURPLE],
		"hl2" => [TextFormat::GOLD, TextFormat::YELLOW, TextFormat::RED],
		"hl3" => [TextFormat::AQUA, TextFormat::AQUA, TextFormat::YELLOW],
		"hl4" => [TextFormat::GREEN, TextFormat::GREEN, TextFormat::RED],
	];
	protected const STACK_DECOR = [
		"b" => TextFormat::BOLD,
		"i" => TextFormat::ITALIC,
		"u" => TextFormat::UNDERLINE,
		"s" => TextFormat::STRIKETHROUGH,
	];

	/** @var string */
	protected $lang;
	/** @var string */
	protected $original;
	/** @var string[] */
	protected $split;
	/** @var string[] */
	protected $delimiters;

	public function __construct(string $lang, string $pattern, Message $message, array $constants){
		$this->lang = $lang;
		$this->original = $pattern;

		$bg = TextFormat::WHITE;
		$colorStack = []; // [isDecor: bool, symbol: string][]
		$temp = "";
		for($i = 0, $iMax = strlen($pattern); $i < $iMax; ++$i){
			if($pattern{$i} === "\\"){
				$char = $pattern{++$i};
				if(!isset(self::ESCAPE_MAP[$char])){
					throw new PluginException("Unknown escape sequence \\{$char}");
				}
				$temp .= self::ESCAPE_MAP[$char];
				continue;
			}

			if($pattern{$i} === "%" || $pattern{$i} === "\$"){
				if($pattern{$i + 1} !== "{"){
					$temp .= $pattern{$i};
					continue;
				}

				$isConstant = $pattern{$i} === "%";

				$i += 2;
				$closePos = strpos($pattern, "}", $i);
				if($closePos === false){
					throw new PluginException(($isConstant ? "Constant" : "Variable") . " token starting at offset $i is never closed");
				}

				$symbol = substr($pattern, $i, $closePos - $i);
				$i = $closePos;

				if($isConstant){
					if(isset($constants[$symbol])){
						$temp .= $constants[$symbol];
						continue;
					}

					if(isset(self::BACKGROUND_COLORS[$symbol])){
						$temp .= self::BACKGROUND_COLORS[$symbol];
						$bg = self::BACKGROUND_COLORS[$symbol];
						continue;
					}

					if(isset(self::STACK_COLORS[$symbol])){
						[$default, $ifNot, $else] = self::STACK_COLORS[$symbol];
						$use = $bg === $ifNot ? $else : $default;
						$colorStack[] = [false, $use];
						$temp .= $use;
						continue;
					}

					if(isset(self::STACK_DECOR[$symbol])){
						$colorStack[] = [true, self::STACK_DECOR[$symbol]];
						$temp .= self::STACK_DECOR;
						continue;
					}

					if($symbol === ""){
						if(empty($colorStack)){
							throw new PluginException("Unmatched %{}");
						}
						[$popIsDecor,] = array_pop($colorStack);
						if($popIsDecor){
							$temp .= TextFormat::RESET;
						}
						$color = $bg;
						foreach($colorStack as [$isDecor, $thisSymbol]){
							if($isDecor && $popIsDecor){
								$temp .= $thisSymbol;
							}else{
								$color = $thisSymbol;
							}
						}
						$temp .= $color;
					}

					continue;
				}

				if(!$message->hasParameter($symbol)){
					throw new PluginException("Translation requires undefined parameter \${{$symbol}}"); // one pair of curly braces is the PHP string interpolation symbol, the other pair is literal.
				}

				$this->split[] = $temp;
				$this->delimiters[] = $symbol;
				$temp = "";

				continue;
			}

			$temp .= $pattern{$i};
		}

		if(!empty($colorStack)){
			throw new PluginException("Unclosed stack constant " . array_search($colorStack[0][1], (new ReflectionClass(TextFormat::class))->getConstants(), true));
		}

		$this->split[] = $temp;
	}

	public function getLang() : string{
		return $this->lang;
	}

	public function getOriginal() : string{
		return $this->original;
	}

	public function translate(array $formattedArgs) : string{
		$output = "";
		for($i = 0, $iMax = count($this->split) - 1; $i < $iMax; ++$i){
			$output .= $this->split[$i] . $formattedArgs[$this->delimiters[$i]];
		}
		return $output . $this->split[$iMax];
	}
}
