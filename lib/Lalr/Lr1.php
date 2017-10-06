<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

use PhpYacc\Core\ArrayObject;


/**
 * @property Lr1|null $next
 * @property Symbol $left
 * @property ArrayObject $item
 */
class Lr1 {
  
    protected $_next;
    protected $_left;
    protected $_item;
    public $look;

    public function __construct(Symbol $left, string $look, ArrayObject $item = null)
    {
        $this->_left = $left;
        $this->_item = $item ?: new ArrayObject;
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

    public function setItem(ArrayObject $item)
    {
        $this->_item = $item;
    }

    public function isTailItem(): bool
    {
        return $this->_item[0]->code === 0;
    }

    public function isHeadItem(): bool
    {
        return $this->_item[-2]->code === 0;
    }

}
