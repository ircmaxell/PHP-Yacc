<?php
declare(strict_types=1);
use PhpYacc\Lexer;
class Parser {
    const SYMBOL_NONE = -1;
    protected $unexpectedTokenRule = 32767;
    protected $defaultAction = -32766;
    protected $invalidSymbol = 165;
    protected $YYNLSTATES = 0;
    protected $YY2TBLSTATE = 0;
    protected $actionTableSize = 0;
    protected $gotoTableSize = 0;
    protected $tokenToSymbolMapSize = 392;
    protected $symbolToName = [
        'EOF',
        'error',
        'T_INCLUDE',
        'T_INCLUDE_ONCE',
        'T_EVAL',
        'T_REQUIRE',
        'T_REQUIRE_ONCE',
        '\',\'',
        'T_LOGICAL_OR',
        'T_LOGICAL_XOR',
        'T_LOGICAL_AND',
        'T_PRINT',
        'T_YIELD',
        'T_DOUBLE_ARROW',
        'T_YIELD_FROM',
        '\'=\'',
        'T_PLUS_EQUAL',
        'T_MINUS_EQUAL',
        'T_MUL_EQUAL',
        'T_DIV_EQUAL',
        'T_CONCAT_EQUAL',
        'T_MOD_EQUAL',
        'T_AND_EQUAL',
        'T_OR_EQUAL',
        'T_XOR_EQUAL',
        'T_SL_EQUAL',
        'T_SR_EQUAL',
        'T_POW_EQUAL',
        '\'?\'',
        '\':\'',
        'T_COALESCE',
        'T_BOOLEAN_OR',
        'T_BOOLEAN_AND',
        '\'|\'',
        '\'^\'',
        '\'&\'',
        'T_IS_EQUAL',
        'T_IS_NOT_EQUAL',
        'T_IS_IDENTICAL',
        'T_IS_NOT_IDENTICAL',
        'T_SPACESHIP',
        '\'<\'',
        'T_IS_SMALLER_OR_EQUAL',
        '\'>\'',
        'T_IS_GREATER_OR_EQUAL',
        'T_SL',
        'T_SR',
        '\'+\'',
        '\'-\'',
        '\'.\'',
        '\'*\'',
        '\'/\'',
        '\'%\'',
        '\'!\'',
        'T_INSTANCEOF',
        '\'~\'',
        'T_INC',
        'T_DEC',
        'T_INT_CAST',
        'T_DOUBLE_CAST',
        'T_STRING_CAST',
        'T_ARRAY_CAST',
        'T_OBJECT_CAST',
        'T_BOOL_CAST',
        'T_UNSET_CAST',
        '\'@\'',
        'T_POW',
        '\'[\'',
        'T_NEW',
        'T_CLONE',
        'T_EXIT',
        'T_IF',
        'T_ELSEIF',
        'T_ELSE',
        'T_ENDIF',
        'T_LNUMBER',
        'T_DNUMBER',
        'T_STRING',
        'T_STRING_VARNAME',
        'T_VARIABLE',
        'T_NUM_STRING',
        'T_INLINE_HTML',
        'T_CHARACTER',
        'T_BAD_CHARACTER',
        'T_ENCAPSED_AND_WHITESPACE',
        'T_CONSTANT_ENCAPSED_STRING',
        'T_ECHO',
        'T_DO',
        'T_WHILE',
        'T_ENDWHILE',
        'T_FOR',
        'T_ENDFOR',
        'T_FOREACH',
        'T_ENDFOREACH',
        'T_DECLARE',
        'T_ENDDECLARE',
        'T_AS',
        'T_SWITCH',
        'T_ENDSWITCH',
        'T_CASE',
        'T_DEFAULT',
        'T_BREAK',
        'T_CONTINUE',
        'T_GOTO',
        'T_FUNCTION',
        'T_CONST',
        'T_RETURN',
        'T_TRY',
        'T_CATCH',
        'T_FINALLY',
        'T_THROW',
        'T_USE',
        'T_INSTEADOF',
        'T_GLOBAL',
        'T_STATIC',
        'T_ABSTRACT',
        'T_FINAL',
        'T_PRIVATE',
        'T_PROTECTED',
        'T_PUBLIC',
        'T_VAR',
        'T_UNSET',
        'T_ISSET',
        'T_EMPTY',
        'T_HALT_COMPILER',
        'T_CLASS',
        'T_TRAIT',
        'T_INTERFACE',
        'T_EXTENDS',
        'T_IMPLEMENTS',
        'T_OBJECT_OPERATOR',
        'T_LIST',
        'T_ARRAY',
        'T_CALLABLE',
        'T_CLASS_C',
        'T_TRAIT_C',
        'T_METHOD_C',
        'T_FUNC_C',
        'T_LINE',
        'T_FILE',
        'T_COMMENT',
        'T_DOC_COMMENT',
        'T_OPEN_TAG',
        'T_OPEN_TAG_WITH_ECHO',
        'T_CLOSE_TAG',
        'T_WHITESPACE',
        'T_START_HEREDOC',
        'T_END_HEREDOC',
        'T_DOLLAR_OPEN_CURLY_BRACES',
        'T_CURLY_OPEN',
        'T_PAAMAYIM_NEKUDOTAYIM',
        'T_NAMESPACE',
        'T_NS_C',
        'T_DIR',
        'T_NS_SEPARATOR',
        'T_ELLIPSIS',
        '\';\'',
        '\'{\'',
        '\'}\'',
        '\'(\'',
        '\')\'',
        '\'`\'',
        '\']\'',
        '\'"\'',
        '\'$\'',
    ];
    protected $errorSymbol = 1;
    protected $tokenToSymbol = [
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    0,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,
    ];
    protected $action = [
    ];
    protected $actionCheck = [
    ];
    protected $actionBase = [
    ];
    protected $actionDefault = [
        32767,
    ];
    protected $goto = [
    ];
    protected $gotoCheck = [
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,
    ];
    protected $gotoBase = [
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,    0,    0,    0,    0,    0,    0,    0,    0,
            0,    0,
    ];
    protected $gotoDefault = [
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,-32768,
        -32768,-32768,
    ];
    protected $ruleToNonTerminal = [
           -1,    0,    1,   -1,    1,    1,    2,    3,    4,    5,
            7,    8,    9,   53,   67,   68,   69,   70,   71,   72,
           73,   85,   86,   87,   88,   89,   90,   91,   92,   93,
           94,   95,  106,  107,  108,  109,  110,  111,  112,  119,
          120,  121,  122,  101,  102,  103,  104,  105,   10,   11,
          130,   96,   97,   98,   99,  100,  131,  132,  127,  128,
          150,  125,  126,  124,  133,  134,  136,  135,  137,  138,
          152,  151,  123,    3,  113,  114,  115,  116,  117,  118,
           76,    4,   76,    6,    6,  155,    0,   -1,    6,   -1,
            6,   11,   12,   13,  123,  150,  150,  150,  110,  110,
           16,  104,  103,  104,  110,  110,  110,  110,   20,   20,
           21,   22,   22,   23,   24,   24,   25,    7,    7,   21,
          153,   21,   15,   26,   26,   27,   76,   30,   30,   31,
            5,   32,   -1,   32,   11,   12,   13,  123,  156,   70,
           70,   87,   86,   89,   96,  100,  101,  105,  112,  113,
           85,   80,   28,  120,   91,   91,   93,  106,  109,  102,
           76,    0,   35,  155,   -1,   54,   58,   57,  107,   -1,
          108,   59,   51,   59,   -1,   34,   -1,  154,  103,   64,
          126,  125,  124,  114,  115,   -1,  127,   -1,  127,   -1,
          128,   71,   69,   71,   11,   28,   11,   28,   35,  155,
           28,   72,   73,   72,   76,  156,  156,   28,   28,   -1,
           74,   98,   99,   28,  155,   11,   28,   -1,   36,   71,
           -1,   38,   71,   -1,   72,   -1,   72,   51,   34,   79,
           80,   81,   -1,   82,   81,   83,   83,   85,   27,   58,
          131,  132,   -1,   84,   -1,   28,  158,  158,   88,   87,
           28,   34,  154,   89,   89,   90,   91,   92,   92,   93,
           78,   78,   67,   -1,   95,   97,   97,  110,  155,  156,
           -1,  100,  102,  103,  103,  103,  103,   58,  102,    5,
          155,  156,  105,  119,   -1,  105,  104,  105,  118,  117,
          116,  113,  114,  115,  106,  107,  106,   78,   78,  108,
          108,   28,   -1,   47,   51,   79,   80,   51,   51,  109,
           68,   51,   51,   51,   51,   51,   51,   51,   51,   51,
           51,   51,   51,   51,   55,   51,   56,   28,   28,   28,
           28,   28,   28,   28,   28,   28,   28,   28,   28,   28,
           28,   28,   28,   28,   46,   47,   52,   54,   28,   28,
           28,   28,   28,   28,   28,   28,   28,   28,  158,   28,
           28,   28,  121,  122,    1,    2,    3,    4,    5,   57,
           58,   59,   60,   61,   62,   63,   69,   64,  112,  160,
           10,   11,   11,   11,   13,  103,  113,  124,   67,   67,
           -1,  110,  118,  119,  118,   60,   58,  121,  122,  113,
           58,    6,  153,  150,   69,  124,    0,   69,  125,   -1,
          158,   -1,   83,  126,   -1,   86,   58,  122,  122,   66,
          131,   80,   84,   74,   75,  137,  138,  152,  133,  134,
          135,  136,  151,  129,  127,  145,  145,  162,  145,   -1,
           28,   51,  158,  129,  130,  158,  129,   91,  125,  127,
          125,  120,  125,  130,  132,  125,   78,  163,  163,  163,
          122,   91,  124,  124,  124,   69,  124,    5,  156,   91,
           76,  156,   91,    0,  130,  133,  134,   51,   79,   28,
           28,   -1,  135,  135,  136,   28,   28,   28,   34,   -1,
          126,  126,  137,  138,   83,   78,  139,  139,  139,  147,
          147,  147,  148,   76,   79,   47,   78,
    ];
    protected $ruleToLength = [
            1,    1,    2,    0,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    3,    1,    1,    1,    0,    1,    0,
            1,    1,    1,    1,    1,    3,    5,    4,    3,    4,
            2,    3,    1,    1,    7,    8,    6,    7,    2,    3,
            1,    2,    3,    1,    2,    3,    1,    1,    3,    1,
            2,    1,    2,    2,    3,    1,    3,    2,    3,    1,
            3,    2,    0,    1,    1,    1,    1,    1,    3,    7,
           10,    5,    7,    9,    5,    3,    3,    3,    3,    3,
            3,    1,    2,    5,    7,    9,    5,    6,    3,    3,
            2,    1,    1,    1,    0,    2,    1,    3,    8,    0,
            4,    2,    1,    3,    0,    1,    0,    1,   10,    7,
            6,    5,    1,    2,    2,    0,    2,    0,    2,    0,
            2,    2,    1,    3,    1,    4,    1,    4,    1,    1,
            4,    2,    1,    3,    3,    3,    4,    4,    5,    0,
            2,    4,    3,    1,    1,    1,    4,    0,    2,    5,
            0,    2,    6,    0,    2,    0,    3,    1,    2,    1,
            1,    2,    0,    1,    3,    4,    6,    1,    2,    1,
            1,    1,    0,    1,    0,    2,    2,    4,    1,    3,
            1,    2,    2,    2,    3,    1,    1,    2,    3,    1,
            1,    3,    2,    0,    3,    4,    9,    3,    1,    3,
            0,    2,    4,    5,    4,    4,    4,    3,    1,    1,
            1,    3,    1,    1,    0,    1,    1,    2,    1,    1,
            1,    1,    1,    1,    2,    1,    3,    1,    3,    2,
            3,    1,    0,    1,    1,    3,    3,    3,    4,    1,
            2,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    2,    2,    2,    2,    3,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    3,
            3,    3,    3,    3,    2,    2,    2,    2,    3,    3,
            3,    3,    3,    3,    3,    3,    3,    3,    3,    5,
            4,    3,    4,    4,    2,    2,    4,    2,    2,    2,
            2,    2,    2,    2,    2,    2,    2,    2,    1,    3,
            2,    1,    2,    4,    2,   10,   11,    7,    3,    2,
            0,    4,    2,    1,    3,    2,    2,    2,    4,    1,
            1,    1,    2,    3,    1,    1,    1,    1,    1,    0,
            3,    0,    1,    1,    0,    1,    1,    3,    3,    3,
            4,    1,    1,    1,    1,    1,    1,    1,    1,    1,
            1,    1,    1,    1,    1,    3,    2,    3,    3,    0,
            1,    1,    3,    1,    1,    3,    1,    1,    4,    4,
            4,    1,    4,    1,    1,    3,    1,    4,    2,    2,
            3,    1,    4,    4,    3,    3,    3,    1,    3,    1,
            1,    3,    1,    1,    4,    3,    1,    1,    1,    3,
            3,    0,    1,    3,    1,    3,    1,    4,    2,    0,
            2,    2,    1,    2,    1,    1,    1,    4,    3,    3,
            3,    6,    3,    1,    1,    2,    1,
    ];
   protected $productions = [
        'start : start',
        'start : top_statement_list',
        'top_statement_list_ex : top_statement_list_ex top_statement',
        'top_statement_list_ex : ',
        'top_statement_list : top_statement_list_ex',
        'reserved_non_modifiers : T_INCLUDE',
        'reserved_non_modifiers : T_INCLUDE_ONCE',
        'reserved_non_modifiers : T_EVAL',
        'reserved_non_modifiers : T_REQUIRE',
        'reserved_non_modifiers : T_REQUIRE_ONCE',
        'reserved_non_modifiers : T_LOGICAL_OR',
        'reserved_non_modifiers : T_LOGICAL_XOR',
        'reserved_non_modifiers : T_LOGICAL_AND',
        'reserved_non_modifiers : T_INSTANCEOF',
        'reserved_non_modifiers : T_NEW',
        'reserved_non_modifiers : T_CLONE',
        'reserved_non_modifiers : T_EXIT',
        'reserved_non_modifiers : T_IF',
        'reserved_non_modifiers : T_ELSEIF',
        'reserved_non_modifiers : T_ELSE',
        'reserved_non_modifiers : T_ENDIF',
        'reserved_non_modifiers : T_ECHO',
        'reserved_non_modifiers : T_DO',
        'reserved_non_modifiers : T_WHILE',
        'reserved_non_modifiers : T_ENDWHILE',
        'reserved_non_modifiers : T_FOR',
        'reserved_non_modifiers : T_ENDFOR',
        'reserved_non_modifiers : T_FOREACH',
        'reserved_non_modifiers : T_ENDFOREACH',
        'reserved_non_modifiers : T_DECLARE',
        'reserved_non_modifiers : T_ENDDECLARE',
        'reserved_non_modifiers : T_AS',
        'reserved_non_modifiers : T_TRY',
        'reserved_non_modifiers : T_CATCH',
        'reserved_non_modifiers : T_FINALLY',
        'reserved_non_modifiers : T_THROW',
        'reserved_non_modifiers : T_USE',
        'reserved_non_modifiers : T_INSTEADOF',
        'reserved_non_modifiers : T_GLOBAL',
        'reserved_non_modifiers : T_VAR',
        'reserved_non_modifiers : T_UNSET',
        'reserved_non_modifiers : T_ISSET',
        'reserved_non_modifiers : T_EMPTY',
        'reserved_non_modifiers : T_CONTINUE',
        'reserved_non_modifiers : T_GOTO',
        'reserved_non_modifiers : T_FUNCTION',
        'reserved_non_modifiers : T_CONST',
        'reserved_non_modifiers : T_RETURN',
        'reserved_non_modifiers : T_PRINT',
        'reserved_non_modifiers : T_YIELD',
        'reserved_non_modifiers : T_LIST',
        'reserved_non_modifiers : T_SWITCH',
        'reserved_non_modifiers : T_ENDSWITCH',
        'reserved_non_modifiers : T_CASE',
        'reserved_non_modifiers : T_DEFAULT',
        'reserved_non_modifiers : T_BREAK',
        'reserved_non_modifiers : T_ARRAY',
        'reserved_non_modifiers : T_CALLABLE',
        'reserved_non_modifiers : T_EXTENDS',
        'reserved_non_modifiers : T_IMPLEMENTS',
        'reserved_non_modifiers : T_NAMESPACE',
        'reserved_non_modifiers : T_TRAIT',
        'reserved_non_modifiers : T_INTERFACE',
        'reserved_non_modifiers : T_CLASS',
        'reserved_non_modifiers : T_CLASS_C',
        'reserved_non_modifiers : T_TRAIT_C',
        'reserved_non_modifiers : T_FUNC_C',
        'reserved_non_modifiers : T_METHOD_C',
        'reserved_non_modifiers : T_LINE',
        'reserved_non_modifiers : T_FILE',
        'reserved_non_modifiers : T_DIR',
        'reserved_non_modifiers : T_NS_C',
        'reserved_non_modifiers : T_HALT_COMPILER',
        'semi_reserved : reserved_non_modifiers',
        'semi_reserved : T_STATIC',
        'semi_reserved : T_ABSTRACT',
        'semi_reserved : T_FINAL',
        'semi_reserved : T_PRIVATE',
        'semi_reserved : T_PROTECTED',
        'semi_reserved : T_PUBLIC',
        'identifier : T_STRING',
        'identifier : semi_reserved',
        'namespace_name_parts : T_STRING',
        'namespace_name_parts : namespace_name_parts T_NS_SEPARATOR T_STRING',
        'namespace_name : namespace_name_parts',
        'semi : \';\'',
        'semi : error',
        'no_comma : ',
        'no_comma : \',\'',
        'optional_comma : ',
        'optional_comma : \',\'',
        'top_statement : statement',
        'top_statement : function_declaration_statement',
        'top_statement : class_declaration_statement',
        'top_statement : T_HALT_COMPILER',
        'top_statement : T_NAMESPACE namespace_name semi',
        'top_statement : T_NAMESPACE namespace_name \'{\' top_statement_list \'}\'',
        'top_statement : T_NAMESPACE \'{\' top_statement_list \'}\'',
        'top_statement : T_USE use_declarations semi',
        'top_statement : T_USE use_type use_declarations semi',
        'top_statement : group_use_declaration semi',
        'top_statement : T_CONST constant_declaration_list semi',
        'use_type : T_FUNCTION',
        'use_type : T_CONST',
        'group_use_declaration : T_USE use_type namespace_name_parts T_NS_SEPARATOR \'{\' unprefixed_use_declarations \'}\'',
        'group_use_declaration : T_USE use_type T_NS_SEPARATOR namespace_name_parts T_NS_SEPARATOR \'{\' unprefixed_use_declarations \'}\'',
        'group_use_declaration : T_USE namespace_name_parts T_NS_SEPARATOR \'{\' inline_use_declarations \'}\'',
        'group_use_declaration : T_USE T_NS_SEPARATOR namespace_name_parts T_NS_SEPARATOR \'{\' inline_use_declarations \'}\'',
        'unprefixed_use_declarations : non_empty_unprefixed_use_declarations optional_comma',
        'non_empty_unprefixed_use_declarations : non_empty_unprefixed_use_declarations \',\' unprefixed_use_declaration',
        'non_empty_unprefixed_use_declarations : unprefixed_use_declaration',
        'use_declarations : non_empty_use_declarations no_comma',
        'non_empty_use_declarations : non_empty_use_declarations \',\' use_declaration',
        'non_empty_use_declarations : use_declaration',
        'inline_use_declarations : non_empty_inline_use_declarations optional_comma',
        'non_empty_inline_use_declarations : non_empty_inline_use_declarations \',\' inline_use_declaration',
        'non_empty_inline_use_declarations : inline_use_declaration',
        'unprefixed_use_declaration : namespace_name',
        'unprefixed_use_declaration : namespace_name T_AS T_STRING',
        'use_declaration : unprefixed_use_declaration',
        'use_declaration : T_NS_SEPARATOR unprefixed_use_declaration',
        'inline_use_declaration : unprefixed_use_declaration',
        'inline_use_declaration : use_type unprefixed_use_declaration',
        'constant_declaration_list : non_empty_constant_declaration_list no_comma',
        'non_empty_constant_declaration_list : non_empty_constant_declaration_list \',\' constant_declaration',
        'non_empty_constant_declaration_list : constant_declaration',
        'constant_declaration : T_STRING \'=\' expr',
        'class_const_list : non_empty_class_const_list no_comma',
        'non_empty_class_const_list : non_empty_class_const_list \',\' class_const',
        'non_empty_class_const_list : class_const',
        'class_const : identifier \'=\' expr',
        'inner_statement_list_ex : inner_statement_list_ex inner_statement',
        'inner_statement_list_ex : ',
        'inner_statement_list : inner_statement_list_ex',
        'inner_statement : statement',
        'inner_statement : function_declaration_statement',
        'inner_statement : class_declaration_statement',
        'inner_statement : T_HALT_COMPILER',
        'non_empty_statement : \'{\' inner_statement_list \'}\'',
        'non_empty_statement : T_IF \'(\' expr \')\' statement elseif_list else_single',
        'non_empty_statement : T_IF \'(\' expr \')\' \':\' inner_statement_list new_elseif_list new_else_single T_ENDIF \';\'',
        'non_empty_statement : T_WHILE \'(\' expr \')\' while_statement',
        'non_empty_statement : T_DO statement T_WHILE \'(\' expr \')\' \';\'',
        'non_empty_statement : T_FOR \'(\' for_expr \';\' for_expr \';\' for_expr \')\' for_statement',
        'non_empty_statement : T_SWITCH \'(\' expr \')\' switch_case_list',
        'non_empty_statement : T_BREAK optional_expr semi',
        'non_empty_statement : T_CONTINUE optional_expr semi',
        'non_empty_statement : T_RETURN optional_expr semi',
        'non_empty_statement : T_GLOBAL global_var_list semi',
        'non_empty_statement : T_STATIC static_var_list semi',
        'non_empty_statement : T_ECHO expr_list semi',
        'non_empty_statement : T_INLINE_HTML',
        'non_empty_statement : expr semi',
        'non_empty_statement : T_UNSET \'(\' variables_list \')\' semi',
        'non_empty_statement : T_FOREACH \'(\' expr T_AS foreach_variable \')\' foreach_statement',
        'non_empty_statement : T_FOREACH \'(\' expr T_AS variable T_DOUBLE_ARROW foreach_variable \')\' foreach_statement',
        'non_empty_statement : T_DECLARE \'(\' declare_list \')\' declare_statement',
        'non_empty_statement : T_TRY \'{\' inner_statement_list \'}\' catches optional_finally',
        'non_empty_statement : T_THROW expr semi',
        'non_empty_statement : T_GOTO T_STRING semi',
        'non_empty_statement : T_STRING \':\'',
        'non_empty_statement : error',
        'statement : non_empty_statement',
        'statement : \';\'',
        'catches : ',
        'catches : catches catch',
        'name_union : name',
        'name_union : name_union \'|\' name',
        'catch : T_CATCH \'(\' name_union T_VARIABLE \')\' \'{\' inner_statement_list \'}\'',
        'optional_finally : ',
        'optional_finally : T_FINALLY \'{\' inner_statement_list \'}\'',
        'variables_list : non_empty_variables_list no_comma',
        'non_empty_variables_list : variable',
        'non_empty_variables_list : non_empty_variables_list \',\' variable',
        'optional_ref : ',
        'optional_ref : \'&\'',
        'optional_ellipsis : ',
        'optional_ellipsis : T_ELLIPSIS',
        'function_declaration_statement : T_FUNCTION optional_ref T_STRING \'(\' parameter_list \')\' optional_return_type \'{\' inner_statement_list \'}\'',
        'class_declaration_statement : class_entry_type T_STRING extends_from implements_list \'{\' class_statement_list \'}\'',
        'class_declaration_statement : T_INTERFACE T_STRING interface_extends_list \'{\' class_statement_list \'}\'',
        'class_declaration_statement : T_TRAIT T_STRING \'{\' class_statement_list \'}\'',
        'class_entry_type : T_CLASS',
        'class_entry_type : T_ABSTRACT T_CLASS',
        'class_entry_type : T_FINAL T_CLASS',
        'extends_from : ',
        'extends_from : T_EXTENDS class_name',
        'interface_extends_list : ',
        'interface_extends_list : T_EXTENDS class_name_list',
        'implements_list : ',
        'implements_list : T_IMPLEMENTS class_name_list',
        'class_name_list : non_empty_class_name_list no_comma',
        'non_empty_class_name_list : class_name',
        'non_empty_class_name_list : non_empty_class_name_list \',\' class_name',
        'for_statement : statement',
        'for_statement : \':\' inner_statement_list T_ENDFOR \';\'',
        'foreach_statement : statement',
        'foreach_statement : \':\' inner_statement_list T_ENDFOREACH \';\'',
        'declare_statement : non_empty_statement',
        'declare_statement : \';\'',
        'declare_statement : \':\' inner_statement_list T_ENDDECLARE \';\'',
        'declare_list : non_empty_declare_list no_comma',
        'non_empty_declare_list : declare_list_element',
        'non_empty_declare_list : non_empty_declare_list \',\' declare_list_element',
        'declare_list_element : T_STRING \'=\' expr',
        'switch_case_list : \'{\' case_list \'}\'',
        'switch_case_list : \'{\' \';\' case_list \'}\'',
        'switch_case_list : \':\' case_list T_ENDSWITCH \';\'',
        'switch_case_list : \':\' \';\' case_list T_ENDSWITCH \';\'',
        'case_list : ',
        'case_list : case_list case',
        'case : T_CASE expr case_separator inner_statement_list',
        'case : T_DEFAULT case_separator inner_statement_list',
        'case_separator : \':\'',
        'case_separator : \';\'',
        'while_statement : statement',
        'while_statement : \':\' inner_statement_list T_ENDWHILE \';\'',
        'elseif_list : ',
        'elseif_list : elseif_list elseif',
        'elseif : T_ELSEIF \'(\' expr \')\' statement',
        'new_elseif_list : ',
        'new_elseif_list : new_elseif_list new_elseif',
        'new_elseif : T_ELSEIF \'(\' expr \')\' \':\' inner_statement_list',
        'else_single : ',
        'else_single : T_ELSE statement',
        'new_else_single : ',
        'new_else_single : T_ELSE \':\' inner_statement_list',
        'foreach_variable : variable',
        'foreach_variable : \'&\' variable',
        'foreach_variable : list_expr',
        'foreach_variable : array_short_syntax',
        'parameter_list : non_empty_parameter_list no_comma',
        'parameter_list : ',
        'non_empty_parameter_list : parameter',
        'non_empty_parameter_list : non_empty_parameter_list \',\' parameter',
        'parameter : optional_param_type optional_ref optional_ellipsis T_VARIABLE',
        'parameter : optional_param_type optional_ref optional_ellipsis T_VARIABLE \'=\' expr',
        'type_expr : type',
        'type_expr : \'?\' type',
        'type : name',
        'type : T_ARRAY',
        'type : T_CALLABLE',
        'optional_param_type : ',
        'optional_param_type : type_expr',
        'optional_return_type : ',
        'optional_return_type : \':\' type_expr',
        'argument_list : \'(\' \')\'',
        'argument_list : \'(\' non_empty_argument_list no_comma \')\'',
        'non_empty_argument_list : argument',
        'non_empty_argument_list : non_empty_argument_list \',\' argument',
        'argument : expr',
        'argument : \'&\' variable',
        'argument : T_ELLIPSIS expr',
        'global_var_list : non_empty_global_var_list no_comma',
        'non_empty_global_var_list : non_empty_global_var_list \',\' global_var',
        'non_empty_global_var_list : global_var',
        'global_var : simple_variable',
        'static_var_list : non_empty_static_var_list no_comma',
        'non_empty_static_var_list : non_empty_static_var_list \',\' static_var',
        'non_empty_static_var_list : static_var',
        'static_var : T_VARIABLE',
        'static_var : T_VARIABLE \'=\' expr',
        'class_statement_list : class_statement_list class_statement',
        'class_statement_list : ',
        'class_statement : variable_modifiers property_declaration_list \';\'',
        'class_statement : method_modifiers T_CONST class_const_list \';\'',
        'class_statement : method_modifiers T_FUNCTION optional_ref identifier \'(\' parameter_list \')\' optional_return_type method_body',
        'class_statement : T_USE class_name_list trait_adaptations',
        'trait_adaptations : \';\'',
        'trait_adaptations : \'{\' trait_adaptation_list \'}\'',
        'trait_adaptation_list : ',
        'trait_adaptation_list : trait_adaptation_list trait_adaptation',
        'trait_adaptation : trait_method_reference_fully_qualified T_INSTEADOF class_name_list \';\'',
        'trait_adaptation : trait_method_reference T_AS member_modifier identifier \';\'',
        'trait_adaptation : trait_method_reference T_AS member_modifier \';\'',
        'trait_adaptation : trait_method_reference T_AS T_STRING \';\'',
        'trait_adaptation : trait_method_reference T_AS reserved_non_modifiers \';\'',
        'trait_method_reference_fully_qualified : name T_PAAMAYIM_NEKUDOTAYIM identifier',
        'trait_method_reference : trait_method_reference_fully_qualified',
        'trait_method_reference : identifier',
        'method_body : \';\'',
        'method_body : \'{\' inner_statement_list \'}\'',
        'variable_modifiers : non_empty_member_modifiers',
        'variable_modifiers : T_VAR',
        'method_modifiers : ',
        'method_modifiers : non_empty_member_modifiers',
        'non_empty_member_modifiers : member_modifier',
        'non_empty_member_modifiers : non_empty_member_modifiers member_modifier',
        'member_modifier : T_PUBLIC',
        'member_modifier : T_PROTECTED',
        'member_modifier : T_PRIVATE',
        'member_modifier : T_STATIC',
        'member_modifier : T_ABSTRACT',
        'member_modifier : T_FINAL',
        'property_declaration_list : non_empty_property_declaration_list no_comma',
        'non_empty_property_declaration_list : property_declaration',
        'non_empty_property_declaration_list : non_empty_property_declaration_list \',\' property_declaration',
        'property_declaration : T_VARIABLE',
        'property_declaration : T_VARIABLE \'=\' expr',
        'expr_list : non_empty_expr_list no_comma',
        'non_empty_expr_list : non_empty_expr_list \',\' expr',
        'non_empty_expr_list : expr',
        'for_expr : ',
        'for_expr : expr_list',
        'expr : variable',
        'expr : list_expr \'=\' expr',
        'expr : array_short_syntax \'=\' expr',
        'expr : variable \'=\' expr',
        'expr : variable \'=\' \'&\' variable',
        'expr : new_expr',
        'expr : T_CLONE expr',
        'expr : variable T_PLUS_EQUAL expr',
        'expr : variable T_MINUS_EQUAL expr',
        'expr : variable T_MUL_EQUAL expr',
        'expr : variable T_DIV_EQUAL expr',
        'expr : variable T_CONCAT_EQUAL expr',
        'expr : variable T_MOD_EQUAL expr',
        'expr : variable T_AND_EQUAL expr',
        'expr : variable T_OR_EQUAL expr',
        'expr : variable T_XOR_EQUAL expr',
        'expr : variable T_SL_EQUAL expr',
        'expr : variable T_SR_EQUAL expr',
        'expr : variable T_POW_EQUAL expr',
        'expr : variable T_INC',
        'expr : T_INC variable',
        'expr : variable T_DEC',
        'expr : T_DEC variable',
        'expr : expr T_BOOLEAN_OR expr',
        'expr : expr T_BOOLEAN_AND expr',
        'expr : expr T_LOGICAL_OR expr',
        'expr : expr T_LOGICAL_AND expr',
        'expr : expr T_LOGICAL_XOR expr',
        'expr : expr \'|\' expr',
        'expr : expr \'&\' expr',
        'expr : expr \'^\' expr',
        'expr : expr \'.\' expr',
        'expr : expr \'+\' expr',
        'expr : expr \'-\' expr',
        'expr : expr \'*\' expr',
        'expr : expr \'/\' expr',
        'expr : expr \'%\' expr',
        'expr : expr T_SL expr',
        'expr : expr T_SR expr',
        'expr : expr T_POW expr',
        'expr : \'+\' expr',
        'expr : \'-\' expr',
        'expr : \'!\' expr',
        'expr : \'~\' expr',
        'expr : expr T_IS_IDENTICAL expr',
        'expr : expr T_IS_NOT_IDENTICAL expr',
        'expr : expr T_IS_EQUAL expr',
        'expr : expr T_IS_NOT_EQUAL expr',
        'expr : expr T_SPACESHIP expr',
        'expr : expr \'<\' expr',
        'expr : expr T_IS_SMALLER_OR_EQUAL expr',
        'expr : expr \'>\' expr',
        'expr : expr T_IS_GREATER_OR_EQUAL expr',
        'expr : expr T_INSTANCEOF class_name_reference',
        'expr : \'(\' expr \')\'',
        'expr : expr \'?\' expr \':\' expr',
        'expr : expr \'?\' \':\' expr',
        'expr : expr T_COALESCE expr',
        'expr : T_ISSET \'(\' variables_list \')\'',
        'expr : T_EMPTY \'(\' expr \')\'',
        'expr : T_INCLUDE expr',
        'expr : T_INCLUDE_ONCE expr',
        'expr : T_EVAL \'(\' expr \')\'',
        'expr : T_REQUIRE expr',
        'expr : T_REQUIRE_ONCE expr',
        'expr : T_INT_CAST expr',
        'expr : T_DOUBLE_CAST expr',
        'expr : T_STRING_CAST expr',
        'expr : T_ARRAY_CAST expr',
        'expr : T_OBJECT_CAST expr',
        'expr : T_BOOL_CAST expr',
        'expr : T_UNSET_CAST expr',
        'expr : T_EXIT exit_expr',
        'expr : \'@\' expr',
        'expr : scalar',
        'expr : \'`\' backticks_expr \'`\'',
        'expr : T_PRINT expr',
        'expr : T_YIELD',
        'expr : T_YIELD expr',
        'expr : T_YIELD expr T_DOUBLE_ARROW expr',
        'expr : T_YIELD_FROM expr',
        'expr : T_FUNCTION optional_ref \'(\' parameter_list \')\' lexical_vars optional_return_type \'{\' inner_statement_list \'}\'',
        'expr : T_STATIC T_FUNCTION optional_ref \'(\' parameter_list \')\' lexical_vars optional_return_type \'{\' inner_statement_list \'}\'',
        'anonymous_class : T_CLASS ctor_arguments extends_from implements_list \'{\' class_statement_list \'}\'',
        'new_expr : T_NEW class_name_reference ctor_arguments',
        'new_expr : T_NEW anonymous_class',
        'lexical_vars : ',
        'lexical_vars : T_USE \'(\' lexical_var_list \')\'',
        'lexical_var_list : non_empty_lexical_var_list no_comma',
        'non_empty_lexical_var_list : lexical_var',
        'non_empty_lexical_var_list : non_empty_lexical_var_list \',\' lexical_var',
        'lexical_var : optional_ref T_VARIABLE',
        'function_call : name argument_list',
        'function_call : callable_expr argument_list',
        'function_call : class_name_or_var T_PAAMAYIM_NEKUDOTAYIM member_name argument_list',
        'class_name : T_STATIC',
        'class_name : name',
        'name : namespace_name_parts',
        'name : T_NS_SEPARATOR namespace_name_parts',
        'name : T_NAMESPACE T_NS_SEPARATOR namespace_name_parts',
        'class_name_reference : class_name',
        'class_name_reference : new_variable',
        'class_name_reference : error',
        'class_name_or_var : class_name',
        'class_name_or_var : dereferencable',
        'exit_expr : ',
        'exit_expr : \'(\' optional_expr \')\'',
        'backticks_expr : ',
        'backticks_expr : T_ENCAPSED_AND_WHITESPACE',
        'backticks_expr : encaps_list',
        'ctor_arguments : ',
        'ctor_arguments : argument_list',
        'constant : name',
        'constant : class_name_or_var T_PAAMAYIM_NEKUDOTAYIM identifier',
        'constant : class_name_or_var T_PAAMAYIM_NEKUDOTAYIM error',
        'array_short_syntax : \'[\' array_pair_list \']\'',
        'dereferencable_scalar : T_ARRAY \'(\' array_pair_list \')\'',
        'dereferencable_scalar : array_short_syntax',
        'dereferencable_scalar : T_CONSTANT_ENCAPSED_STRING',
        'scalar : T_LNUMBER',
        'scalar : T_DNUMBER',
        'scalar : T_LINE',
        'scalar : T_FILE',
        'scalar : T_DIR',
        'scalar : T_CLASS_C',
        'scalar : T_TRAIT_C',
        'scalar : T_METHOD_C',
        'scalar : T_FUNC_C',
        'scalar : T_NS_C',
        'scalar : dereferencable_scalar',
        'scalar : constant',
        'scalar : T_START_HEREDOC T_ENCAPSED_AND_WHITESPACE T_END_HEREDOC',
        'scalar : T_START_HEREDOC T_END_HEREDOC',
        'scalar : \'"\' encaps_list \'"\'',
        'scalar : T_START_HEREDOC encaps_list T_END_HEREDOC',
        'optional_expr : ',
        'optional_expr : expr',
        'dereferencable : variable',
        'dereferencable : \'(\' expr \')\'',
        'dereferencable : dereferencable_scalar',
        'callable_expr : callable_variable',
        'callable_expr : \'(\' expr \')\'',
        'callable_expr : dereferencable_scalar',
        'callable_variable : simple_variable',
        'callable_variable : dereferencable \'[\' optional_expr \']\'',
        'callable_variable : constant \'[\' optional_expr \']\'',
        'callable_variable : dereferencable \'{\' expr \'}\'',
        'callable_variable : function_call',
        'callable_variable : dereferencable T_OBJECT_OPERATOR property_name argument_list',
        'variable : callable_variable',
        'variable : static_member',
        'variable : dereferencable T_OBJECT_OPERATOR property_name',
        'simple_variable : T_VARIABLE',
        'simple_variable : \'$\' \'{\' expr \'}\'',
        'simple_variable : \'$\' simple_variable',
        'simple_variable : \'$\' error',
        'static_member : class_name_or_var T_PAAMAYIM_NEKUDOTAYIM simple_variable',
        'new_variable : simple_variable',
        'new_variable : new_variable \'[\' optional_expr \']\'',
        'new_variable : new_variable \'{\' expr \'}\'',
        'new_variable : new_variable T_OBJECT_OPERATOR property_name',
        'new_variable : class_name T_PAAMAYIM_NEKUDOTAYIM simple_variable',
        'new_variable : new_variable T_PAAMAYIM_NEKUDOTAYIM simple_variable',
        'member_name : identifier',
        'member_name : \'{\' expr \'}\'',
        'member_name : simple_variable',
        'property_name : T_STRING',
        'property_name : \'{\' expr \'}\'',
        'property_name : simple_variable',
        'property_name : error',
        'list_expr : T_LIST \'(\' list_expr_elements \')\'',
        'list_expr_elements : list_expr_elements \',\' list_expr_element',
        'list_expr_elements : list_expr_element',
        'list_expr_element : variable',
        'list_expr_element : list_expr',
        'list_expr_element : expr T_DOUBLE_ARROW variable',
        'list_expr_element : expr T_DOUBLE_ARROW list_expr',
        'list_expr_element : ',
        'array_pair_list : inner_array_pair_list',
        'inner_array_pair_list : inner_array_pair_list \',\' array_pair',
        'inner_array_pair_list : array_pair',
        'array_pair : expr T_DOUBLE_ARROW expr',
        'array_pair : expr',
        'array_pair : expr T_DOUBLE_ARROW \'&\' variable',
        'array_pair : \'&\' variable',
        'array_pair : ',
        'encaps_list : encaps_list encaps_var',
        'encaps_list : encaps_list encaps_string_part',
        'encaps_list : encaps_var',
        'encaps_list : encaps_string_part encaps_var',
        'encaps_string_part : T_ENCAPSED_AND_WHITESPACE',
        'encaps_base_var : T_VARIABLE',
        'encaps_var : encaps_base_var',
        'encaps_var : encaps_base_var \'[\' encaps_var_offset \']\'',
        'encaps_var : encaps_base_var T_OBJECT_OPERATOR T_STRING',
        'encaps_var : T_DOLLAR_OPEN_CURLY_BRACES expr \'}\'',
        'encaps_var : T_DOLLAR_OPEN_CURLY_BRACES T_STRING_VARNAME \'}\'',
        'encaps_var : T_DOLLAR_OPEN_CURLY_BRACES T_STRING_VARNAME \'[\' expr \']\' \'}\'',
        'encaps_var : T_CURLY_OPEN variable \'}\'',
        'encaps_var_offset : T_STRING',
        'encaps_var_offset : T_NUM_STRING',
        'encaps_var_offset : \'-\' T_NUM_STRING',
        'encaps_var_offset : T_VARIABLE',
    ];
    protected function initReduceCallbacks() {
        $this->reduceCallbacks = [
            0 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            1 => function($stackPos) {
                $this->semValue = $this->handleNamespaces($stackPos-(1-1));
            },
            2 => function($stackPos) {
                pushNormalizing($stackPos-(2-1), $stackPos-(2-2));
            },
            3 => function($stackPos) {
                init();
            },
            4 => function($stackPos) {
                makeNop($nop, $this->lookaheadStartAttributes);
            if ($nop !== null) { $stackPos-(1-1)[] = $nop; } $this->semValue = $stackPos-(1-1);
            },
            5 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            6 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            7 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            8 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            9 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            10 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            11 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            12 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            13 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            14 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            15 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            16 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            17 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            18 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            19 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            20 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            21 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            22 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            23 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            24 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            25 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            26 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            27 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            28 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            29 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            30 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            31 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            32 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            33 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            34 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            35 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            36 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            37 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            38 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            39 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            40 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            41 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            42 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            43 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            44 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            45 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            46 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            47 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            48 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            49 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            50 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            51 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            52 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            53 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            54 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            55 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            56 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            57 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            58 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            59 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            60 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            61 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            62 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            63 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            64 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            65 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            66 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            67 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            68 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            69 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            70 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            71 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            72 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            73 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            74 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            75 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            76 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            77 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            78 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            79 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            80 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            81 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            82 => function($stackPos) {
                init($stackPos-(1-1));
            },
            83 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            84 => function($stackPos) {
                $this->semValue = Name[$stackPos-(1-1)];
            },
            85 => function($stackPos) {
                /* nothing */
            },
            86 => function($stackPos) {
                /* nothing */
            },
            87 => function($stackPos) {
                /* nothing */
            },
            88 => function($stackPos) {
                $this->emitError(new Error('A trailing comma is not allowed here', attributes()));
            },
            89 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            90 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            91 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            92 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            93 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            94 => function($stackPos) {
                $this->semValue = Stmt\HaltCompiler[$this->lexer->handleHaltCompiler()];
            },
            95 => function($stackPos) {
                $this->semValue = Stmt\Namespace_[$stackPos-(3-2), null]; $this->checkNamespace($this->semValue);
            },
            96 => function($stackPos) {
                $this->semValue = Stmt\Namespace_[$stackPos-(5-2), $stackPos-(5-4)]; $this->checkNamespace($this->semValue);
            },
            97 => function($stackPos) {
                $this->semValue = Stmt\Namespace_[null, $stackPos-(4-3)]; $this->checkNamespace($this->semValue);
            },
            98 => function($stackPos) {
                $this->semValue = Stmt\Use_[$stackPos-(3-2), Stmt\Use_::TYPE_NORMAL];
            },
            99 => function($stackPos) {
                $this->semValue = Stmt\Use_[$stackPos-(4-3), $stackPos-(4-2)];
            },
            100 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            101 => function($stackPos) {
                $this->semValue = Stmt\Const_[$stackPos-(3-2)];
            },
            102 => function($stackPos) {
                $this->semValue = Stmt\Use_::TYPE_FUNCTION;
            },
            103 => function($stackPos) {
                $this->semValue = Stmt\Use_::TYPE_CONSTANT;
            },
            104 => function($stackPos) {
                $this->semValue = Stmt\GroupUse[new Name($stackPos-(7-3), stackAttributes(#3)), $stackPos-(7-6), $stackPos-(7-2)];
            },
            105 => function($stackPos) {
                $this->semValue = Stmt\GroupUse[new Name($stackPos-(8-4), stackAttributes(#4)), $stackPos-(8-7), $stackPos-(8-2)];
            },
            106 => function($stackPos) {
                $this->semValue = Stmt\GroupUse[new Name($stackPos-(6-2), stackAttributes(#2)), $stackPos-(6-5), Stmt\Use_::TYPE_UNKNOWN];
            },
            107 => function($stackPos) {
                $this->semValue = Stmt\GroupUse[new Name($stackPos-(7-3), stackAttributes(#3)), $stackPos-(7-6), Stmt\Use_::TYPE_UNKNOWN];
            },
            108 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            109 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            110 => function($stackPos) {
                init($stackPos-(1-1));
            },
            111 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            112 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            113 => function($stackPos) {
                init($stackPos-(1-1));
            },
            114 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            115 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            116 => function($stackPos) {
                init($stackPos-(1-1));
            },
            117 => function($stackPos) {
                $this->semValue = Stmt\UseUse[$stackPos-(1-1), null, Stmt\Use_::TYPE_UNKNOWN]; $this->checkUseUse($this->semValue, #1);
            },
            118 => function($stackPos) {
                $this->semValue = Stmt\UseUse[$stackPos-(3-1), $stackPos-(3-3), Stmt\Use_::TYPE_UNKNOWN]; $this->checkUseUse($this->semValue, #3);
            },
            119 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            120 => function($stackPos) {
                $this->semValue = $stackPos-(2-2);
            },
            121 => function($stackPos) {
                $this->semValue = $stackPos-(1-1); $this->semValue->type = Stmt\Use_::TYPE_NORMAL;
            },
            122 => function($stackPos) {
                $this->semValue = $stackPos-(2-2); $this->semValue->type = $stackPos-(2-1);
            },
            123 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            124 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            125 => function($stackPos) {
                init($stackPos-(1-1));
            },
            126 => function($stackPos) {
                $this->semValue = Node\Const_[$stackPos-(3-1), $stackPos-(3-3)];
            },
            127 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            128 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            129 => function($stackPos) {
                init($stackPos-(1-1));
            },
            130 => function($stackPos) {
                $this->semValue = Node\Const_[$stackPos-(3-1), $stackPos-(3-3)];
            },
            131 => function($stackPos) {
                pushNormalizing($stackPos-(2-1), $stackPos-(2-2));
            },
            132 => function($stackPos) {
                init();
            },
            133 => function($stackPos) {
                makeNop($nop, $this->lookaheadStartAttributes);
            if ($nop !== null) { $stackPos-(1-1)[] = $nop; } $this->semValue = $stackPos-(1-1);
            },
            134 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            135 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            136 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            137 => function($stackPos) {
                throw new Error('__HALT_COMPILER() can only be used from the outermost scope', attributes());
            },
            138 => function($stackPos) {
                $this->semValue = $stackPos-(3-2); prependLeadingComments($this->semValue);
            },
            139 => function($stackPos) {
                $this->semValue = Stmt\If_[$stackPos-(7-3), ['stmts' => toArray($stackPos-(7-5)), 'elseifs' => $stackPos-(7-6), 'else' => $stackPos-(7-7)]];
            },
            140 => function($stackPos) {
                $this->semValue = Stmt\If_[$stackPos-(10-3), ['stmts' => $stackPos-(10-6), 'elseifs' => $stackPos-(10-7), 'else' => $stackPos-(10-8)]];
            },
            141 => function($stackPos) {
                $this->semValue = Stmt\While_[$stackPos-(5-3), $stackPos-(5-5)];
            },
            142 => function($stackPos) {
                $this->semValue = Stmt\Do_   [$stackPos-(7-5), toArray($stackPos-(7-2))];
            },
            143 => function($stackPos) {
                $this->semValue = Stmt\For_[['init' => $stackPos-(9-3), 'cond' => $stackPos-(9-5), 'loop' => $stackPos-(9-7), 'stmts' => $stackPos-(9-9)]];
            },
            144 => function($stackPos) {
                $this->semValue = Stmt\Switch_[$stackPos-(5-3), $stackPos-(5-5)];
            },
            145 => function($stackPos) {
                $this->semValue = Stmt\Break_[$stackPos-(3-2)];
            },
            146 => function($stackPos) {
                $this->semValue = Stmt\Continue_[$stackPos-(3-2)];
            },
            147 => function($stackPos) {
                $this->semValue = Stmt\Return_[$stackPos-(3-2)];
            },
            148 => function($stackPos) {
                $this->semValue = Stmt\Global_[$stackPos-(3-2)];
            },
            149 => function($stackPos) {
                $this->semValue = Stmt\Static_[$stackPos-(3-2)];
            },
            150 => function($stackPos) {
                $this->semValue = Stmt\Echo_[$stackPos-(3-2)];
            },
            151 => function($stackPos) {
                $this->semValue = Stmt\InlineHTML[$stackPos-(1-1)];
            },
            152 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            153 => function($stackPos) {
                $this->semValue = Stmt\Unset_[$stackPos-(5-3)];
            },
            154 => function($stackPos) {
                $this->semValue = Stmt\Foreach_[$stackPos-(7-3), $stackPos-(7-5)[0], ['keyVar' => null, 'byRef' => $stackPos-(7-5)[1], 'stmts' => $stackPos-(7-7)]];
            },
            155 => function($stackPos) {
                $this->semValue = Stmt\Foreach_[$stackPos-(9-3), $stackPos-(9-7)[0], ['keyVar' => $stackPos-(9-5), 'byRef' => $stackPos-(9-7)[1], 'stmts' => $stackPos-(9-9)]];
            },
            156 => function($stackPos) {
                $this->semValue = Stmt\Declare_[$stackPos-(5-3), $stackPos-(5-5)];
            },
            157 => function($stackPos) {
                $this->semValue = Stmt\TryCatch[$stackPos-(6-3), $stackPos-(6-5), $stackPos-(6-6)]; $this->checkTryCatch($this->semValue);
            },
            158 => function($stackPos) {
                $this->semValue = Stmt\Throw_[$stackPos-(3-2)];
            },
            159 => function($stackPos) {
                $this->semValue = Stmt\Goto_[$stackPos-(3-2)];
            },
            160 => function($stackPos) {
                $this->semValue = Stmt\Label[$stackPos-(2-1)];
            },
            161 => function($stackPos) {
                $this->semValue = array(); /* means: no statement */
            },
            162 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            163 => function($stackPos) {
                makeNop($this->semValue, $this->startAttributeStack[#1]);
            if ($this->semValue === null) $this->semValue = array(); /* means: no statement */
            },
            164 => function($stackPos) {
                init();
            },
            165 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            166 => function($stackPos) {
                init($stackPos-(1-1));
            },
            167 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            168 => function($stackPos) {
                $this->semValue = Stmt\Catch_[$stackPos-(8-3), parseVar($stackPos-(8-4)), $stackPos-(8-7)];
            },
            169 => function($stackPos) {
                $this->semValue = null;
            },
            170 => function($stackPos) {
                $this->semValue = Stmt\Finally_[$stackPos-(4-3)];
            },
            171 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            172 => function($stackPos) {
                init($stackPos-(1-1));
            },
            173 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            174 => function($stackPos) {
                $this->semValue = false;
            },
            175 => function($stackPos) {
                $this->semValue = true;
            },
            176 => function($stackPos) {
                $this->semValue = false;
            },
            177 => function($stackPos) {
                $this->semValue = true;
            },
            178 => function($stackPos) {
                $this->semValue = Stmt\Function_[$stackPos-(10-3), ['byRef' => $stackPos-(10-2), 'params' => $stackPos-(10-5), 'returnType' => $stackPos-(10-7), 'stmts' => $stackPos-(10-9)]];
            },
            179 => function($stackPos) {
                $this->semValue = Stmt\Class_[$stackPos-(7-2), ['type' => $stackPos-(7-1), 'extends' => $stackPos-(7-3), 'implements' => $stackPos-(7-4), 'stmts' => $stackPos-(7-6)]];
            $this->checkClass($this->semValue, #2);
            },
            180 => function($stackPos) {
                $this->semValue = Stmt\Interface_[$stackPos-(6-2), ['extends' => $stackPos-(6-3), 'stmts' => $stackPos-(6-5)]];
            $this->checkInterface($this->semValue, #2);
            },
            181 => function($stackPos) {
                $this->semValue = Stmt\Trait_[$stackPos-(5-2), ['stmts' => $stackPos-(5-4)]];
            },
            182 => function($stackPos) {
                $this->semValue = 0;
            },
            183 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_ABSTRACT;
            },
            184 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_FINAL;
            },
            185 => function($stackPos) {
                $this->semValue = null;
            },
            186 => function($stackPos) {
                $this->semValue = $stackPos-(2-2);
            },
            187 => function($stackPos) {
                $this->semValue = array();
            },
            188 => function($stackPos) {
                $this->semValue = $stackPos-(2-2);
            },
            189 => function($stackPos) {
                $this->semValue = array();
            },
            190 => function($stackPos) {
                $this->semValue = $stackPos-(2-2);
            },
            191 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            192 => function($stackPos) {
                init($stackPos-(1-1));
            },
            193 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            194 => function($stackPos) {
                $this->semValue = toArray($stackPos-(1-1));
            },
            195 => function($stackPos) {
                $this->semValue = $stackPos-(4-2);
            },
            196 => function($stackPos) {
                $this->semValue = toArray($stackPos-(1-1));
            },
            197 => function($stackPos) {
                $this->semValue = $stackPos-(4-2);
            },
            198 => function($stackPos) {
                $this->semValue = toArray($stackPos-(1-1));
            },
            199 => function($stackPos) {
                $this->semValue = null;
            },
            200 => function($stackPos) {
                $this->semValue = $stackPos-(4-2);
            },
            201 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            202 => function($stackPos) {
                init($stackPos-(1-1));
            },
            203 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            204 => function($stackPos) {
                $this->semValue = Stmt\DeclareDeclare[$stackPos-(3-1), $stackPos-(3-3)];
            },
            205 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            206 => function($stackPos) {
                $this->semValue = $stackPos-(4-3);
            },
            207 => function($stackPos) {
                $this->semValue = $stackPos-(4-2);
            },
            208 => function($stackPos) {
                $this->semValue = $stackPos-(5-3);
            },
            209 => function($stackPos) {
                init();
            },
            210 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            211 => function($stackPos) {
                $this->semValue = Stmt\Case_[$stackPos-(4-2), $stackPos-(4-4)];
            },
            212 => function($stackPos) {
                $this->semValue = Stmt\Case_[null, $stackPos-(3-3)];
            },
            213 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            214 => function($stackPos) {
                $this->semValue = $this->semStack[$stackPos];
            },
            215 => function($stackPos) {
                $this->semValue = toArray($stackPos-(1-1));
            },
            216 => function($stackPos) {
                $this->semValue = $stackPos-(4-2);
            },
            217 => function($stackPos) {
                init();
            },
            218 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            219 => function($stackPos) {
                $this->semValue = Stmt\ElseIf_[$stackPos-(5-3), toArray($stackPos-(5-5))];
            },
            220 => function($stackPos) {
                init();
            },
            221 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            222 => function($stackPos) {
                $this->semValue = Stmt\ElseIf_[$stackPos-(6-3), $stackPos-(6-6)];
            },
            223 => function($stackPos) {
                $this->semValue = null;
            },
            224 => function($stackPos) {
                $this->semValue = Stmt\Else_[toArray($stackPos-(2-2))];
            },
            225 => function($stackPos) {
                $this->semValue = null;
            },
            226 => function($stackPos) {
                $this->semValue = Stmt\Else_[$stackPos-(3-3)];
            },
            227 => function($stackPos) {
                $this->semValue = array($stackPos-(1-1), false);
            },
            228 => function($stackPos) {
                $this->semValue = array($stackPos-(2-2), true);
            },
            229 => function($stackPos) {
                $this->semValue = array($stackPos-(1-1), false);
            },
            230 => function($stackPos) {
                $this->semValue = array($stackPos-(1-1), false);
            },
            231 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            232 => function($stackPos) {
                $this->semValue = array();
            },
            233 => function($stackPos) {
                init($stackPos-(1-1));
            },
            234 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            235 => function($stackPos) {
                $this->semValue = Node\Param[parseVar($stackPos-(4-4)), null, $stackPos-(4-1), $stackPos-(4-2), $stackPos-(4-3)]; $this->checkParam($this->semValue);
            },
            236 => function($stackPos) {
                $this->semValue = Node\Param[parseVar($stackPos-(6-4)), $stackPos-(6-6), $stackPos-(6-1), $stackPos-(6-2), $stackPos-(6-3)]; $this->checkParam($this->semValue);
            },
            237 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            238 => function($stackPos) {
                $this->semValue = Node\NullableType[$stackPos-(2-2)];
            },
            239 => function($stackPos) {
                $this->semValue = $this->handleBuiltinTypes($stackPos-(1-1));
            },
            240 => function($stackPos) {
                $this->semValue = 'array';
            },
            241 => function($stackPos) {
                $this->semValue = 'callable';
            },
            242 => function($stackPos) {
                $this->semValue = null;
            },
            243 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            244 => function($stackPos) {
                $this->semValue = null;
            },
            245 => function($stackPos) {
                $this->semValue = $stackPos-(2-2);
            },
            246 => function($stackPos) {
                $this->semValue = array();
            },
            247 => function($stackPos) {
                $this->semValue = $stackPos-(4-2);
            },
            248 => function($stackPos) {
                init($stackPos-(1-1));
            },
            249 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            250 => function($stackPos) {
                $this->semValue = Node\Arg[$stackPos-(1-1), false, false];
            },
            251 => function($stackPos) {
                $this->semValue = Node\Arg[$stackPos-(2-2), true, false];
            },
            252 => function($stackPos) {
                $this->semValue = Node\Arg[$stackPos-(2-2), false, true];
            },
            253 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            254 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            255 => function($stackPos) {
                init($stackPos-(1-1));
            },
            256 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(1-1)];
            },
            257 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            258 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            259 => function($stackPos) {
                init($stackPos-(1-1));
            },
            260 => function($stackPos) {
                $this->semValue = Stmt\StaticVar[parseVar($stackPos-(1-1)), null];
            },
            261 => function($stackPos) {
                $this->semValue = Stmt\StaticVar[parseVar($stackPos-(3-1)), $stackPos-(3-3)];
            },
            262 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            263 => function($stackPos) {
                init();
            },
            264 => function($stackPos) {
                $this->semValue = Stmt\Property[$stackPos-(3-1), $stackPos-(3-2)]; $this->checkProperty($this->semValue, #1);
            },
            265 => function($stackPos) {
                $this->semValue = Stmt\ClassConst[$stackPos-(4-3), $stackPos-(4-1)]; $this->checkClassConst($this->semValue, #1);
            },
            266 => function($stackPos) {
                $this->semValue = Stmt\ClassMethod[$stackPos-(9-4), ['type' => $stackPos-(9-1), 'byRef' => $stackPos-(9-3), 'params' => $stackPos-(9-6), 'returnType' => $stackPos-(9-8), 'stmts' => $stackPos-(9-9)]];
            $this->checkClassMethod($this->semValue, #1);
            },
            267 => function($stackPos) {
                $this->semValue = Stmt\TraitUse[$stackPos-(3-2), $stackPos-(3-3)];
            },
            268 => function($stackPos) {
                $this->semValue = array();
            },
            269 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            270 => function($stackPos) {
                init();
            },
            271 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            272 => function($stackPos) {
                $this->semValue = Stmt\TraitUseAdaptation\Precedence[$stackPos-(4-1)[0], $stackPos-(4-1)[1], $stackPos-(4-3)];
            },
            273 => function($stackPos) {
                $this->semValue = Stmt\TraitUseAdaptation\Alias[$stackPos-(5-1)[0], $stackPos-(5-1)[1], $stackPos-(5-3), $stackPos-(5-4)];
            },
            274 => function($stackPos) {
                $this->semValue = Stmt\TraitUseAdaptation\Alias[$stackPos-(4-1)[0], $stackPos-(4-1)[1], $stackPos-(4-3), null];
            },
            275 => function($stackPos) {
                $this->semValue = Stmt\TraitUseAdaptation\Alias[$stackPos-(4-1)[0], $stackPos-(4-1)[1], null, $stackPos-(4-3)];
            },
            276 => function($stackPos) {
                $this->semValue = Stmt\TraitUseAdaptation\Alias[$stackPos-(4-1)[0], $stackPos-(4-1)[1], null, $stackPos-(4-3)];
            },
            277 => function($stackPos) {
                $this->semValue = array($stackPos-(3-1), $stackPos-(3-3));
            },
            278 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            279 => function($stackPos) {
                $this->semValue = array(null, $stackPos-(1-1));
            },
            280 => function($stackPos) {
                $this->semValue = null;
            },
            281 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            282 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            283 => function($stackPos) {
                $this->semValue = 0;
            },
            284 => function($stackPos) {
                $this->semValue = 0;
            },
            285 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            286 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            287 => function($stackPos) {
                $this->checkModifier($stackPos-(2-1), $stackPos-(2-2), #2); $this->semValue = $stackPos-(2-1) | $stackPos-(2-2);
            },
            288 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_PUBLIC;
            },
            289 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_PROTECTED;
            },
            290 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_PRIVATE;
            },
            291 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_STATIC;
            },
            292 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_ABSTRACT;
            },
            293 => function($stackPos) {
                $this->semValue = Stmt\Class_::MODIFIER_FINAL;
            },
            294 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            295 => function($stackPos) {
                init($stackPos-(1-1));
            },
            296 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            297 => function($stackPos) {
                $this->semValue = Stmt\PropertyProperty[parseVar($stackPos-(1-1)), null];
            },
            298 => function($stackPos) {
                $this->semValue = Stmt\PropertyProperty[parseVar($stackPos-(3-1)), $stackPos-(3-3)];
            },
            299 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            300 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            301 => function($stackPos) {
                init($stackPos-(1-1));
            },
            302 => function($stackPos) {
                $this->semValue = array();
            },
            303 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            304 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            305 => function($stackPos) {
                $this->semValue = Expr\Assign[$stackPos-(3-1), $stackPos-(3-3)];
            },
            306 => function($stackPos) {
                $this->semValue = Expr\Assign[$stackPos-(3-1), $stackPos-(3-3)];
            },
            307 => function($stackPos) {
                $this->semValue = Expr\Assign[$stackPos-(3-1), $stackPos-(3-3)];
            },
            308 => function($stackPos) {
                $this->semValue = Expr\AssignRef[$stackPos-(4-1), $stackPos-(4-4)];
            },
            309 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            310 => function($stackPos) {
                $this->semValue = Expr\Clone_[$stackPos-(2-2)];
            },
            311 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Plus      [$stackPos-(3-1), $stackPos-(3-3)];
            },
            312 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Minus     [$stackPos-(3-1), $stackPos-(3-3)];
            },
            313 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Mul       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            314 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Div       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            315 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Concat    [$stackPos-(3-1), $stackPos-(3-3)];
            },
            316 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Mod       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            317 => function($stackPos) {
                $this->semValue = Expr\AssignOp\BitwiseAnd[$stackPos-(3-1), $stackPos-(3-3)];
            },
            318 => function($stackPos) {
                $this->semValue = Expr\AssignOp\BitwiseOr [$stackPos-(3-1), $stackPos-(3-3)];
            },
            319 => function($stackPos) {
                $this->semValue = Expr\AssignOp\BitwiseXor[$stackPos-(3-1), $stackPos-(3-3)];
            },
            320 => function($stackPos) {
                $this->semValue = Expr\AssignOp\ShiftLeft [$stackPos-(3-1), $stackPos-(3-3)];
            },
            321 => function($stackPos) {
                $this->semValue = Expr\AssignOp\ShiftRight[$stackPos-(3-1), $stackPos-(3-3)];
            },
            322 => function($stackPos) {
                $this->semValue = Expr\AssignOp\Pow       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            323 => function($stackPos) {
                $this->semValue = Expr\PostInc[$stackPos-(2-1)];
            },
            324 => function($stackPos) {
                $this->semValue = Expr\PreInc [$stackPos-(2-2)];
            },
            325 => function($stackPos) {
                $this->semValue = Expr\PostDec[$stackPos-(2-1)];
            },
            326 => function($stackPos) {
                $this->semValue = Expr\PreDec [$stackPos-(2-2)];
            },
            327 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\BooleanOr [$stackPos-(3-1), $stackPos-(3-3)];
            },
            328 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\BooleanAnd[$stackPos-(3-1), $stackPos-(3-3)];
            },
            329 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\LogicalOr [$stackPos-(3-1), $stackPos-(3-3)];
            },
            330 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\LogicalAnd[$stackPos-(3-1), $stackPos-(3-3)];
            },
            331 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\LogicalXor[$stackPos-(3-1), $stackPos-(3-3)];
            },
            332 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\BitwiseOr [$stackPos-(3-1), $stackPos-(3-3)];
            },
            333 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\BitwiseAnd[$stackPos-(3-1), $stackPos-(3-3)];
            },
            334 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\BitwiseXor[$stackPos-(3-1), $stackPos-(3-3)];
            },
            335 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Concat    [$stackPos-(3-1), $stackPos-(3-3)];
            },
            336 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Plus      [$stackPos-(3-1), $stackPos-(3-3)];
            },
            337 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Minus     [$stackPos-(3-1), $stackPos-(3-3)];
            },
            338 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Mul       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            339 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Div       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            340 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Mod       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            341 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\ShiftLeft [$stackPos-(3-1), $stackPos-(3-3)];
            },
            342 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\ShiftRight[$stackPos-(3-1), $stackPos-(3-3)];
            },
            343 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Pow       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            344 => function($stackPos) {
                $this->semValue = Expr\UnaryPlus [$stackPos-(2-2)];
            },
            345 => function($stackPos) {
                $this->semValue = Expr\UnaryMinus[$stackPos-(2-2)];
            },
            346 => function($stackPos) {
                $this->semValue = Expr\BooleanNot[$stackPos-(2-2)];
            },
            347 => function($stackPos) {
                $this->semValue = Expr\BitwiseNot[$stackPos-(2-2)];
            },
            348 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Identical     [$stackPos-(3-1), $stackPos-(3-3)];
            },
            349 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\NotIdentical  [$stackPos-(3-1), $stackPos-(3-3)];
            },
            350 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Equal         [$stackPos-(3-1), $stackPos-(3-3)];
            },
            351 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\NotEqual      [$stackPos-(3-1), $stackPos-(3-3)];
            },
            352 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Spaceship     [$stackPos-(3-1), $stackPos-(3-3)];
            },
            353 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Smaller       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            354 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\SmallerOrEqual[$stackPos-(3-1), $stackPos-(3-3)];
            },
            355 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Greater       [$stackPos-(3-1), $stackPos-(3-3)];
            },
            356 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\GreaterOrEqual[$stackPos-(3-1), $stackPos-(3-3)];
            },
            357 => function($stackPos) {
                $this->semValue = Expr\Instanceof_[$stackPos-(3-1), $stackPos-(3-3)];
            },
            358 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            359 => function($stackPos) {
                $this->semValue = Expr\Ternary[$stackPos-(5-1), $stackPos-(5-3),   $stackPos-(5-5)];
            },
            360 => function($stackPos) {
                $this->semValue = Expr\Ternary[$stackPos-(4-1), null, $stackPos-(4-4)];
            },
            361 => function($stackPos) {
                $this->semValue = Expr\BinaryOp\Coalesce[$stackPos-(3-1), $stackPos-(3-3)];
            },
            362 => function($stackPos) {
                $this->semValue = Expr\Isset_[$stackPos-(4-3)];
            },
            363 => function($stackPos) {
                $this->semValue = Expr\Empty_[$stackPos-(4-3)];
            },
            364 => function($stackPos) {
                $this->semValue = Expr\Include_[$stackPos-(2-2), Expr\Include_::TYPE_INCLUDE];
            },
            365 => function($stackPos) {
                $this->semValue = Expr\Include_[$stackPos-(2-2), Expr\Include_::TYPE_INCLUDE_ONCE];
            },
            366 => function($stackPos) {
                $this->semValue = Expr\Eval_[$stackPos-(4-3)];
            },
            367 => function($stackPos) {
                $this->semValue = Expr\Include_[$stackPos-(2-2), Expr\Include_::TYPE_REQUIRE];
            },
            368 => function($stackPos) {
                $this->semValue = Expr\Include_[$stackPos-(2-2), Expr\Include_::TYPE_REQUIRE_ONCE];
            },
            369 => function($stackPos) {
                $this->semValue = Expr\Cast\Int_    [$stackPos-(2-2)];
            },
            370 => function($stackPos) {
                $this->semValue = Expr\Cast\Double  [$stackPos-(2-2)];
            },
            371 => function($stackPos) {
                $this->semValue = Expr\Cast\String_ [$stackPos-(2-2)];
            },
            372 => function($stackPos) {
                $this->semValue = Expr\Cast\Array_  [$stackPos-(2-2)];
            },
            373 => function($stackPos) {
                $this->semValue = Expr\Cast\Object_ [$stackPos-(2-2)];
            },
            374 => function($stackPos) {
                $this->semValue = Expr\Cast\Bool_   [$stackPos-(2-2)];
            },
            375 => function($stackPos) {
                $this->semValue = Expr\Cast\Unset_  [$stackPos-(2-2)];
            },
            376 => function($stackPos) {
                $attrs = attributes();
            $attrs['kind'] = strtolower($stackPos-(2-1)) === 'exit' ? Expr\Exit_::KIND_EXIT : Expr\Exit_::KIND_DIE;
            $this->semValue = new Expr\Exit_($stackPos-(2-2), $attrs);
            },
            377 => function($stackPos) {
                $this->semValue = Expr\ErrorSuppress[$stackPos-(2-2)];
            },
            378 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            379 => function($stackPos) {
                $this->semValue = Expr\ShellExec[$stackPos-(3-2)];
            },
            380 => function($stackPos) {
                $this->semValue = Expr\Print_[$stackPos-(2-2)];
            },
            381 => function($stackPos) {
                $this->semValue = Expr\Yield_[null, null];
            },
            382 => function($stackPos) {
                $this->semValue = Expr\Yield_[$stackPos-(2-2), null];
            },
            383 => function($stackPos) {
                $this->semValue = Expr\Yield_[$stackPos-(4-4), $stackPos-(4-2)];
            },
            384 => function($stackPos) {
                $this->semValue = Expr\YieldFrom[$stackPos-(2-2)];
            },
            385 => function($stackPos) {
                $this->semValue = Expr\Closure[['static' => false, 'byRef' => $stackPos-(10-2), 'params' => $stackPos-(10-4), 'uses' => $stackPos-(10-6), 'returnType' => $stackPos-(10-7), 'stmts' => $stackPos-(10-9)]];
            },
            386 => function($stackPos) {
                $this->semValue = Expr\Closure[['static' => true, 'byRef' => $stackPos-(11-3), 'params' => $stackPos-(11-5), 'uses' => $stackPos-(11-7), 'returnType' => $stackPos-(11-8), 'stmts' => $stackPos-(11-10)]];
            },
            387 => function($stackPos) {
                $this->semValue = array(Stmt\Class_[null, ['type' => 0, 'extends' => $stackPos-(7-3), 'implements' => $stackPos-(7-4), 'stmts' => $stackPos-(7-6)]], $stackPos-(7-2));
            $this->checkClass($this->semValue[0], -1);
            },
            388 => function($stackPos) {
                $this->semValue = Expr\New_[$stackPos-(3-2), $stackPos-(3-3)];
            },
            389 => function($stackPos) {
                list($class, $ctorArgs) = $stackPos-(2-2); $this->semValue = Expr\New_[$class, $ctorArgs];
            },
            390 => function($stackPos) {
                $this->semValue = array();
            },
            391 => function($stackPos) {
                $this->semValue = $stackPos-(4-3);
            },
            392 => function($stackPos) {
                $this->semValue = $stackPos-(2-1);
            },
            393 => function($stackPos) {
                init($stackPos-(1-1));
            },
            394 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            395 => function($stackPos) {
                $this->semValue = Expr\ClosureUse[parseVar($stackPos-(2-2)), $stackPos-(2-1)];
            },
            396 => function($stackPos) {
                $this->semValue = Expr\FuncCall[$stackPos-(2-1), $stackPos-(2-2)];
            },
            397 => function($stackPos) {
                $this->semValue = Expr\FuncCall[$stackPos-(2-1), $stackPos-(2-2)];
            },
            398 => function($stackPos) {
                $this->semValue = Expr\StaticCall[$stackPos-(4-1), $stackPos-(4-3), $stackPos-(4-4)];
            },
            399 => function($stackPos) {
                $this->semValue = Name[$stackPos-(1-1)];
            },
            400 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            401 => function($stackPos) {
                $this->semValue = Name[$stackPos-(1-1)];
            },
            402 => function($stackPos) {
                $this->semValue = Name\FullyQualified[$stackPos-(2-2)];
            },
            403 => function($stackPos) {
                $this->semValue = Name\Relative[$stackPos-(3-3)];
            },
            404 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            405 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            406 => function($stackPos) {
                $this->semValue = Expr\Error[]; $this->errorState = 2;
            },
            407 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            408 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            409 => function($stackPos) {
                $this->semValue = null;
            },
            410 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            411 => function($stackPos) {
                $this->semValue = array();
            },
            412 => function($stackPos) {
                $this->semValue = array(Scalar\EncapsedStringPart[Scalar\String_::parseEscapeSequences($stackPos-(1-1), '`')]);
            },
            413 => function($stackPos) {
                parseEncapsed($stackPos-(1-1), '`', true); $this->semValue = $stackPos-(1-1);
            },
            414 => function($stackPos) {
                $this->semValue = array();
            },
            415 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            416 => function($stackPos) {
                $this->semValue = Expr\ConstFetch[$stackPos-(1-1)];
            },
            417 => function($stackPos) {
                $this->semValue = Expr\ClassConstFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            418 => function($stackPos) {
                $this->semValue = Expr\ClassConstFetch[$stackPos-(3-1), new Expr\Error(stackAttributes(#3))]; $this->errorState = 2;
            },
            419 => function($stackPos) {
                $attrs = attributes(); $attrs['kind'] = Expr\Array_::KIND_SHORT;
            $this->semValue = new Expr\Array_($stackPos-(3-2), $attrs);
            },
            420 => function($stackPos) {
                $attrs = attributes(); $attrs['kind'] = Expr\Array_::KIND_LONG;
            $this->semValue = new Expr\Array_($stackPos-(4-3), $attrs);
            },
            421 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            422 => function($stackPos) {
                $attrs = attributes(); $attrs['kind'] = strKind($stackPos-(1-1));
            $this->semValue = new Scalar\String_(Scalar\String_::parse($stackPos-(1-1)), $attrs);
            },
            423 => function($stackPos) {
                $this->semValue = $this->parseLNumber($stackPos-(1-1), attributes());
            },
            424 => function($stackPos) {
                $this->semValue = Scalar\DNumber[Scalar\DNumber::parse($stackPos-(1-1))];
            },
            425 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Line[];
            },
            426 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\File[];
            },
            427 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Dir[];
            },
            428 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Class_[];
            },
            429 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Trait_[];
            },
            430 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Method[];
            },
            431 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Function_[];
            },
            432 => function($stackPos) {
                $this->semValue = Scalar\MagicConst\Namespace_[];
            },
            433 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            434 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            435 => function($stackPos) {
                $attrs = attributes(); setDocStringAttrs($attrs, $stackPos-(3-1));
            $this->semValue = new Scalar\String_(Scalar\String_::parseDocString($stackPos-(3-1), $stackPos-(3-2)), $attrs);
            },
            436 => function($stackPos) {
                $attrs = attributes(); setDocStringAttrs($attrs, $stackPos-(2-1));
            $this->semValue = new Scalar\String_('', $attrs);
            },
            437 => function($stackPos) {
                $attrs = attributes(); $attrs['kind'] = Scalar\String_::KIND_DOUBLE_QUOTED;
            parseEncapsed($stackPos-(3-2), '"', true); $this->semValue = new Scalar\Encapsed($stackPos-(3-2), $attrs);
            },
            438 => function($stackPos) {
                $attrs = attributes(); setDocStringAttrs($attrs, $stackPos-(3-1));
            parseEncapsedDoc($stackPos-(3-2), true); $this->semValue = new Scalar\Encapsed($stackPos-(3-2), $attrs);
            },
            439 => function($stackPos) {
                $this->semValue = null;
            },
            440 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            441 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            442 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            443 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            444 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            445 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            446 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            447 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(1-1)];
            },
            448 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[$stackPos-(4-1), $stackPos-(4-3)];
            },
            449 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[$stackPos-(4-1), $stackPos-(4-3)];
            },
            450 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[$stackPos-(4-1), $stackPos-(4-3)];
            },
            451 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            452 => function($stackPos) {
                $this->semValue = Expr\MethodCall[$stackPos-(4-1), $stackPos-(4-3), $stackPos-(4-4)];
            },
            453 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            454 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            455 => function($stackPos) {
                $this->semValue = Expr\PropertyFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            456 => function($stackPos) {
                $this->semValue = parseVar($stackPos-(1-1));
            },
            457 => function($stackPos) {
                $this->semValue = $stackPos-(4-3);
            },
            458 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(2-2)];
            },
            459 => function($stackPos) {
                $this->semValue = Expr\Error[]; $this->errorState = 2;
            },
            460 => function($stackPos) {
                $this->semValue = Expr\StaticPropertyFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            461 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(1-1)];
            },
            462 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[$stackPos-(4-1), $stackPos-(4-3)];
            },
            463 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[$stackPos-(4-1), $stackPos-(4-3)];
            },
            464 => function($stackPos) {
                $this->semValue = Expr\PropertyFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            465 => function($stackPos) {
                $this->semValue = Expr\StaticPropertyFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            466 => function($stackPos) {
                $this->semValue = Expr\StaticPropertyFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            467 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            468 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            469 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(1-1)];
            },
            470 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            471 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            472 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(1-1)];
            },
            473 => function($stackPos) {
                $this->semValue = Expr\Error[]; $this->errorState = 2;
            },
            474 => function($stackPos) {
                $this->semValue = Expr\List_[$stackPos-(4-3)];
            },
            475 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            476 => function($stackPos) {
                init($stackPos-(1-1));
            },
            477 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(1-1), null, false];
            },
            478 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(1-1), null, false];
            },
            479 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(3-3), $stackPos-(3-1), false];
            },
            480 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(3-3), $stackPos-(3-1), false];
            },
            481 => function($stackPos) {
                $this->semValue = null;
            },
            482 => function($stackPos) {
                $this->semValue = $stackPos-(1-1); $end = count($this->semValue)-1; if ($this->semValue[$end] === null) unset($this->semValue[$end]);
            },
            483 => function($stackPos) {
                push($stackPos-(3-1), $stackPos-(3-3));
            },
            484 => function($stackPos) {
                init($stackPos-(1-1));
            },
            485 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(3-3), $stackPos-(3-1),   false];
            },
            486 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(1-1), null, false];
            },
            487 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(4-4), $stackPos-(4-1),   true];
            },
            488 => function($stackPos) {
                $this->semValue = Expr\ArrayItem[$stackPos-(2-2), null, true];
            },
            489 => function($stackPos) {
                $this->semValue = null;
            },
            490 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            491 => function($stackPos) {
                push($stackPos-(2-1), $stackPos-(2-2));
            },
            492 => function($stackPos) {
                init($stackPos-(1-1));
            },
            493 => function($stackPos) {
                init($stackPos-(2-1), $stackPos-(2-2));
            },
            494 => function($stackPos) {
                $this->semValue = Scalar\EncapsedStringPart[$stackPos-(1-1)];
            },
            495 => function($stackPos) {
                $this->semValue = Expr\Variable[parseVar($stackPos-(1-1))];
            },
            496 => function($stackPos) {
                $this->semValue = $stackPos-(1-1);
            },
            497 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[$stackPos-(4-1), $stackPos-(4-3)];
            },
            498 => function($stackPos) {
                $this->semValue = Expr\PropertyFetch[$stackPos-(3-1), $stackPos-(3-3)];
            },
            499 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(3-2)];
            },
            500 => function($stackPos) {
                $this->semValue = Expr\Variable[$stackPos-(3-2)];
            },
            501 => function($stackPos) {
                $this->semValue = Expr\ArrayDimFetch[Expr\Variable[$stackPos-(6-2)], $stackPos-(6-4)];
            },
            502 => function($stackPos) {
                $this->semValue = $stackPos-(3-2);
            },
            503 => function($stackPos) {
                $this->semValue = Scalar\String_[$stackPos-(1-1)];
            },
            504 => function($stackPos) {
                $this->semValue = $this->parseNumString($stackPos-(1-1), attributes());
            },
            505 => function($stackPos) {
                $this->semValue = $this->parseNumString('-' . $stackPos-(2-2), attributes());
            },
            506 => function($stackPos) {
                $this->semValue = Expr\Variable[parseVar($stackPos-(1-1))];
            },
        ];
    }
}