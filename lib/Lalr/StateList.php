<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use ArrayObject;
use PhpYacc\Grammar\Symbol;

class StateList {
    protected $next;
    protected $state;

    public function __construct(State $state = null, StateList $next = null)
    {
        $this->state = $state;
        $this->next = $next;
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

    public function setState(State $state)
    {
        $this->state = $state;
    }

    public function setNext(StateList $next = null)
    {
        $this->next = $next;
    }

}