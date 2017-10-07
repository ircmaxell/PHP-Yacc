<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use ArrayObject;
use PhpYacc\Grammar\Symbol;

/**
 * @property StateList|null $next
 * @property State $state
 */
class StateList {
    protected $_next;
    protected $_state;

    public function __construct(State $state, StateList $next = null)
    {
        $this->_state = $state;
        $this->_next = $next;
    }

    public function __get($name)
    {
        return $this->{'_'.$name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setState(State $state)
    {
        $this->_state = $state;
    }

    public function setNext(StateList $next = null)
    {
        $this->_next = $next;
    }

}