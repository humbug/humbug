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
use Symfony\Component\Finder\Finder;

class MutableIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorIsEmptyWhenNoMutablesAreFound()
    {
        $container = $this->prophesize('Humbug\Container');
        $container->getMutableFiles(Argument::type('Symfony\Component\Finder\Finder'))->willReturn([]);

        $iterator = new MutableIterator($container->reveal(), new Finder());

        $this->assertCount(0, $iterator);
        $this->assertEmpty(iterator_to_array($iterator));
    }

    public function testIteratorContainsMutableFilesReturnedByFinder()
    {
        $finder = new Finder();

        $finder->files();
        $finder->name('*.php');
        $finder->in([ __DIR__ . '/_files/mutables' ]);

        $container = new Container([ 'options' => '' ]);
        $iterator = new MutableIterator($container, $finder);

        $this->assertCount(3, $iterator);
        $this->assertCount(3, iterator_to_array($iterator));
    }
}
