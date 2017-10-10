<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Lalr\Lr1;
use PhpYacc\Lalr\Conflict;
use PhpYacc\Lalr\Reduce;

/**
 * @property Reduce[] $reduce
 * @property Conflict $conflict
 * @property Symbol $through
 * @property Lr1 $items
 * @property int $number
 */
class State
{

    /** @var State[] */
    public $shifts = []; // public for indirect array modification
    protected $_reduce;
    protected $_conflict;
    protected $_through;
    protected $_items;
    protected $_number;

    public function __construct(Symbol $through, Lr1 $items)
    {
        $this->_through = $through;
        $this->_items = $items;
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

    public function setItems(Lr1 $items)
    {
        $this->_items = $items;
    }

    public function setNumber(int $number)
    {
        $this->_number = $number;
    }

    public function isReduceOnly(): bool
    {
        return empty($this->shifts)
            && $this->_reduce[0]->symbol->isNilSymbol();
    }
}
