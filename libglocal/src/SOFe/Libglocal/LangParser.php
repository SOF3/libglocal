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

use pocketmine\utils\TextFormat;
use SOFe\Libglocal\ArgDefault\ArgDefault;
use SOFe\Libglocal\ArgDefault\ArgFallbackDefault;
use SOFe\Libglocal\ArgDefault\NumberLiteralDefault;
use SOFe\Libglocal\ArgDefault\StringLiteralDefault;
use SOFe\Libglocal\Component\ArgRefTranslationComponent;
use SOFe\Libglocal\Component\ComponentHolder;
use SOFe\Libglocal\Component\LiteralTranslationComponent;
use SOFe\Libglocal\Component\MessageRefTranslationComponent;
use SOFe\Libglocal\Component\StackSpanTranslationComponent;
use SOFe\Libglocal\Component\StyleSpanTranslationComponent;
use SOFe\Libglocal\Component\TranslationComponent;
use Throwable;
use function array_merge;
use function array_shift;
use function array_slice;
use function array_unshift;
use function assert;
use function fclose;
use function feof;
use function fgets;
use function implode;
use function in_array;
use function max;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
use function preg_match;
use function trim;

class LangParser implements Thrower{
	private const SCOPE_ROOT = 1;
	private const SCOPE_GROUP = 2;
	private const SCOPE_MESSAGE = 3;
	private const SCOPE_MODIFIER = 4;
	private const SCOPE_CONSTRAINT = 5;

	private const SPAN_STYLES = [
		"info" => TextFormat::WHITE,
		"success" => TextFormat::GREEN,
		"notice" => TextFormat::AQUA,
		"warn" => TextFormat::YELLOW,
		"warning" => TextFormat::YELLOW,
		"error" => TextFormat::RED,
	];
	private const SPAN_STACKS = [
		"hl1" => [true, TextFormat::LIGHT_PURPLE, null, null],
		"hl2" => [true, TextFormat::GOLD, TextFormat::YELLOW, TextFormat::RED],
		"hl3" => [true, TextFormat::AQUA, TextFormat::AQUA, TextFormat::YELLOW],
		"hl4" => [true, TextFormat::GREEN, TextFormat::GREEN, TextFormat::RED],
		"b" => [false, TextFormat::BOLD, null, null, null],
		"i" => [false, TextFormat::ITALIC, null, null, null],
		"u" => [false, TextFormat::UNDERLINE, null, null, null],
		"s" => [false, TextFormat::STRIKETHROUGH, null, null, null],
	];

	/** @var LangManager */
	protected $manager;
	/** @var string */
	protected $fileHumanName;
	/** @var resource */
	protected $fh;

	protected $line = 0;
	protected $scope = self::SCOPE_ROOT;
	protected $indentStack = [];
	protected $idStack = [];
	/** @var Message|null */
	protected $currentMessage = null;
	/** @var Translation|null */
	protected $currentTranslation = null;
	/** @var ComponentHolder|null */
	protected $currentComponentHolder = null;
	/** @var MessageArg|null */
	protected $currentArg = null;

	/** @var bool */
	protected $base = false;
	/** @var string */
	protected $langId;
	/** @var string */
	protected $langLocal;
	/** @var string[] */
	protected $authors = [];
	/** @var string|null */
	protected $version = null;
	/** @var Message[] */
	protected $localMessages = [];


	public function __construct(LangManager $manager, string $fileHumanName, $fh){
		$this->manager = $manager;
		$this->fileHumanName = $fileHumanName;
		$this->fh = $fh;
	}

	public function close() : void{
		fclose($this->fh);
	}

	public function isBase() : bool{
		return $this->base;
	}

	public function getLangId() : string{
		return $this->langId;
	}

	public function getLangLocal() : string{
		return $this->langLocal;
	}

	public function getAuthors() : array{
		return $this->authors;
	}

	public function getVersion() : ?string{
		return $this->version;
	}

	public function getLocalMessage(string $id) : ?Message{
		return $this->localMessages[$id] ?? null;
	}

