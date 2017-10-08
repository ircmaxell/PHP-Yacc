<?php

namespace PhpParser\Parser;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

/* This is an automatically GENERATED file, which should not be manually edited.
 * Instead edit one of the following:
 *  * the grammar files grammar/php5.y or grammar/php7.y
 *  * the skeleton file grammar/parser.template
 *  * the preprocessing script grammar/rebuildParsers.php
 */
class Parser extends \PhpParser\ParserAbstract
{
    protected $tokenToSymbolMapSize = 257;
    protected $actionTableSize = 2;
    protected $gotoTableSize = 0;

    protected $invalidSymbol = 3;
    protected $errorSymbol = 1;
    protected $defaultAction = -32766;
    protected $unexpectedTokenRule = 32767;

    protected $YY2TBLSTATE  = 0;
    protected $YYNLSTATES   = 2;

    protected $symbolToName = array(
        "EOF",
        "error",
        "'1'"
    );

    protected $tokenToSymbol = array(
            0,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    2,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    1
    );

    protected $action = array(
            3,    0
    );

    protected $actionCheck = array(
            2,    0
    );

    protected $actionBase = array(
           -2,    1
    );

    protected $actionDefault = array(
        32767,32767
    );

    protected $goto = array(
    );

    protected $gotoCheck = array(
    );

    protected $gotoBase = array(
            0,    0
    );

    protected $gotoDefault = array(
        -32768,    1
    );

    protected $ruleToNonTerminal = array(
            0,    1
    );

    protected $ruleToLength = array(
            1,    1
    );

    protected $productions = array(
        "start : start",
        "start : '1'"
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
