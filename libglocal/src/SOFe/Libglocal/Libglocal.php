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
use pocketmine\utils\Utils;
use RuntimeException;
use function file_put_contents;
use function fopen;
use function get_class;
use function gettype;
use function is_array;
use function is_dir;
use function is_object;
use function json_decode;
use function mkdir;
use function str_replace;
use function strpos;
use function substr;

final class Libglocal{
	public static function init(Plugin $plugin, string $langDir = "lang/") : LangManager{
		$manager = new LangManager($plugin->getLogger());

		if($plugin instanceof ArgTypeProvider){
			$manager->addTypeProvider($plugin);
		}

		if(!is_dir($plugin->getDataFolder() . "libglocal-default-lib")){
			$plugin->getLogger()->info("Downloading libglocal-default-lib for the first time...");

			self::downloadDefaultLib($plugin);
		}

		if(substr($langDir, -1) !== "/"){
			$langDir .= "/";
		}
		foreach($plugin->getResources() as $name => $file){
			$file = (string) $file;
			if(strpos(str_replace("\\", "/", $name), str_replace("\\", "/", $langDir)) === 0){
				$manager->loadFile($name, fopen($file, "rb"));
			}
		}

		// TODO load online translations
		// TODO load extracted files

		$manager->init();
		return $manager;
	}

	private static function downloadDefaultLib(Plugin $plugin) : void{
		$folder = $plugin->getDataFolder() . "libglocal-default-lib/";
		if(!is_dir($folder) && !mkdir($folder, 0777, true)){
			throw new RuntimeException("Failed to create $folder");
		}

		$files = Utils::getURL("https://api.github.com/repos/SOF3/libglocal/contents/default-lib");
		foreach(json_decode($files) as $file){
			$plugin->getLogger()->debug("Downloading $file->name");
			file_put_contents($folder . $file->name, Utils::getURL($file->download_url));
		}
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
