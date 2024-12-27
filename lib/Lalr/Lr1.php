<?php

declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

class Lr1
{
    /** @var Lr1|null $next */
    public $next;
    /** @var Symbol|null $left */
    public $left;
    /** @var Item $item */
    public $item;
    /** @var Bitset|null $look */
    public $look;

    public function __construct(Symbol $left = null, Bitset $look, Item $item)
    {
        $this->left = $left;
        $this->look = $look;
        $this->item = $item;
    }

    public function isTailItem(): bool
    {
        return $this->item->isTailItem();
    }

    public function isHeadItem(): bool
    {
        return $this->item->isHeadItem();
    }

    public function dump(): string
    {
        $result = '';
        $lr1 = $this;
        while ($lr1 !== null) {
            $result .= $lr1->item . "\n";
            $lr1 = $lr1->next;
        }
        return $result . "\n";
    }
}
