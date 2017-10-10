<?php
declare(strict_types=1);

namespace PhpYacc\Code;

use PhpYacc\Grammar\Symbol;

use PhpYacc\Lalr\State;

class CompressResult
{
    const YYUNEXPECTED = 32767;
    const YYDEFAULT = -32766;
    const VACANT = -32768;

    public $yytranslate = [];

    public $yyaction = [];

    public $yybase = [];
    public $yybasesize;

    public $yycheck = [];

    public $yydefault = [];

    public $yygoto = [];

    public $yygbase = [];

    public $yygcheck = [];

    public $yygdefault = [];

    public $yylhs = [];

    public $yylen = [];

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
    public $prims = [];
    public $primof = [];

    public $class2nd = [];

    public $nstates = 0;
    public $nterms = 0;
    public $nclasses = 0;
    public $naux = 0;

    public function __construct(int $nstates, int $nterms)
    {
        $this->nstates = $nstates;
        $this->nterms = $nterms;
    }

    public function encode_rederr(int $code): int
    {
        return $code < 0 ? self::YYUNEXPECTED : $code;
    }

    public function convert_symbol(Symbol $symbol): int
    {
        return $symbol->isTerminal() ? $this->ctermindex[$symbol->code] : $symbol->code;
    }

    public function resetFrequency()
    {
        $this->frequency = array_fill(0, $this->nstates, 0);
    }

    public function cmp_states(int $x, int $y): int
    {
        for ($i = 0; $i < $this->nterms; $i++) {
            if ($this->term_action[$x][$i] != $this->term_action[$y][$i]) {
                return $this->term_action[$x][$i] - $this->term_action[$y][$i];
            }
        }
        return 0;
    }
}
