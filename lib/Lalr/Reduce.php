<?php
declare(strict_types=1);

namespace PhpYacc\Lalr;

use PhpYacc\Grammar\Symbol;

/**
 * @property Symbol $symbol
 * @property int $number
 */
class Reduce
{
    protected $_symbol;
    protected $_number;

    public function __construct(Symbol $symbol, int $number)
    {
        $this->_symbol = $symbol;
        $this->_number = $number;
    }

    public function __get($name)
    {
        return $this->{'_'.$name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setNumber(int $number)
    {
        $this->_number = $number;
    }
}
