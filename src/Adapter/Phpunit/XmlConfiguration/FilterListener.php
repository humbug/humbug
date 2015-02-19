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

class FilterListener implements Visitor
{
    /**
     * @param Visitor[] $arguments
     */
    private $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function visitElement(\DOMElement $domElement)
    {
        (new ObjectVisitor('\Humbug\Phpunit\Listener\FilterListener', $this->arguments))->visitElement($domElement);
    }
}
