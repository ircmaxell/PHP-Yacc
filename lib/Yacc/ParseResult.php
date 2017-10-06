<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Grammar\Context;

class ParseResult {
    public $pureFlag = false;
    public $startSymbol = null;
    public $expected = null;
    public $unioned = false;
    public $eofToken = null;
    public $startPrime = null;
    public $grams = [];

    public $ctx;

    public function __construct(Context $ctx)
    {
        $this->ctx = $ctx;
    }

    public function addGram(Production $p)
    {
        $this->grams[] = $p;
        return $p;
    }

    public function gram(int $i): Production
    {
        return $this->grams[$i];
    }

    /** @return Production[] */
    public function grams(): array
    {
        return $this->grams;
    }
}