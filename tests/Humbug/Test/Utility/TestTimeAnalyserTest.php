<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\Utility;

use Humbug\Utility\TestTimeAnalyser;

class TestTimeAnalyserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->root = dirname(__FILE__) . '/_files/testtimeanalyser';
    }

    public function testAnalysisOfJunitLogFormatShowsLeastTimeTestCaseFirst()
    {
        $file = $this->root . '/testtimes.xml';
        $analyser = new TestTimeAnalyser($file);
        $analysis = $analyser->process()->getTestCases();
        $first = array_shift($analysis);
        $this->assertEquals('/home/sb/ArrayTest2.php', $first['file']);
    }
}
