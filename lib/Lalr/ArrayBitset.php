<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

class ArrayBitset implements Bitset
{
    const NBITS = \PHP_INT_SIZE * 8;

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
        return ($this->array[$i / self::NBITS] & (1 << ($i % self::NBITS))) !== 0;
    }

    public function setBit(int $i)
    {
        $this->array[$i / self::NBITS] |= (1 << ($i % self::NBITS));
    }

    public function clearBit(int $i)
    {
        $this->array[$i / self::NBITS] &= ~(1 << ($i % self::NBITS));
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
}
