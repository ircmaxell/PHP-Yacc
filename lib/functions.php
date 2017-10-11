<?php
declare(strict_types=1);
namespace PhpYacc;

function stable_sort(array &$array, callable $cmp)
{
    $indexedArray = [];
    $i = 0;
    foreach ($array as $item) {
        $indexedArray[] = [$item, $i++];
    }

    usort($indexedArray, function (array $a, array $b) use ($cmp) {
        $result = $cmp($a[0], $b[0]);
        if ($result !== 0) {
            return $result;
        }
        return $a[1] - $b[1];
    });

    $array = [];
    foreach ($indexedArray as $item) {
        $array[] = $item[0];
    }
}
