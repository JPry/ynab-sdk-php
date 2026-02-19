<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
	->setRiskyAllowed(false)
	->setIndent("\t")
	->setLineEnding("\n")
	->setRules([
		'@PSR12' => true,
		'array_syntax' => ['syntax' => 'short'],
		'indentation_type' => true,
		'line_ending' => true,
		'no_trailing_whitespace' => true,
		'single_quote' => true,
	])
	->setFinder($finder);
