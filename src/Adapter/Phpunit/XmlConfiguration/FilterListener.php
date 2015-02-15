<?php

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
