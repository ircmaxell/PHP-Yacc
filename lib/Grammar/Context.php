<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

use Generator;

class Context {

    protected $counter = 0;
    protected $symbolHash = [];
    protected $symbols = [];

    public function nilSymbol(): Symbol
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol->isNilSymbol()) {
                return $symbol;
            }
        }
        return $this->intern("@nil");
    }

    public function nSymbols(): int
    {
        return $this->counter;
    }

    public function terminals(): Generator
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol->isTerminal()) {
                yield $symbol;
            }
        }
    }

    public function nonTerminals(): Generator
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol->isNonTerminal()) {
                yield $symbol;
            }
        }
    }

    public function genNonTerminal(): Symbol
    {
        $buffer = sprintf("@%d", $this->nonTerminalCounter++);
        return $this->internSymbol($buffer, false);
    }

    public function internSymbol(string $s, bool $isTerm): Symbol
    {
        $p = $this->intern($s);
        
        if (!$p->isNilSymbol()) {
            return $p;
        }

        $p->terminal = ($isTerm || $p->name[0] === "'") ? Symbol::TERMINAL : Symbol::NONTERMINAL;
        $p->associativity   = Symbol::UNDEF;
        $p->precedence      = Symbol::UNDEF;
        $p->value           = null;
        return $p;
    }

    public function intern(string $s): Symbol
    {
        if (isset($this->symbolHash[$s])) {
            return $this->symbolHash[$s];
        }
        $p = new Symbol($this->counter++, $s, 0);
        return $this->addSymbol($p);
    }

    public function addSymbol(Symbol $symbol): Symbol
    {
        $this->symbols[] = $symbol;
        $this->symbolHash[$symbol->name] = $symbol;
        return $symbol;
    }

    public function symbols(): array
    {
        return $this->symbols;
    }


}