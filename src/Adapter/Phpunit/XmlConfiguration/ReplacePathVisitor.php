<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;

use Humbug\Adapter\Locator;

class ReplacePathVisitor implements Visitor
{
    /**
     * @var Locator
     */
    private $locator;

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    public function visitElement(\DOMElement $domElement)
    {
        $domElement->nodeValue = $this->locator->locate($domElement->nodeValue);
    }
}