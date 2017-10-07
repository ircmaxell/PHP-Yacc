<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

use PhpYacc\Lalr\Item;


/**
 * @property Lr1|null $next
 * @property Symbol $left
 * @property Item $item
 */
class Lr1 {
  
    protected $_next;
    protected $_left;
    protected $_item;
    public $look;

    public function __construct(Symbol $left, string $look, Item $item)
    {
        $this->_left = $left;
        $this->look = $look;
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

    public function isTailItem(): bool
    {
        return $this->_item->isTailItem();
    }

    public function isHeadItem(): bool
    {
        return $this->_item->isHeadItem();
    }

}
