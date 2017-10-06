<?php

namespace PhpYacc\Yacc;

use PhpYacc\Token;

function isWhite(string $c): bool
{
    return $c === ' ' || $c === "\t" || $c === "\r" || $c === "\x0b" || $c === "\x0c";
}

function isSymCh(string $c): bool
{
    return ctype_alnum($c) || $c === '_';
}

function isOctal(string $char): bool
{
    $n = ord($char);
    return $n >= 48 && $n <= 55;
}

function isGsym(Token $t): bool
{
    return $t->t === Tokens::NAME || $t->t === "'";
}

function charval(string $string): string
{
    $n = 0;
    $length = strlen($string);
    if ($length === 0) {
        return $string;
    }
    $c = $string[$n++];
    if ($c !== '\\') {
        return $c;
    }
    $c = $string[$n++];
    if (isOctal($c)) {
        $value = (int) $c;
        for ($i = 0; $n < $length && isOctal($string[$n]) && $i < 3; $i++) {
            $value = $value * 8 + $string[$n++];
        }
        return chr($value);
    }
    switch ($c) {
        case 'n': return "\n";
        case 't': return "\t";
        case 'b': return "\x08";
        case 'r': return "\r";
        case 'f': return "\x0C";
        case 'v': return "\x0B";
        case 'a': return "\x07";
        default:
            return $c;
    }
}

function parseDollar(int $type, int $nth, int $len, string $typename): string
{
    $result = '';
    return $typename;
}