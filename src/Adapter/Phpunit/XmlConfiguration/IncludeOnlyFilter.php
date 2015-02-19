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

namespace Humbug\Adapter\Phpunit\XmlConfiguration;

class IncludeOnlyFilter implements Visitor
{
    /**
     * @var array
     */
    private $testSuites;

    public function __construct($testSuites = [])
    {
        $this->testSuites = $testSuites;
    }

    public function visitElement(\DOMElement $domElement)
    {
        (new ObjectVisitor('\Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter', $this->testSuites))->visitElement($domElement);
    }
}
