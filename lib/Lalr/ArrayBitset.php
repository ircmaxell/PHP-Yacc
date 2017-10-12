<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

class ArrayBitset extends Bitset
{

    private $nbits = 31;
    private $numBits;
    private $array;

    public function __construct(int $numBits)
    {
        // Will be 63 on 64 bit machines, 31 on 32 bit machines
        $this->nbits = (int) (log(PHP_INT_MAX) / log(2));
        $this->numBits = $numBits;
        $this->array = array_fill(0, intdiv($numBits + $this->nbits - 1, $this->nbits), 0);
    }

    public function __clone()
    {
        $this->array = array_values($this->array);
    }

    public function testBit(int $i): bool
    {
        return ($this->array[$i / $this->nbits] & (1 << ($i % $this->nbits))) !== 0;
    }

    public function setBit(int $i)
    {
        $this->array[$i / $this->nbits] |= (1 << ($i % $this->nbits));
    }

    public function clearBit(int $i)
    {
        $this->array[$i / $this->nbits] &= ~(1 << ($i % $this->nbits));
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
