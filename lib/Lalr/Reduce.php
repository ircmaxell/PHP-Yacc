<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

class Reduce
{
    /** @var Symbol */
    public $symbol;
    /** @var int */
    public $number;

    public function __construct(Symbol $symbol, int $number)
    {
        $this->symbol = $symbol;
        $this->number = $number;
    }
}
