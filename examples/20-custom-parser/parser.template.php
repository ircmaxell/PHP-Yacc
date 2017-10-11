<?php
$meta @
@semval($) $yyval
@semval($,%t) $yyval
@semval(%n) $yyastk[$yysp-(%l-%n)]
@semval(%n,%t) $yyastk[$yysp-(%l-%n)]
@include;

/* Prototype file of PHP parser.
 * Written by Masato Bito
 * This file is PUBLIC DOMAIN.
 */

$buffer = null;
$token = null;
$toktype = null;

@tokenval
define('%s', %n);
@endtokenval


/*
  #define yyclearin (yychar = -1)
  #define yyerrok (yyerrflag = 0)
  #define YYRECOVERING (yyerrflag != 0)
  #define YYERROR  goto yyerrlab
*/


/** Debug mode flag **/
$yydebug = false;

/** lexical element object **/
$yylval = null;

function yyprintln($msg)
{
    echo "$msg\n";
}

function yyflush()
{
    return;
}

@if -t
$yydebug = true;

$yyterminals = array(
    @listvar terminals
    , "???"
    );


function yytokname($n)
{
    switch ($n) {
    @switch-for-token-name;
        default:
            return "???";
    }
}

$yyproduction = array(
    @production-strings;
);


/* Traditional Debug Mode */
function YYTRACE_NEWSTATE($state, $sym)
{
    global $yydebug, $yyterminals;
    if ($yydebug)
        yyprintln("% State " . $state . ", Lookahead "
            . ($sym < 0 ? "--none--" : $yyterminals[$sym]));
}

function YYTRACE_READ($sym)
{
    global $yydebug, $yyterminals;
    if ($yydebug)
        yyprintln("% Reading " . $yyterminals[$sym]);
}

function YYTRACE_SHIFT($sym)
{
    global $yydebug, $yyterminals;
    if ($yydebug)
        yyprintln("% Shift " . $yyterminals[$sym]);
}

function YYTRACE_ACCEPT()
{
    global $yydebug;
    if ($yydebug) yyprintln("% Accepted.");
}

function YYTRACE_REDUCE($n)
{
    global $yydebug, $yyproduction;
    if ($yydebug)
        yyprintln("% Reduce by (" . $n . ") " . $yyproduction[$n]);
}

function YYTRACE_POP($state)
{
    global $yydebug;
    if ($yydebug)
        yyprintln("% Recovering, uncovers state " . $state);
}

function YYTRACE_DISCARD($sym)
{
    global $yydebug, $yyterminals;
    if ($yydebug)
        yyprintln("% Discard " . $yyterminals[$sym]);
}
@endif


$yytranslate = array(
    @listvar yytranslate
  );

define('YYBADCH', @(YYBADCH));
define('YYMAXLEX', @(YYMAXLEX));
define('YYTERMS', @(YYTERMS));
define('YYNONTERMS', @(YYNONTERMS));

$yyaction = array(
    @listvar yyaction
  );

define('YYLAST', @(YYLAST));

$yycheck = array(
    @listvar yycheck
  );

$yybase = array(
    @listvar yybase
  );

define('YY2TBLSTATE', @(YY2TBLSTATE));

$yydefault = array(
    @listvar yydefault
  );



$yygoto = array(
    @listvar yygoto
  );

define('YYGLAST', @(YYGLAST));

$yygcheck = array(
    @listvar yygcheck
  );

$yygbase = array(
    @listvar yygbase
  );

$yygdefault = array(
    @listvar yygdefault
  );

$yylhs = array(
    @listvar yylhs
  );

$yylen = array(
    @listvar yylen
  );

define('YYSTATES', @(YYSTATES));
define('YYNLSTATES', @(YYNLSTATES));
define('YYINTERRTOK', @(YYINTERRTOK));
define('YYUNEXPECTED', @(YYUNEXPECTED));
define('YYDEFAULT', @(YYDEFAULT));

/*
 * Parser entry point
 */

