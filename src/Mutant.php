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
use Humbug\Utility\CoverageData;
use Humbug\Utility\Diff;
use Humbug\Utility\Tokenizer;
use Symfony\Component\Process\PhpProcess;

class Mutant
{
    /**
     * The mutation's parameters
     * @var array
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
     * @var string
     */
    protected $diff;

    /**
     * @var PhpProcess
     */
    protected $process;

    /**
     * @var MutantResult
     */
    protected $result;

    public function __construct(array $mutation, Container $container, CoverageData $coverage)
    {
        $this->mutation = $mutation;
        $this->tests = $coverage->getTestClasses($mutation['file'], $mutation['line']);
        $this->container = $container;

        $this->file = $container->getCacheDirectory() . '/humbug.mutant.' . uniqid() . '.php';

        // generate mutated file
        $mutatorClass = $mutation['mutator'];

        $originalFileContent = file_get_contents($mutation['file']);
        $tokens = Tokenizer::getTokens($originalFileContent);
        $mutatedFileContent = $mutatorClass::mutate($tokens, $mutation['index']);

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
            $this->mutation['file'], // file to intercept
            $this->file, // mutated file to substitute
            $this->tests
        );
    }

    /**
     * @param bool $timeoutFlag
     * @return MutantResult
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

            $this->result = new MutantResult(
                $this->container->getAdapter()->ok($process->getOutput()),
                $process->isSuccessful(),
                $timeoutFlag,
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
            file_get_contents($this->mutation['file']),
            file_get_contents($this->file)
        );
    }

    /**
     * @return array
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
            'mutator' => $this->mutation['mutator'],
            'class' => $this->mutation['class'],
            'method' => $this->mutation['method'],
            'line' => $this->mutation['line'],
            'diff' => $this->getDiff(),
            'stdout' => $this->getProcess()->getOutput(),
            'stderr' => $this->getProcess()->getErrorOutput(),
            'tests' => $this->getTests()
        ];
    }

    private function getMutationFileRelativePath()
    {
        $path = explode(DIRECTORY_SEPARATOR, $this->mutation['file']);
        $baseDirectory = explode(DIRECTORY_SEPARATOR, $this->container->getBaseDirectory());

        return join(DIRECTORY_SEPARATOR, array_diff($path, $baseDirectory));
    }
}
