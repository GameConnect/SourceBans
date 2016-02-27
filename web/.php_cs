<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/src');

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        // Symfony
        'unused_use',
        'whitespacy_lines',
        'duplicate_semicolon',
        'single_array_no_trailing_comma',
        'multiline_array_trailing_comma',
        // Contrib
        'concat_with_spaces',
        'ordered_use',
        'short_array_syntax',
        'multiline_spaces_before_semicolon',
    ])
    ->finder($finder);
