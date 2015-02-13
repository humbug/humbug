<?php

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
        $domDocument = $domElement->ownerDocument;

        $domElement->setAttribute('class', '\Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter');

        $arguments = $domDocument->createElement('arguments');
        $domElement->appendChild($arguments);

        foreach ($this->testSuites as $suite) {
            $arguments->appendChild($domDocument->createElement('string', $suite));
        }
    }
}