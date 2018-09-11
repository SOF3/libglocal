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
use function implode;
use pocketmine\plugin\Plugin;
use RuntimeException;
use SOFe\Libglocal\Graph\GenericGraph;
use SOFe\Libglocal\Math\MathPredicate;
use SOFe\Libglocal\Math\MathRule;
use SOFe\Libglocal\Parser\Ast\LibglocalFile;
use SOFe\Libglocal\Parser\Ast\Message\MessageParentBlock;
use SOFe\Libglocal\Parser\Lexer\LibglocalLexer;

class LangManager{
	/** @var Plugin */
	protected $plugin;

	/** @var LibglocalFile[] */
	protected $baseFiles = [];
	/** @var LibglocalFile[] */
	protected $overrideFiles = [];

	/** @var bool[] */
	protected $modules = [];
	/** @var Message[] */
	protected $messages = [];


	/** @var MathRule[][] */
	protected $mathRules = [];


	public function loadLang(string $data) : void{
		$lexer = new LibglocalLexer($data);
		$file = new LibglocalFile($lexer);
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
					throw new InitException("Dependency module {$before->getTarget()} has not been loaded");
				}
			}
		}

		$sorted = $graph->topSort($badModules);
		if($sorted === null){
			throw new InitException("Cyclic dependency detected among modules " . implode(", ", $badModules));
		}

		foreach($sorted as $file){
			$this->register($file);
		}
	}

	protected function register(LibglocalFile $file) : void{
		$lang = $file->getLang()->getId();

		foreach($file->getMathRules() as $rule){
			if($rule->isRestriction()){
				throw new InitException("@@ math rules are not allowed at the global context");
			}


			$predicates = [];
			foreach($rule->getPredicates() as $predicate){
				$predicates[] = new MathPredicate($predicate->getMod(), $predicate->getComparator(), $predicate->getOperand());
			}
			$this->mathRules[$lang][] = new MathRule($rule->getName(), $predicates);
		}
	}

	protected function visitMessages(string $prefix, MessageParentBlock $block) : Generator{
		foreach($block->getGroups() as $group){
			yield from $this->visitMessages("$prefix.{$group->getId()}", $group);
		}
		foreach($block->getMessages() as $message){
			yield "{$prefix}.{$message->getId()}" => $message;
		}
	}
}
