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

namespace SOFe\Libglocal\Argument;

use SOFe\Libglocal\Parser\Ast\Attribute\AttributeValueElement;
use SOFe\Libglocal\Parser\Ast\Constraint\ConstraintBlock;
use SOFe\Libglocal\Parser\Ast\Modifier\ArgModifierBlock;
use SOFe\Libglocal\Parser\Ast\Modifier\DocModifierBlock;

class Argument{
	/** @var string */
	protected $name;
	/** @var string[] */
	protected $docs;
	/** @var ArgType */
	protected $type;

	/** @var ConstraintBlock[] */
	private $constraintBlocks = [];
	/** @var null|AttributeValueElement */
	private $defaultBlock;

	public function __construct(ArgModifierBlock $block){
		$this->name = $block->getName();

		$docBuffer = "";
		foreach($block->getConstraints() as $doc){
			if(!($doc instanceof DocModifierBlock)){
				$this->constraintBlocks[] = $doc;
				continue;
			}
			if($doc->getValue() === null || empty($doc->getValue()->toString())){
				if(!empty($docBuffer)){
					$this->docs[] = $docBuffer;
				}
				$docBuffer = "";
			}else{
				$docBuffer .= $doc->getValue()->toString() . " ";
			}
		}
		if(!empty($docBuffer)){
			$this->docs[] = $docBuffer;
		}

		$type = $block->getType();
		$flags = $block->getTypeFlags();
		$this->type = ArgType::create($type, $flags);
		if($this->type === null){
			throw $block->throwInit("Invalid argument type $type");
		}

		$this->defaultBlock = $block->getDefault();
	}

	public function init() : void{
		foreach($this->constraintBlocks as $constraint){
			$this->type->acceptConstraint($constraint);
		}
		$this->type->setDefault($this->defaultBlock);
	}

	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string[]
	 */
	public function getDocs() : array{
		return $this->docs;
	}

	public function getType() : ArgType{
		return $this->type;
	}
}
