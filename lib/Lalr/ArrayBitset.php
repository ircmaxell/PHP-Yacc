<?php

declare(strict_types=1);

namespace PhpYacc\Lalr;

class ArrayBitset implements Bitset
{
    public const NBITS = \PHP_INT_SIZE * 8;

    private $numBits;
    private $array;

    public function __construct(int $numBits)
    {
        $this->numBits = $numBits;
        $this->array = array_fill(0, intdiv($numBits + self::NBITS - 1, self::NBITS), 0);
    }

    public function __clone()
    {
        $this->array = array_values($this->array);
    }

    public function testBit(int $i): bool
    {
        $offset = intdiv($i, self::NBITS);
        return ($this->array[$offset] & (1 << ($i % self::NBITS))) !== 0;
    }

    public function setBit(int $i)
    {
        $offset = intdiv($i, self::NBITS);
        $this->array[$offset] |= (1 << ($i % self::NBITS));
    }

    public function clearBit(int $i)
    {
        $offset = intdiv($i, self::NBITS);
        $this->array[$offset] &= ~(1 << ($i % self::NBITS));
    }

    public function or(Bitset $other): bool
    {
        assert($this->numBits === $other->numBits);

        $changed = false;
        foreach ($this->array as $key => $value) {
            $this->array[$key] = $value | $other->array[$key];
            $changed = $changed || $value !== $this->array[$key];
        }
        return $changed;
    }

    public function getIterator()
    {
        $numElems = count($this->array);
        for ($n = 0; $n < $numElems; $n++) {
            $elem = $this->array[$n];
            if ($elem !== 0) {
                for ($i = 0; $i < self::NBITS; $i++) {
                    if ($elem & (1 << $i)) {
                        yield $n * self::NBITS + $i;
                    }
                }
            }
        }
    }
}
