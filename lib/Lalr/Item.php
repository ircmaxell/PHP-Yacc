<?php

declare(strict_types=1);

namespace PhpYacc\Lalr;

use IteratorAggregate;
use ArrayAccess;
use PhpYacc\Exception\LogicException;
use PhpYacc\Yacc\Production;

class Item implements ArrayAccess, IteratorAggregate
{
    protected $production;
    protected $pos = 0;

    public function __construct(Production $production, int $offset)
    {
        assert($offset >= 1);
        assert($offset <= count($production->body));
        $this->production = $production;
        $this->pos = $offset;
    }

    public function getIterator(): \Traversable
    {
        for ($i = $this->pos; $i < \count($this->production->body); $i++) {
            yield $this->production->body[$i];
        }
    }

    public function slice(int $n): Item
    {
        return new Item($this->production, $this->pos + $n);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->production->body[$offset + $this->pos]);
    }

    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new LogicException("Offset $offset does not exist");
        }
        return $this->production->body[$offset + $this->pos];
    }

    public function offsetSet($offset, $value): void
    {
        throw new LogicException("Not supported");
    }

    public function offsetUnset($offset): void
    {
        throw new LogicException("Not supported");
    }

    public function isHeadItem()
    {
        return $this->pos === 1;
    }

    public function isTailItem()
    {
        return $this->pos === count($this->production->body);
    }

    public function getProduction(): Production
    {
        return $this->production;
    }

    public function getPos(): int
    {
        return $this->pos;
    }

    public function __toString()
    {
        $result = "(" . $this->production->num . ")";
        for ($i = 0; $i < count($this->production->body); $i++) {
            if ($i === 1) {
                $result .= " :";
            }
            if ($i === $this->pos) {
                $result .= " .";
            }
            $result .= " " . $this->production->body[$i]->name;
        }
        if ($i === 1) {
            $result .= " :";
        }
        if ($i === $this->pos) {
            $result .= " .";
        }
        return $result;
    }
}
