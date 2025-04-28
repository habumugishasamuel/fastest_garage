<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->name('*.php')
    ->exclude('vendor');

return (new Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'single_quote' => true,
        'no_trailing_whitespace' => true,
        'no_extra_blank_lines' => true,
        'align_multiline_comment' => true,
    ])
    ->setFinder($finder); 