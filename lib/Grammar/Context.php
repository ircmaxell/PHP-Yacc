<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Yacc\Production;

use Generator;

/**
 * @property Symbol[] $symbols
 * @property Symbol $nilsymbol
 * @property Symbol[] $terminals
 * @property Symbol[] $nonterminals
 * @property int $nsymbols
 * @property int $nterminals
 * @property int $nnonterminals
 * @property Production[] $grams
 * @property int $nstates
 * @property State[] $states
 * @property int $nnonleafstates
 */
class Context
{
    protected $_nsymbols = 0;
    protected $symbolHash = [];
    protected $_symbols = [];
    protected $_nilsymbol = null;
    protected $finished = false;
    protected $_nterminals;
    protected $_nnonterminals;

    protected $_states;
    protected $_nstates = 0;
    protected $_nnonleafstates = 0;

    public $pureFlag = false;
    public $startSymbol = null;
    public $expected = null;
    public $unioned = false;
    public $eofToken = null;
    public $errorToken = null;
    public $startPrime = null;
    protected $_grams = [];
    protected $_ngrams = 0;

    protected $debugFile;

    public function __construct(string $file = null)
    {
        $this->debugFile = $file ? fopen($file, 'w') : null;
    }

    public function debug(string $data)
    {
        if ($this->debugFile) {
            fwrite($this->debugFile, $data);
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'terminals': return $this->terminals();
            case 'nonterminals': return $this->nonTerminals();
        }
        if (!isset($this->{'_' . $name})) {
            throw new \LogicException("Should never happen: unknown property $name");
        }
        return $this->{'_' . $name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

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
        $nb = $code;
        foreach ($this->nilSymbols() as $nil) {
            $nil->nb = $nb;
            $nil->code = $code++;
        }
        foreach ($this->nonTerminals() as $nonterm) {
            $nonterm->nb = $nb;
            $nonterm->code = $code++;
        }

        usort($this->_symbols, function ($a, $b) {
            return $a->code <=> $b->code;
        });
    }

    public function nilSymbol(): Symbol
    {
        if ($this->_nilsymbol === null) {
            $this->_nilsymbol = $this->intern("@nil");
        }
        return $this->_nilsymbol;
    }

    public function nSymbols(): int
    {
        return $this->_nsymbols;
    }

    public function nTerminals(): int
    {
        return $this->_nterminals;
    }

    public function nNonTerminals(): int
    {
        return $this->_nnonterminals;
    }

    public function terminals(): Generator
    {
        foreach ($this->_symbols as $symbol) {
            if ($symbol->isTerminal()) {
                yield $symbol;
            }
        }
    }

    public function nilSymbols(): Generator
    {
        foreach ($this->_symbols as $symbol) {
            if ($symbol->isNilSymbol()) {
                yield $symbol;
            }
        }
    }

    public function nonTerminals(): Generator
    {
        foreach ($this->_symbols as $symbol) {
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
        $p = new Symbol($this->_nsymbols++, $s, 0);
        return $this->addSymbol($p);
    }

    public function addSymbol(Symbol $symbol): Symbol
    {
        $this->finished = false;
        $this->_symbols[] = $symbol;
        $this->symbolHash[$symbol->name] = $symbol;
        $this->_nterminals = 0;
        $this->_nnonterminals = 0;
        foreach ($this->_symbols as $symbol) {
            if ($symbol->isTerminal()) {
                $this->_nterminals++;
            } elseif ($symbol->isNonTerminal()) {
                $this->_nnonterminals++;
            }
        }
        return $symbol;
    }

    public function symbols(): array
    {
        return $this->_symbols;
    }

    public function symbol(int $code): Symbol
    {
        foreach ($this->_symbols as $symbol) {
            if ($symbol->code === $code) {
                return $symbol;
            }
        }
    }

    public function addGram(Production $p)
    {
        $p->num = $this->_ngrams++;
        $this->_grams[] = $p;
        return $p;
    }

    public function gram(int $i): Production
    {
        assert($i < $this->_ngrams);
        return $this->_grams[$i];
    }

    public function setStates(array $states)
    {
        foreach ($states as $state) {
            assert($state instanceof State);
        }
        $this->_states = $states;
        $this->_nstates = count($states);
    }

    public function setNNonLeafStates(int $n)
    {
        $this->_nnonleafstates = $n;
    }

}
