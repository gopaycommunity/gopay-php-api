<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/examples',
    ])
    ->name('*.php')
    ->append([
        __DIR__ . '/factory.php',
        __FILE__,
    ]);

return (new PhpCsFixer\Config())
    // Risky rules can change behavior, which is not acceptable in a public SDK
    ->setRiskyAllowed(false)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setRules([
        '@PER-CS' => true,
        '@PHP81Migration' => true,
        'array_indentation' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'single_quote' => true,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder);
