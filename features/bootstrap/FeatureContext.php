<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Tester\ApplicationTester;
use Humbug\Console\Application;
use Humbug\Command\Humbug as HumbugCommand;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    private $application;

    private $appTester;

    private $startingDirectory;

    private $differ;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        require_once __DIR__ . '/../../bootstrap.php';

        $this->application = new Application;
        $this->application->setAutoExit(false);
        $this->application->add(new HumbugCommand);
        $this->appTester = new ApplicationTester($this->application);
        $this->differ = new Differ();
    }

    /**
     * @beforeScenario
     */
    public function retainStartingDirectory()
    {
        $this->startingDirectory = getcwd();
    }

    /**
     * @afterScenario
     */
    public function restoreWorkingDirectory()
    {
        if ($this->startingDirectory !== getcwd()) {
            chdir($this->startingDirectory);
        }
    }

    /**
     * @Given I am in any directory
     */
    public function iAmInAnyDirectory()
    {
        chdir(sys_get_temp_dir());
    }

    /**
     * @When I run humbug
     */
    public function iRunHumbug()
    {
        $this->appTester->run(['run', '--no-progress-bar'=>true]);
    }

    /**
     * @When I run humbug with :arg1
     */
    public function iRunHumbugWith($arg1)
    {
        $arguments = explode(' ', $arg1);
        $this->appTester->run($arguments);
    }

    /**
     * @Then I should see:
     */
    public function iShouldSee(PyStringNode $string)
    {
        $string = (string) $string;
        $output = $this->appTester->getDisplay();
        if (trim($output) !== trim($string)) {
            throw new \RuntimeException(sprintf(
                'Output difference:%s%s',
                PHP_EOL,
                $this->differ->diff(trim($string), trim($output))
            ));
        }
    }

    /**
     * @Then I should see containing:
     * @Then I should see output containing:
     */
    public function iShouldSeeContaining(PyStringNode $string)
    {
        if (!preg_match('/' . preg_quote((string) $string, '/') . '/', $this->appTester->getDisplay())) {
            throw new \RuntimeException(sprintf(
                'Output did not match expected pattern:%s%s', PHP_EOL, $this->appTester->getDisplay()
            ));
        }
    }
}
