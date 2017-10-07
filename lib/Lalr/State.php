<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use ArrayObject;
use PhpYacc\Grammar\Symbol;

/**
 * @property State|null $next
 * @property State[] $shifts
 * @property Reduce $reduce
 * @property Conflict $conflict
 * @property Symbol $through
 * @property Lr1 $items
 */
class State {

    protected $_next;
    protected $_shifts;
    protected $_reduce;
    protected $_conflict;
    protected $_through;
    protected $_items;


    public function __construct()
    {
        $this->_shifts = [];

    }

    public function __get($name)
    {
        return $this->{'_'.$name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setConflict(Conflict $conflict)
    {
        $this->_conflict = $conflict;
    }

    public function setReduce(Reduce $reduce)
    {
        $this->_reduce = $reduce;
    }

    public function setThrough(Symbol $through)
    {
        $this->_through = $through;
    }

    public function setNext(State $next = null)
    {
        $this->_next = $next;
    }

    public function setItems(Lr1 $items)
    {
        $this->_items = $items;
    }

    public function setShifts(array $shifts) {
        $this->_shifts = $shifts;
    }

}