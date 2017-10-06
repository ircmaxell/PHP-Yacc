<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PHPUnit\Framework\TestCase;

class LexerTest extends Testcase {

    public static function provideTestAtoms()
    {
        return [
            ["   \t", Tokens::SPACE],
            ["\n", Tokens::NEWLINE],
            ["/* Fooo*/", Tokens::COMMENT],
            ["// Foo", Tokens::COMMENT],
            ["%%", Tokens::MARK],
            ["%token", Tokens::TOKEN],
            ["'foo'", "'"],
        ];
    }
    
    /**
     * @dataProvider provideTestAtoms
     */
    public function testAtoms(string $source, $expected)
    {
        $lexer = $this->boot($source);
        $token = $lexer->rawGet();
        $this->assertEquals($expected, $token->t);
        $this->assertEquals($source, $token->v);
    }


    protected function boot(string $source): Lexer
    {
        $f = fopen("php://memory", "w");
        fwrite($f, $source);
        fseek($f, 0);
        $tok = new Lexer();
        $tok->init($f);
        return $tok;
    }

}