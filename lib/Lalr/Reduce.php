<?php

declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

class Reduce
{
    public Symbol $symbol;
    public int $number;

    public function __construct(Symbol $symbol, int $number)
    {
        $this->symbol = $symbol;
        $this->number = $number;
    }
}
