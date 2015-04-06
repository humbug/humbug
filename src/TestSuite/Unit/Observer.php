<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Unit;

interface Observer
{
    /**
     * Called when the initial unit test suite is started.
     */
    public function onStartSuite();

    /**
     * Called upon progress of the test suite.
     * @param int $count
     */
    public function onProgress($count);

    /**
     * Called when the unit test stops.
     * @param Result $result
     */
    public function onStopSuite(Result $result);

    public function isDisabled();
}
