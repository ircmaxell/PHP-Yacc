<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

/**
 * @property int $code
 * @property Symbol|null $type
 * @property mixed $value
 * @property int $precedence
 * @property int $associativity
 * @property string $name
 * @property int $terminal
 */
class Symbol {

    const UNDEF = 0;
    const LEFT = 1;
    const RIGHT = 2;
    const NON = 3;
    const MASK = 3;

    const TERMINAL = 0x100;
    const NONTERMINAL = 0x200;

    protected $_code;
    protected $_type;

    protected $_value;
    protected $_precedence;
    protected $_associativity;
    protected $_name;

    protected $_terminal = self::UNDEF;

    public function __construct(int $code, string $name, $value, int $precedence = self::UNDEF, int $associativity = self::UNDEF, Symbol $type = null)
    {
        $this->_code = $code;
        $this->_name = $name;
        $this->_value = $value;
        $this->_precedence = $precedence;
        $this->_associativity = $associativity;
        $this->_type = $type;
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
        return $this->{'_'.$name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setTerminal(int $terminal)
    {
        $this->_terminal = $terminal;
    }

    public function setAssociativity(int $associativity)
    {
        $this->_associativity = $associativity;
    }

    public function setPrecedence(int $precedence)
    {
        $this->_precedence = $precedence;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function setType(Symbol $type = null)
    {
        $this->_type = $type;
    }


}