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
    public function begin($file, $headerFile): void;

    public function commit(): void;

    public function write(string $text, bool $includeHeader = false): void;

    public function writeQuoted(string $text): void;

    public function comment(string $text): void;

    public function inline_comment(string $text): void;

    public function case_block(string $indent, int $num, string $value): void;
}
