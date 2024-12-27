<?php

/**
 * Created by PhpStorm.
 * User: ircmaxell
 * Date: 10/12/17
 * Time: 8:32 AM
 */

namespace PhpYacc\Lalr;

interface Bitset extends \IteratorAggregate
{
    public function testBit(int $i): bool;

    public function setBit(int $i);

    public function clearBit(int $i);

    public function or(Bitset $other): bool;
}
