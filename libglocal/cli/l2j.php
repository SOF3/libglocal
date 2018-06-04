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

use SOFe\Libglocal\CLI\DummyLogger;
use SOFe\Libglocal\LangManager;

require_once __DIR__ . "/../cli-autoload.php";
require_once __DIR__ . "/polyfill.php";

if(!isset($argv[2])){
	throw new InvalidArgumentException(/** @lang text */
		"Usage: php $argv[0] def <lang files>...");
}

$manager = new LangManager(new DummyLogger);
for($i = 2; $i < $argc; ++$i){
	$file = $argv[$i];
	if(!is_file($file)){
		throw new InvalidArgumentException("$file is not a file");
	}
	$manager->loadFile($file, fopen($file, "rb"));
}

$manager->init(false);

echo json_encode($manager->getMessages(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
