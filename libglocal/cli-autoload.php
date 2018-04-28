<?php

declare(strict_types=1);

spl_autoload_register(function(string $class){
	if(is_file($file = __DIR__ . "/src/" . str_replace("\\", "/", $class) . ".php")){
		require_once $file;
	}
});
