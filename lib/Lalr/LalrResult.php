<?php

namespace PhpYacc\Lalr;

use PhpYacc\Yacc\ParseResult;

class LalrResult
{
    public array $grams;
    public int $nstates = 0;
    public array $states;
    public string $output;
    public int $nnonleafstates;

    public function __construct(array $grams, array $states, int $nnonleafstates, string $output)
    {
        $this->grams = $grams;
        $this->states = $states;
        $this->nstates = count($states);
        $this->output = $output;
        $this->nnonleafstates = $nnonleafstates;
    }
}
