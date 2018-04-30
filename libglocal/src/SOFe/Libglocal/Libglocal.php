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
use pocketmine\plugin\PluginException;
use function fclose;
use function fgets;
use function is_string;
use function trim;

final class Libglocal{
	public static function init(Plugin $plugin, string $langDir = "lang/") : LanguageManager{
		$index = $plugin->getResource($langDir . "index.txt");
		if($index === null){
			throw new PluginException("resources/{$langDir}index.txt is missing in " . $plugin->getName());
		}

		$mgr = new LanguageManager($plugin);
		if($plugin instanceof MessageParameterFactory){
			$mgr->addParameterFactory($plugin);
		}

		$lineNumber = 0;
		while(is_string($line = fgets($index))){
			++$lineNumber;
			$line = trim($line);
			if(!empty($line) && $line{0} !== "#"){
				$fh = $plugin->getResource($fileName = $langDir . $line . ".yml");
				if($fh === null){
					throw new PluginException("resources/{$fileName} is missing in {$plugin->getName()}, defined on line $lineNumber of resources/{$langDir}index.txt");
				}
				$mgr->loadFile($line, $fh, $fileName);
				fclose($fh);
			}
		}

		return $mgr;
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
}
