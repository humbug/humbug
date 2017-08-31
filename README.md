Humbug: Mutation Testing for PHP
================================

Humbug is a Mutation Testing framework for PHP. It is currently in development and
so, while it does actually work quite well, it will have rough edges that a team
of minions are slaving to hammer out. If it falls out of the gate, you have been
warned ;).

[![Build Status](https://travis-ci.org/humbug/humbug.svg)](https://travis-ci.org/humbug/humbug) [![Build status](https://ci.appveyor.com/api/projects/status/j1v1iwcv5o8ohb7p/branch/master?svg=true)](https://ci.appveyor.com/project/humbug/humbug/branch/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/humbug/humbug/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/humbug/humbug/?branch=master)
[![StyleCI](https://styleci.io/repos/28300862/shield?style=flat)](https://styleci.io/repos/28300862) [![Total Downloads](https://poser.pugx.org/humbug/humbug/downloads.png)](https://packagist.org/packages/humbug/humbug)
[![Slack](https://img.shields.io/badge/slack-%23humbug-red.svg?style=flat-square)](https://symfony.com/slack-invite)

⚠️️ Update your remotes! Humbug has transferred to a new location. While your
existing repositories will redirect transparently for any operations, take some
time to transition to the new URL.
```sh
$ git remote set-url upstream https://github.com/humbug/humbug.git
```
Replace `upstream` with the name of the remote you use locally; `upstream` is commonly
used but you may be using something else. You may also using a different URL (e.g. git@github.com:mockery/mockery.git).
Run `git remote -v` to see what you're actually using.

**Table of Contents**

- [Introduction](#introduction)
- [Contributing](#contributing)
- [Installation](#installation)
	- [Git](#git)
	- [Phar](#phar)
	- [Composer](#composer)
- [Usage](#usage)
	- [Configuration](#configuration)
		- [Configure command](#configure-command)
		- [Manual Configuration](#manual-configuration)
	- [Running Humbug](#running-humbug)
		- [The Metrics](#the-metrics)
- [Command Line Options](#command-line-options)
	- [Overriding The Configured Timeout](#overriding-the-configured-timeout)
	- [Restricting Files To Mutate](#restricting-files-to-mutate)
	- [Mutate specific files](#mutate-specific-files)
	- [Incremental Analysis](#incremental-analysis)
- [Performance](#performance)
- [Mutators](#mutators)

Introduction
------------

Mutation Testing is, in a nutshell, giving your unit tests a run for their money.
It involves injecting small defects into source code and then checking if the unit
tests noticed. If they do, then your unit tests have "killed" the mutation. If not,
the mutation has escaped detection. As unit tests are intended to prevent regressions,
having a real regression pass unnoticed would be a bad thing!

Whereas Code Coverage can tell you what code your tests are executing, Mutation
Testing is intended to help you judge how well your unit tests actually perform
and where they could be improved.

I've written in more detail about why Mutation Testing is worth having: [Lies, Damned Lies and Code Coverage: Towards Mutation Testing](http://padraic.github.io/humbug.html)

Contributing
------------

Humbug is an open source project that welcomes pull requests and issues from anyone.
Before opening pull requests, please read our short [Contribution Guide](https://github.com/humbug/humbug/blob/master/.github/CONTRIBUTING.md).


Installation
------------

#### Git

You can clone and install Humbug's dependencies using Composer:

```sh
git clone https://github.com/humbug/humbug.git
cd humbug
/path/to/composer.phar install
```

The humbug command is now at bin/humbug.

#### Phar

If you don't want to track the master branch directly, you can install the Humbug
phar as follows:

```sh
wget https://padraic.github.io/humbug/downloads/humbug.phar
wget https://padraic.github.io/humbug/downloads/humbug.phar.pubkey
# If you wish to make humbug.phar directly executable
chmod +x humbug.phar
```

On Windows, you can just download using a browser or from Powershell v3 using the
following commands where `wget` is an alias for `Invoke-WebRequest`:

```sh
wget https://padraic.github.io/humbug/downloads/humbug.phar -OutFile humbug.phar
wget https://padraic.github.io/humbug/downloads/humbug.phar.pubkey -OutFile humbug.phar.pubkey
```

If you're stuck with Powershell v2:

```sh
$client = new-object System.Net.WebClient
$client.DownloadFile("https://padraic.github.io/humbug/downloads/humbug.phar", "humbug.phar")
$client.DownloadFile("https://padraic.github.io/humbug/downloads/humbug.phar.pubkey", "humbug.phar.pubkey")
```

##### PHAR Updates

The phar is signed with an openssl private key. You will need the pubkey file
to be stored beside the phar file at all times in order to use it. If you rename
`humbug.phar` to `humbug`, for example, then also rename the key from
`humbug.phar.pubkey` to `humbug.pubkey`.

The phar releases are currently done manually so they will not be updated with the
same frequency as git master. To update your current phar, just run:

```sh
./humbug.phar self-update
```

Note: Using a phar means that fixes may take longer to reach your version, but there's
more assurance of having a stable development version. The public key is
downloaded only once. It is re-used by self-update to verify future phar releases.

Once releases commence towards stable, there will be an alpha, beta, RC and a final
release. Your development track phar file will self-update automatically until
it reaches a stable release. If you wish to continue tracking the development
level phars, you will need to indicate this using one of the stability flags:

```sh
./humbug.phar self-update --dev
```

###### Self-Update Request Debugging

If you experience any issues self-updating with unexpected `openssl` or SSL errors,
please ensure that you have enabled the `openssl` extension. On Windows, you can
do this by adding or uncommenting the following line in the `php.ini` file for
PHP on the command line (if different than the file for your http server):

```
extension=php_openssl.dll
```

Certain other SSL errors may arise due missing certificates. You can rectify this
by finding their location on your system (e.g. `C:/xampp/php/ext/cacert.pem`), or
alternatively downloading a copy from http://curl.haxx.se/ca/cacert.pem. Then
ensure the following option is correctly pointing to this file:

```
openssl.cafile=C:/path/to/cacert.pem
```

#### Composer

Due to Humbug's dependencies being pegged to recent versions, adding Humbug to
composer.json may give rise to conflicts. The above two methods of installation are
preferred where this occurs. You can however install it globally as any other
general purpose tool:

```sh
composer global require 'humbug/humbug=~1.0@dev'
```

And if you haven't done so previously...add this to `~/.bash_profile` (or `~/.bashrc`):

```sh
export PATH=~/.composer/vendor/bin:$PATH
```

Humbug currently works on PHP 5.4 or greater.

Usage
-----

### Configuration

Humbug is still under development so, to repeat, beware of rough edges.

#### Configure command

To configure humbug in your project you may run: 

```sh
humbug configure
```

This tool will ask some questions required to create the Humbug configuration file
(`humbug.json.dist`).

#### Manual Configuration

In the base directory of your project create a `humbug.json.dist` file:

```js
{
    "timeout": 10,
    "source": {
        "directories": [
            "src"
        ]
    },
    "logs": {
        "text": "humbuglog.txt",
        "json": "humbuglog.json"
    }
}
```

You can commit the `humbug.json.dist` to your VCS and override it locally with a
`humbug.json` file.

Edit as appropriate. If you do not define at least one log, detailed information
about escaped mutants will not be available. The Text log is human readable.
If source files exist in the base directory, or files in the source directories
must be excluded, you can add exclude patterns (here's one for files in base
directory where composer vendor and Tests directories are excluded):

```js
{
    "timeout": 10,
    "source": {
        "directories": [
            "."
        ],
        "excludes": [
            "vendor",
            "Tests"
        ]
    },
    "logs": {
        "text": "humbuglog.txt"
    }
}
```

If, from your project's base directory, you must run tests from another directory
then you can signal this also. You should not need to run Humbug from any directory
other than your project's base directory.

```js
{
    "chdir": "tests",
    "timeout": 10,
    "source": {
        "directories": [
            "src"
        ],
    }
}
```

### Running Humbug

Ensure that your tests are all in a passing state (incomplete and skipped tests
are allowed). Humbug will quit if any of your tests are failing.

The magic command, while in your project's base directory (using the PHAR download) is:

```sh
./humbug.phar
```

or if you just cloned Humbug:

```sh
../humbug/bin/humbug
```

or if you added Humbug as a composer dependency to your project:

```sh
./vendor/bin/humbug
```

Instead of php with the xdebug extension you may also run Humbug via [phpdbg](http://phpdbg.com/):

```sh
phpdbg -qrr humbug.phar
```

If all went well, you will get something similar to:

```
 _  _            _
| || |_  _ _ __ | |__ _  _ __ _
| __ | || | '  \| '_ \ || / _` |
|_||_|\_,_|_|_|_|_.__/\_,_\__, |
                          |___/ 
Humbug version 1.0-dev

Humbug running test suite to generate logs and code coverage data...

  361 [==========================================================] 28 secs

Humbug has completed the initial test run successfully.
Tests: 361 Line Coverage: 64.86%

Humbug is analysing source files...

Mutation Testing is commencing on 78 files...
(.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out)

.....M.M..EMMMMMSSSSMMMMMSMMMMMSSSE.ESSSSSSSSSSSSSSSSSM..M.. |   60 ( 7/78)
...MM.ES..SSSSSSSSSS...MMM.MEMME.SSSS.............SSMMSSSSM. |  120 (12/78)
M.M.M...TT.M...T.MM....S.....SSS..M..SMMSM...........M...... |  180 (17/78)
MM...M...ESSSEM..MMM.M.MM...SSS.SS.M.SMMMMMMM..SMMMMS....... |  240 (24/78)
.........SMMMSMMMM.MM..M.SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS |  300 (26/78)
SSSSSSSSM..E....S......SS......M.SS..S..M...SSSSSSSS....MMM. |  360 (37/78)
.M....MM..SM..S..SSSSSSSS.EM.S.E.M............M.....M.SM.M.M |  420 (45/78)
..M....MMS...MMSSS................M.....EME....SEMS...SSSSSS |  480 (52/78)
SSSSS.EMSSSSM..M.MMMM...SSE.....MMM.M..MM..MSSSSSSSSSSSSSSSS |  540 (60/78)
SSS....SSSSSSSSMM.SSS..........S..M..MSSMS.SSSSSSSSSSSSSSSSS |  600 (68/78)
......E...M..........SM.....M..MMMMM.MMMMMSSSSSSSM.SS

653 mutations were generated:
     284 mutants were killed
     218 mutants were not covered by tests
     131 covered mutants were not detected
      17 fatal errors were encountered
       3 time outs were encountered

Metrics:
    Mutation Score Indicator (MSI): 47%
    Mutation Code Coverage: 67%
    Covered Code MSI: 70%

Remember that some mutants will inevitably be harmless (i.e. false positives).

Humbug results are being logged as JSON to: log.json
Humbug results are being logged as TEXT to: log.txt
```

To explain the perhaps cryptic progress output:
* Killed Mutation (.): A mutation that caused unit tests to fail which is a positive
outcome.
* Escaped Mutation (M): A mutation where the unit tests still passed which is not
what we want! Our unit tests should detect any behaviour changes.
* Uncovered Mutation (S): A mutation which occurs on a line not covered by any unit
test. Since there are no unit tests, this is another undesireable result.
* Fatal Error (E): A mutation created a fatal error. Usually a positive result since
it's obviously going to be noticed. In some cases, however, it might be a Humbug
problem that needs fixing.
* Timeout (T): This is where unit tests exceed the allowed timeout configured for
Humbug. Likely a positive result if your timeout is appropriate, and often occurs
when a mutation ends up creating an infinite loop.

Kills, errors and timeouts are all counted as detected mutations. We report errors
in the logs on the off chance that Humbug itself encountered an internal error, i.e.
a bug to be reported as an issue here!

#### The Metrics

The example summary results reported a number of metric scores:
* A Mutation Score Indicator (MSI) of 47%. This means that 47% of all generated
mutations were detected (i.e. kills, timeouts or fatal errors). The MSI is the
primary Mutation Testing metric. Given the code coverage of 65%, there is a 18%
discrepancy so Code Coverage was a terrible quality measurement in this example.
* Mutation Code Coverage is 67%. On average it should be within the same ballpark
as your normal code coverage, but code coverage ignores mutation frequency.
* The Mutation Score Indicator for code that is actually covered by tests was 70%
(i.e. ignoring code not even tested). This gives you some idea of how effective
the tests that do exist really are.

If you examine these metrics, the standout issue is that the MSI of 47% is 18 points
lower than the reported Code Coverage at 65%. These unit tests are far less effective
than Code Coverage alone could detect.

Interpreting these results requires some context. The logs will list all undetected
mutations as diffs against the original source code. Examining these will provide
further insight as to what specific mutations went undetected.

Command Line Options
--------------------

Humbug has a few command line options of note, other than those normally associated
with any Symfony Console application.

#### Overriding The Configured Timeout

You can manually set the timeout threshold for any single test:

```sh
humbug --timeout=10
```

#### Restricting Files To Mutate

If you're only interested in mutating a subset of your files, you can pass
any number of `--file` options containing simple file names, globs or regular
expressions. Basically, these are all passed to the Symfony Finder's `name()`
method.

```sh
humbug --file=NewClass.php --file=*Driver.php
```

This in no way restricts the initial Humbug check on the overall test suite which
is still executed in full to ensure all tests are passing correctly before
proceeding.

#### Mutate specific files

If you want to mutate only a few specific files, you can pass 
any number of `--path` options containing full path file names. This option will be passed 
to a filter `\Closure` that will intersect files found using the config and/or `--file` option
 with the files provided by you using the `--path` option.

```sh
humbug --path=src/Data/NewClass.php --path=src/Driver/Driver.php
```

Note: This in no way restricts the initial Humbug check on the overall test suite which
is still executed in full to ensure all tests are passing correctly before
proceeding.

#### Incremental Analysis

Incremental Analysis (IA) is an experimental unfinished mode of operation where results
are cached locally between runs and reused where it makes sense. At present, this
mode operates very naively by eliminating test runs where both the immediate file
being mutated and the relevant tests for a mutated line have not been modified
since the last run (as determined by comparing the SHA1 of the files involved).

```sh
humbug --incremental
```

The IA mode offers a significant performance increase for relatively stable code
bases, and you're free to test it and see how it fares in real life. In the future,
it does need to take into accounts changes in files which contain parent classes,
imported traits and the classes of its immediate dependencies, all of which have
an impact on the behaviour of any given object.

IA utilises a local permanent cache, e.g. `/home/padraic/.humbug`.

Performance
-----------

Mutation Testing has traditionally been slow. The concept being to re-run your test
suite for each mutation generated. To speed things up significantly, Humbug does the
following:

* On each test run, it only uses those test classes which cover the specific file
and line on which the mutation was inserted.
* It orders test classes to run so that the slowest go last (hopefully the faster
tests will detect mutations early!).
* If a mutation falls on a line not covered by any tests, well, we don't bother
running any tests.
* Performance may, depending on the source code, be significantly impacted by timeouts.
The default of 60s may be far too high for smaller codebases, and far too low for
larger ones. As a rule of thumb, it shouldn't exceed the seconds needed to
normally run the tests being mutated (and can be set lower).

While all of this speeds up Humbug, do be aware that a Humbug run will be slower than
unit testing. A 2 second test suite may require 30 seconds for mutation testing. Or
5 minutes. It all depends on the interplay between lines of code, number of tests,
level of code coverage, and the performance of both code and tests.

Mutators
--------

Humbug implements a basic suite of Mutators, which essentially tells us when a
particular PHP token can be mutated, and also apply that mutation to an array
of tokens.

Note: Source code held within functions (rather than class methods) is not mutated
at this time.

Binary Arithmetic:

| Original | Mutated | Original | Mutated |
| :------: |:-------:| :------: |:-------:| 
| + | - | /= | *= |
| - | + | %= | *= |
| * | / | **= | /= |
| / | * | & | &#124; |
| % | * | &#124; | & |
| ** | / | ^ | & |
| += | -= | ~ |  |
| -= | += | >> | << |
| *= | /= | << | >> |

Boolean Substitution:

This temporarily encompasses logical mutators.

| Original | Mutated |
| :------: |:-------:| 
| true | false |
| false | true |
| && | &#124;&#124; |
| &#124;&#124; | && |
| and | or |
| or | and |
| ! |  |

Conditional Boundaries:

| Original | Mutated
| :------: |:-------:
| >        | >=
| <        | <=
| >=       | >
| <=       | <

Negated Conditionals:

| Original | Mutated | Original | Mutated |
| :------: |:-------:| :------: |:-------:| 
| == | != | > | <= |
| != | == | < | >= |
| <> | == | >= | < |
| === | !== | <= | > |
| !== | === |

Increments:

| Original | Mutated |
| :------: |:-------:| 
| ++ | -- |
| -- | ++ |

Return Values:

| Original | Mutated | Original | Mutated |
| :------: |:-------:| :------: |:-------:|
| return true; | return false; | return <Any Float > 1.0>; | return -(<Float> + 1); |
| return false; | return true; | return $this; | return null; |
| return 0; | return 1; | return function(); | function(); return null; |
| return <Any Integer>; | return 0; | return new Class; | new Class; return null; |
| return 0.0; | return 1.0; | return (`Anything`); | (`Anything`); return null; |
| return 1.0; | return 0.0; |

Literal Numbers:

| Original | Mutated |
| :------: |:-------:| 
| 0 | 1 |
| 1 | 0 |
| Int > 1 | Int + 1 |
| Float >= 1 / <= 2 | Float + 1 |
| Float > 2 | 1 |

If Statements:

All if statements are covered largely by previous mutators, but there are special
cases such as using native functions or class methods without any compares or operations, e.g.
`is_int()` or `in_array()`. This would not cover functions defined in files since
they don't exist until runtime (something else to work on!).

| Original | Mutated |
| :------: |:-------:| 
| if(is_int(1)) | if(!is_int(1)) |

More Mutators will be added over time.

JSON Log Stats
--------------

```sh
bin/humbug stats ../my-project/humbuglog.json ../my-project/list-of-classes.txt --skip-killed=yes [-vvv]
```

Parses stats from humbuglog.json or your custom named JSON log.

CLI reference:
```sh
humbug stats [humbuglog.json location] [class list location] [--skip-killed=yes] [-vvv]
```

    humbuglog.json location, defaults to ./humbuglog.json

    class list location, a path to a text file containing full class names, one per line.

    only this files-related stats would be shown
    --skip-killed=yes is used to completely skip output of "killed" section
    various verbosity levels define amount of info to be displayed:
        by default, there's one line per class with amount of mutants killed/escaped/errored/timed out (depending on output section)
        -v adds one line per each mutant with line number and method name
        -vv adds extra line for each mutant, displaying diff view of line mutant is detected in
        -vvv shows full diff with several lines before and after

This can be tested on humbug itself, by running in humbug's dir:

bin/humbug
bin/humbug stats [-vvv]


Did I Say Rough Edges?
----------------------

This is a short list of known issues:

* Humbug does initial test runs, logging and code coverage. Should allow user to do that optionally.
* Test classes (not tests) are run in a specific order, fastest first. Interdependent test classes may
therefore fail regularly which will skew the results.
* Currently 100% PHPUnit specific, well 98.237%. There is an adapter where PHPUnit code is being shovelled.
* Certain test suite may make assumptions about having sole access to resources like /tmp which
will cause errors when Humbug tries using same.
* Fine grained test ordering by speed (as opposed to large grained test class ordering) is awaiting
implementation.
* Should test classes be used to carry non-PHPUnit dependent testing code (e.g. register_shutdown_function()),
it may create issues when combined with one or more of Humbugs optimisations which assume a finished
test really is finished.

Bah, Humbug!
============

Courtesy of [Craig Davis](https://github.com/craig-davis) who saw potential in a once empty repository :P.

```
                    .:::::::::::...
                  .::::::::::::::::::::.
                .::::::::::::::::::::::::.
               ::::::::::::::::::::::::::::.
              :::::::::::::::::::::::::::::::  .,uuu   ...
             :::::::::::::::::::::::::::::::: dHHHHHLdHHHHb
       ....:::::::'`    ::::::::::::::::::' uHHHHHHHHHHHHHF
   .uHHHHHHHHH'         ::::::::::::::`.  uHHHHHHHHHHHHHP"
   HHHHHHHHHHH          `:::::::::::',dHHuHHHHHHHHP".g@@g
  J"HHHHHHHHHP        4H ::::::::'  u$$$.
  ".HHHHHHHHP"     .,uHP :::::' uHHHHHHHHHHP"",e$$$$$c
   HHHHHHHF'      dHHHHf `````.HHHHHHHHHHP",d$$$$$$$P%C
 .dHHHP""         JHHHHbuuuu,JHHHHHHHHP",d$$$$$$$$$e=,z$$$$$$$$ee..
 ""              .HHHHHHHHHHHHHHHHHP",gdP"  ..3$$$Jd$$$$$$$$$$$$$$e.
                 dHHHHHHHHHHHHHHP".edP    " .zd$$$$$$$$$$$"3$$$$$$$$c
                 `???""??HHHHP",e$$F" .d$,?$$$$$$$$$$$$$F d$$$$$$$$F"
                       ?be.eze$$$$$".d$$$$ $$$E$$$$P".,ede`?$$$$$$$$
                      4."?$$$$$$$  z$$$$$$ $$$$r.,.e ?$$$$ $$$$$$$$$
                      '$c  "$$$$ .d$$$$$$$ 3$$$.$$$$ 4$$$ d$$$$P"`,,
                       """- "$$".`$$"    " $$f,d$$P".$$P zeee.zd$$$$$.
                     ze.    .C$C"=^"    ..$$$$$$P".$$$'e$$$$$P?$$$$$$
                 .e$$$$$$$"="$f",c,3eee$$$$$$$$P $$$P'd$$$$"..::.."?$%
                4d$$$P d$$$dF.d$$$$$$$$$$$$$$$$f $$$ d$$$" :::::::::.
               $$$$$$ d$$$$$ $$$$$$$$$$$$$$$$$$ J$$",$$$'.::::::::::::
              "$$$$$$ ?$$$$ d$$$$$$$$$$$$$$$P".dP'e$$$$':::::::::::::::
              4$$$$$$c $$$$b`$$$$$$$$$$$P"",e$$",$$$$$' ::::::::::::::::
              ' ?"?$$$b."$$$$.?$$$$$$P".e$$$$F,d$$$$$F ::::::::::::::::::
                    "?$$bc."$b.$$$$F z$$P?$$",$$$$$$$ ::::::::::::::::::::
                        `"$$c"?$$$".$$$)e$$F,$$$$$$$' ::::::::::::::::::::
                        ':. "$b...d$$P4$$$",$$$$$$$" :::::::::::::::::::::
                        ':::: "$$$$$".,"".d$$$$$$$F ::::::::::::::::::::::
                         :::: be."".d$$$4$$$$$$$$F :::::::::::::::::::::::
                          :::: "??$$$$$$$$$$?$P" :::::::::::::::::::::::::
                           :::::: ?$$$$$$$$f .::::::::::::::::::::::::::::
                            :::::::`"????"".::::::::::::::::::::::::::::::
```
