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
    if (file_exists($file) && !defined('HUMBUG_COMPOSER_INSTALL')) {
        define('HUMBUG_COMPOSER_INSTALL', $file);
        break;
    }
}
unset($file);
if (!defined('HUMBUG_COMPOSER_INSTALL')) {
    fwrite(STDERR, 'You need to run "composer.phar install" to install Humbug.' . PHP_EOL);
    exit(1);
}
if (PHP_SAPI !== 'phpdbg' && !defined('HHVM_VERSION') && !extension_loaded('xdebug')) {
    fwrite(STDERR, 'You need to install and enable xdebug in order to allow for code coverage generation.' . PHP_EOL);
    exit(1);
}

require_once HUMBUG_COMPOSER_INSTALL;

// php codecoverage 4.0 shimming
if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
    class_alias('SebastianBergmann\CodeCoverage\CodeCoverage', 'PHP_CodeCoverage');
    class_alias('SebastianBergmann\CodeCoverage\Report\Text', 'PHP_CodeCoverage_Report_Text');
    class_alias('SebastianBergmann\CodeCoverage\Report\PHP', 'PHP_CodeCoverage_Report_PHP');
    class_alias('SebastianBergmann\CodeCoverage\Report\Clover', 'PHP_CodeCoverage_Report_Clover');
    class_alias('SebastianBergmann\CodeCoverage\Report\Html\Facade', 'PHP_CodeCoverage_Report_HTML');
    class_alias('SebastianBergmann\CodeCoverage\Exception', 'PHP_CodeCoverage_Exception');
}
