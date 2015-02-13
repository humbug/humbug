<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;

class FastestFirstFilter implements Visitor
{
    public function __construct($pathToStats)
    {
        $this->pathToStats = $pathToStats;
    }

    public function visitElement(\DOMElement $domElement)
    {
        $domDocument = $domElement->ownerDocument;

        $domElement->setAttribute('class', '\Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter');

        $arguments = $domDocument->createElement('arguments');
        $domElement->appendChild($arguments);

        $arguments->appendChild($domDocument->createElement('string', $this->pathToStats));
    }
}