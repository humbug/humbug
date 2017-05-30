Feature: Use Humbug
    So that I can see a Humbug mutant kill result
    As a developer
    I need to be able to run Humbug

    Scenario:
        Given the class file "src/Foo.php" contains:
            """
            <?php
            class Foo
            {
                public function add($a, $b) {
                    $result = $a + $b; //mutate
                    trigger_error('Houston...'); // simulate error
                }
            }
            """
        And the test file "tests/FooTest.php" contains:
            """
            <?php
            class FooTest extends \PHPUnit_Framework_TestCase
            {
                public function testAddsNumbers() {
                    $foo = new Foo;
                    $this->assertEquals(3, $foo->add(2, 1));
                }
            }
            """
        And the phpunit bootstrap file "tests/bootstrap.php" contains:
            """
            <?php
            require __DIR__ . '/../src/Foo.php';
            """
        And the phpunit config file "phpunit.xml.dist" contains:
            """
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:noNamespaceSchemaLocation="http://schema.phpunit.de/4.4/phpunit.xsd"
                bootstrap="./tests/bootstrap.php">

                <testsuites>
                    <testsuite name="Foo Test Suite">
                        <directory>tests/</directory>
                    </testsuite>
                </testsuites>

                <filter>
                    <whitelist>
                        <directory>./src/</directory>
                    </whitelist>
                </filter>
            </phpunit>
            """
        And the humbug config file contains:
            """
            {
                "source": {
                    "directories": [
                        "src"
                    ]
                },
                "timeout": 10,
                "logs": {
                    "text": "humbuglog.txt"
                }
            }
            """
        When I run humbug
        Then I should see output containing:
            """
            Failure Message:
            """
