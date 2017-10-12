<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

use PhpYacc\Lalr\Item;

/**
 * @property Lr1|null $next
 * @property Symbol|null $left
 * @property Item $item
 * @property Bitset|null $look
 */
class Lr1
{
    protected $_next;
    protected $_left;
    protected $_item;
    protected $_look;

    public function __construct(Symbol $left = null, Bitset $look, Item $item)
    {
        $this->_left = $left;
        $this->_look = $look;
        $this->_item = $item;
    }

    public function __get($name)
    {
        return $this->{'_'.$name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setNext(Lr1 $next = null)
    {
        $this->_next = $next;
    }

    public function setItem(Item $item)
    {
        $this->_item = $item;
    }

    public function setLook(Bitset $look = null)
    {
        $this->_look = $look;
    }

    public function isTailItem(): bool
    {
        return $this->_item->isTailItem();
    }

    public function isHeadItem(): bool
    {
        return $this->_item->isHeadItem();
    }

    public function dump(): string
    {
        $result = '';
        $lr1 = $this;
        while ($lr1 !== null) {
            $result .= $lr1->item . "\n";
            $lr1 = $lr1->next;
        }
        return $result . "\n";
    }
}
