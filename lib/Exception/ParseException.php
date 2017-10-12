<?php
/**
 * Created by PhpStorm.
 * User: ircmaxell
 * Date: 10/12/17
 * Time: 3:53 PM
 */

namespace PhpYacc\Exception;

use PhpYacc\Yacc\Token;

class ParseException extends PhpYaccException
{
    public static function unexpected(Token $token, $expecting): self
    {
        return new self(sprintf("Unexpected %s, expecting %s at %s:%d", Token::decode($token->t), Token::decode($expecting), $token->fn, $token->ln));
    }
}
