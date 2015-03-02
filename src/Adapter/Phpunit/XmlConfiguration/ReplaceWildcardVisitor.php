<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;

use Humbug\Adapter\Locator;

class ReplaceWildcardVisitor implements Visitor
{
    /**
     * @var Locator
     */
    private $locator;

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    public function visitElement(\DOMNode $domElement)
    {
        $directories = $this->locator->locateDirectories($domElement->nodeValue);

        $domDocument = $domElement->ownerDocument;

        $parentNode = $domElement->parentNode;
        $domElement->parentNode->removeChild($domElement);

        foreach ($directories as $directory) {
            $parentNode->appendChild($domDocument->createElement($domElement->tagName, $directory));
        }
    }
}
