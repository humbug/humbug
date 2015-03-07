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
    public function testAddShadowToCollector()
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
        $mutant = $this->prophesize('Humbug\Mutant');
        $result = $this->prophesize('Humbug\TestSuite\Mutant\Result');

        $result->isError()->willReturn((bool) $isError);
        $result->isKill()->willReturn((bool) $isKill);
        $result->isTimeout()->willReturn((bool) $isTimeout);
        $result->getMutant()->willReturn($mutant->reveal());

        $result->toArray()->willReturn([
            'id' => uniqid(),
            'stderr' => $isError ? 'Error' : ''
        ]);

        return $result->reveal();
    }

    public function testAddErrorResultToCollector()
    {
        $collector = new Collector();

        $error = $this->getMutant(true, false, false);

        $collector->collect($error);

        $this->assertEquals(1, $collector->getTotalCount());
        $this->assertEquals(1, $collector->getErrorCount());
        $this->assertEquals(0, $collector->getKilledCount());
        $this->assertEquals(0, $collector->getTimeoutCount());
        $this->assertEquals(0, $collector->getEscapeCount());
        $this->assertEquals(1, $collector->getMeasurableTotal());
        $this->assertEquals(1, $collector->getVanquishedTotal());
        $this->assertContains($error, $collector->getErrors());
    }

    public function testAddKillResultToCollector()
    {
        $collector = new Collector();

        $kill = $this->getMutant(false, true, false);

        $collector->collect($kill);

        $this->assertEquals(1, $collector->getTotalCount());
        $this->assertEquals(0, $collector->getErrorCount());
        $this->assertEquals(1, $collector->getKilledCount());
        $this->assertEquals(0, $collector->getTimeoutCount());
        $this->assertEquals(0, $collector->getEscapeCount());
        $this->assertEquals(1, $collector->getMeasurableTotal());
        $this->assertEquals(1, $collector->getVanquishedTotal());
        $this->assertContains($kill, $collector->getKilled());
    }

    public function testAddTimeoutResultToCollector()
    {
        $collector = new Collector();

        $timeout = $this->getMutant(false, false, true);

        $collector->collect($timeout);

        $this->assertEquals(1, $collector->getTotalCount());
        $this->assertEquals(0, $collector->getErrorCount());
        $this->assertEquals(0, $collector->getKilledCount());
        $this->assertEquals(1, $collector->getTimeoutCount());
        $this->assertEquals(0, $collector->getEscapeCount());
        $this->assertEquals(1, $collector->getMeasurableTotal());
        $this->assertEquals(1, $collector->getVanquishedTotal());
        $this->assertContains($timeout, $collector->getTimeouts());
    }

    public function testAddEscapedResultToCollector()
    {
        $collector = new Collector();

        $escape = $this->getMutant(false, false, false);

        $collector->collect($escape);

        $this->assertEquals(1, $collector->getTotalCount());
        $this->assertEquals(0, $collector->getErrorCount());
        $this->assertEquals(0, $collector->getKilledCount());
        $this->assertEquals(0, $collector->getTimeoutCount());
        $this->assertEquals(1, $collector->getEscapeCount());
        $this->assertEquals(1, $collector->getMeasurableTotal());
        $this->assertEquals(0, $collector->getVanquishedTotal());
        $this->assertContains($escape, $collector->getEscaped());
    }

    public function testToArrayGeneratesAllGroupKeys()
    {
        $collector = new Collector();

        $array = $collector->toGroupedMutantArray();

        $this->assertArrayHasKey('escaped', $array);
        $this->assertArrayHasKey('errored', $array);
        $this->assertArrayHasKey('timeouts', $array);
        $this->assertArrayHasKey('killed', $array);
    }

    public function testToArrayContainsCollectedItemsInCorrectGroups()
    {
        $collector = new Collector();

        $error = $this->getMutant(true, false, false);
        $kill = $this->getMutant(false, true, false);
        $timeout = $this->getMutant(false, false, true);
        $escape = $this->getMutant(false, false, false);

        $collector->collect($error);
        $collector->collect($kill);
        $collector->collect($timeout);
        $collector->collect($escape);

        $array = $collector->toGroupedMutantArray();

        $this->assertContains($error->toArray(), $array['errored']);
        $this->assertContains($kill->toArray(), $array['killed']);
        $this->assertContains($timeout->toArray(), $array['timeouts']);
        $this->assertContains($escape->toArray(), $array['escaped']);
    }
}
