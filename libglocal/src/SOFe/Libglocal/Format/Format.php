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

namespace SOFe\Libglocal\Format;

use pocketmine\utils\TextFormat;

class Format{
	/** @var string|null */
	public $color = null;
	public $b = false;
	public $i = false;
	public $u = false;
	public $s = false;

	public function add(Format $that) : Format{
		$new = new Format;
		$new->color = $that->color;
		$new->b = $this->b || $that->b;
		$new->i = $this->i || $that->i;
		$new->u = $this->u || $that->u;
		$new->s = $this->s || $that->s;
		return $new;
	}

	public function raw() : string{
		$output = "";
		if($this->color !== null){
			$output .= $this->color;
		}
		if($this->b){
			$output .= TextFormat::BOLD;
		}
		if($this->i){
			$output .= TextFormat::ITALIC;
		}
		if($this->u){
			$output .= TextFormat::UNDERLINE;
		}
		if($this->s){
			$output .= TextFormat::STRIKETHROUGH;
		}
		return $output;
	}

	public function transition(?Format $from) : string{
		if($from === null){
			return $this->raw();
		}

		$output = "";

		if(
			$from->b && !$this->b &&
			$from->i && !$this->i &&
			$from->u && !$this->u &&
			$from->s && !$this->s){
			$output .= TextFormat::RESET;
		}

		if($this->color !== null && $this->color !== $from->color){
			$output .= $this->color;
		}

		if(!$from->b && $this->b){
			$output .= TextFormat::BOLD;
		}
		if(!$from->i && $this->i){
			$output .= TextFormat::ITALIC;
		}
		if(!$from->u && $this->u){
			$output .= TextFormat::UNDERLINE;
		}
		if(!$from->s && $this->s){
			$output .= TextFormat::STRIKETHROUGH;
		}

		return $output;
	}

	public static function format(FormattedString $string) : string{
		$tokens = $string->tokenize(null);
		$output = "";

		$previous = null;
		foreach($tokens as $token){
			$output .= $token->getFormat()->transition($previous);
			$output .= $token->getValue();
		}

		return $output;
	}
}
