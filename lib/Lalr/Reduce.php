<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;


class Reduce {
  
    protected $symbol;
    protected $number;

    public function __construct(Symbol $symbol, int $number)
    {
        $this->symbol = $symbol;
        $this->number = $number;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

}
