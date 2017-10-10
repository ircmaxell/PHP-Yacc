<?php
declare(strict_types=1);

namespace PhpYacc\Code;

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
    return $x === CompressResult::VACANT;
}

function best_covering(array $table, Preimage $preimage): int
{
    // TODO
}

function printact(int $act): string
{
    if (is_vacant($act)) {
        return '  . ';
    }
    return sprintf("%4d", $act);
}
