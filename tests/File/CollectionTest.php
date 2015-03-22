<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Test\File;

use Humbug\File\Collection;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    private $tmp;

    public function setup()
    {
        $this->tmp = vfsStream::setup('tempDir');
    }

    public function testAddingSourceFile()
    {
        file_put_contents(vfsStream::url('tempDir/Foo.php'), '012345');
        file_put_contents(vfsStream::url('tempDir/Bar.php'), '543210');

        $collection = new Collection;
        $collection->addSourceFile(vfsStream::url('tempDir/Foo.php'));
        $collection->addSourceFile(vfsStream::url('tempDir/Bar.php'));

        $this->assertTrue($collection->hasSourceFile(vfsStream::url('tempDir/Foo.php')));
        $this->assertEquals(
            'fdf8bc5814536f66012884e146a8887a44709a56',
            $collection->getSourceFileHash(vfsStream::url('tempDir/Foo.php'))
        );

        $this->assertTrue($collection->hasSourceFile(vfsStream::url('tempDir/Bar.php')));
        $this->assertEquals(
            '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792',
            $collection->getSourceFileHash(vfsStream::url('tempDir/Bar.php'))
        );
    }

    public function testAddingTestFile()
    {
        file_put_contents(vfsStream::url('tempDir/FooTest.php'), '012345');
        file_put_contents(vfsStream::url('tempDir/BarTest.php'), '543210');

        $collection = new Collection;
        $collection->addTestFile(vfsStream::url('tempDir/FooTest.php'));
        $collection->addTestFile(vfsStream::url('tempDir/BarTest.php'));

        $this->assertTrue($collection->hasTestFile(vfsStream::url('tempDir/FooTest.php')));
        $this->assertEquals(
            'fdf8bc5814536f66012884e146a8887a44709a56',
            $collection->getTestFileHash(vfsStream::url('tempDir/FooTest.php'))
        );

        $this->assertTrue($collection->hasTestFile(vfsStream::url('tempDir/BarTest.php')));
        $this->assertEquals(
            '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792',
            $collection->getTestFileHash(vfsStream::url('tempDir/BarTest.php'))
        );
    }

    public function testImportingData()
    {
        $data = [
            'source_files' => [
                [
                    'name' => vfsStream::url('tempDir/Foo.php'),
                    'hash' => 'fdf8bc5814536f66012884e146a8887a44709a56'
                ],
                [
                    'name' => vfsStream::url('tempDir/Bar.php'),
                    'hash' => '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792'
                ],
            ],
            'test_files' => [
                [
                    'name' => vfsStream::url('tempDir/FooTest.php'),
                    'hash' => 'fdf8bc5814536f66012884e146a8887a44709a56'
                ],
                [
                    'name' => vfsStream::url('tempDir/BarTest.php'),
                    'hash' => '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792'
                ],
            ],
        ];

        $collection = new Collection($data);

        $this->assertTrue($collection->hasSourceFile(vfsStream::url('tempDir/Foo.php')));
        $this->assertEquals(
            'fdf8bc5814536f66012884e146a8887a44709a56',
            $collection->getSourceFileHash(vfsStream::url('tempDir/Foo.php'))
        );

        $this->assertTrue($collection->hasSourceFile(vfsStream::url('tempDir/Bar.php')));
        $this->assertEquals(
            '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792',
            $collection->getSourceFileHash(vfsStream::url('tempDir/Bar.php'))
        );

        $this->assertTrue($collection->hasTestFile(vfsStream::url('tempDir/FooTest.php')));
        $this->assertEquals(
            'fdf8bc5814536f66012884e146a8887a44709a56',
            $collection->getTestFileHash(vfsStream::url('tempDir/FooTest.php'))
        );

        $this->assertTrue($collection->hasTestFile(vfsStream::url('tempDir/BarTest.php')));
        $this->assertEquals(
            '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792',
            $collection->getTestFileHash(vfsStream::url('tempDir/BarTest.php'))
        );
    }

    public function testExportingData()
    {
        $expected = $data = [
            'source_files' => [
                [
                    'name' => vfsStream::url('tempDir/Foo.php'),
                    'hash' => 'fdf8bc5814536f66012884e146a8887a44709a56'
                ],
                [
                    'name' => vfsStream::url('tempDir/Bar.php'),
                    'hash' => '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792'
                ],
            ],
            'test_files' => [
                [
                    'name' => vfsStream::url('tempDir/FooTest.php'),
                    'hash' => 'fdf8bc5814536f66012884e146a8887a44709a56'
                ],
                [
                    'name' => vfsStream::url('tempDir/BarTest.php'),
                    'hash' => '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792'
                ],
            ],
        ];

        file_put_contents(vfsStream::url('tempDir/Foo.php'), '012345');
        file_put_contents(vfsStream::url('tempDir/Bar.php'), '543210');
        file_put_contents(vfsStream::url('tempDir/FooTest.php'), '012345');
        file_put_contents(vfsStream::url('tempDir/BarTest.php'), '543210');

        $collection = new Collection;
        $collection->addSourceFile(vfsStream::url('tempDir/Foo.php'));
        $collection->addSourceFile(vfsStream::url('tempDir/Bar.php'));
        $collection->addTestFile(vfsStream::url('tempDir/FooTest.php'));
        $collection->addTestFile(vfsStream::url('tempDir/BarTest.php'));

        $this->assertSame($expected, $collection->toArray());
    }
}
