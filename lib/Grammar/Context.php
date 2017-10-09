<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

use Generator;

class Context {

    protected $counter = 0;
    protected $symbolHash = [];
    protected $symbols = [];
    protected $nilSymbol = null;
    protected $finished = false;
    protected $nterms;
    protected $nnonterms;
    protected $nb;

    public function finish()
    {
        if ($this->finished) {
            return;
        }
        $this->finished = true;
        $code = 0;
        foreach ($this->terminals() as $term) {
            $term->code = $code++;
        }
        $this->nb = $code;
        foreach ($this->nilSymbols() as $nil) {
            $nil->nb = $this->nb;
            $nil->code = $code++;
        }
        foreach ($this->nonTerminals() as $nonterm) {
            $nonterm->nb = $this->nb;
            $nonterm->code = $code++;
        }

        usort($this->symbols, function($a, $b) {
            return $a->code <=> $b->code;
        });
    }

    public function nilSymbol(): Symbol
    {
        if ($this->nilSymbol === null) {
            $this->nilSymbol = $this->intern("@nil");
        }
        return $this->nilSymbol;
    }

    public function nSymbols(): int
    {
        return $this->counter;
    }

    public function nTerminals(): int
    {
        return $this->nterms;
    }

    public function nNonTerminals(): int
    {
        return $this->nnonterms;
    }

    public function terminals(): Generator
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol->isTerminal()) {
                yield $symbol;
            }
        }
    }

    public function nilSymbols(): Generator
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol->isNilSymbol()) {
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
        $this->finished = false;
        $this->symbols[] = $symbol;
        $this->symbolHash[$symbol->name] = $symbol;
        $this->nterms = 0;
        $this->nnonterms = 0;
        foreach ($this->symbols as $symbol) {
            if ($symbol->isTerminal()) {
                $this->nterms++;
            } elseif ($symbol->isNonTerminal()) {
                $this->nnonterms++;
            }
        }
        return $symbol;
    }

    public function symbols(): array
    {
        return $this->symbols;
    }

    public function symbol(int $code): Symbol
    {
        foreach ($this->symbols as $symbol) {
            if ($symbol->code === $code) {
                return $symbol;
            }
        }
    }


}