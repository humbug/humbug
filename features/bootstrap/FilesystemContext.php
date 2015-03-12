<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Filesystem\Filesystem;

class FilesystemContext implements Context, SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @beforeScenario
     */
    public function prepWorkingDirectory()
    {
        $this->workingDirectory = tempnam(sys_get_temp_dir(), 'humbug-behat');
        $this->filesystem->remove($this->workingDirectory);
        $this->filesystem->mkdir($this->workingDirectory);
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
     */
    public function theClassOrTestFileContains($file, PyStringNode $contents)
    {
        $this->theFileContains($file, $contents);
        require_once($file);
    }

    /**
     * @Given the config file contains:
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
     * @Then the file :file should contain:
     */
    public function theFileShouldContain($file, PyStringNode $contents)
    {
        $this->throwExceptionIfFalse(
            file_exists($file),
            sprintf('Expected file exist: %s', $file)
        );
        $this->throwExceptionIfFalse(
            trim(file_get_contents($file)) == trim((string) $contents),
            sprintf('Expected file to contain:%s%s%s but actually contained%s%s', PHP_EOL, (string) $contents, PHP_EOL, PHP_EOL, file_get_contents($file))
        );
    }

    private function throwExceptionIfFalse($result, $message)
    {
        if ($result === false) {
            throw new \RuntimeException($message);
        }
    }
}
