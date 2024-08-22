<?php

declare(strict_types=1);
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__.'/src')
;

$header = <<<'EOF'

    EOF;

return (new Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PHP74Migration' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none',
            'location' => 'after_declare_strict',
        ],
        'strict_param' => true,
        'declare_strict_types' => true,
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder)
;
