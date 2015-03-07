<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\Test;

use Humbug\Container;
use Humbug\MutableIterator;
use Prophecy\Argument;

class MutableIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorIsEmptyWhenNoMutablesAreFound()
    {
        $container = $this->prophesize('Humbug\Container');
        $container->getMutableFiles(Argument::type('Symfony\Component\Finder\Finder'))->willReturn([]);

        $iterator = new MutableIterator($container->reveal(), [], []);

        $this->assertCount(0, $iterator);
        $this->assertEmpty(iterator_to_array($iterator));
    }

    public function testIteratorContainsMutableFilesFromDirectories()
    {
        $container = new Container([ 'options' => '' ]);
        $iterator = new MutableIterator($container, [ __DIR__ . '/_files/mutables' ], []);

        $this->assertCount(2, $iterator);
        $this->assertCount(2, iterator_to_array($iterator));
    }

    public function testIteratorDoesNotContainMutableFilesFromExcludedDirectories()
    {
        $container = new Container([ 'options' => '' ]);
        $iterator = new MutableIterator(
            $container,
            [ __DIR__ . '/_files/mutables' ],
            [ 'exclude' ]
        );

        $this->assertCount(1, $iterator);
        $this->assertCount(1, iterator_to_array($iterator));
    }
}
