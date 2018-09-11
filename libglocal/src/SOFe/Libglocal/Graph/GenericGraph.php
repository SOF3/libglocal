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

namespace SOFe\Libglocal\Graph;

use RuntimeException;
use function array_filter;
use function array_shift;
use function count;

class GenericGraph{
	/** @var GenericNode[] */
	protected $nodes = [];

	/** @var bool */
	protected $invalid = false;

	public function addNode($identifier, $value) : void{
		$node = new GenericNode($identifier, $value);
		$this->nodes[$node->identifier] = $node;
	}

	public function addEdge($before, $after) : bool{
		if(!isset($this->nodes[$before], $this->nodes[$after])){
			return false;
		}
		$this->nodes[$before]->out[$after] = $this->nodes[$after];
		$this->nodes[$after]->in[$before] = $this->nodes[$before];
		return true;
	}


	/**
	 * Implements a topological sort using Kahn's algorithm
	 *
	 * @param array $badIdentifiers
	 * @param array $badValues
	 *
	 * @return array|null Returns an array of node values sorted by breadth-first order
	 */
	public function topSort(array &$badIdentifiers = [], array &$badValues = []) : ?array{
		if($this->invalid){
			throw new RuntimeException("Each GenericGraph instance can only be sorted once");
		}
		$this->invalid = true;

		$out = [];
		$heads = array_filter($this->nodes, function(GenericNode $node) : bool{
			return empty($node->in);
		});

		while(!empty($heads)){
			/** @var GenericNode $head */
			$head = array_shift($heads);
			$out[] = $head->value;
			foreach($head->out as $child){
				unset($child->in[$head->identifier]);
				if(empty($child->in)){
					$heads[] = $child;
				}
			}
		}

		if(count($out) === count($this->nodes)){
			return $out;
		}

		$badIdentifiers = [];
		$badValues = [];
		foreach($this->nodes as $node){
			if(!empty($node->in)){
				$badIdentifiers[] = $node->identifier;
				$badValues[] = $node->value;
			}
		}
		return null;
	}
}
