<?php

namespace PhpYacc\Lalr;
use PhpYacc\Yacc\ParseResult;

class LalrResult {
    public $grams;
    public $states;
    public $output;
    
    public function __construct(array $grams, array $states, string $output)
    {
        $this->grams = $grams;
        $this->states = $states;
        $this->output = $output;
    }

} 