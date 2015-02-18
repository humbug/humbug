<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     rafal.wartalski@gmail.com
 */

namespace Humbug\Adapter\Phpunit;

use Humbug\Exception\RuntimeException;

class ConfigurationLocator
{
    /**
     * @param $configurationDir
     * @return string
     *
     */
    public function locate($configurationDir)
    {
        $conf = $configurationDir . '/phpunit.xml';

        if (file_exists($conf)) {
            return realpath($conf);
        }

        if (file_exists($conf . '.dist')) {
            return realpath($conf . '.dist');
        }

        throw new RuntimeException('Unable to locate phpunit.xml(.dist) file. This is required by Humbug.');
    }
}
