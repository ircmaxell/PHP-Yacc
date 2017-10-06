<?php

namespace PhpYacc\Lalr;
use PhpYacc\Grammar\Context;

class LalrResult {
    public $nullable = [];
    public $first = [];
    public $statesThrough = [];
    public $states = [];
    public $follow = [];

    public $ctx;
    public function __construct(Context $ctx)
    {
        $this->ctx = $ctx;
        $nNonTerminals = count($ctx->nonTerminals());
        $this->nullable = array_fill(0, $nNonTerminals, false);
        $blank = str_repeat("\0", ceil($nNonTerminals / 8));
        $this->first = array_fill(0, $nNonTerminals, $blank);
        $this->follow = array_fill(0, $nNonTerminals, $blank);
        foreach ($ctx->symbols() as $symbol) {
            $this->statesThrough[$symbol->code] = new StateList();
        }
        $this->statesThrough[$ctx->nilsym()] = new StateList();
    }

} 