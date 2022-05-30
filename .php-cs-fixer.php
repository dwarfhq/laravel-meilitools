<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PhpCsFixer'            => true,
    'binary_operator_spaces' => [
        'operators' => [
            '=>' => 'align_single_space_minimal',
        ],
    ],
    'class_definition' => [
        'single_line'              => true,
        'space_before_parenthesis' => true,
    ],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'increment_style'            => ['style' => 'post'],
    'native_constant_invocation' => [
        'fix_built_in' => true,
        'include'      => [
            'DIRECTORY_SEPARATOR',
            'PHP_SAPI',
            'PHP_VERSION_ID',
        ],
        'scope'  => 'namespaced',
        'strict' => true,
    ],
    'native_function_invocation' => [
        'include' => ['@compiler_optimized'],
        'scope'   => 'namespaced',
        'strict'  => true,
    ],
    'no_alias_functions' => [
        'sets' => ['@all'],
    ],
    'no_empty_comment'                                 => false,
    'no_superfluous_phpdoc_tags'                       => false,
    'no_unreachable_default_argument_value'            => true,
    'not_operator_with_successor_space'                => false,
    'nullable_type_declaration_for_default_null_value' => true,
    'ordered_class_elements'                           => false,
    'ordered_imports'                                  => [
        'sort_algorithm' => 'alpha',
        'imports_order'  => ['class', 'function', 'const'],
    ],
    'ordered_traits'                      => true,
    'php_unit_test_class_requires_covers' => false,
    'phpdoc_annotation_without_dot'       => false,
    'phpdoc_no_empty_return'              => false,
    'phpdoc_types_order'                  => [
        'sort_algorithm'  => 'none',
        'null_adjustment' => 'always_last',
    ],
    'simplified_null_return'    => true,
    'single_line_comment_style' => [
        'comment_types' => ['hash'],
    ],
    'yoda_style' => false,
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
;
