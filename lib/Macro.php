<?php

namespace PhpYacc;

use PhpYacc\Grammar\Context;
use Generator;
use Iterator;

abstract class Macro {

    abstract public function apply(Context $ctx, array $symbols, Iterator $tokens, int $n, array $attribute): Generator;

    protected static function next(Iterator $it): Token
    {
        $it->next();
        if (!$it->valid()) {
            throw new RuntimeException("Syntax error, expected more tokens");
        }
        return $it->current();
    }

}