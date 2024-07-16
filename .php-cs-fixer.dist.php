<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->in([
        __DIR__.'/src',
        __DIR__.'/config',
        __DIR__.'/tests',
    ])
    ->exclude([
        __DIR__.'/config/secrets',
        __DIR__.'/tests/Doubles',
    ])
    ->notPath([
        'decrypt.private.php',
        'encrypt.public.php',
    ])
;

$config = new PhpCsFixer\Config();
$config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        // [PER:risky & Symfony:risky source: https://cs.symfony.com/doc/ruleSets/PhpCsFixerRisky.html]
        '@PER' => true,
        '@PHP82Migration' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony:risky' => true,
        '@Symfony' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
        'declare_strict_types' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'phpdoc_to_param_type' => true,
        'date_time_immutable' => true,
        'not_operator_with_successor_space' => true,
        'method_chaining_indentation' => true,
        'single_line_throw' => false,
        'single_quote' => ['strings_containing_single_quote_chars' => true],
        'cast_spaces' => ['space' => 'single'],
        'blank_line_before_statement' => ['statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try']],
        'phpdoc_align' => [
            'align' => 'left',
            'tags' => [
                'method',
                'param',
                'property',
                'property-read',
                'property-write',
                'return',
                'throws',
                'type',
                'var',
            ],
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
    ])
    ->setRiskyAllowed(true);

return $config;
