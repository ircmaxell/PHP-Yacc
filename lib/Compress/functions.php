<?php
declare(strict_types=1);

namespace PhpYacc\Compress;

function vacant_row(array $array, int $length): bool
{
    for ($i = 0; $i < $length; $i++) {
        if (!is_vacant(($array[$i]))) {
            return false;
        }
    }
    return true;
}

function eq_row(array $a, array $b, int $length): bool
{
    for ($i = 0; $i < $length; $i++) {
        if ($a[$i] !== $b[$i]) {
            return false;
        }
    }
    return true;
}

function is_vacant(int $x): bool
{
    return $x === Compress::VACANT;
}

function printact(int $act): string
{
    if (is_vacant($act)) {
        return '  . ';
    }
    return sprintf("%4d", $act);
}
