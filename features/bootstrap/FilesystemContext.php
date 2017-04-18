<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Filesystem\Filesystem;
use SebastianBergmann\Diff\Differ;

class FilesystemContext implements Context, SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
        $this->differ = new Differ;
    }

    /**
     * @beforeScenario
     */
    public function prepWorkingDirectory()
    {
        $this->workingDirectory = tempnam(sys_get_temp_dir(), 'humbug-behat');
        $this->filesystem->remove($this->workingDirectory);
        $this->filesystem->mkdir($this->workingDirectory);
        $this->filesystem->symlink(__DIR__ . '/../../composer.json', $this->workingDirectory . '/composer.json');
        $this->filesystem->symlink(__DIR__ . '/../../vendor', $this->workingDirectory . '/vendor');
        chdir($this->workingDirectory);
    }

    /**
     * @afterScenario
     */
    public function removeWorkingDirectory()
    {
        $this->filesystem->remove($this->workingDirectory);
    }

    /**
     * @Given the class file :file contains:
     * @Given the test file :file contains:
     * @Given the phpunit config file :file contains:
     * @Given the phpunit bootstrap file :file contains:
     */
    public function theClassOrTestFileContains($file, PyStringNode $contents)
    {
        $this->theFileContains($file, $contents);
    }

    /**
     * @Given the humbug config file contains:
     */
    public function theConfigFileContains(PyStringNode $contents)
    {
        $this->theFileContains('humbug.json', $contents);
    }

    /**
     * @Given there is no file :file
     */
    public function thereIsNoFile($file)
    {
        $this->throwExceptionIfFalse(!file_exists($file), sprintf('Expected file to not exist: %s', $file));
    }

    /**
     * @Then the file :file should exist.
     * @Then the file :file should exist
     */
    public function theFileShouldExist($file)
    {
        $this->throwExceptionIfFalse(file_exists($file), sprintf('Expected file to exist: %s', $file));
    }

    /**
     * @Then the file :file should contain:
     */
    public function theFileShouldContain($file, PyStringNode $contents)
    {
        $this->throwExceptionIfFalse(
            file_exists($file),
            sprintf('Expected file exist: %s', $file)
        );
        $writtenContent = preg_replace(
            "/humbug-behat[0-9A-Za-z]+\//",
            'humbug-behatJ6Dj5I/',
            file_get_contents($file)
        );
        $this->throwExceptionIfFalse(
            trim($writtenContent) == trim((string) $contents),
            sprintf(
                'Actual file content differs:%s%s',
                PHP_EOL,
                $this->differ->diff((string) $contents, $writtenContent)
            )
        );
    }

    private function throwExceptionIfFalse($result, $message)
    {
        if ($result === false) {
            throw new \RuntimeException($message);
        }
    }

    private function theFileContains($file, PyStringNode $contents)
    {
        $this->filesystem->dumpFile($file, (string) $contents);
    }
}
