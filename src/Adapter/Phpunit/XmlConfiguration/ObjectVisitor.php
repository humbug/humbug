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

class ObjectVisitor implements Visitor
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $arguments = [];

    public function __construct($className, array $arguments = [])
    {
        $this->className = $className;
        $this->arguments = $arguments;
    }

    public function visitNode(\DOMNode $domNode)
    {
        $domNode->setAttribute('class', $this->className);

        $dom = $domNode->ownerDocument;

        if (empty($this->arguments)) {
            return;
        }

        $arguments = $dom->createElement('arguments');
        $domNode->appendChild($arguments);

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
            $argumentValue->visitNode($object);

            return $object;
        }

        throw new \InvalidArgumentException('Unsuported type: '. gettype($argumentValue));
    }
}
