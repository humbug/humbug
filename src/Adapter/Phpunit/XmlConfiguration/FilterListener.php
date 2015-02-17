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
        $domDocument = $domElement->ownerDocument;

        $domElement->setAttribute('class', '\Humbug\Phpunit\Listener\FilterListener');

        if (empty($this->arguments)) {
            return;
        }

        $arguments = $domDocument->createElement('arguments');
        $domElement->appendChild($arguments);

        foreach($this->arguments as $argument) {
            $object = $domDocument->createElement('object');
            $arguments->appendChild($object);
            $argument->visitElement($object);
        }
    }
}
