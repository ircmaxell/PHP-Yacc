<?php

namespace PhpYacc\Yacc;

use PhpYacc\Grammar\Context;
use Generator;
use Iterator;
use PhpYacc\Macro as CoreMacro;
use PhpYacc\Token;

abstract class Macro extends CoreMacro {

    protected function parse(string $string, int $lineNumber, string $filename): array
    {
        $i = 0;
        $length = strlen($string);
        $buffer = '';
        $tokens = [];
        while ($i < $length) {
            if (isSymCh($string[$i])) {
                do {
                    $buffer .= $string[$i++];
                } while (isSymCh($string[$i]));
                $type = ctype_digit($buffer) ? Tokens::NUMBER : Tokens::NAME;
                $tokens[] = new Token($type, $buffer, $lineNumber, $filename);
                $buffer = '';
            } else {
                $tokens[] = new Token($string[$i], $string[$i++], $lineNumber, $filename);
            }
        }
        return $tokens;
    }

}