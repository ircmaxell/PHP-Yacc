<?php declare(strict_types=1);

namespace PhpYacc\Yacc;

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
    return $t->t === Token::NAME || $t->t === "'";
}

function charval(string $string): int
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
    if (isOctal($c)) {
        $value = (int) $c;
        for ($i = 0; $n < $length && isOctal($string[$n]) && $i < 3; $i++) {
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
