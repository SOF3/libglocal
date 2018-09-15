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
use SOFe\Libglocal\Graph\GenericGraph;
use SOFe\Libglocal\Math\MathPredicate;
use SOFe\Libglocal\Math\MathRule;
use SOFe\Libglocal\Parser\Ast\AstRoot;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageParentBlock;
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;
use SOFe\Libglocal\Translation\Translation;
use function implode;

class LangManager{
	/** @var LibglocalConfig */
	protected $config;

	/** @var AstRoot[] */
	protected $baseFiles = [];
	/** @var AstRoot[] */
	protected $overrideFiles = [];

	/** @var bool[] */
	protected $modules = [];
	/** @var Message[] */
	protected $messages = [];
	/** @var Message[][] */
	protected $localMessages = [];


	/** @var MathRule[][] */
	protected $mathRules = [];


	public function getConfig() : LibglocalConfig{
		return $this->config;
	}

	public function loadLang(string $fileName, string $data) : void{
		$lexer = new LibglocalLexer($fileName, $data);
		$file = new AstRoot($lexer);
		if($file->getLang()->isBase()){
			$this->baseFiles[] = $file;
		}else{
			$this->overrideFiles[] = $file;
		}
	}

	public function init() : void{
		$graph = new GenericGraph();
		foreach($this->baseFiles as $file){
			if(!$graph->addNode($module = $file->getMessages()->getModule(), $file)){
				throw $file->getMessages()->throwInit("Duplicate definition of module $module. There can only be one base file for each module.");
			}
		}
		foreach($this->baseFiles as $after){
			foreach($after->getRequires() as $before){
				if(!$graph->addEdge($before->getTarget(), $after->getMessages()->getModule())){
					throw $before->throwInit("Dependency module {$before->getTarget()} has not been loaded, required");
				}
			}
		}

		$sorted = $graph->topSort($badModules);
		if($sorted === null){
			throw new InitException("Cyclic dependency detected among modules " . implode(", ", $badModules), null);
		}

		foreach($sorted as $file){
			$this->registerMathRules($file);
		}
		foreach($sorted as $file){
			$this->registerMessages($file);
		}
		foreach($this->messages as $message){
			$message->resolveBase();
		}

		foreach($this->overrideFiles as $file){
			$this->loadTranslations($file);
		}
	}

	protected function register(AstRoot $file) : void{
		$this->registerMathRules($file);
		$this->registerMessages($file);
	}

	protected function visitMessages(?string $parent, MessageParentBlock $block) : Generator{
		$prefix = $parent !== null ? "{$parent}." : "";
		foreach($block->getGroups() as $group){
			yield from $this->visitMessages($prefix . $group->getId(), $group);
		}
		foreach($block->getMessages() as $message){
			yield $prefix . $message->getId() => $message;
		}
	}

	protected function registerMathRules(AstRoot $file) : void{
		$lang = $file->getLang()->getId();

		foreach($file->getMathRules() as $rule){
			if($rule->isRestriction()){
				$rule->throwInit("@@ math rules are not allowed at the global context");
			}


			$predicates = [];
			foreach($rule->getPredicates() as $predicate){
				$predicates[] = new MathPredicate($predicate->getMod(), $predicate->getComparator(), $predicate->getOperand());
			}
			$this->mathRules[$lang][] = new MathRule($rule->getName(), $predicates);
		}
	}

	protected function registerMessages(AstRoot $file) : void{
		/** @var MessageBlock $block */
		foreach($this->visitMessages(null, $file->getMessages()) as $id => $block){
			$message = new Message($this, $id, $block);
			if($message->getVisibility() === Message::LOCAL){
				$this->registerLocalMessage($file->getLang()->getId(), $id, $block, $message);
			}else{
				$this->registerGlobalMessage($id, $block, $message);
			}
		}
	}

	protected function registerLocalMessage(string $lang, string $id, MessageBlock $block, Message $message) : void{
		if(isset($this->messages[$id])){
			throw $block->throwInit("Definition of local message $id masks global message of the same name");
		}
		if(isset($this->localMessages[$lang][$id])){
			throw $block->throwInit("Duplicate definition of local message $id. Only the base definition of local messages should be qualified with \"local:\".");
		}
		$this->localMessages[$lang][$id] = $message;
	}

	protected function registerGlobalMessage(string $id, MessageBlock $block, Message $message) : void{
		if(isset($this->messages[$id])){
			throw $block->throwInit("Duplicate definition of global message $id");
		}

		foreach($this->localMessages as $lang => $messages){
			if(isset($messages[$id])){
				throw $block->throwInit("Definition of global message $id will be masked by local message $id in $lang");
			}
		}

		$this->messages[$id] = $message;
	}

	protected function loadTranslations(AstRoot $file) : void{
		$lang = $file->getLang()->getId();
		$localMessages = [];
		/** @var MessageBlock $block */
		foreach($this->visitMessages(null, $file->getMessages()) as $id => $block){
			if(Message::detectVisibility($block) === Message::LOCAL){
				$message = new Message($this, $id, $block);
				$this->registerLocalMessage($lang, $id, $block, $message);
				$localMessages[] = $message;
			}
		}
		foreach($localMessages as $message){
			$message->resolveBase();
		}
		foreach($this->visitMessages(null, $file->getMessages()) as $id => $block){
			if(Message::detectVisibility($block) !== Message::LOCAL){
				$message = $this->localMessages[$lang][$id] ?? $this->messages[$id] ?? null;
				if($message === null){
					throw $block->throwInit("Undefined message $id. Non-local declarations in non-base files must override a message.");
				}

				$translation = new Translation($message, $block, $lang);
				$message->putTranslation($translation);
				$translation->resolve();
			}
		}
	}
}
