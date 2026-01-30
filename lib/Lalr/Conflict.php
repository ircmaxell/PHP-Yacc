<?php

declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

abstract class Conflict
{
    protected $next;
    protected $symbol;

    protected function __construct(Symbol $symbol, ?Conflict $next)
    {
        $this->next = $next;
        $this->symbol = $symbol;
    }

    public function isShiftReduce(): bool
    {
        return false;
    }

    public function isReduceReduce(): bool
    {
        return false;
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    public function next()
    {
        return $this->next;
    }

    public function setNext(?Conflict $next)
    {
        $this->next = $next;
    }
}
