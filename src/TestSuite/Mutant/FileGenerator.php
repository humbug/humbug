<?php

/**
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

use Humbug\Mutation;
use Humbug\Utility\Tokenizer;
use Symfony\Component\Finder\Finder;

class FileGenerator
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @param string $tempDirectory
     */
    public function __construct($tempDirectory)
    {
        $this->tempDirectory = $tempDirectory;
    }

    /**
     * Generates a mutant file from a mutation
     * @param Mutation $mutation
     *
     * @return string
     */
    public function generateFile(Mutation $mutation)
    {
        $id = $this->createId($mutation); // uniqid()
        $file = $this->tempDirectory . '/mutant.humbug.' . $id . '.php';

        // generate mutated file
        $mutatorClass = $mutation->getMutator();

        $originalFileContent = file_get_contents($mutation->getFile());
        $tokens = Tokenizer::getTokens($originalFileContent);
        $mutatedFileContent = $mutatorClass::mutate($tokens, $mutation->getIndex());

        file_put_contents($file, $mutatedFileContent);

        return $file;
    }

    /**
     * @return void
     */
    public function cleanup()
    {
        $finder = new Finder;
        $finder->files()->ignoreUnreadableDirs()->name('mutant.humbug.*.php')->in($this->tempDirectory);
        foreach ($finder as $file) {
            unlink($file->getRealpath());
        }
    }

    private function createId(Mutation $mutation)
    {
        return md5(
            implode('', $mutation->toArray())
        );
    }
}
