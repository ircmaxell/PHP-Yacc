<?php
declare(strict_types=1);

namespace PhpYacc\Compress;

function vacant_row(array $a): bool
{
    foreach ($a as $value) {
        if (!is_vacant($value)) {
            return false;
        }
    }
    return true;
}

function eq_row(array $a, array $b): bool
{
    if (count($a) !== count($b)) {
        return false;
    }
    foreach ($a as $key => $value) {
        if ($value !== $b[$key]) {
            return false;
        }
    }
    return true;
}

function is_vacant($x): bool
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
