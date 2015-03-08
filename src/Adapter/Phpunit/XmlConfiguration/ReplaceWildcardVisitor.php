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

    public function visitNode(\DOMNode $domNode)
    {
        $directories = $this->locator->locateDirectories($domNode->nodeValue);

        $domDocument = $domNode->ownerDocument;

        $parentNode = $domNode->parentNode;
        $domNode->parentNode->removeChild($domNode);

        foreach ($directories as $directory) {
            // TODO: Check for any bug here since DOMAttr would not have a tagName
            $parentNode->appendChild($domDocument->createElement($domNode->tagName, $directory));
        }
    }
}
