<?php

namespace PhpYacc;

interface Lexer {

    public function peek(): Token;

    public function get(): Token;

    public function unget();

    public function rawGet(): Token;

}
