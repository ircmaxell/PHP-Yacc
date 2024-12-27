<?php

namespace PhpYacc\Lalr;

use PhpYacc\Yacc\ParseResult;

class LalrResult
{
    public $grams;
    public $nstates = 0;
    public $states;
    public $output;
    public $nnonleafstates;

    public function __construct(array $grams, array $states, int $nnonleafstates, string $output)
    {
        $this->grams = $grams;
        $this->states = $states;
        $this->nstates = count($states);
        $this->output = $output;
        $this->nnonleafstates = $nnonleafstates;
    }
}
