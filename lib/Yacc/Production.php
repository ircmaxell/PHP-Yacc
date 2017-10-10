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

    protected $_link;
    protected $_associativity;
    protected $_precedence;
    protected $_position;
    protected $_action;
    protected $_body;
    protected $_num = -1;

    public function __construct(string $action = null, int $position)
    {
        $this->_action = $action;
        $this->_position = $position;
        $this->_body = [];
    }

    public function __get($name)
    {
        return $this->{'_'.$name};
    }

    public function __set($name, $value)
    {
        $this->{'set' . $name}($value);
    }

    public function setBody(array $new)
    {
        foreach ($new as $symbol) {
            assert($symbol instanceof Symbol);
        }
        $this->_body = $new;
    }

    public function setAssociativityFlag(int $flag)
    {
        $this->_associativity |= $flag;
    }

    public function setLink(Production $link = null)
    {
        $this->_link = $link;
    }

    public function setAssociativity(int $associativity)
    {
        $this->_associativity = $associativity;
    }

    public function setPrecedence(int $precedence)
    {
        $this->_precedence = $precedence;
    }

    public function setAction(string $action)
    {
        $this->_action = $action;
    }

    public function setNum(int $num)
    {
        $this->_num = $num;
    }

    public function appendToBody(Symbol $symbol)
    {
        $this->_body[] = $symbol;
    }

    public function isEmpty(): bool
    {
        return count($this->_body) === 1;
    }
}
