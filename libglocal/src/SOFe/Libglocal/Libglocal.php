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

use pocketmine\plugin\Plugin;
use function fopen;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function preg_match;
use function preg_quote;
use function substr;

final class Libglocal{
	public static function init(Plugin $plugin, string $langDir = "lang/") : LangManager{
		if(substr($langDir, -1) !== "/"){
			$langDir .= "/";
		}

		$manager = new LangManager($plugin);

		if($plugin instanceof ArgTypeProvider){
			$manager->addTypeProvider($plugin);
		}

		// TODO download stdlib

		foreach($plugin->getResources() as $file){
			$file = (string) $file;
			if(preg_match('~/' . preg_quote($langDir, '~') . '[^/]+.lang$/~',  $file)){
				$manager->loadFile($file, fopen($file, "rb"));
			}
		}

		$manager->init();
		return $manager;
	}

	public static function isLinearArray(array $array) : bool{
		$i = 0;
		foreach($array as $key => $value){
			if($key !== ($i++)){
				return false;
			}
		}
		return true;
	}

	public static function printVar($var) : string{
		if(is_object($var)){
			return get_class($var);
		}
		if(is_array($var)){
			return "array";
		}
		return gettype($var) . "({$var})";
	}
}
