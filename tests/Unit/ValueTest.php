<?php

namespace Test\Unit;

use OtherCode\Eidetic\Value;
use Test\TestCase;

/**
 * Class ValueTest
 * @package Test\Unit
 */
class ValueTest extends TestCase
{
    public function testInstantiateEmpty()
    {
        $x = new Value();
        $this->assertEquals(null, $x->getValue());
    }

    public function testInstantiateValue()
    {
        $x = new Value('X');
        $this->assertEquals('X', $x->getValue());
    }

    public function testEquality()
    {
        $x = new Value();
        $y = new Value('X');

        $this->assertTrue($x->equals($x));
        $this->assertFalse($x->equals($y));
    }
}