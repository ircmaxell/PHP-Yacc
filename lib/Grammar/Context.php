<?php

declare(strict_types=1);

namespace PhpYacc\Grammar;

use PhpYacc\Exception\LogicException;
use PhpYacc\Yacc\Production;
use PhpYacc\Yacc\Macro\DollarExpansion;
use Generator;

use function PhpYacc\character_value;

class Context
{
    public array $macros = [
        DollarExpansion::SEMVAL_LHS_TYPED => '',
        DollarExpansion::SEMVAL_LHS_UNTYPED => '',
        DollarExpansion::SEMVAL_RHS_TYPED => '',
        DollarExpansion::SEMVAL_RHS_UNTYPED => '',
    ];

    public int $nsymbols = 0;
    public int $nterminals = 0;
    public int $nnonterminals = 0;

    /** @var array<string, Symbol> */
    protected array $symbolHash = [];
    /** @var Symbol[] */
    protected array $_symbols = [];
    protected ?Symbol $_nilsymbol = null;
    protected bool $finished = false;

    protected array $_states;
    public int $nstates = 0;
    public int $nnonleafstates = 0;

    public bool $aflag = false;
    public bool $tflag = false;
    public bool $allowSemanticValueReferenceByName = false;
    public string $pspref = '';
    public bool $verboseDebug = false;

    public string $filename = 'YY';
    public bool $pureFlag = false;
    public ?Symbol $startSymbol = null;
    public int $expected = 0;
    public bool $unioned = false;
    public ?string $union_body = null;
    public ?Symbol $eofToken = null;
    public ?Symbol $errorToken = null;
    public ?Symbol $startPrime = null;
    /** @var Production[] */
    protected array $_grams = [];
    public int $ngrams = 0;

    public array $default_act = [];
    public array $default_goto = [];
    public array $term_action = [];
    public array $class_action = [];
    public array $nonterm_goto = [];
    public array $class_of = [];
    public array $ctermindex = [];
    public array $otermindex = [];
    public array $frequency = [];
    public array $state_imagesorted = [];
    public int $nprims = 0;
    public array $prims = [];
    public array $primof = [];
    public array $class2nd = [];
    public int $nclasses = 0;
    public int $naux = 0;

    public $debugFile;
    public $errorFile;

    public function __construct(
        string $filename = 'YY',
        $errorFile = null,
        $debugFile = null,
        bool $verboseDebug = false
    ) {
        $this->filename = $filename;
        $this->errorFile = $errorFile;
        $this->debugFile = $debugFile;
        $this->verboseDebug = $verboseDebug;
    }

    public function error(string $data): void
    {
        if ($this->errorFile) {
            fwrite($this->errorFile, $data);
        }
    }

    public function debug(string $data): void
    {
        if ($this->debugFile) {
            fwrite($this->debugFile, $data);
        }
    }

    public function finish(): void
    {
        if ($this->finished) {
            return;
        }
        $this->finished = true;
        $code = 0;
        foreach ($this->terminals() as $term) {
            $term->code = $code++;
        }
        foreach ($this->nonTerminals() as $nonterm) {
            $nonterm->code = $code++;
        }
        foreach ($this->nilSymbols() as $nil) {
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
                $p->value = character_value(substr($s, 1, -1));
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
        throw new LogicException("Should never happen: unknown symbol $code");
    }

    public function addGram(Production $p): Production
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

    /**
     * @return Production[]
     */
    public function grams(): array
    {
        return $this->_grams;
    }

    /**
     * @return State[]
     */
    public function states(): array
    {
        return $this->_states;
    }

    public function setStates(array $states): void
    {
        foreach ($states as $state) {
            assert($state instanceof State);
        }
        $this->_states = $states;
        $this->nstates = count($states);
    }

    public function setNNonLeafStates(int $n): void
    {
        $this->nnonleafstates = $n;
    }
}
