<?php
declare(strict_types=1);

namespace PhpYacc\Grammar;

use function PhpYacc\Yacc\charval;
use PhpYacc\Yacc\Production;
use PhpYacc\Yacc\Macro\DollarExpansion;
use Generator;

/**
 * @property Symbol[] $symbols
 * @property Symbol $nilsymbol
 * @property Symbol[] $terminals
 * @property Symbol[] $nonterminals
 * @property Production[] $grams
 * @property int $ngrams
 * @property int $nstates
 * @property State[] $states
 * @property int $nnonleafstates
 */
class Context
{
    public $macros = [
        DollarExpansion::SEMVAL_LHS_TYPED => '',
        DollarExpansion::SEMVAL_LHS_UNTYPED => '',
        DollarExpansion::SEMVAL_RHS_TYPED => '',
        DollarExpansion::SEMVAL_RHS_UNTYPED => '',
    ];

    public $nsymbols = 0;
    public $nterminals = 0;
    public $nnonterminals = 0;

    protected $symbolHash = [];
    protected $_symbols = [];
    protected $_nilsymbol = null;
    protected $finished = false;

    protected $_states;
    public $nstates = 0;
    public $nnonleafstates = 0;

    public $aflag = false;
    public $tflag = false;
    public $pspref = '';

    public $filename = 'YY';
    public $pureFlag = false;
    public $startSymbol = null;
    public $expected = null;
    public $unioned = false;
    public $eofToken = null;
    public $errorToken = null;
    public $startPrime = null;
    protected $_grams = [];
    public $ngrams = 0;

    public $default_act = [];
    public $default_goto = [];
    public $term_action = [];
    public $class_action = [];
    public $nonterm_goto = [];
    public $class_of = [];
    public $ctermindex = [];
    public $otermindex = [];
    public $frequency = [];
    public $state_imagesorted = [];
    public $nprims = 0;
    public $prims = [];
    public $primof = [];
    public $class2nd = [];
    public $nclasses = 0;
    public $naux = 0;

    protected $debugFile;

    public function __construct(string $filename = 'YY', string $file = null)
    {
        $this->filename = $filename;
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
        foreach ($this->nonTerminals() as $nonterm) {
            $nonterm->nb = $nb;
            $nonterm->code = $code++;
        }
        foreach ($this->nilSymbols() as $nil) {
            $nil->nb = $nb;
            $nil->code = $code++;
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

    public function terminals(): Generator
    {
        foreach ($this->_symbols as $symbol) {
            if ($symbol->isterminal) {
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
            if ($symbol->isnonterminal) {
                yield $symbol;
            }
        }
    }

    public function genNonTerminal(): Symbol
    {
        $buffer = sprintf("@%d", $this->nnonterminals);
        return $this->internSymbol($buffer, false);
    }

    public function internSymbol(string $s, bool $isTerm): Symbol
    {
        $p = $this->intern($s);
        
        if (!$p->isNilSymbol()) {
            return $p;
        }
        if ($isTerm || $s[0] === "'") {
            if ($s[0] === "'") {
                $p->value = charval(substr($s, 1, 1));
            } else {
                $p->value = -1;
            }
            $p->terminal = Symbol::TERMINAL;
        } else {
            $p->value = null;
            $p->terminal = Symbol::NONTERMINAL;
        }

        $p->associativity   = Symbol::UNDEF;
        $p->precedence      = Symbol::UNDEF;
        return $p;
    }

    public function intern(string $s): Symbol
    {
        if (isset($this->symbolHash[$s])) {
            return $this->symbolHash[$s];
        }
        $p = new Symbol($this->nsymbols++, $s);
        return $this->addSymbol($p);
    }

    public function addSymbol(Symbol $symbol): Symbol
    {
        $this->finished = false;
        $this->_symbols[] = $symbol;
        $this->symbolHash[$symbol->name] = $symbol;
        $this->nterminals = 0;
        $this->nnonterminals = 0;
        foreach ($this->_symbols as $symbol) {
            if ($symbol->isterminal) {
                $this->nterminals++;
            } elseif ($symbol->isnonterminal) {
                $this->nnonterminals++;
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
        $p->num = $this->ngrams++;
        $this->_grams[] = $p;
        return $p;
    }

    public function gram(int $i): Production
    {
        assert($i < $this->ngrams);
        return $this->_grams[$i];
    }

    public function setStates(array $states)
    {
        foreach ($states as $state) {
            assert($state instanceof State);
        }
        $this->_states = $states;
        $this->nstates = count($states);
    }

    public function setNNonLeafStates(int $n)
    {
        $this->nnonleafstates = $n;
    }
}
