<?php
declare(strict_types=1);

namespace PhpYacc\Lalr\Conflict;

use PhpYacc\Grammar\Symbol;
use PhpYacc\Lalr\{
    Conflict
};

class ReduceReduce extends Conflict {

    protected $reduce1;
    protected $reduce2;

    public function __construct(int $reduce1, int $reduce2, Symbol $symbol, Conflict $next = null)
    {
        $this->reduce1 = $reduce1;
        $this->reduce2 = $reduce2;
        parent::__construct($symbol, $next);
    }

    public function isReduceReduce(): bool
    {
        return true;
    }

    public function reduce1(): int
    {
        return $this->reduce1;
    }

    public function reduce2(): int
    {
        return $this->reduce2;
    }

}