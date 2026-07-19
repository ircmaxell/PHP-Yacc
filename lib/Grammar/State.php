<?php

declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Lalr\Lr1;
use PhpYacc\Lalr\Conflict;
use PhpYacc\Lalr\Reduce;

class State
{
    /** @var State[] */
    public array $shifts = [];
    /** @var non-empty-array<Reduce> */
    public array $reduce;
    public ?Conflict $conflict = null;
    public Symbol $through;
    public Lr1 $items;
    public int $number;

    public function __construct(Symbol $through, Lr1 $items)
    {
        $this->through = $through;
        $this->items = $items;
    }

    public function isReduceOnly(): bool
    {
        return empty($this->shifts)
            && $this->reduce[0]->symbol->isNilSymbol();
    }
}
