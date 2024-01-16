<?php

use BackSystem\Base\Fixer\BlankLineAroundClassBodyFixer;
use BackSystem\Base\Fixer\OneAttributePerLineFixer;

$base = dirname(__DIR__, 5);

$finder = PhpCsFixer\Finder::create()
    ->in($base . '/src')
    ->in($base . '/tests')
    ->exclude('var');

$config = new PhpCsFixer\Config();

return $config->setUsingCache(false)
    ->registerCustomFixers([new BlankLineAroundClassBodyFixer(), new OneAttributePerLineFixer()])
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'curly_braces_position' => [
            'classes_opening_brace' => 'same_line',
            'functions_opening_brace' => 'same_line',
        ],
        'global_namespace_import' => ['import_classes' => true],
        'no_blank_lines_after_class_opening' => false,
        'types_spaces' => ['space' => 'single'],
        'single_line_empty_body' => true,
        'Custom/blank_line_around_class_body' => true,
        'Custom/one_attribute_per_line' => true,
    ])->setFinder($finder);
