<?php
/**
 * Created by PhpStorm.
 * User: ircmaxell
 * Date: 10/10/17
 * Time: 3:44 PM
 */

namespace PhpYacc\CodeGen\Language;

use PhpYacc\CodeGen\Language;

class PHP implements Language
{
    public function comment(string $text): string
    {
        return '/* ' . $text . " */";
    }

    public function case_block(int $num, string $value)
    {
        return sprintf('case %d: return %s;', $num, var_export($value, true));
    }
}
