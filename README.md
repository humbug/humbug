Humbug: Mutation Testing for PHP
================================

Humbug is a Mutation Testing framework for PHP. It is currently in development and
so, while it does actually work quite well, it will have rough edges that a team
of minions are slaving to hammer out. If it falls out of the gate, you have been
warned ;).

[![Build Status](https://travis-ci.org/padraic/humbug.svg)](https://travis-ci.org/padraic/humbug) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/padraic/humbug/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/padraic/humbug/?branch=master)

Mutation Testing is, in a nutshell, giving your unit tests a run for their money.
It involves injecting small defects into source code and then checking if the unit
tests noticed. If they do, then your unit tests have "killed" the mutation. If not,
the mutation has escaped detection. As unit tests are intended to prevent regressions,
having a real regression pass unnoticed would be a bad thing!

Whereas Code Coverage can tell you what code your tests are executing, Mutation
Testing is intended to help you judge how well your unit tests actually perform
and where they could be improved.

I've written in more detail about why Mutation Testing is worth having: [Lies, Damned Lies and Code Coverage: Towards Mutation Testing](http://blog.astrumfutura.com/2015/01/lies-damned-lies-and-code-coverage-towards-mutation-testing/)

Installation
------------

#### Git

You can clone and install Humbug's dependencies using Composer:

```sh
git clone https://github.com/padraic/humbug.git
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

#### Composer

Due to Humbug's dependencies being pegged to recent versions, adding Humbug to
composer.json may give rise to conflicts. The above two methods of installation are
preferred where this occurs.

Humbug currently works on PHP 5.4 or greater.

Usage
-----

### Configuration

Humbug is still under development so, to repeat, beware of rough edges.

In the base directory of your project create a humbug.json file:

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

Edit as appropriate. If you do not define at least one log, detailed information
about escaped mutants will not be available. The Text log is the most human readable.
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

#### Configure command

To configure humbug in your project you may also run 

```sh
humbug configure
```

This tool will ask some questions required to create Humbug configuration file(humbug.json). 

### Running Humbug

Ensure that your tests are all in a passing state (incomplete and skipped tests
are allowed). Humbug will quit if any of your tests are failing.

The magic command, while in your project's base directory (and assuming humbug
was cloned at same level as your project directory):

```sh
../humbug/bin/humbug
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

  242 [=======================================================] 30 secs

Humbug has completed the initial test run successfully.

Humbug is analysing source files...

Mutation Testing is commencing...
(.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out)

SS..E.SM..ESSMMMMMSSSSSSSSSSSSSSSSSSE.ESSSSSSSSSSSSSSSSSSSM. |   60 ( 7/76)
.M....MMMESSS..SSSSSSSSSSSS...MMM.MEMMESS.SS........SSS....S |  120 (12/76)
SMMSSSSM.M.M.M...TT.M...T.MM.SSSS...SSSSSSSSSSSSSM..M..M.SSS |  180 (16/76)
S.......M.......MMM...M...SSSS.SSSEM..M.M.M.MM...SSSSS.SSS.M |  240 (18/76)
SMMMMMMM.STSSSTTTSSSSS.......SSSSS............SSSMMMSSSMMMMM |  300 (23/76)
.MM.M.SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSESS |  360 (29/76)
S....S......SS......M...SSS..S..M...SSSSSSSSSSSSSSSSSS..MMMM |  420 (36/76)
M..M....MMM..SSSSSSS..SSSSSSSSSS.EM.SS.ME.MSSSS...........MM |  480 (43/76)
.SSS....M.SM.M.M..M..SS.MMMSS.......MMMS................SSSS |  540 (50/76)
SSSSSSSSSSSSSSSSSSSE.SSSEMEMMMMMMS.MSSSSSSSMSSSSSSSSSSSSSSSS |  600 (51/76)
S.MEMSSSSSSM..SMM.MMMM...SSS...EMMMEM.MMS.MMSSSSSSSSS.M..SSS |  660 (60/76)
SSSSSSMM.SSSSSS...MSS......SSSSSS..M..MSSMSMSSSSSSSSSSSSSSSS |  720 (66/76)
SSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSS

779 mutations were generated:
     229 mutants were killed
     410 mutants were not covered by tests
     119 covered mutants were not detected
      14 fatal errors were encountered
       7 time outs were encountered

Out of 369 test covered mutations, 68% were detected.
Out of 779 total mutations, 32% were detected.
Out of 779 total mutations, 53% were not covered by tests.

Remember that some mutants will inevitably be harmless (i.e. false positives).

Humbug results are being logged as JSON to: humbuglog.json
Humbug results are being logged as TEXT to: humbuglog.txt

Time: 2.26 minutes Memory: 8.75MB
```

To explain the perhaps cryptic progress output, a killed mutant is a defect which
was detected by the test suite, an escaped mutation is a defect that was never
detected, an uncovered mutant is a mutation occuring on a line that Code Coverage
indicates is not executed, a fatal error is anything which led to the adapter
crashing (typically if a mutation creates a syntax or logical problem in the
source code) and a timed out mutant is a mutation which took longer than your
defined timeout to execute (perhaps an infinite loop which mutations will infrequently
create in source code).

Kills, errors and timeouts are all counted as detected mutations. We report errors
in the logs on the off chance that Humbug itself encountered an internal error, i.e.
a bug to be reported as an issue here!

The example summary results reported a number of statistics:
* The headline news is that 68% of mutations which were covered by unit tests
were detected by your unit tests. Analysing the log may assist in improving that score.
* A combined detection score of 32% was achieved. This includes mutations which
occured in source code not covered by any of the tests run. Better code coverage
would increase this combined score.
* Finally, 53% of the mutations generated occured in source not covered by unit
tests. For example, if you had a 90% Code Coverage then this is telling you that
the 10% uncovered code is generating 53% of all mutations. Such discrepancies are
proof that Code Coverage is, ahem, unreliable.

Interpreting these results requires some context. The logs will list all undetected
mutations as diffs against the original source code. Examining these will provide
further insight as to what specific mutations went undetected.

Command Line Options
--------------------

Humbug has a few command line options of note, other than those normally associated
with any Symfony Console application.

####Overriding The Configured Timeout

You can manually set the timeout threshold for any single test:

```sh
humbug --timeout=10
```

####Restricting Files To Mutate

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

Binary Arithmetic:

| Original | Mutated |
| :------: |:-------:| 
| + | - |
| - | + |
| * | / |
| / | * |
| % | * |
| ** | / |
| += | -= |
| -= | += |
| *= | /= |
| /= | *= |
| %= | *= |
| **= | /= |
| & | &#124; |
| &#124; | & |
| ^ | & |
| ~ |  |
| >> | << |
| << | >> |

Boolean Substitution:

This temporarily encompasses logical mutators.

| Original | Mutated |
| :------: |:-------:| 
| true | false |
| false | true |
| && | &#124;&#124; |
| &#124;&#124; | && |
| and | for |
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

| Original | Mutated |
| :------: |:-------:| 
| == | != |
| != | == |
| <> | == |
| === | !== |
| !== | === |
| > | <= |
| < | >= |
| >= | < |
| <= | > |

Increments:

| Original | Mutated |
| :------: |:-------:| 
| ++ | -- |
| -- | ++ |

Return Values:

| Original | Mutated |
| :------: |:-------:| 
| return true; | return false; |
| return false; | return true; |
| return 0; | return 1; |
| return <Any Integer>; | return 0; |
| return 0.0; | return 1.0; |
| return 1.0; | return 0.0; |
| return <Any Float > 1.0>; | return -(<Float> + 1); |
| return $this; | return null; |
| return function(); | function(); return null; |
| return new Class; | new Class; return null; |

Literal Numbers:

| Original | Mutated |
| :------: |:-------:| 
| 0 | 1 |
| 1 | 0 |
| Int > 1 | Int + 1 |
| Float >= 1 / <= 2 | Float + 1 |
| Float > 2 | 1 |

More Mutators will be added over time.

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