	public function parseHeader() : void{
		while(!feof($this->fh) && ($line = fgets($this->fh)) !== false){
			++$this->line;
			while($line !== "" && in_array(mb_substr($line, -1), [" ", "\t", "\n", "\r", "\0", "\x0B"], true) !== false){
				$line = mb_substr($line, 0, -1);
			}
			if(($line = MultibyteLineReader::trim($line, false, true)) === ""){
				continue;
			}
			$this->parseLine($line);

			if($this->scope === self::SCOPE_GROUP){
				return;
			}
		}
	}

	public function parseMessages() : void{
		while(!feof($this->fh) && ($line = fgets($this->fh)) !== false){
			++$this->line;
			while($line !== "" && in_array(mb_substr($line, -1), [" ", "\t", "\n", "\r", "\0", "\x0B"], true) !== false){
				$line = mb_substr($line, 0, -1);
			}
			if(($line = MultibyteLineReader::trim($line, false, true)) === ""){
				continue;
			}
			$this->parseLine($line);
		}
	}

	public function throw(string $exception) : Throwable{
		throw new ParseException("Error parsing lang file: {$exception} on line {$this->line} in $this->fileHumanName");
	}

	private function parseLine(string $line) : void{
		$reader = new MultibyteLineReader($this, $line);

		if($this->scope === self::SCOPE_ROOT){
			$this->parseRootState($reader);
		}else{
			$this->parseTreeLine($reader);
		}
	}

	private function parseRootState(MultibyteLineReader $reader) : void{
		if(($match = $reader->consumeRegex('/^(base[ \t]+)?lang[ \t]+/iu')) !== null){
			if(isset($this->langId)){
				$this->throw("Duplicate lang declaration");
			}

			$this->base = isset($match[1]);
			$this->langId = $reader->consumeUntilAny(" \t", "Missing lang local name; syntax: [base] lang <lang ID> <lang local name>");
			$reader->readWhitespace();
			$this->langLocal = $reader->remaining();
			return;
		}

		if($reader->consumeRegex('/^author[ \t]+/iu') !== null){
			while(($author = $reader->consumeUntilAny(",")) !== null){
				$author = MultibyteLineReader::trim($author, true, true);
				if($author !== null){
					$this->authors[] = $author;
				}
				$reader->consume(1);
			}

			$author = MultibyteLineReader::trim($reader->remaining(), true, true);
			if($author !== null){
				$this->authors[] = $author;
			}

			return;
		}

		if($reader->consumeRegex('/^version[ \t]+/iu', null) !== null){
			if(isset($this->version)){
				$this->throw("Duplicate version declaration");
			}

			$this->version = $reader->remaining();
			return;
		}

		if($reader->remainingLower() === "messages"){
			if(!isset($this->langId)){
				$this->throw("lang type must be declared before the messages");
			}
			$this->scope = self::SCOPE_GROUP;
			return;
		}

		throw $this->throw("Only lang, author, version or messages allowed in root scope");
	}

