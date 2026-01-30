<?php

declare(strict_types=1);

namespace PhpYacc\Yacc;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpYacc\Grammar\Context;

class ParserTest extends TestCase
{
    public static function provideParserDebugCases(): array
    {
        return [
            [
                <<<CODE
                    %%
                    expr: 
                          '1'             { $$ = 1; }
                    ;
                    %%
                    CODE, [
                    "pureFlag" => false,
                    "nsymbols" => 5,
                    "nterminals" => 2,
                    "nnonterminals" => 2,
                    "ngrams" => 2,
                ], [
                    "terminals" => [
                        49 => "'1'",
                    ],
                    "nonterminals" => [
                        "expr",
                    ],
                    "grams" => [
                        [
                            "action" => "",
                            "empty" => false,
                            "body" => [3, 4],
                        ],
                        [
                            "action" => " m2(0,1) = 1; ",
                            "empty" => false,
                            "body" => [4, 2],
                        ],
                    ],
                ],
            ],

            [
                <<<CODE
                    %pure_parser
                    %%
                    expr: 
                        '1'             { $$ = 1; }
                      | '2'             { $$ = 2; }
                    ;
                    %%
                    CODE, [
                    "pureFlag" => true,
                    "nsymbols" => 6,
                    "nterminals" => 3,
                    "nnonterminals" => 2,
                    "ngrams" => 3,
                ], [
                    "terminals" => [
                        49 => "'1'",
                        50 => "'2'",
                    ],
                    "nonterminals" => [
                        "expr",
                    ],
                    "grams" => [
                        [
                            "action" => "",
                            "empty" => false,
                            "body" => [4, 5],
                        ],
                        [
                            "action" => " m2(0,1) = 1; ",
                            "empty" => false,
                            "body" => [5, 2],
                        ],
                        [
                            "action" => " m2(0,1) = 2; ",
                            "empty" => false,
                            "body" => [5, 3],
                        ],
                    ],
                ],
            ],

            [
                <<<CODE
                    %left '+'
                    %right '-'
                    %nonassoc '(' ')'
                    %token T_NUMBER
                    %%
                    expr: 
                        T_NUMBER       { $$ = $1; }
                      | expr '+' expr  { $$ = $1 + $3; }
                      | expr '-' expr  { $$ = $1 - $3; }
                      | '(' expr ')'   { { $$ = ($2); } }
                    ;
                    %%
                    CODE, [
                    "pureFlag" => false,
                    "nsymbols" => 9,
                    "nterminals" => 7,
                    "nnonterminals" => 1,
                    "ngrams" => 5,
                ], [
                    "terminals" => [
                        43 => "'+'",
                        45 => "'-'",
                        40 => "'('",
                        41 => "')'",
                        257 => "T_NUMBER",
                    ],
                    "nonterminals" => [
                        "expr",
                    ],
                    "grams" => [
                        [
                            "action" => "",
                            "empty" => false,
                            "body" => [7, 8],
                        ],
                        [
                            "action" => " m2(0,1) = m4(1,1); ",
                            "empty" => false,
                            "body" => [8, 6],
                        ],
                        [
                            "action" => " m2(0,3) = m4(1,3) + m4(3,3); ",
                            "empty" => false,
                            "body" => [8, 8, 2, 8],
                        ],
                        [
                            "action" => " m2(0,3) = m4(1,3) - m4(3,3); ",
                            "empty" => false,
                            "body" => [8, 8, 3, 8],
                        ],
                        [
                            "action" => " { m2(0,3) = (m4(2,3)); } ",
                            "empty" => false,
                            "body" => [8, 4, 8, 5],
                        ],
                    ],
                ],
            ],

        ];
    }

    #[DataProvider("provideParserDebugCases")]
    public function testParserWithDebug(string $grammar, array $directProps, array $info)
    {
        $parser = new Parser(new Lexer(), new MacroSet());
        $context =  new Context('YY');
        $context->macros = [
            1 => "m1(%n,%l,%t)",
            2 => "m2(%n,%l)",
            3 => "m3(%n,%l,%t)",
            4 => "m4(%n,%l)",
        ];
        $parser->parse($grammar, $context);
        foreach ($directProps as $prop => $expected) {
            $this->assertEquals($expected, $context->$prop, "context->$prop");
        }
        $this->assertEquals($context->eofToken, $context->symbols[0], "eofToken: symbol[0]");
        $this->assertEquals($context->errorToken, $context->symbols[1], "errorToken: symbol[1]");
        $i = 2;
        foreach ($info['terminals'] as $value => $token) {
            $symbol = $context->symbols[$i];
            $this->assertEquals($token, $symbol->name, "terminal: symbol[$i]->name");
            $this->assertEquals($value, $symbol->value, "terminal: symbol[$i]->value");
            $i++;
        }
        $this->assertEquals($context->startPrime, $context->symbols[$i], "startPrime: symbol[$i]");
        $i++;
        foreach ($info['nonterminals'] as $token) {
            $symbol = $context->symbols[$i];
            $this->assertEquals($token, $symbol->name, "nonterminal: symbol[$i]->name");
            $i++;
        }
        foreach ($info['grams'] as $key => $expect) {
            $gram = $context->grams[$key];
            $this->assertEquals($expect['action'], $gram->action, "gram[$key]->action");
            $this->assertEquals($expect['empty'], $gram->isEmpty(), "gram[$key]->isEmpty()");
            $this->assertEquals(count($expect['body']), count($gram->body), "count(gram[$key]->body)");
            foreach ($expect['body'] as $k => $v) {
                $this->assertEquals($v, $gram->body[$k]->code, "gram[$key]->body[$k]");
            }
        }
    }
}
