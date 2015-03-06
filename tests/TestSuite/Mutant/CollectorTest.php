<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\Test\TestSuite\Mutant;

use Humbug\TestSuite\Mutant\Collector;

class CollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testShadowMutantCollection()
    {
        $collector = new Collector();

        $this->assertEquals(0, $collector->getTotalCount());
        $this->assertEquals(0, $collector->getShadowCount());
        $this->assertEquals(0, $collector->getMeasurableTotal());
        $this->assertEquals(0, $collector->getVanquishedTotal());

        $collector->collectShadow();

        $this->assertEquals(1, $collector->getTotalCount());
        $this->assertEquals(1, $collector->getShadowCount());
        $this->assertEquals(0, $collector->getMeasurableTotal());
        $this->assertEquals(0, $collector->getVanquishedTotal());
    }

    private function getMutant($isError, $isKill, $isTimeout)
    {
        $mutant = $mutant=  $this->prophesize('Humbug\Mutant');
        $result = $this->prophesize('Humbug\TestSuite\Mutant\Result');

        $result->isError()->willReturn((bool) $isError);
        $result->isKill()->willReturn((bool) $isKill);
        $result->isTimeout()->willReturn((bool) $isTimeout);

        $mutant->getResult()->willReturn($result->reveal());

        return $mutant->reveal();
    }

    public function testResultCollection()
    {
        $collector = new Collector();

        $error = $this->getMutant(true, false, false);

        $collector->collect($error, $error->getResult());

        $this->assertEquals(1, $collector->getTotalCount());
        $this->assertEquals(1, $collector->getErrorCount());
        $this->assertEquals(0, $collector->getKilledCount());
        $this->assertEquals(0, $collector->getTimeoutCount());
        $this->assertEquals(0, $collector->getEscapeCount());
        $this->assertEquals(1, $collector->getMeasurableTotal());
        $this->assertEquals(1, $collector->getVanquishedTotal());
        $this->assertContains($error, $collector->getErrors());

        $kill = $this->getMutant(false, true, false);

        $collector->collect($kill, $kill->getResult());

        $this->assertEquals(2, $collector->getTotalCount());
        $this->assertEquals(1, $collector->getErrorCount());
        $this->assertEquals(1, $collector->getKilledCount());
        $this->assertEquals(0, $collector->getTimeoutCount());
        $this->assertEquals(0, $collector->getEscapeCount());
        $this->assertEquals(2, $collector->getMeasurableTotal());
        $this->assertEquals(2, $collector->getVanquishedTotal());
        $this->assertContains($kill, $collector->getKilled());

        $timeout = $this->getMutant(false, false, true);

        $collector->collect($timeout, $timeout->getResult());

        $this->assertEquals(3, $collector->getTotalCount());
        $this->assertEquals(1, $collector->getErrorCount());
        $this->assertEquals(1, $collector->getKilledCount());
        $this->assertEquals(1, $collector->getTimeoutCount());
        $this->assertEquals(0, $collector->getEscapeCount());
        $this->assertEquals(3, $collector->getMeasurableTotal());
        $this->assertEquals(3, $collector->getVanquishedTotal());
        $this->assertContains($timeout, $collector->getTimeouts());

        $escape = $this->getMutant(false, false, false);

        $collector->collect($escape, $escape->getResult());

        $this->assertEquals(4, $collector->getTotalCount());
        $this->assertEquals(1, $collector->getErrorCount());
        $this->assertEquals(1, $collector->getKilledCount());
        $this->assertEquals(1, $collector->getTimeoutCount());
        $this->assertEquals(1, $collector->getEscapeCount());
        $this->assertEquals(4, $collector->getMeasurableTotal());
        $this->assertEquals(3, $collector->getVanquishedTotal());
        $this->assertContains($escape, $collector->getEscaped());
    }
}