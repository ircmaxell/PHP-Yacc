<?php

declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Exception\LogicException;
use PhpYacc\Yacc\Production;

/**
 * @property Symbol|null $type
 * @property mixed $value
 * @property int $precedence
 * @property int $associativity
 * @property string $name
 * @property int $terminal
 */
class Symbol
{
    public const UNDEF = 0;
    public const LEFT = 1;
    public const RIGHT = 2;
    public const NON = 3;
    public const MASK = 3;

    public const TERMINAL = 0x100;
    public const NONTERMINAL = 0x200;

    /** @var int */
    public $code;
    protected $_type;

    protected $_value;
    protected $_precedence;
    protected $_associativity;
    protected $_name;

    public $isterminal = false;
    public $isnonterminal = false;

    protected $_terminal = self::UNDEF;

    public function __construct(int $code, string $name, $value = null, int $terminal = self::UNDEF, int $precedence = self::UNDEF, int $associativity = self::UNDEF, ?Symbol $type = null)
    {
        $this->code = $code;
        $this->_name = $name;
        $this->_value = $value;
        $this->setTerminal($terminal);
        $this->_precedence = $precedence;
        $this->_associativity = $associativity;
        $this->_type = $type;
    }

    public function isNilSymbol(): bool
    {
        return $this->_terminal === self::UNDEF;
    }

    public function __get($name)
    {
        return $this->{'_' . $name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setTerminal(int $terminal)
    {
        $this->_terminal = $terminal;
        if ($terminal === self::TERMINAL) {
            $this->isterminal = true;
            $this->isnonterminal = false;
        } elseif ($terminal === self::NONTERMINAL) {
            $this->isterminal = false;
            $this->isnonterminal = true;
        } else {
            $this->isterminal = false;
            $this->isnonterminal = false;
        }
        $this->setValue($this->_value); // force check to prevent issues
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
        if ($this->isterminal && !is_int($value)) {
            throw new LogicException("Terminals value must be an integer, " . gettype($value) . " provided");
        } elseif ($this->isnonterminal  && !($value instanceof Production || $value === null)) {
            throw new LogicException("NonTerminals value must be a production, " . gettype($value) . " provided");
        }
        $this->_value = $value;
    }

    public function setType(?Symbol $type): void
    {
        $this->_type = $type;
    }

    public function setAssociativityFlag(int $flag)
    {
        $this->_associativity |= $flag;
    }
}
