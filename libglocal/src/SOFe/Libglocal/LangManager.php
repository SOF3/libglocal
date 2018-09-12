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
use pocketmine\plugin\Plugin;
use SOFe\Libglocal\Graph\GenericGraph;
use SOFe\Libglocal\Math\MathPredicate;
use SOFe\Libglocal\Math\MathRule;
use SOFe\Libglocal\Parser\Ast\AstRoot;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;
use SOFe\Libglocal\Parser\Ast\Message\MessageParentBlock;
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;
use function implode;

class LangManager{
	/** @var Plugin */
	protected $plugin;

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
			$graph->addNode($file->getMessages()->getModule(), $file);
		}
		foreach($this->baseFiles as $after){
			foreach($after->getRequires() as $before){
				if(!$graph->addEdge($before->getTarget(), $after->getMessages()->getModule())){
					$after->throwInit("Dependency module {$before->getTarget()} has not been loaded, required");
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
				$file->throwInit("@@ math rules are not allowed at the global context");
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
		foreach($this->visitMessages(null, $file->getMessages()) as $block){
			$message = new Message($block);
			if($message->getVisibility() === Message::LOCAL){
				$messages =& $this->localMessages[$file->getLang()->getId()];
			}else{
				$messages =& $this->messages;
			}
			if(isset($messages[$block->getId()])){
				throw $file->throwInit("Duplicate definition of message " . $block->getId());
			}
			$messages[$block->getId()] = $message;
		}
	}

	protected function loadTranslations(AstRoot $file) : void{
		// TODO
	}
}
