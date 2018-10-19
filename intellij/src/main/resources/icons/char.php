<?php

chdir(__DIR__);

if(!isset($argv[5])){
	echo "Usage: php char.php <name> <char> <r> <g> <b>\n";
	exit(1);
}

$name = $argv[1];
$char = $argv[2];
$r = (int) $argv[3];
$g = (int) $argv[4];
$b = (int) $argv[5];

if(strlen($name) < 1 || strlen($char) !== 1 || $r < 0 || $r > 255 || $g < 0 || $g > 255 || $b < 0 || $b > 255){
	echo "Wrong args\n";
	exit(1);
}

foreach([
	"-13px" => 13,
	"-16px" => 16,
	"-13px@2x" => 26,
	"-16px@2x" => 32,
] as $suffix => $size){
	$fontSize = (int) ($size * 0.6);
	$image = imagecreatetruecolor($size, $size);
	imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
	[, , $lrx, $lry, , , $ulx, $uly] = imagettfbbox($fontSize, 0, "font.ttf", $char);
	$width = $lrx - $ulx;
	$height = $lry - $uly;
	$posX = (int) max(0, $size / 2 - $width / 2);
	$posY = (int) max(0, $size / 2 - $height / 2) + $height;
	imagettftext($image, $fontSize, 0, $posX, $posY, imagecolorallocate($image, 0, 0, 0), "font.ttf", $char);
	imagepng($image, "{$name}{$suffix}.png");
}
