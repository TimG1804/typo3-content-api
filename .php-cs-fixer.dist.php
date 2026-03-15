<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
    ])
    ->name('*.php');

$config = new Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,

        // Strict types on every PHP file
        'declare_strict_types' => true,

        // Import cleanup and ordering
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],

        // PHPDoc
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => false,
        ],

        // Strings
        'single_quote' => true,

        // Trailing commas in multi-line structures
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],

        // Blank line before control-flow statements
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'yield'],
        ],

        // Return type hints
        'void_return' => true,

        // Native function calls in namespaced code — adds leading backslash for performance
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
            'strict' => true,
        ],

        // PHPUnit
        'php_unit_method_casing' => true,
        'php_unit_test_annotation' => ['style' => 'annotation'],
    ])
    ->setFinder($finder);
