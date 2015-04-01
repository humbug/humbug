<?php

/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */
namespace Humbug;

use Humbug\TestSuite\Mutant\FileGenerator;
use Humbug\TestSuite\Mutant\Result;
use Humbug\Utility\CoverageData;
use Humbug\Utility\Diff;
use Humbug\Utility\Tokenizer;
use Humbug\Exception\NoCoveringTestsException;
use Symfony\Component\Process\PhpProcess;
use Serializable;

class Mutant implements Serializable
{
    /**
     * The mutation's parameters
     * @var Mutation
     */
    protected $mutation;

    /**
     * @var string
     */
    protected $baseDirectory;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var array
     */
    protected $tests;

    /**
     * @var array
     */
    protected $testMethods;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var Diff
     */
    protected $diff;

    public function __construct(Mutation $mutation, FileGenerator $generator, CoverageData $coverage, $baseDirectory)
    {
        $this->mutation = $mutation;

        try {
            $this->tests = $coverage->getTestClasses(
                $mutation->getFile(),
                $mutation->getLine()
            );
        } catch (NoCoveringTestsException $e) {
            $this->tests = [];
        }

        $this->testMethods = $coverage->getTestMethods(
            $mutation->getFile(),
            $mutation->getLine()
        );

        $this->file = $generator->generateFile($mutation);
        $this->baseDirectory = $baseDirectory;
        $this->diff = Diff::getInstance();
    }

    public function __destruct()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * @param Diff $diff
     */
    public function setDiffGenerator(Diff $diff)
    {
        $this->diff = $diff;
    }

    /**
     * @return string
     */
    public function getDiff()
    {
        return $this->diff->difference(
            file_get_contents($this->mutation->getfile()),
            file_get_contents($this->file)
        );
    }

    /**
     * @return Mutation
     */
    public function getMutation()
    {
        return $this->mutation;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * For debug and logging purposes
     * @return array
     */
    public function toArray()
    {
        return [
            'file' => $this->getMutationFileRelativePath(),
            'mutator' => $this->mutation->getMutator(),
            'class' => $this->mutation->getClass(),
            'method' => $this->mutation->getMethod(),
            'line' => $this->mutation->getLine(),
            'diff' => $this->getDiff(),
            'tests' => $this->testMethods
        ];
    }

    public function serialize()
    {
        $data = [
            'mutation' => $this->mutation,
            'tests' => $this->tests,
            'file' => $this->file,
            'baseDirectory' => $this->baseDirectory
        ];
        return serialize($data);
    }

    public function unserialize($string)
    {
        $data = unserialize($string);
        $this->mutation = $data['mutation'];
        $this->tests = $data['tests'];
        $this->file = $data['file'];
        $this->baseDirectory = $data['baseDirectory'];
    }

    private function getMutationFileRelativePath()
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->mutation->getFile());
        $baseDirectory = explode(DIRECTORY_SEPARATOR, $this->baseDirectory);

        return join(DIRECTORY_SEPARATOR, array_diff($path, $baseDirectory));
    }
}
