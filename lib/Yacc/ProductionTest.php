<?php
declare(strict_types=1);

namespace PhpYacc\Yacc;

use PHPUnit\Framework\TestCase;

class ProductionTest extends Testcase
{

    public function testSetAssociativity()
    {
        $prod = new Production("", 1);
        $this->assertEquals(0, $prod->associativity);
        $prod->setAssociativityFlag(1);
        $this->assertEquals(1, $prod->associativity);
        $prod->setAssociativityFlag(2);
        $this->assertEquals(3, $prod->associativity);
    }

    public function testIsEmpty()
    {
        $prod = new Production('', 1);
        $this->assertTrue($prod->isEmpty());
        $prod->body[] = 1;
        $this->assertTrue($prod->isEmpty());
        $prod->body[] = 1;
        $this->assertFalse($prod->isEmpty());
    }
}