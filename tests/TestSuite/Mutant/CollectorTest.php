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

class CollectorTest extends \PHPUnit\Framework\TestCase
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

    private function getMutantResult($isError, $isKill, $isTimeout)
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

        $error = $this->getErroredMutantResult();

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

        $kill = $this->getKilledMutantResult();

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

        $timeout = $this->getTimedOutMutantResult();

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

        $escape = $this->getEscapedMutantResult();

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

        $error = $this->getErroredMutantResult();
        $kill = $this->getKilledMutantResult();
        $timeout = $this->getTimedOutMutantResult();
        $escape = $this->getEscapedMutantResult();

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

    /**
     * @return object
     */
    public function getErroredMutantResult()
    {
        return $this->getMutantResult(true, false, false);
    }

    /**
     * @return object
     */
    public function getKilledMutantResult()
    {
        return $this->getMutantResult(false, true, false);
    }

    /**
     * @return object
     */
    public function getTimedOutMutantResult()
    {
        return $this->getMutantResult(false, false, true);
    }

    /**
     * @return object
     */
    public function getEscapedMutantResult()
    {
        return $this->getMutantResult(false, false, false);
    }
}
