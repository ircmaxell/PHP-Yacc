<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

class StringBitset implements Bitset
{
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

    private $numBits;
    private $str;

    public function __construct(int $numBits)
    {
        $this->numBits = $numBits;
        $this->str = str_repeat("\0", intdiv($numBits + self::NBITS - 1, self::NBITS));
    }

    public function testBit(int $i): bool
    {
        $offset = intdiv($i, self::NBITS);
        return ((ord($this->str[$offset]) >> ($i % self::NBITS)) & 1) !== 0;
    }

    public function setBit(int $i)
    {
        $offset = intdiv($i, self::NBITS);
        $char = $this->str[$offset];
        $char |= self::MASKS[$i % self::NBITS];
        $this->str[$offset] = $char;
    }

    public function clearBit(int $i)
    {
        $offset = intdiv($i, self::NBITS);
        $char = $this->str[$offset];
        $char &= ~self::MASKS[$i % self::NBITS];
        $this->str[$offset] = $char;
    }

    public function or(Bitset $other): bool
    {
        assert($this->numBits === $other->numBits);

        $changed = false;
        for ($i = 0; $i < $this->numBits; $i += self::NBITS) {
            $offset = $i / self::NBITS;
            if ("\0" !== ($other->str[$offset] & ~$this->str[$offset])) {
                $changed = true;
                $this->str[$offset] = $this->str[$offset] | $other->str[$offset];
            }
        }
        return $changed;
    }

    public function getIterator()
    {
        for ($i = 0; $i < $this->numBits; $i++) {
            if ($this->testBit($i)) {
                yield $i;
            }
        }
    }
}
