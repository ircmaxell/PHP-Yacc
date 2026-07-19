<?php

declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Exception\LogicException;
use PhpYacc\Yacc\Production;

class Symbol
{
    public const UNDEF = 0;
    public const LEFT = 1;
    public const RIGHT = 2;
    public const NON = 3;
    public const MASK = 3;

    public const TERMINAL = 0x100;
    public const NONTERMINAL = 0x200;

    public int $code;
    public ?Symbol $type;
    public int $precedence;
    public int $associativity;
    public string $name;

    protected Production|int|null $_value;

    public bool $isterminal = false;
    public bool $isnonterminal = false;

    protected int $_terminal = self::UNDEF;

    public function __construct(int $code, string $name, $value = null, int $terminal = self::UNDEF, int $precedence = self::UNDEF, int $associativity = self::UNDEF, ?Symbol $type = null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->_value = $value;
        $this->setTerminal($terminal);
        $this->precedence = $precedence;
        $this->associativity = $associativity;
        $this->type = $type;
    }

    public function isNilSymbol(): bool
    {
        return $this->_terminal === self::UNDEF;
    }

    public function terminal(): int
    {
        return $this->_terminal;
    }

    public function setTerminal(int $terminal): void
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

    public function value(): Production|int|null
    {
        return $this->_value;
    }

    public function setValue(Production|int|null $value): void
    {
        if ($this->isterminal && !is_int($value)) {
            throw new LogicException("Terminals value must be an integer, " . gettype($value) . " provided");
        } elseif ($this->isnonterminal  && !($value instanceof Production || $value === null)) {
            throw new LogicException("NonTerminals value must be a production, " . gettype($value) . " provided");
        }
        $this->_value = $value;
    }

    public function setAssociativityFlag(int $flag): void
    {
        $this->associativity |= $flag;
    }
}
