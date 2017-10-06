<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

class Symbol {

    const UNDEF = 0;
    const LEFT = 1;
    const RIGHT = 2;
    const NON = 3;
    const MASK = 3;

    const TERMINAL = 0x100;
    const NONTERMINAL = 0x200;

    protected $code;
    protected $type;

    protected $value;
    protected $precedence;
    protected $associativity;
    protected $name;

    protected $terminal = self::UNDEF;

    public function __construct(int $code, string $name, $value, int $precedence = self::UNDEF, int $associativity = self::UNDEF, Symbol $type = null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->value = $value;
        $this->precedence = $precedence;
        $this->associativity = $associativity;
        $this->type = $type;
    }

    public function isTerminal(): bool
    {
        return $this->terminal === self::TERMINAL;
    }

    public function isNonTerminal(): bool
    {
        return $this->terminal === self::NONTERMINAL;
    }

    public function isNilSymbol(): bool
    {
        return $this->terminal === self::UNDEF;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setTerminal(int $terminal)
    {
        $this->terminal = $terminal;
    }

    public function setAssociativity(int $associativity)
    {
        $this->associativity = $associativity;
    }

    public function setPrecedence(int $precedence)
    {
        $this->precedence = $precedence;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setType(Symbol $type = null)
    {
        $this->type = $type;
    }


}