<?php

namespace Humbug\Adapter\Phpunit\XmlConfiguration;


class TimeCollectorListener implements Visitor
{
    public function __construct($pathToTimesHumbugJson)
    {
        $this->pathToTimesHumbugJson = $pathToTimesHumbugJson;
    }

    public function visitElement(\DOMElement $domElement)
    {
        $domDocument = $domElement->ownerDocument;

        $domElement->setAttribute('class', '\Humbug\Phpunit\Listener\TimeCollectorListener');

        $arguments = $domDocument->createElement('arguments');
        $domElement->appendChild($arguments);

        $loggerObject = $domDocument->createElement('object');
        $arguments->appendChild($loggerObject);

        $loggerObject->setAttribute('class', '\Humbug\Phpunit\Logger\JsonLogger');

        $loggerArguments = $domDocument->createElement('arguments');
        $loggerObject->appendChild($loggerArguments);

        $stringLoggerArgument = $domDocument->createElement('string', $this->pathToTimesHumbugJson);
        $loggerArguments->appendChild($stringLoggerArgument);
    }
}
