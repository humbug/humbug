<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

foreach ([__DIR__ . '/../../autoload.php', __DIR__ . '/vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        define('HUMBUG_COMPOSER_INSTALL', $file);
        break;
    }
}
unset($file);
if (!defined('HUMBUG_COMPOSER_INSTALL')) {
    fwrite(STDERR, 'You need to run "composer.phar install" to install Humbug.' . PHP_EOL);
    exit(1);
}
if (!defined('HHVM_VERSION') && !extension_loaded('xdebug')) {
    fwrite(STDERR, 'You need to install and enable xdebug in order to allow for code coverage generation.' . PHP_EOL);
    exit(1);
}
require_once HUMBUG_COMPOSER_INSTALL;
