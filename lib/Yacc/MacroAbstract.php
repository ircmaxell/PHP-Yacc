<?php

namespace PhpYacc\Yacc;

use Iterator;
use function PhpYacc\is_sym_character;
use PhpYacc\Macro;
use RuntimeException;

abstract class MacroAbstract implements Macro
{
    protected function parse(string $string, int $lineNumber, string $filename): array
    {
        $i = 0;
        $length = strlen($string);
        $buffer = '';
        $tokens = [];
        while ($i < $length) {
            if (is_sym_character($string[$i])) {
                do {
                    $buffer .= $string[$i++];
                } while ($i < $length && is_sym_character($string[$i]));
                $type = ctype_digit($buffer) ? Token::NUMBER : Token::NAME;
                $tokens[] = new Token($type, $buffer, $lineNumber, $filename);
                $buffer = '';
            } else {
                $tokens[] = new Token($string[$i], $string[$i++], $lineNumber, $filename);
            }
        }
        return $tokens;
    }

    protected static function next(Iterator $it): Token
    {
        $it->next();
        if (!$it->valid()) {
            throw new RuntimeException("Syntax error, expected more tokens");
        }
        return $it->current();
    }
}
