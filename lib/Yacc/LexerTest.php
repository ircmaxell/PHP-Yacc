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
            ["'f'", "'"],
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
        $lexer = new Lexer();
        $lexer->startLexing($source, "xxx");
        return $lexer;
    }
}
