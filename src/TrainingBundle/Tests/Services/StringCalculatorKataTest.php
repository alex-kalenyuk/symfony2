<?php

namespace TrainingBundle\Tests\Services;

use TrainingBundle\Services\StringCalculatorKata;

/**
 * Class StringCalculatorKataTest is TDD Kata
 * @link http://osherove.com/tdd-kata-1/
 */
class StringCalculatorKataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderAdd()
     */
    public function testAdd($numbers, $expected)
    {
        $calculator = new StringCalculatorKata();
        $actual = $calculator->add($numbers);
        $this->assertInternalType('int', $actual);
        $this->assertEquals($expected, $actual);
    }

    public function dataProviderAdd()
    {
        return [
            ["", 0],
            ["1", 1],
            ["1,2", 3],
            ["1,3,5,2", 11],
            ["1\n3,5,2", 11],
            ["//;1\n3;5;2", 11]
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage negatives not allowed: -5
     */
    public function testAddNegative()
    {
        $calculator = new StringCalculatorKata();
        $calculator->add("//;1\n3;-5;2");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage negatives not allowed: -3,-5
     */
    public function testAddMultiNegatives()
    {
        $calculator = new StringCalculatorKata();
        $calculator->add("//;1\n-3;-5;2");
    }
}
