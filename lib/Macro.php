<?php

namespace PhpYacc;

use PhpYacc\Grammar\Context;
use Generator;
use Iterator;

interface Macro
{

    public function apply(Context $ctx, array $symbols, Iterator $tokens, int $n, array $attribute): Generator;

}
