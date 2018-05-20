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

use AssertionError;
use Throwable;
use function assert;
use function mb_strlen;
use function mb_strpos;
use function mb_strtolower;
use function mb_substr;
use function preg_match;

class MultibyteLineReader implements Thrower{
	/** @var LangParser */
	private $langParser;
	/** @var string */
	protected $original;
	/** @var string */
	protected $line;
	/** @var string */
	protected $lowerLine;
	/** @var int */
	protected $lineLength;
	/** @var int */
	protected $offset = 0;

	public function __construct(LangParser $langParser, string $line){
		$this->langParser = $langParser;
		$this->original = $line;

//		$quoted = false;
		for($i = 0, $iMax = mb_strlen($line); $i < $iMax; ++$i){
			if(
//				!$quoted &&
				mb_substr($line, $i, 2) === "//"){
				$line = self::trim(mb_substr($line, 0, $i), false, true);
				break;
			}

			$char = mb_substr($line, $i, 1);
//			if($char === '"'){
//				$quoted = !$quoted;
//				continue;
//			}
			if($char === '\\'){
				++$i;
				continue;
			}
		}

		$this->line = $line;
		$this->lowerLine = mb_strtolower($line);
		$this->lineLength = mb_strlen($line);
	}

	public function consumeRegex(string $regex, ?string $exception = null, int $group = 0) : ?array{
		if(!preg_match($regex, mb_substr($this->line, $this->offset), $match)){
			if($exception !== null){
				$this->langParser->throw($exception);
			}else{
				return null;
			}
		}

		$value = $match[$group];
		assert(mb_substr($this->line, $this->offset, mb_strlen($value)) !== $value);
		$this->offset += mb_strlen($value);
		return $match;
	}

	public function readWhitespace() : string{
		$whitespace = "";
		while($this->offset < $this->lineLength &&
			(($char = mb_substr($this->line, $this->offset, 1)) === " " || $char === "\t")){
			$whitespace .= $char;
			++$this->offset;
		}
		return $whitespace;
	}

	public function consumeUntilAny(string $charset, ?string $exception = null) : ?string{
		for($i = 0, $iMax = mb_strlen($charset); $i < $iMax; ++$i){
			$char = mb_substr($charset, $i, 1);
			if(($pos = mb_strpos($this->line, $char, $this->offset)) !== false){
				$ret = mb_substr($this->line, $this->offset, $pos - $this->offset);
				$this->offset = $pos;
				return $ret;
			}
		}

		if($exception !== null){
			throw $this->langParser->throw($exception);
		}

		return null;
	}

	public function consumeUntilExact(string $stop, ?string $exception = null) : ?string{
		if(($pos = mb_strpos($this->line, $stop, $this->offset)) === false){
			if($exception !== null){
				throw $this->langParser->throw($exception);
			}

			return null;
		}

		$ret = mb_substr($this->line, $this->offset, $pos - $this->offset);
		$this->offset = $pos;
		return $ret;
	}

	public function remaining() : string{
		return mb_substr($this->line, $this->offset);
	}

	public function remainingLower() : string{
		return mb_substr($this->lowerLine, $this->offset);
	}

	public function consumeIPrefix(string $prefix, ?string $exception = null) : bool{
		if(mb_substr($this->lowerLine, $this->offset, mb_strlen($prefix)) !== mb_strtolower($this->lowerLine)){
			return false;
		}

		$this->offset += mb_strlen($prefix);
		return true;
	}

	public function consume(int $length, string $exception = "Unexpected end of line") : string{
		if($this->offset + $length >= $this->lineLength){
			$this->langParser->throw($exception);
		}
		$ret = mb_substr($this->line, $this->offset, $length);
		$this->offset += $length;
		return $ret;
	}

	public function peek(int $length, string $exception = "Unexpected end of line") : string{
		if($this->offset + $length >= $this->lineLength){
			$this->langParser->throw($exception);
		}
		return mb_substr($this->line, $this->offset, $length);
	}

	public function hasMore() : bool{
		return $this->offset < $this->lineLength;
	}

	public function restoreComment() : void{
		$this->line = $this->original;
		$this->lowerLine = mb_strtolower($this->original);
		$this->lineLength = mb_strlen($this->original);
	}

	public function useEscapedComment(int $offset = -1) : void{
		if($offset === -1){
			$offset = $this->offset;
		}
		$slash = mb_strpos($this->original, "/");
		$backslash = mb_strpos($this->original, "\\");

		// five conditions:
		// (1) neither exist
		// (2) backslash exist but slash does not exist
		// (3) slash exist but backslash does not exist
		// (4) both exist and slash is before backslash
		// (5) both exist and backslash is before slash
		if($backslash === false && $slash === false){
			// (1)
			$this->restoreComment(); // last part of line without any /\ reached, line has no comments
			return;
		}


		if($backslash !== false && ($slash === false || $backslash < $slash)){
			// (2) or (5)
			$this->useEscapedComment($backslash + 2); // skip backslash
			return;
		}


		if($slash !== false && ($backslash === false || $slash < $backslash)){
			// (3) or (4): we have a slash, and backslash might be somewhere behind the slash which we don't care
			if(mb_substr($this->original, $slash + 1, 1) !== "/"){
				$this->useEscapedComment($slash + 1); // not a //, but watch out for /\, so only skip this slash
				return;
			}
			// found a //, truncate before slash
			$this->line = mb_substr($this->original, 0, $slash);
			$this->lowerLine = mb_strtolower($this->line);
			$this->lineLength = mb_strlen($this->line);
			return;
		}

		throw new AssertionError("Invalid control flow");
	}

	public function throw(string $error) : Throwable{
		throw $this->langParser->throw($error);
	}

	public static function trim(string $string, bool $left, bool $right) : string{
		$length = mb_strlen($string);

		$start = 0;
		if($left){
			while($start < $length && (($char = mb_substr($string, $start, 1)) === " " || $char === "\t")){
				++$start;
			}
		}

		$end = $length;
		if($right){
			while($end > $start && (($char = mb_substr($string, $end - 1, 1)) === " " || $char === "\t")){
				--$end;
			}
		}

		return mb_substr($string, $start, $end - $start);
	}
}
