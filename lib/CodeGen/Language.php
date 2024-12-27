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
    public function begin($file, $headerFile);

    public function commit();

    public function write(string $text, bool $includeHeader = false);

    public function writeQuoted(string $text);

    public function comment(string $text);

    public function inline_comment(string $text);

    public function case_block(string $indent, int $num, string $value);
}
