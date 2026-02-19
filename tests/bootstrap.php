<?php

declare(strict_types=1);

$packageRoot = dirname(__DIR__);
$autoload = "{$packageRoot}/vendor/autoload.php";

if (is_file($autoload)) {
	require_once $autoload;
} else {
	throw new RuntimeException(
		'Could not find vendor/autoload.php. Run "composer install" before running tests.',
	);
}
