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


class TimeCollectorListener implements Visitor
{
    /**
     * @var string
     */
    private $pathToTimesHumbugJson;

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
