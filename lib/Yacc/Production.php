<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Grammar\Symbol;

class Production
{
    const EMPTY = 0x10;

    /** @var Production|null */
    public $link;
    /** @var int */
    public $associativity;
    /** @var int */
    public $precedence;
    /** @var int */
    public $position;
    /** @var string */
    public $action;
    /** @var Symbol[] */
    public $body;
    /** @var int */
    public $num = -1;

    public function __construct(string $action = null, int $position)
    {
        $this->action = $action;
        $this->position = $position;
        $this->body = [];
    }

    public function setAssociativityFlag(int $flag)
    {
        $this->associativity |= $flag;
    }

    public function isEmpty(): bool
    {
        return count($this->body) <= 1;
    }
}
