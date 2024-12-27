<?php

namespace PhpYacc\Yacc;

use Iterator;
use PhpYacc\Exception\LogicException;
use PhpYacc\Exception\ParseException;
use PhpYacc\Macro;
use RuntimeException;

use function PhpYacc\is_sym_character;

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
            throw new LogicException("Unexpected end of action stream: this should never happen");
        }
        return $it->current();
    }
}
