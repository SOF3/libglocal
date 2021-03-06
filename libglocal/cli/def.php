<?php /** @noinspection DisconnectedForeachInstructionInspection */

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

use SOFe\Libglocal\Format\Format;
use SOFe\Libglocal\LangManager;
use SOFe\Libglocal\LibglocalConfig;

require_once __DIR__ . "/../cli-autoload.php";
require_once __DIR__ . "/polyfill.php";

if(!isset($argv[4])){
	throw new InvalidArgumentException(/** @lang text */
		"Usage: php $argv[0] def <base lang file> <src> <fqn> [eol lf|crlf] [spaces <indent size>] [struct \"interface|([abstract|final] class)\"]");
}
$file = $argv[2];
if(!is_file($file)){
	throw new InvalidArgumentException("$file is not a file");
}

$manager = new LangManager(new class implements LibglocalConfig{
	public function format(string $id, Format $context) : Format{
		return Format::create(null);
	}

	public function logDebug(string $message) : void{
		echo "[DEBUG] $message\n";
	}

	public function logInfo(string $message) : void{
		echo "[INFO] $message\n";
	}

	public function logNotice(string $message) : void{
		echo "[NOTICE] $message\n";
	}

	public function logWarning(string $message) : void{
		echo "[WARNING] $message\n";
	}
});
$manager->loadLang($file, file_get_contents($file));
$manager->init();

$EOL = PHP_EOL;
$INDENT = "\t";
$STRUCT = "interface";

for($i = 5; isset($argv[$i + 1]); ++$i){
	if($argv[$i] === "eol"){
		$EOL = $argv[++$i] === "crlf" ? "\r\n" : "\n";
	}elseif($argv[$i] === "spaces"){
		$INDENT = str_repeat(" ", (int) $argv[++$i]);
	}elseif($argv[$i] === "struct"){
		$STRUCT = $argv[++$i];
	}
}

$fqn = explode("\\", $argv[4]);
if($argv[3] === "stdout"){
	$phpFile = "php://stdout";
}else{
	$phpFile = realpath($argv[3]) . "/" . str_replace("\\", "/", $argv[4]) . ".php";
	@mkdir(dirname($phpFile), 0777, true);
}

$php = fopen($phpFile, "wb");
fwrite($php, "<?php{$EOL}{$EOL}");
fwrite($php, "/*{$EOL}");
fwrite($php, " * libglocal message ID constant file{$EOL}");
fwrite($php, " *{$EOL}");
fwrite($php, " * This file is automatically generated by libglocal-def{$EOL}");
fwrite($php, " */{$EOL}{$EOL}");
fwrite($php, "declare(strict_types=1);{$EOL}{$EOL}");
fwrite($php, "namespace " . implode("\\", array_slice($fqn, 0, -1)) . ";{$EOL}{$EOL}");
fwrite($php, "{$STRUCT} " . array_slice($fqn, -1)[0] . "{");

/** @var Message $message */
foreach($manager->getMessages() as $message){
	fwrite($php, "{$EOL}{$INDENT}/**{$EOL}");
	if($message->getDoc() !== null){
		foreach(explode("\n", $message->getDoc()) as $line){
			fwrite($php, "{$INDENT} * " . trim($line) . $EOL);
		}
		fwrite($php, "{$INDENT} *{$EOL}");
	}
	fwrite($php, "{$INDENT} * <h3>Base translation</h3>{$EOL}");
	fwrite($php, "{$INDENT} * <blockquote>{$EOL}");
	fwrite($php, "{$INDENT} *     <p>");
	foreach($message->getBaseTranslation()->getComponents() as $component){
		fwrite($php, $component->toHtml());
	}
	fwrite($php, "</p>{$EOL}");
	fwrite($php, "{$INDENT} * </blockquote>{$EOL}");
	if(!empty($message->getArgs())){
		fwrite($php, "{$INDENT} * <h3>Arguments:</h3>{$EOL}");
		foreach($message->getArgs() as $argName => $arg){
			fwrite($php, "{$INDENT} * - `{$argName}`: {$arg->getType()->getName()}</li>{$EOL}");
		}
	}
	fwrite($php, "{$INDENT} */{$EOL}");
	fwrite($php, "{$INDENT}public const " . strtoupper(preg_replace('/[^A-Za-z0-9]+/', "_", $message->getId())) . " = " .
		json_encode($message->getId()) . ";{$EOL}");
}

fwrite($php, "}{$EOL}");
fclose($php);
