<?php
declare(strict_types=1);

namespace PhpYacc\Code;

class Preimage
{
    public $index = 0;
    public $classes = [];

    public function __construct(int $index)
    {
        $this->index = $index;
    }

    public static function compare(Preimage $x, Preimage $y): int
    {
        if (count($x->classes) !== count($y->classes)) {
            return count($x->classes) - count($y->classes);
        }
        foreach ($x->classes as $key => $value) {
            if ($value !== $y->classes[$key]) {
                return $value - $y->classes[$key];
            }
        }
        return 0;
    }
}
