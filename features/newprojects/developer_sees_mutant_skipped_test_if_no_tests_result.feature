Feature: Use Humbug
    So that I can see a Humbug mutant test skipped result with no tests
    As a developer
    I need to be able to run Humbug

    Scenario:
        Given the class file "src/Foo.php" contains:
            """
            <?php
            class Foo
            {
                public function add($a, $b) {
                    return $a + $b;
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
        Then I should see:
            """
            Humbug running test suite to generate logs and code coverage data...

            Humbug has completed the initial test run successfully.

            Humbug is analysing source files...

            Mutation Testing is commencing on 1 files...
            (.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out)

            S

            1 mutations were generated:
                   0 mutants were killed
                   1 mutants were not covered by tests
                   0 covered mutants were not detected
                   0 fatal errors were encountered
                   0 time outs were encountered

            Metrics:
                Mutation Score Indicator (MSI): 0%
                Mutation Code Coverage: 0%
                Covered Code MSI: 0%

            Remember that some mutants will inevitably be harmless (i.e. false positives).

            Humbug results are being logged as TEXT to: humbuglog.txt
            """
