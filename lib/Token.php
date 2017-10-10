<?php
declare(strict_types=1);
namespace PhpYacc;

class Token
{
    public $t;
    public $v;
    public function __construct($token, string $value, int $lineNumber, string $filename)
    {
        $this->t = $token;
        $this->v = $value;
        $this->ln = $lineNumber;
        $this->fn = $filename;
    }

    public function __toString(): string
    {
        return "[{$this->fn}:{$this->ln}] {$this->t} ({$this->v})";
    }
}
