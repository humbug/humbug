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

use Humbug\Exception\LogicException;
use Humbug\TestSuite\Mutant\Result;
use Humbug\Utility\CoverageData;
use Humbug\Utility\Diff;
use Humbug\Utility\Tokenizer;
use Symfony\Component\Process\PhpProcess;

class Mutant
{
    /**
     * The mutation's parameters
     * @var Mutation
     */
    protected $mutation;

    /**
     * @var Container
     */
    protected $container;

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
     * @var string
     */
    protected $diff;

    /**
     * @var PhpProcess
     */
    protected $process;

    /**
     * @var Result
     */
    protected $result;

    public function __construct(Mutation $mutation, Container $container, CoverageData $coverage)
    {
        $this->mutation = $mutation;
        $this->tests = $coverage->getTestClasses(
            $mutation->getFile(),
            $mutation->getLine()
        );
        $this->testMethods = $coverage->getTestMethods(
            $mutation->getFile(),
            $mutation->getLine()
        );

        $this->container = $container;
        $this->file = $container->getCacheDirectory() . '/humbug.mutant.' . uniqid() . '.php';

        // generate mutated file
        $mutatorClass = $mutation->getMutator();

        $originalFileContent = file_get_contents($mutation->getFile());
        $tokens = Tokenizer::getTokens($originalFileContent);
        $mutatedFileContent = $mutatorClass::mutate($tokens, $mutation->getIndex());

        file_put_contents($this->file, $mutatedFileContent);
    }

    public function __destruct()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * Return the test process
     * If it doesn't exist it will be created
     *
     * @return \Symfony\Component\Process\PhpProcess
     */
    public function getProcess()
    {
        if ($this->process) {
            return $this->process;
        }

        return $this->process = $this->container->getAdapter()->getProcess(
            $this->container,
            false,
            $this->mutation->getFile(), // file to intercept
            $this->file, // mutated file to substitute
            $this->tests
        );
    }

    /**
     * @param bool $timeoutFlag
     * @return Result
     *
     * @throws LogicException when the test process is not terminated.
     */
    public function getResult($timeoutFlag)
    {
        if (! $this->result) {
            $process = $this->getProcess();

            if (! $process->isTerminated()) {
                throw new LogicException('Process is not terminated yet.');
            }

            $status = Result::getStatusCode(
                $this->container->getAdapter()->ok($process->getOutput()),
                $process->isSuccessful(),
                $timeoutFlag
            );

            $this->result = new Result(
                $status,
                $process->getOutput(),
                $process->getErrorOutput()
            );
        }

        return $this->result;
    }

    /**
     * @return string
     */
    public function getDiff()
    {
        return Diff::difference(
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
            'stdout' => $this->getProcess()->getOutput(),
            'stderr' => $this->getProcess()->getErrorOutput(),
            'tests' => $this->testMethods()
        ];
    }

    private function getMutationFileRelativePath()
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->mutation->getFile());
        $baseDirectory = explode(DIRECTORY_SEPARATOR, $this->container->getBaseDirectory());

        return join(DIRECTORY_SEPARATOR, array_diff($path, $baseDirectory));
    }
}
