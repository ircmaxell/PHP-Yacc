<?php

namespace PhpParser\Parser;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

/* This is an automatically GENERATED file, which should not be manually edited.
 */
class Parser extends \PhpParser\ParserAbstract
{
    protected $tokenToSymbolMapSize = 258;
    protected $actionTableSize      = 7;
    protected $gotoTableSize        = 2;

    protected $invalidSymbol       = 5;
    protected $errorSymbol         = 1;
    protected $defaultAction       = -32766;
    protected $unexpectedTokenRule = 32767;

    protected $YY2TBLSTATE = 0;
    protected $YYNLSTATES  = 4;

    protected $symbolToName = array(
        "EOF",
        "error",
        "'+'",
        "T_FOO",
        "'1'"
    );

    protected $tokenToSymbol = array(
            0,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    2,    5,    5,    5,    5,    5,    4,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    5,    5,    5,    5,
            5,    5,    5,    5,    5,    5,    1,    3
    );

    protected $action = array(
            0,    0,    0,    2,    0,    1,    6
    );

    protected $actionCheck = array(
           -1,    0,   -1,    2,   -1,    3,    4
    );

    protected $actionBase = array(
            2,    2,    2,    1
    );

    protected $actionDefault = array(
        32767,32767,32767,32767
    );

    protected $goto = array(
            7,    5
    );

    protected $gotoCheck = array(
            1,    1
    );

    protected $gotoBase = array(
            0,   -1
    );

    protected $gotoDefault = array(
        -32768,    3
    );

    protected $ruleToNonTerminal = array(
            0,    1,    1,    1
    );

    protected $ruleToLength = array(
            1,    3,    1,    2
    );

    protected $productions = array(
        "\$start : expr",
        "expr : expr '+' expr",
        "expr : '1'",
        "expr : T_FOO expr"
    );

    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
            0 => function ($stackPos) {
            $this->semValue = $this->semStack[$stackPos];
        },
            1 => function ($stackPos) {
             $this->semValue = $stackPos-(3-1) + $stackPos-(3-3); 
            },
            2 => function ($stackPos) {
             $this->semValue = 1; 
            },
            3 => function ($stackPos) {
             $this->semValue = $stackPos-(2-2); 
            },
        ];
    }
}
