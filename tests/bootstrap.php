<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

error_reporting(E_ALL);

if (function_exists('date_default_timezone_set')
&& function_exists('date_default_timezone_get')) {
    date_default_timezone_set(@date_default_timezone_get());
}

require __DIR__ . '/../vendor/autoload.php';
