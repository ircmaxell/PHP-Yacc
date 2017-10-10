<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PHPUnit\Framework\TestCase;

class LexerTest extends Testcase
{
    public static function provideTestAtoms()
    {
        return [
            ["   \t", Token::SPACE],
            ["\n", Token::NEWLINE],
            ["/* Fooo*/", Token::COMMENT],
            ["// Foo", Token::COMMENT],
            ["%%", Token::MARK],
            ["%token", Token::TOKEN],
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
