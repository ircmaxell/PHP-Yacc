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
    protected $tokenToSymbolMapSize = 1;
    protected $actionTableSize      = 2;
    protected $gotoTableSize        = 0;

    protected $invalidSymbol       = 3;
    protected $errorSymbol         = 2;
    protected $defaultAction       = -32766;
    protected $unexpectedTokenRule = 32767;

    protected $YY2TBLSTATE = 0;
    protected $YYNLSTATES  = 3;

    protected $symbolToName = array(
        "EOF",
        "error",
        "'1'"
    );

    protected $tokenToSymbol = array(
            2
    );

    protected $action = array(
            2,    0
    );

    protected $actionCheck = array(
            2,    0
    );

    protected $actionBase = array(
           -2,    1,    0
    );

    protected $actionDefault = array(
        32767,32767,    1
    );

    protected $goto = array(
    );

    protected $gotoCheck = array(
    );

    protected $gotoBase = array(
            0,    0,    0
    );

    protected $gotoDefault = array(
        -32768,    1,-32768
    );

    protected $ruleToNonTerminal = array(
            0,    1
    );

    protected $ruleToLength = array(
            1,    1
    );

    protected $productions = array(
        "\$start : expr",
        "expr : '1'"
    );

    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
            0 => function ($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            1 => function ($stackPos) {
                 $this->semValue = 1; 
            },
        ];
    }
}
