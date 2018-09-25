<?php

/*
 * Retrieved from https://github.com/pmmp/PocketMine-MP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\utils {
	abstract class TextFormat{
		public const ESCAPE = "\xc2\xa7"; //§
		public const EOL = "\n";

		public const BLACK = TextFormat::ESCAPE . "0";
		public const DARK_BLUE = TextFormat::ESCAPE . "1";
		public const DARK_GREEN = TextFormat::ESCAPE . "2";
		public const DARK_AQUA = TextFormat::ESCAPE . "3";
		public const DARK_RED = TextFormat::ESCAPE . "4";
		public const DARK_PURPLE = TextFormat::ESCAPE . "5";
		public const GOLD = TextFormat::ESCAPE . "6";
		public const GRAY = TextFormat::ESCAPE . "7";
		public const DARK_GRAY = TextFormat::ESCAPE . "8";
		public const BLUE = TextFormat::ESCAPE . "9";
		public const GREEN = TextFormat::ESCAPE . "a";
		public const AQUA = TextFormat::ESCAPE . "b";
		public const RED = TextFormat::ESCAPE . "c";
		public const LIGHT_PURPLE = TextFormat::ESCAPE . "d";
		public const YELLOW = TextFormat::ESCAPE . "e";
		public const WHITE = TextFormat::ESCAPE . "f";

		public const OBFUSCATED = TextFormat::ESCAPE . "k";
		public const BOLD = TextFormat::ESCAPE . "l";
		public const STRIKETHROUGH = TextFormat::ESCAPE . "m";
		public const UNDERLINE = TextFormat::ESCAPE . "n";
		public const ITALIC = TextFormat::ESCAPE . "o";
		public const RESET = TextFormat::ESCAPE . "r";
	}
}
