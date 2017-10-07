<?php
declare(strict_types=1);

namespace PhpYacc\Core;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use PhpYacc\Grammar\Symbol;

class ArrayObject implements ArrayAccess, IteratorAggregate, Countable  {

    protected $offset = 0;
    protected $array = [];

    public function __construct($input = [], int $offset = 0)
    {
        if ($input instanceof ArrayObject) {
            if ($input->array instanceof ArrayObject) {
                $this->array = $input->array;
                $offset += $input->offset;
                assert($input->array->offset === 0);
            } else {
                $this->array = $input;
            }
        } elseif (is_array($input)) {
            assert($offset === 0);
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
            assert($value instanceof Symbol);
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
        assert($value instanceof Symbol);
        $this->array[$index + $this->offset] = $value;
    }

    public function offsetUnset($index)
    {
        unset($this->array[$index + $this->offset]);
    }

    public function count() {
        return \count($this->array);
    }

    public function __toString() {
        $result = '';
        for ($i = 1; $i < count($this->array) + 1; $i++) {
            if ($i === 2) {
                $result .= ": ";
            }
            if ($i === $this->offset) {
                $result .= ". ";
            }
            $result .= $this->array[$i]->name . " ";
        }
        if ($i === 2) {
            $result .= ": ";
        }
        if ($i === $this->offset) {
            $result .= ". ";
        }
        return $result;
    }
}