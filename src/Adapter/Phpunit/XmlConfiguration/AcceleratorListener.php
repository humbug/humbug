<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;

class AcceleratorListener implements Visitor
{
    public function visitElement(\DOMElement $domElement)
    {
        $domDocument = $domElement->ownerDocument;

        $arguments = $domDocument->createElement('arguments');

        $boolean = $domDocument->createElement('boolean', 'true');
        $arguments->appendChild($boolean);

        $domElement->setAttribute('class', '\MyBuilder\PhpunitAccelerator\TestListener');
        $domElement->appendChild($arguments);
    }
}
