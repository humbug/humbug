<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Mutant;

interface Observer
{
    /**
     * Called when the given mutation test suite starts.
     * @param Runner $testSuite
     *
     * @return mixed
     */
    public function onStartRun(Runner $testSuite);

    /**
     * Called whenever a shadow mutant is encountered.
     * @param Runner $testSuite
     * @param $mutationIndex
     *
     * @return mixed
     */
    public function onShadowMutant(Runner $testSuite, $mutationIndex);

    /**
     * Called when a mutation test is done.
     * @param Runner $testSuite
     * @param Mutant $mutant
     * @param Result $result
     * @param $index
     *
     * @return mixed
     */
    public function onMutantDone(Runner $testSuite, Mutant $mutant, Result $result, $index);

    /**
     * Called when the given mutation test suite stops.
     * @param Runner $testSuite
     * @param Collector $resultCollector
     *
     * @return mixed
     */
    public function onEndRun(Runner $testSuite, Collector $resultCollector);
}