	private function parseTreeLine(MultibyteLineReader $reader) : void{
		if(trim($reader->remaining()) === ""){
			return;
		}
		[$indentString] = $reader->consumeRegex('/^[ \t]+/u', "messages must be the last root element");

		$indents = $this->countIndent($indentString);
		// $this->scope is the type of the previous line
		if($indents <= 0){ // same indentation or dedent
			// idStack contains the message IDs of the parent groups
			// idStack only needs to be popped when we dedent from one group to a parent group

			// if we dedent from a modifier or higher, we dedent group only if we dedent out of the message scope, i.e. the first ($this->scope - SCOPE_MESSAGE) dedents should not count
			// e.g. constraint, dedent 0 is another constraint, does not pop groups, dedent 1 is a modifier, dedent 2 is a group/message in the same group
			// e.g. modifier, dedent 0 is another modifier, does not pop groups, dedent 1 is a group/message in the same group, dedent 2 pops one group
			// e.g. message, dedent 0 is a group/message in the same group, dedent 1 pops one group
			// e.g. group, dedent 0 is also a group/message in the same group (but needs to pop the previous group!), dedent 1 pops 2 groups
			$groupPops = -$indents + 1 - ($this->scope - self::SCOPE_GROUP);
			if($groupPops > 0){
				$this->idStack = array_slice($this->idStack, 0, -$groupPops);
			}

			if($this->scope >= self::SCOPE_MODIFIER){
				$this->scope = max($this->scope + $indents, self::SCOPE_GROUP);
				// same indentation => same scope
				// dedent 1 => one lower scope
				if($this->scope === self::SCOPE_MESSAGE){
					$this->scope = self::SCOPE_GROUP; // we don't know if this is a message or a group, so we always assume it's a group
				}
			}
//			$this->scope = max($this->scope + $indents - 1, self::SCOPE_GROUP);
			// now $this->scope is the parent scope of the current line

			// scope is only _read_ at the indent parser, but _written_ both at the indent parser and at the EOL (to determine tree vs message)
			// sibling = scope retained
			// modifier => dedent 1 into another message or tree (may be changed)
			// message => dedent 1 into tree
		}else{ // indented
			if($this->scope >= self::SCOPE_MESSAGE){
				if($this->scope === self::SCOPE_CONSTRAINT){
					$this->throw("Cannot indent from a modifier constraint");
				}
				// tree => indent 1 into message or tree (may be changed)
				// message => indent 1 into modifier
				// modifier => indent 1 into constraint
				++$this->scope;
			}
		}
		// now $this->scope is the expected type of the current line
//		echo "Parse line: " . Terminal::$COLOR_AQUA . "$line\n" . Terminal::$FORMAT_RESET;
//		echo "indents=$indents, scope = $this->scope, idStack = " . json_encode($this->idStack) . "\n";

		if($this->scope === self::SCOPE_GROUP || $this->scope === self::SCOPE_MESSAGE){
			$this->parseChildMessageGroupOrEntry($reader);
		}elseif($this->scope === self::SCOPE_MODIFIER){
			$this->parseModifier($reader);
		}elseif($this->scope === self::SCOPE_CONSTRAINT){
			if(!$this->currentArg->getType()->parseConstraint($reader)){
				$this->throw("This argType does not support this constraint");
			}
		}else{
			$this->throw("Indentation error");
		}
	}

	private function parseChildMessageGroupOrEntry(MultibyteLineReader $reader) : void{
		$match = $reader->consumeRegex('/^([\w.-]+)(?:[ \t]+(.+))?/u', "MESSAGE_ID expected", 1);
		$messageId = $match[1];
		if(!isset($match[2])){
			$this->scope = self::SCOPE_GROUP;
			$this->idStack[] = $messageId;
//			echo ("Start group $messageId, full ID " . implode(".", $this->idStack)) . "\n";
			return;
		}

		$reader->readWhitespace();
		$reader->useEscapedComment();

		$fullId = implode(".", array_merge($this->idStack, [$messageId]));
//		echo "Start message $fullId\n";
		if($this->base){
			$this->currentMessage = new Message($this->manager, $fullId);
			if(isset($this->manager->getMessages()[$fullId])){
				$this->throw("Duplicate declaration of message {$fullId}");
			}
			$this->manager->getMessages()[$fullId] = $this->currentMessage;
		}else{
			if(isset($this->manager->getMessages()[$fullId])){
				$this->currentMessage = $this->manager->getMessages()[$fullId];
			}else{
				$this->manager->getLogger()->debug("[libglocal] The message $fullId from $this->fileHumanName will not be visible to other files because it is not declared in the base lang file");
				$this->localMessages[$fullId] = $this->currentMessage = new Message($this->manager, $fullId);
			}
		}


		$this->currentMessage->getTranslations()[$this->langId] = $this->currentComponentHolder
			= $this->currentTranslation = new Translation($this->currentMessage, $fullId, $this->langId, $reader->remaining());
		if($this->base){
			$this->currentMessage->setBaseTranslation($this->currentTranslation);
		}

		$this->parseMessageValue($reader, false);

		$this->scope = self::SCOPE_MESSAGE;
	}

