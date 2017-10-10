<?php
declare(strict_types=1);
use PhpYacc\Lexer;
class Parser
{
    protected $tokenToSymbolMapSize = 257;
    protected $actionTableSize      = 1;
    protected $gotoTableSize        = 0;

    protected $invalidSymbol       = 3;
    protected $errorSymbol         = 1;
    protected $defaultAction       = -32766;
    protected $unexpectedTokenRule = 32767;

    protected $YY2TBLSTATE = 0;
    protected $YYNLSTATES  = 1;
    protected $symbolToName = [
        '$EOF',
        'error',
        '\'1\'',
    ];
    protected $tokenToSymbol = [
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    1,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    0,
    ];

    protected $action = [
            2,
    ];

    protected $actionCheck = [
            1,
    ];

    protected $actionBase = [
           -1,
    ];

    protected $actionDefault = [
        32767,32767,
    ];

    protected $goto = [
    ];

    protected $gotoCheck = [
            0,    0,
    ];

    protected $gotoBase = [
            0,    0,
    ];

    protected $gotoDefault = [
        -32768,    1,
    ];

    protected $ruleToNonTerminal = [
            0,    1,
    ];

    protected $ruleToLength = [
            1,    1,
    ];

   protected $productions = [
        '$start : expr',
        'expr : \'1\'',
    ];
    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
            0 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            1 => function($stackPos) {
                $this->semValue = 1;
            },
        ];
    }
}