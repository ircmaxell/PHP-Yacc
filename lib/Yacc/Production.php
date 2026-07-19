<?php

declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Grammar\Symbol;

class Production
{
    public const EMPTY = 0x10;

    public ?Production $link;
    public int $associativity = 0;
    public int $precedence;
    public int $position;
    public string $action;
    /** @var Symbol[] */
    public array $body;
    public int $num = -1;

    public function __construct(string $action, int $position)
    {
        $this->action = $action;
        $this->position = $position;
        $this->body = [];
    }

    public function setAssociativityFlag(int $flag): void
    {
        $this->associativity |= $flag;
    }

    public function isEmpty(): bool
    {
        return count($this->body) <= 1;
    }
}
