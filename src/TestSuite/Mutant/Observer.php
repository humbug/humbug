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
    public function onStartRun(Runner $testSuite);

    public function onShadowMutant(Runner $testSuite, $mutationIndex);

    public function onMutantDone(Runner $testSuite, Mutant $mutant, Result $result, $index);

    public function onEndRun(Runner $testSuite, Collector $resultCollector);
}