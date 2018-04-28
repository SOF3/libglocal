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

namespace SOFe\Libglocal\Parameter;

use InvalidArgumentException;
use pocketmine\plugin\PluginException;
use SOFe\Libglocal\MessageParameter;
use function explode;
use function fmod;
use function is_numeric;
use function sprintf;
use function strlen;
use function strpos;
use function substr;

class QuantityParameter implements MessageParameter{
	public const EQ = '=';
	public const GT = '>';
	public const GE = '>=';
	public const LT = '<';
	public const LE = '<=';
	public const MOD = "%";
	public const PREFIX = '^';
	public const SUFFIX = '$';

	protected $filters = [];
	protected $default;

	public function __construct(array $config){
		foreach($config as $filter => $pattern){
			if($filter === "type"){
				continue;
			}

			if($filter === "default"){
				$this->default = $pattern;
				continue;
			}

			if($filter{0} === self::PREFIX || $filter{0} === self::SUFFIX){
				$this->filters[] = [$filter{0}, substr($filter, 1), $pattern];
				continue;
			}

			if(strlen($filter) > 2 && $filter{1} === "="){
				if($filter{0} === ">" || $filter{0} === "<"){
					$operator = $filter{0} === ">" ? self::GE : self::LE;
					$this->filters[] = [$operator, substr($filter, 2), $pattern];
					continue;
				}

				throw new PluginException("Unknown filter pattern " . $filter);
			}

			if($filter{0} === self::LT || $filter{0} === self::GT || $filter{0} === self::EQ){
				$this->filters[] = [$filter{0}, substr($filter, 1), $pattern];
				continue;
			}

			if($filter{0} === self::MOD){
				$args = explode("=", substr($filter, 1));
				if(!isset($args[1])){
					throw new PluginException("Modulus filters should contain two parameters delimited by equal sign");
				}
				if(!is_numeric($args[0]) || !is_numeric($args[1])){
					throw new PluginException("Syntax: %a=b, where a and b are numbers");
				}
				$this->filters[] = [self::MOD, (float) $args[0], (float) $args[1], $pattern];
			}

			if(!is_numeric($filter)){
				throw new PluginException("Unknown filter pattern " . $filter);
			}
			$this->filters[] = [self::EQ, $filter, $pattern];
		}
	}

	public function acceptValue($value) : string{
		if(!is_numeric($value)){
			throw new InvalidArgumentException("This parameter only accepts numeric values");
		}

		foreach($this->filters as $args){
			switch($args[0]){
				case self::PREFIX:
					if(strpos($value, $args[1]) === 0){
						return sprintf($args[2], $value);
					}
					continue 2; // wtf php
				case self::SUFFIX:
					if(substr($value, -strlen($args[1])) === $args[1]){
						return sprintf($args[2], $value);
					}
					continue 2;
				case self::EQ:
					/** @noinspection TypeUnsafeComparisonInspection */
					if($args[1] == $value){
						return sprintf($args[2], $value);
					}
					continue 2;
				case self::GT:
					if($args[1] > $value){
						return sprintf($args[2], $value);
					}
					continue 2;
				case self::LT:
					if($args[1] < $value){
						return sprintf($args[2], $value);
					}
					continue 2;
				case self::GE:
					if($args[1] >= $value){
						return sprintf($args[2], $value);
					}
					continue 2;
				case self::LE:
					if($args[1] <= $value){
						return sprintf($args[2], $value);
					}
					continue 2;
				case self::MOD:
					if(fmod($value, $args[1]) === $args[2]){
						return sprintf($args[2], $value);
					}
					continue 2;
			}
		}

		if(!isset($this->default)){
			throw new PluginException("A value not matching any of the filters was passed, but default filter is not defined.");
		}

		return sprintf($this->default, $value);
	}
}
