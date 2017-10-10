<?php
declare(strict_types=1);
use PhpYacc\Lexer;
class Parser {
    const SYMBOL_NONE = -1;
    protected $unexpectedTokenRule = 32767;
    protected $defaultAction = -32766;
    protected $invalidSymbol = 5;
    protected $YYNLSTATES = 4;
    protected $YY2TBLSTATE = 0;
    protected $actionTableSize = 4;
    protected $gotoTableSize = 1;
    protected $tokenToSymbolMapSize = 258;
    protected $symbolToName = [
        'EOF',
        'error',
        '\'+\'',
        'T_FOO',
        '\'1\'',
    ];
    protected $errorSymbol = 1;
    protected $tokenToSymbol = [
            0,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    2,    4,    4,    4,    4,    4,    3,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    4,    4,    4,    4,
            4,    4,    4,    4,    4,    4,    1,    4,
    ];
    protected $action = [
            3,    0,    0,    2,
    ];
    protected $actionCheck = [
            3,    0,   -1,    2,
    ];
    protected $actionBase = [
           -3,    1,   -3,    0,
    ];
    protected $actionDefault = [
        32767,32767,32767,    2,
    ];
    protected $goto = [
            5,
    ];
    protected $gotoCheck = [
            0,   -2,
    ];
    protected $gotoBase = [
            0,   -2,
    ];
    protected $gotoDefault = [
        -32768,    1,
    ];
    protected $ruleToNonTerminal = [
            0,    0,    3,
    ];
    protected $ruleToLength = [
            1,    3,    1,
    ];
   protected $productions = [
        'start : expr',
        'expr : expr \'+\' expr',
        'expr : \'1\'',
    ];
    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
            0 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            1 => function($stackPos) {
                $this->semValue = $stackPos-(3-1) + $stackPos-(3-3);
            },
            2 => function($stackPos) {
                $this->semValue = 1;
            },
        ];
    }
}