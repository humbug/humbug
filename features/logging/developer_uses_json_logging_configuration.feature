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
                public function add1($a, $b) {
                    return $a + $b;
                }

                public function add2($a, $b) {
                    return $a + $b;
                }

                public function add3($a, $b) {
                    return $a + $b;
                }

                public function add4($a, $b) {
                    return $a + $b;
                }
            }
            """
        And the test file "tests/FooNormalTest.php" contains:
            """
            <?php
            class FooNormalTest extends \PHPUnit_Framework_TestCase
            {
                public function testAddsNumbers() {
                    $foo = new Foo;
                    $this->assertEquals(3, $foo->add1(2, 1));
                }
            }
            """
        And the test file "tests/FooErrorTest.php" contains:
            """
            <?php
            class FooErrorTest extends \PHPUnit_Framework_TestCase
            {
                public function testAddsNumbersError() {
                    $foo = new Foo;
                    $r = $foo->add2(2, 1);
                    // emulate error
                    if ($r !== 3) $foo->bar();
                    $this->assertEquals(3, $r);
                }
            }
            """
        And the test file "tests/FooTimeoutTest.php" contains:
            """
            <?php
            class FooTimeoutTest extends \PHPUnit_Framework_TestCase
            {
                public function testAddsNumbersTimeout() {
                    $foo = new Foo;
                    $r = $foo->add3(2, 1);
                    // emulate timeout
                    if ($r !== 3) sleep(3);
                    $this->assertEquals(3, $r);
                }
            }
            """
        And the test file "tests/FooEscapeTest.php" contains:
            """
            <?php
            class FooEscapeTest extends \PHPUnit_Framework_TestCase
            {
                public function testMultipliesNumbers() {
                    $foo = new Foo;
                    $foo->add4(2, 1);
                    // No assertion here...
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
                "timeout": 2,
                "logs": {
                    "json": "humbuglog.json"
                }
            }
            """
        When I run humbug
        Then the file "humbuglog.json" should exist.