	private function parseMessageValue(MultibyteLineReader $reader, bool $inSpan) : void{
		$buffer = "";

		while(($literal = $reader->consumeUntilAny("#\$%/\\" . ($inSpan ? "}" : ""))) !== null){
			if($literal !== ""){
				$buffer .= $literal;
			}

			$char = $reader->consume(1);
			if($char === "\\"){
				$buffer .= self::resolveEscape($reader->consume(1), $this);
				continue;
			}

			if($char === "/"){
				if($reader->peek(1) === "/"){
					if($inSpan){
						$this->throw("Comments not allowed inside spans. Change // to \\// if you really intended to write slashes.");
					}

					if($buffer !== ""){
						$this->currentComponentHolder->getComponents()[] = new LiteralTranslationComponent($this->currentTranslation, $buffer);
					}
					return;
				}

				$buffer .= "/";
				continue;
			}

			if($char === "}"){
				if($buffer !== ""){
					$this->currentComponentHolder->getComponents()[] = new LiteralTranslationComponent($this->currentTranslation, $buffer);
				}
				return;
			}

			assert($char === "#" || $char === "\$" || $char === "%");

			if(($next = $reader->consume(1)) !== "{"){
				$buffer .= $char . $next;
				continue;
			}

			if($buffer !== ""){
				$this->currentComponentHolder->getComponents()[] = new LiteralTranslationComponent($this->currentTranslation, $buffer);
				$buffer = ""; // buffer was flushed
			}

			if($char === "\$"){
				$argName = MultibyteLineReader::trim($reader->consumeUntilAny("}"), true, true);
				$this->currentComponentHolder->getComponents()[] = new ArgRefTranslationComponent($this->currentTranslation, $argName);
				$reader->consume(1, "should be }");
				continue;
			}

			if($char === "#"){
				$this->currentComponentHolder->getComponents()[] = $this->parseMessageRef($reader);
				continue;
			}

			assert($char === "%");
			$this->currentComponentHolder->getComponents()[] = $this->parseSpanRef($reader);
		}

		$buffer .= $reader->remaining();
		if($buffer !== ""){
			$this->currentComponentHolder->getComponents()[] = new LiteralTranslationComponent($this->currentTranslation, $buffer);
		}
	}

	private function parseMessageRef(MultibyteLineReader $reader) : MessageRefTranslationComponent{
		$reader->readWhitespace();
		$messageId = $reader->consumeUntilAny(" \t,}", "MESSAGE_ID expected after %{");
		$args = [];
		while(([$argName] = $reader->consumeRegex('/^[, \t]+([\w.-]+)[ \t]*=[ \t]*/u')) !== null){
			$args[$argName] = $this->parseDefaultValue($reader);
		}
		$substr = $reader->consumeUntilAny("}", "#{} is not closed");
		if(trim($substr, ", \t") !== ""){
			$this->throw("Invalid content in #{}: $substr");
		}
		return new MessageRefTranslationComponent($this, $messageId, $args);
	}

	private function parseSpanRef(MultibyteLineReader $reader) : TranslationComponent{
		$reader->readWhitespace();
		$match = $reader->consumeRegex('/^([\w.-]+)([ \t\}])/u', "Invalid span name");
		$name = mb_strtolower($match[1]);

		if(isset(self::SPAN_STYLES[$name])){
			if($match[2] !== "}"){
				$this->throw("Style spans %{{$name}} should not have content");
			}
			return new StyleSpanTranslationComponent($this->currentTranslation, self::SPAN_STYLES[$name]);
		}

		if(!isset(self::SPAN_STACKS[$name])){
			$this->throw("Unknown span name %{{$name}}");
		}

		$args = self::SPAN_STACKS[$name];
		$child = new StackSpanTranslationComponent($this->currentTranslation, $name, ...$args);
		$parent = $this->currentComponentHolder;
		$this->currentComponentHolder = $child;
		$this->parseMessageValue($reader, true);
		$this->currentComponentHolder = $parent;

		return $child;
	}

	private function parseModifier(MultibyteLineReader $reader) : void{
		if($reader->consumeRegex('/^arg[ \t]+/iu') !== null){
			$this->parseArgModifier($reader);
			return;
		}

		if($reader->consumeRegex('/^doc[ \t]+/iu') !== null){
			$this->parseDocModifier($reader);
			return;
		}

		if(($match = $reader->consumeRegex('/^(since|updated)[ \t]+/iu')) !== null){
			$this->parseVersionModifier($reader, mb_strtolower($match[1]) === "since");
			return;
		}

		$this->throw("Unknown modifier statement. Is the line mis-indented?");
	}

