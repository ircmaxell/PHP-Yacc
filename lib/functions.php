<?php

declare(strict_types=1);

namespace PhpYacc;

use PhpYacc\Yacc\Token;

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

function is_white(string $c): bool
{
    return $c === ' ' || $c === "\t" || $c === "\r" || $c === "\x0b" || $c === "\x0c";
}

function is_sym_character(string $c): bool
{
    return ctype_alnum($c) || $c === '_';
}

function is_octal(string $char): bool
{
    $n = ord($char);
    return $n >= 48 && $n <= 55;
}

function is_gsym(Token $t): bool
{
    return $t->t === Token::NAME || $t->t === "'";
}

function character_value(string $string): int
{
    $n = 0;
    $length = strlen($string);
    if ($length === 0) {
        return 0;
    }
    $c = $string[$n++];
    if ($c !== '\\') {
        return ord($c);
    }
    $c = $string[$n++];
    if (is_octal($c)) {
        $value = (int) $c;
        for ($i = 0; $n < $length && is_octal($string[$n]) && $i < 3; $i++) {
            $value = $value * 8 + $string[$n++];
        }
        return $value;
    }
    switch ($c) {
        case 'n': return ord("\n");
        case 't': return ord("\t");
        case 'b': return ord("\x08");
        case 'r': return ord("\r");
        case 'f': return ord("\x0C");
        case 'v': return ord("\x0B");
        case 'a': return ord("\x07");
        default:
            return ord($c);
    }
}
