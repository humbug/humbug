<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;


interface Visitor
{
    public function visitElement(\DOMElement $domElement);
}
