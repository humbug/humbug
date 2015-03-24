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

use Humbug\Exception\InvalidArgumentException;

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

    /**
     * @param string $className
     */
    public function __construct($className, array $arguments = [])
    {
        $this->className = $className;
        $this->arguments = $arguments;
    }

    public function visitElement(\DOMNode $domElement)
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
        if (is_int($argumentValue)) {
            return $dom->createElement('integer', $argumentValue);
        }
        if ($argumentValue instanceof ObjectVisitor) {
            $object = $dom->createElement('object');
            $argumentValue->visitElement($object);
            return $object;
        }
        throw new InvalidArgumentException('Unsupported type: '. gettype($argumentValue));
    }
}