function yyparse()
{
    global $buffer, $token, $toktype, $yyaction, $yybase, $yycheck, $yydebug,
           $yydebug, $yydefault, $yygbase, $yygcheck, $yygdefault, $yygoto, $yylen,
           $yylhs, $yylval, $yyproduction, $yyterminals, $yytranslate;

    $yyastk = array();
    $yysstk = array();

    $yyn = $yyl = 0;
    $yystate = 0;
    $yychar = -1;

    $yysp = 0;
    $yysstk[$yysp] = 0;
    $yyerrflag = 0;
    while (true) {
        @if -t
        YYTRACE_NEWSTATE($yystate, $yychar);
@endif
    if ($yybase[$yystate] == 0)
        $yyn = $yydefault[$yystate];
    else {
        if ($yychar < 0) {
            if (($yychar = yylex()) <= 0) $yychar = 0;
            $yychar = $yychar < YYMAXLEX ? $yytranslate[$yychar] : YYBADCH;
            @if -t
            YYTRACE_READ($yychar);
@endif
      }

        if ((($yyn = $yybase[$yystate] + $yychar) >= 0
                && $yyn < YYLAST && $yycheck[$yyn] == $yychar
                || ($yystate < YY2TBLSTATE
                    && ($yyn = $yybase[$yystate + YYNLSTATES] + $yychar) >= 0
                    && $yyn < YYLAST && $yycheck[$yyn] == $yychar))
            && ($yyn = $yyaction[$yyn]) != YYDEFAULT) {
            /*
             * >= YYNLSTATE: shift and reduce
             * > 0: shift
             * = 0: accept
             * < 0: reduce
             * = -YYUNEXPECTED: error
             */
            if ($yyn > 0) {
                /* shift */
                @if -t
                YYTRACE_SHIFT($yychar);
@endif
          $yysp++;

          $yysstk[$yysp] = $yystate = $yyn;
          $yyastk[$yysp] = $yylval;
          $yychar = -1;

          if ($yyerrflag > 0)
              $yyerrflag--;
          if ($yyn < YYNLSTATES)
              continue;

          /* $yyn >= YYNLSTATES means shift-and-reduce */
          $yyn -= YYNLSTATES;
        } else
                $yyn = -$yyn;
        } else
            $yyn = $yydefault[$yystate];
    }

    while (true) {
        /* reduce/error */
        if ($yyn == 0) {
            /* accept */
            @if -t
            YYTRACE_ACCEPT();
@endif
        yyflush();
        return 0;
      }
        else if ($yyn != YYUNEXPECTED) {
            /* reduce */
            $yyl = $yylen[$yyn];
            $n = $yysp-$yyl+1;
            $yyval = isset($yyastk[$n]) ? $yyastk[$n] : null;
            @if -t
            YYTRACE_REDUCE($yyn);
@endif
        /* Following line will be replaced by reduce actions */
        switch($yyn) {
        @reduce
            case %n:
                {%b} break;
                @endreduce
        }
        /* Goto - shift nonterminal */
        $yysp -= $yyl;
        $yyn = $yylhs[$yyn];
        if (($yyp = $yygbase[$yyn] + $yysstk[$yysp]) >= 0 && $yyp < YYGLAST
            && $yygcheck[$yyp] == $yyn)
            $yystate = $yygoto[$yyp];
        else
            $yystate = $yygdefault[$yyn];

        $yysp++;

        $yysstk[$yysp] = $yystate;
        $yyastk[$yysp] = $yyval;
      }
        else {
            /* error */
            switch ($yyerrflag) {
                case 0:
                    yyerror("syntax error");
                case 1:
                case 2:
                    $yyerrflag = 3;
                    /* Pop until error-expecting state uncovered */

                    while (!(($yyn = $yybase[$yystate] + YYINTERRTOK) >= 0
                        && $yyn < YYLAST && $yycheck[$yyn] == YYINTERRTOK
                        || ($yystate < YY2TBLSTATE
                            && ($yyn = $yybase[$yystate + YYNLSTATES] + YYINTERRTOK) >= 0
                            && $yyn < YYLAST && $yycheck[$yyn] == YYINTERRTOK))) {
                        if ($yysp <= 0) {
                            yyflush();
                            return 1;
                        }
                        $yystate = $yysstk[--$yysp];
                        @if -t
                        YYTRACE_POP($yystate);
@endif
          }
                    $yyn = $yyaction[$yyn];
                    @if -t
                YYTRACE_SHIFT(YYINTERRTOK);
@endif
          $yysstk[++$yysp] = $yystate = $yyn;
          break;

                case 3:
                    @if -t
                YYTRACE_DISCARD($yychar);
@endif
          if ($yychar == 0) {
              yyflush();
              return 1;
          }
          $yychar = -1;
          break;
            }
        }

        if ($yystate < YYNLSTATES)
            break;
        /* >= YYNLSTATES means shift-and-reduce */
        $yyn = $yystate - YYNLSTATES;
    }
  }
}

@tailcode;