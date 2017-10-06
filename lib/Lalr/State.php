<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use ArrayObject;
use PhpYacc\Grammar\Symbol;

class State {

    protected $next;
    protected $shifts;
    protected $reduce;
    protected $conflict;
    protected $through;
    protected $items;


    public function __construct()
    {
        $this->shifts = new ArrayObject([]);

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

    public function setConflict(Conflict $conflict)
    {
        $this->conflict = $conflict;
    }

    public function setReduce(Reduce $reduce)
    {
        $this->reduce = $reduce;
    }

    public function setThrough(Symbol $through)
    {
        $this->through = $through;
    }

    public function setNext(State $next = null)
    {
        $this->next = $next;
    }

    public function setItems(Lr1 $items)
    {
        $this->items = $items;
    }


}