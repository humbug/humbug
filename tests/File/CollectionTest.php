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
        $collection->addFile(vfsStream::url('tempDir/Foo.php'));
        $collection->addFile(vfsStream::url('tempDir/Bar.php'));

        $this->assertTrue($collection->hasFile(vfsStream::url('tempDir/Foo.php')));
        $this->assertEquals(
            'fdf8bc5814536f66012884e146a8887a44709a56',
            $collection->getFileHash(vfsStream::url('tempDir/Foo.php'))
        );

        $this->assertTrue($collection->hasFile(vfsStream::url('tempDir/Bar.php')));
        $this->assertEquals(
            '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792',
            $collection->getFileHash(vfsStream::url('tempDir/Bar.php'))
        );
    }

    public function testHasFileIsFalseWhenFileNotAdded()
    {
        file_put_contents(vfsStream::url('tempDir/Foo.php'), '012345');
        $collection = new Collection;
        $collection->addFile(vfsStream::url('tempDir/Foo.php'));
        $this->assertFalse($collection->hasFile(vfsStream::url('tempDir/Bar.php')));
    }

    public function testImportingData()
    {
        $data = [
            vfsStream::url('tempDir/Foo.php') => 'fdf8bc5814536f66012884e146a8887a44709a56',
            vfsStream::url('tempDir/Bar.php') => '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792'
        ];

        $collection = new Collection($data);

        $this->assertTrue($collection->hasFile(vfsStream::url('tempDir/Foo.php')));
        $this->assertEquals(
            'fdf8bc5814536f66012884e146a8887a44709a56',
            $collection->getFileHash(vfsStream::url('tempDir/Foo.php'))
        );

        $this->assertTrue($collection->hasFile(vfsStream::url('tempDir/Bar.php')));
        $this->assertEquals(
            '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792',
            $collection->getFileHash(vfsStream::url('tempDir/Bar.php'))
        );
    }

    public function testExportingData()
    {
        $expected = [
            vfsStream::url('tempDir/Foo.php') => 'fdf8bc5814536f66012884e146a8887a44709a56',
            vfsStream::url('tempDir/Bar.php') => '3e5ff6d0dbcd5851f75f892999f5d972c3cb5792'
        ];

        file_put_contents(vfsStream::url('tempDir/Foo.php'), '012345');
        file_put_contents(vfsStream::url('tempDir/Bar.php'), '543210');

        $collection = new Collection;
        $collection->addFile(vfsStream::url('tempDir/Foo.php'));
        $collection->addFile(vfsStream::url('tempDir/Bar.php'));

        $this->assertSame($expected, $collection->toArray());
    }
}
