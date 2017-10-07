<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use ArrayObject;
use PhpYacc\Grammar\Symbol;

/**
 * @property State|null $next
 * @property Reduce[] $reduce
 * @property Conflict $conflict
 * @property Symbol $through
 * @property Lr1 $items
 */
class State {

    /** @var State[] */
    public $shifts = []; // public for indirect array modification
    protected $_next;
    protected $_reduce;
    protected $_conflict;
    protected $_through;
    protected $_items;


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

    public function setReduce(array $reduce)
    {
        foreach ($reduce as $r) {
            assert($r instanceof Reduce);
        }
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

}