<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PhpYacc\Grammar\Symbol;

/**
 * @property Production|null $link
 * @property int $associativity
 * @property int $precedence
 * @property int $position
 * @property string $action
 * @property Symbol[] $body
 * @property int $num
 */
class Production
{
    const EMPTY = 0x10;

    public $link;
    public $associativity;
    public $precedence;
    public $position;
    public $action;
    public $body;
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
