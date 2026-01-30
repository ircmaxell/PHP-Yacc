<?php

declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Lalr\Lr1;
use PhpYacc\Lalr\Conflict;
use PhpYacc\Lalr\Reduce;

class State
{
    /** @var State[] */
    public $shifts = [];
    /** @var non-empty-array<Reduce> */
    public $reduce;
    /** @var Conflict|null */
    public $conflict;
    /** @var Symbol */
    public $through;
    /** @var Lr1 */
    public $items;
    /** @var int */
    public $number;

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
