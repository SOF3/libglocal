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

namespace SOFe\Libglocal\Translation;

use AssertionError;
use SOFe\Libglocal\LangManager;
use SOFe\Libglocal\Message;
use SOFe\Libglocal\Parser\Ast\Literal\Component\ArgRefComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\LiteralComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\LiteralStringComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\MessageRefComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\Component\SpanComponentElement;
use SOFe\Libglocal\Parser\Ast\Literal\LiteralElement;
use SOFe\Libglocal\Parser\Ast\Message\MessageBlock;
use SOFe\Libglocal\Translation\Component\ArgRefResolvedComponent;
use SOFe\Libglocal\Translation\Component\MessageRefResolvedComponent;
use SOFe\Libglocal\Translation\Component\ResolvedComponent;
use SOFe\Libglocal\Translation\Component\SpanResolvedComponent;
use SOFe\Libglocal\Translation\Component\StaticResolvedComponent;

class Translation{
	/** @var Message */
	protected $message;
	/** @var MessageBlock */
	protected $definition;
	/** @var string */
	protected $lang;

	/** @var ResolvedComponent[] */
	protected $components = [];


	public function __construct(Message $message, MessageBlock $block, string $lang){
		$this->message = $message;
		$this->definition = $block;
		$this->lang = $lang;
		$this->components = $this->createResolvedComponents($message->getManager(), $block->getLiteral());
	}

	public function resolve() : void{
		foreach($this->components as $component){
			$component->resolve($this->message->getManager());
		}
	}

	/**
	 * @param LangManager    $manager
	 * @param LiteralElement $element
	 *
	 * @return ResolvedComponent[]
	 */
	public function createResolvedComponents(LangManager $manager, LiteralElement $element) : array{
		$components = [];
		foreach($element->getComponents() as $component){
			$components[] = $this->createResolvedComponent($manager, $component);
		}
		return $components;
	}

	public function createResolvedComponent(LangManager $manager, LiteralComponentElement $element) : ResolvedComponent{
		if($element instanceof LiteralStringComponentElement){
			return new StaticResolvedComponent($element->toString());
		}
		if($element instanceof SpanComponentElement){
			return new SpanResolvedComponent($manager->getConfig()->format($element->getName()),
				$this->createResolvedComponents($manager, $element->getLiteral()));
		}
		if($element instanceof ArgRefComponentElement){
			return new ArgRefResolvedComponent($this, $element);
		}
		if($element instanceof MessageRefComponentElement){
			return new MessageRefResolvedComponent($element);
		}

		throw new AssertionError("Unknown literal component type");
	}

	public function getMessage() : Message{
		return $this->message;
	}
}
