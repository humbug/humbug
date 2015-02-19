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

class FastestFirstFilter implements Visitor
{
    /**
     * @var string
     */
    private $pathToStats;

    public function __construct($pathToStats)
    {
        $this->pathToStats = $pathToStats;
    }

    public function visitElement(\DOMElement $domElement)
    {
        (new ObjectVisitor('\Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter', [$this->pathToStats]))->visitElement($domElement);
    }
}
