<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PHPUnit\Framework\TestCase;
use PhpYacc\Exception\LexingException;

class TokenTest extends Testcase
{
    public function testToString()
    {
        $token = new Token(Token::TOKEN, "%token", 42, "foo.php");
        $this->assertEquals("[foo.php:42] Token::TOKEN (%token)", "$token");
    }

    public function testToStringWithLiteralToken()
    {
        $token = new Token("'f'", "f", 42, "foo.php");
        $this->assertEquals("[foo.php:42] 'f' (f)", "$token");
    }

    /**
     * @expectedException PhpYacc\Exception\LexingException
     * @expectedExceptionMessage Unknown token found: -2
     */
    public function testUnknownToken()
    {
        new Token(-2, '', 42, 'foo.php');
    }
}
