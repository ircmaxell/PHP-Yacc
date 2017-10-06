<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

use PhpYacc\Core\ArrayObject;


class Lr1 {
  
    protected $next;
    protected $left;
    protected $item;
    public $look;

    public function __construct(Symbol $left, string $look, ArrayObject $item = null)
    {
        $this->left = $left;
        $this->item = $item ?: new ArrayObject;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $cb = 'set' . $name;
        $this->{'set' . $name}($value);
    }

    public function setNext(Lr1 $next = null)
    {
        $this->next = $next;
    }

    public function setItem(ArrayObject $item)
    {
        $this->item = $item;
    }

    public function isTailItem(): bool
    {
        return $this->item[0]->code === 0;
    }

    public function isHeadItem(): bool
    {
        return $this->item[-2]->code === 0;
    }

}
