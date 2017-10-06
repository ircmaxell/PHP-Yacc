<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Context;

const NBITS = 8;

const MASKS = [
    "\x01",
    "\x02",
    "\x04",
    "\x08",
    "\x10",
    "\x20",
    "\x40",
    "\x80",
];

function orbits(Context $ctx, string &$d, string $s): bool
{
    $changed = false;
    $nSymbols = $ctx->nSymbols();
    for ($i = 0; $i < $nSymbols; $i += NBITS) {
        if ("\0" !== ($s[$i / 8] & ~$d[$i / 8])) {
            $changed = true;
            $d[$i / 8] = $d[$i / 8] | $s[$i / 8];
        }
    }
    return $changed;
}

function testBit(string $s, int $i): int 
{
    return (ord($s[(int) ($i / NBITS)]) >> ($i % NBITS)) & 1;
}

function setBit(string &$s, int $i)
{
    $offset = (int) ($i / NBITS);
    $char = $s[$offset];
    $char |= MASKS[$i % NBITS];
    $s[$offset] = $char;
}

function clearBit(string &$s, int $i)
{
    $offset = (int) ($i / NBITS);
    $char = $s[$offset];
    $char &= ~MASKS[$i % NBITS];
    $s[$offset] = $char;
}

function nextElement(Context $ctx, int $e, string $p): int
{
    $nSymbols = $ctx->nSymbols();
    for (; $e < $nSymbols; $e++) {
        if (testBit($p, $e)) {
            break;
        }
    }
    return $e;
}

function forEachMember(Context $ctx, int $v, string $set)
{
    $nSymbols = $ctx->nSymbols();
    for ($v = 0; ($v = nextElement($ctx, $v, $set)) < $nSymbols; $v++) {
        yield $v;
    }
}


function isSameSet(Lr1 $left = null, Lr1 $right = null): bool
{
    $p = $left;
    $t = $right;
    while ($t !== null) {
        if ($p === null || $p->item !== $t->item) {
            return false;
        }
        $p = $p->next;
        $t = $t->next;
    }
    return $p === null || $p->headitem();
}

function dumpSet(Context $ctx, string $string)
{
    foreach ($ctx->symbols() as $symbol) {
        if (testBit($string, $symbol->code)) {
            echo "{$symbol->name} ";
        }
    }
}