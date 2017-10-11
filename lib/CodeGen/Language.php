<?php
/**
 * Created by PhpStorm.
 * User: ircmaxell
 * Date: 10/10/17
 * Time: 3:44 PM
 */

namespace PhpYacc\CodeGen;

interface Language
{
    public function comment(string $text): string;

    public function case_block(int $num, string $value);
}
