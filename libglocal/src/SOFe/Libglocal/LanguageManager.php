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

use Generator;
use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginException;
use SOFe\Libglocal\Parameter\DefaultMessageParameterFactory;
use function gettype;
use function is_array;
use function is_string;
use function stream_get_contents;
use function yaml_parse;

class LanguageManager{
	/** @var Plugin */
	protected $plugin;
	/** @var MessageParameterFactory[] */
	protected $parameterFactories = [];

	/** @var string */
	protected $base;
	/** @var Message[] */
	protected $translations = [];

	public function __construct(Plugin $plugin){
		$this->plugin = $plugin;
		$this->parameterFactories[] = new DefaultMessageParameterFactory();
	}

	public function addParameterFactory(MessageParameterFactory $factory) : void{
		$this->parameterFactories[] = $factory;
	}

	public function loadFile(string $lang, $fh, string $fileName) : void{
		$yml = yaml_parse(stream_get_contents($fh));

		if(isset($yml["language"]) && $yml["language"] !== $lang){
			throw new PluginException("$fileName declares language {$yml["language"]}, expected $lang");
		}

		if($base = !isset($this->base)){
			if(!($yml["base"] ?? false)){
				throw new PluginException("The base language must be the first loaded file");
			}
			$this->base = $lang;
		}

		if(!isset($yml["messages"])){
			throw new PluginException("The main messages should be defined in the \"messages\" attribute");
		}

		if($base){
			/** @var Message $message */
			foreach($this->defineMessages($lang, $yml["messages"]) as $message){
				$this->translations[$message->getId()] = $message;
			}
		}

		$this->loadMessages($lang, $yml["constants"] ?? [], $yml["messages"]);
	}

	public function hasTranslation(string $key) : bool{
		return isset($this->translations[$key]);
	}

	public function translate(string $lang, string $key, array $args) : string{
		if(!isset($key)){
			throw new InvalidArgumentException("Undefined translation $key");
		}

		$tr = $this->translations[$key];
		$formattedArgs = [];
		foreach($tr->getParameters() as $paramName => $param){
			if(!isset($args[$paramName])){
				throw new InvalidArgumentException("Error translating $key: Required argument $paramName is missing");
			}

			try{
				$formattedArgs[] = $param->acceptValue($args[$paramName]);
			}catch(InvalidArgumentException $e){
				throw new InvalidArgumentException("Error translating $key (parameter {$paramName}): " . $e->getMessage());
			}
		}

		return $tr->getTranslation($lang)->translate($formattedArgs);
	}

	private function defineMessages(string $lang, array $array, string $idStack = "") : Generator{
		foreach($array as $id => $value){
			if(is_array($value)){
				if(!isset($value["_"])){
					yield from $this->defineMessages($lang, $value, $idStack . $id . ".");
					continue;
				}

				try{
					$parameters = isset($value["\$"]) ? $this->createParametersFromDollar($value["\$"], $idStack . $id) : [];
				}catch(PluginException | InvalidArgumentException $e){
					throw new PluginException("Problem analyzing the parameter signature of translation $id for $lang: " . $e->getMessage());
				}
				$message = new Message($idStack . $id, $parameters);
				yield $message->getId() => $message;
				continue;
			}

			if(is_string($value)){
				$message = new Message($idStack . $id, []);
				yield $message->getId() => $message;
				continue;
			}

			throw new PluginException("Unexpected value type \"" . gettype($value) . "\" for {$idStack}{$id}");
		}
	}

	private function createParameter(string $type, array $config = []) : MessageParameter{
		foreach($this->parameterFactories as $factory){
			$param = $factory->createParameter($type, $config);
			if($param !== null){
				return $param;
			}
		}

		throw new PluginException("Unsupported parameter type \"$type\"");
	}

	private function createParametersFromDollar($dollar, string $id) : array{
		if(is_string($dollar)){
			return [$dollar => $this->createParameter("string")];
		}

		if(is_array($dollar)){
			$parameters = [];
			if(Libglocal::isLinearArray($dollar)){
				foreach($dollar as $name){
					$parameters[$name] = $this->createParameter("string");
				}
				return $parameters;
			}

			foreach($dollar as $name => $config){
				if(is_string($config)){
					$parameters[$name] = $this->createParameter($config);
				}elseif(is_array($config)){
					if(!isset($config["type"])){
						throw new PluginException("Parameter declaration should contain type for \$ in $id");
					}
					$parameters[$name] = $this->createParameter($config["type"], $config);
				}
			}
			return $parameters;
		}

		throw new PluginException("Expected string or array, got " . gettype($dollar));
	}

	private function loadMessages(string $lang, array $constants, array $messages, string $idStack = "") : void{
		foreach($messages as $id => $value){
			if(is_array($value)){
				if(!isset($value["_"])){
					$this->loadMessages($lang, $constants, $value, $idStack . $id . ".");
					continue;
				}

				$this->loadTranslation($lang, $constants, $value["_"], $idStack . $id);
				continue;
			}

			if(is_string($value)){
				$this->loadTranslation($lang, $constants, $value, $idStack . $id);
				continue;
			}

			throw new PluginException("Expected string or array, got " . gettype($value) . " while loading {$idStack}{$id} of $lang");
		}
	}

	private function loadTranslation(string $lang, array $constants, string $translation, string $id) : void{
		if(!isset($this->translations[$id])){
			$this->plugin->getLogger()->warning("$lang declares $id, which is not defined in the base language file");
			return;
		}
		$tr = new Translation($lang, $translation, $this->translations[$id], $constants);
		$this->translations[$id]->addTranslation($tr);
	}
}
