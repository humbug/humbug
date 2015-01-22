<?php

require_once dirname(__FILE__).'/Math.php';

class MM2_MathTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group PHPUnitRunnerTesting
     */
    public function testAdds()
    {
        $math = new \Phpunit_MM2_Math;
        $this->assertEquals(4, $math->add(2, 2));
    }
}
