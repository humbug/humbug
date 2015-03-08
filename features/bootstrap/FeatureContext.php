<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

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

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->application = new Application;
        $this->application->setAutoExit(false);
        $this->application->add(new HumbugCommand);
        $this->appTester = new ApplicationTester($this->application);
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
     * @When I run humbug with :arg1
     */
    public function iRunHumbugWith($arg1)
    {
        $arguments = explode(' ', $arg1);
        $this->appTester->run($arguments);
    }

    /**
     * @Then I should see
     */
    public function iShouldSee(PyStringNode $string)
    {
        $string = (string) $string;
        if (trim($this->appTester->getDisplay()) !== trim($string)) {
            throw new \Exception(sprintf(
                'Actual output was:%s%s', PHP_EOL, $string
            ));
        }
    }
}
