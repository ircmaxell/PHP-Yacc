<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRules([
        '@auto' => true,
    ])
    ->setFinder(
        (new Finder())
            ->in(__DIR__)
            ->path([
                'lib/',
            ])
            ->append([__FILE__])
    )
;
