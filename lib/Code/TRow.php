<?php
declare(strict_types=1);

namespace PhpYacc\Code;


class TRow
{

    public $index;
    public $mini;
    public $maxi;
    public $nent;

    public function __construct(int $index) 
    {
        $this->index = $index;
        $this->mini = -1;
        $this->maxi = 0;
        $this->nent = 0;
    }

    public function span(): int
    {
        return $this->maxi - $this->mini;
    }

    public function nhole(): int
    {
        return $this->span() - $this->nent;
    }

    public static function compare(TRow $a, TRow $b): int
    {
        if ($a->nent !== $b->nent) {
            return $b->nent - $a->nent;
        }
        if ($a->span() !== $b->span()) {
            return $b->span() - $a->span();
        }
        return $a->mini - $b->mini;
    }

}