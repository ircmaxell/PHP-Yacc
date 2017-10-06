<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Core\ArrayObject;

class Production {
    const EMPTY = 0x10;

    protected $link;
    protected $associativity;
    protected $precedence;
    protected $position;
    protected $action;
    protected $body;

    public function __construct(string $action = null, int $position)
    {
        $this->action = $action;
        $this->position = $position;
        $this->body = new ArrayObject([]);
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

    public function setBody(ArrayObject $new)
    {
        $this->body = $new;
    }

    public function setAssociativityFlag(int $flag)
    {
        $this->associativity |= $flag;
    }

    public function setLink(Production $link = null)
    {
        $this->link = $link;
    }

    public function setAssociativity(int $associativity)
    {
        $this->associativity = $associativity;
    }

    public function setPrecedence(int $precedence)
    {
        $this->precedence = $precedence;
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }
}