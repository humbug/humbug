Feature: Developer runs Humbug with --version option
    In order to know the version
    As a Humbug user
    I need to be able to read the version

    Scenario: View Humbug version
        Given I am in any directory
        When I run humbug with "--version"
        Then I should see:
            """
             _  _            _
            | || |_  _ _ __ | |__ _  _ __ _
            | __ | || | '  \| '_ \ || / _` |
            |_||_|\_,_|_|_|_|_.__/\_,_\__, |
                                      |___/
            Humbug 1.0-dev
            """
