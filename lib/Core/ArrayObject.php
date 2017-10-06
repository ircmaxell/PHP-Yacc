<?php
declare(strict_types=1);

namespace PhpYacc\Core;

use IteratorAggregate;
use ArrayAccess;

class ArrayObject implements ArrayAccess, IteratorAggregate {

    protected $offset = 0;
    protected $array = [];

    public function __construct($input = [], int $offset = 0)
    {
        if ($input instanceof ArrayObject) {
            $this->array = $input;
        } elseif (is_array($input)) {
            $this->array = $input;
        } else {
            throw new \InvalidArgumentException("Unknown type passed");
        }
        $this->offset = $offset;
    }

    public function getIterator()
    {
        $skip = $this->offset;
        foreach ($this->array as $value) {
            $skip--;
            if ($skip >= 0) {
                continue;
            }
            yield $value;
        }
    }

    public function slice(int $n): ArrayObject
    {
        return new ArrayObject($this, $n);
    }

    public function sliceInto(array $new, int $offset)
    {
        $i = 0;
        foreach ($new as $value) {
            $this->array[$i++ + $offset] = $value;
        }
    }

    public function offsetExists($index)
    {
        return isset($this->array[$index + $this->offset]);
    }

    public function offsetGet($index)
    {
        if (!$this->offsetExists($index)) {
            return null;
        }
        return $this->array[$index + $this->offset];
    }

    public function offsetSet($index, $value)
    {
        $this->array[$index + $this->offset] = $value;
    }

    public function offsetUnset($index)
    {
        unset($this->array[$index + $this->offset]);
    }
}