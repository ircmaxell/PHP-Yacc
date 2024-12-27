<?php

declare(strict_types=1);

namespace PhpYacc\Compress;

use PhpYacc\Grammar\Symbol;
use PhpYacc\Lalr\State;

class CompressResult
{
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

    public $yyncterms = 0;
    public $yytranslatesize = 0;
}
