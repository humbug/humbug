<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;

class ObjectVisitor implements Visitor
{
    /**
     * @var string
     */
    private $className;

    public function __construct($className, array $arguments = [])
    {
        $this->className = $className;
        $this->arguments = $arguments;
    }

    public function visitElement(\DOMElement $domElement)
    {
        $domElement->setAttribute('class', $this->className);

        $dom = $domElement->ownerDocument;

        if (empty($this->arguments)) {
            return;
        }

        $arguments = $dom->createElement('arguments');
        $domElement->appendChild($arguments);

        foreach ($this->arguments as $argumentValue) {
            $argument = $this->createElementByType($argumentValue, $dom);

            $arguments->appendChild($argument);
        }
    }

    /**
     * @param $argumentValue
     * @param \DOMDocument $dom
     * @return \DOMElement
     */
    private function createElementByType($argumentValue, \DOMDocument $dom)
    {
        if (is_bool($argumentValue)) {
            $nodeValue = ($argumentValue === false) ? 'false' : 'true';

            return $dom->createElement('boolean', $nodeValue);
        }

        if (is_string($argumentValue)) {
            return $dom->createElement('string', $argumentValue);
        }

        if ($argumentValue instanceof ObjectVisitor) {
            $object = $dom->createElement('object');
            $argumentValue->visitElement($object);

            return $object;
        }

        throw new \InvalidArgumentException('Unsuported type: '. gettype($argumentValue));
    }
}
