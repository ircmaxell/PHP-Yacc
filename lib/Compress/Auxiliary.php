<?php

declare(strict_types=1);

namespace PhpYacc\Compress;

class Auxiliary
{
    public ?self $next;
    public int $index;
    public int $gain;
    public Preimage $preimage;
    public array $table = [];
}
