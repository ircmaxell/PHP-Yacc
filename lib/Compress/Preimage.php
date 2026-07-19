<?php

declare(strict_types=1);

namespace PhpYacc\Compress;

class Preimage
{
    public int $index = 0;
    public array $classes = [];
    public int $length = 0;

    public function __construct(int $index)
    {
        $this->index = $index;
    }

    public static function compare(Preimage $x, Preimage $y): int
    {
        if ($x->length !== $y->length) {
            return $x->length - $y->length;
        }
        foreach ($x->classes as $key => $value) {
            if ($value !== $y->classes[$key]) {
                return $value - $y->classes[$key];
            }
        }
        return 0;
    }
}
