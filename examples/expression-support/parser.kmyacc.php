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
    protected $actionTableSize = 4;
    protected $gotoTableSize = 1;

    protected $invalidSymbol = 4;
    protected $errorSymbol = 1;
    protected $defaultAction = -32766;
    protected $unexpectedTokenRule = 32767;

    protected $YY2TBLSTATE  = 0;
    protected $YYNLSTATES   = 3;

    protected $symbolToName = array(
        "EOF",
        "error",
        "'+'",
        "'1'"
    );

    protected $tokenToSymbol = array(
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
            4,    4,    4,    4,    4,    4,    1,    4
    );

    protected $action = array(
            5,    0,    0,    2
    );

    protected $actionCheck = array(
            3,    0,   -1,    2
    );

    protected $actionBase = array(
           -3,    1,   -3
    );

    protected $actionDefault = array(
        32767,32767,32767
    );

    protected $goto = array(
            4
    );

    protected $gotoCheck = array(
            1
    );

    protected $gotoBase = array(
            0,   -2
    );

    protected $gotoDefault = array(
        -32768,    1
    );

    protected $ruleToNonTerminal = array(
            0,    1,    1
    );

    protected $ruleToLength = array(
            1,    3,    1
    );

    protected $productions = array(
        "start : expr",
        "expr : expr '+' expr",
        "expr : '1'"
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
        ];
    }
}
