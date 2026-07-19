<?php

declare(strict_types=1);

namespace PhpYacc\Compress;

class CompressResult
{
    public array $yytranslate = [];

    public array $yyaction = [];

    public array $yybase = [];
    public int $yybasesize;

    public array $yycheck = [];

    public array $yydefault = [];

    public array $yygoto = [];

    public array $yygbase = [];

    public array $yygcheck = [];

    public array $yygdefault = [];

    public array $yylhs = [];

    public array $yylen = [];

    public int $yyncterms = 0;
    public int $yytranslatesize = 0;
}