	private function parseArgModifier(MultibyteLineReader $reader) : void{
		$argName = $reader->consumeUntilAny(" \t") ?? $reader->consumeRemaining();
		if(!preg_match('/^[\w.-]+$/u', $argName)){
			$this->throw("$argName is not a valid argument name");
		}

		$type = null;
		$reader->readWhitespace();
		$argType = mb_strtolower($reader->consumeUntilAny(" \t") ?? ($reader->consumeRemaining() ?: "string"));
		if($argType !== ""){
			if(!preg_match('/^(?:([\w.-]+):)?([\w.-]+)$/u', $argType, $match)){
				$this->throw("$argType is not a valid argument type");
			}
			$type = $this->manager->createArgType($match[1] !== "" ? $match[1] : null, $match[2]);
		}

		$default = null;
		$reader->readWhitespace();
		if($reader->hasMore() && $reader->peek(1) === "="){
			$reader->consume(1);
			$reader->readWhitespace();
			$default = $this->parseDefaultValue($reader);
		}

		$reader->readWhitespace();
		if($reader->hasMore()){
			$this->throw("End of line expected, got " . $reader->remaining());
		}

		$this->currentArg = new MessageArg($this->currentMessage, $argName, $type, $default);
		if($this->base){
			$this->currentMessage->getArgs()[$argName] = $this->currentArg;
		}else{
			$this->currentTranslation->getArgOverrides()[$argName] = $this->currentArg;
		}
	}

	private function parseDocModifier(MultibyteLineReader $reader) : void{
		if(!$this->base){
			$this->throw("doc statement is only allowed in base lang file");
		}

		$reader->useEscapedComment();
		$this->currentMessage->setDoc($reader->remaining());
	}

	private function parseVersionModifier(MultibyteLineReader $reader, bool $since) : void{
		if($since){
			return; // since is useless right now
		}

		if($this->base){
			$this->currentMessage->setUpdatedVersion($reader->remaining());
		}else{
			$this->currentTranslation->setUpdated($reader->remaining());
		}
	}

	private function parseDefaultValue(MultibyteLineReader $reader) : ArgDefault{
		if(($number = $reader->consumeRegex('/^-?[0-9]+(?:\.[0-9]+)?/u')) !== null){
			return new NumberLiteralDefault((float) $number);
		}

		if($reader->peek(1) === '"'){
			$reader->consume(1);
			$literal = "";
			while(($substr = $reader->consumeUntilAny("\\\"")) !== null){
				$literal .= $substr;
				if($reader->consume(1) === '"'){
					break;
				}

				// escape
				$escape = $reader->consume(1);
				switch($escape){
					case 'n':
						$literal .= "\n";
						break;
					case '\\':
					case '"':
						$literal .= $escape;
						break;
					default:
						throw $this->throw("Invalid escape sequence \\$escape");
				}
			}

			return new StringLiteralDefault($literal);
		}

		[$argName] = $reader->consumeRegex('/^[\w.-]+/u', "Invalid default value");
		return new ArgFallbackDefault($this->currentMessage, $this->langId, $argName);
	}

	/**
	 * @param string $indent
	 * @return int (-count($this->indentStack), 1]
	 */
	private function countIndent(string $indent) : int{
		if(empty($this->indentStack)){
			array_unshift($this->indentStack, $indent);
			return 1; // indent
		}
		if(mb_strlen($indent) > mb_strlen($this->indentStack[0])){
			array_unshift($this->indentStack, $indent);
			return 1; // indent
		}

		$i = 0;
		while(!empty($this->indentStack)){
			if($this->indentStack[0] === $indent){
				return -$i;
			}
			array_shift($this->indentStack);
			++$i;
		}
		throw $this->throw("Inconsistent indentation");
	}

	public function getCurrentMessage() : ?Message{
		return $this->currentMessage;
	}

	public function getCurrentTranslation() : ?Translation{
		return $this->currentTranslation;
	}

	public static function resolveEscape(string $char, Thrower $thrower) : string{
		switch($char){
			case '#':
			case '$':
			case '%':
			case '/':
			case '\\':
			case '}':
				return $char;
			case 'n':
				return "\n";
			case 's':
				return " ";
			case '0':
				return "";
			default:
				throw $thrower->throw("Illegal escape sequence \\$char");
		}
	}
}
