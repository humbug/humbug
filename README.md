Humbug: Mutation Testing for PHP
================================

Humbug is a Mutation Testing framework for PHP. It is currently in development and
so, while it does actually work quite well, it will have rough edges that a team
of minions are slaving to hammer out. If it falls out of the gate, you have been
warned ;).

Mutation Testing is, in a nutshell, giving your unit tests a run for their money.
It involves injecting small defects into source code and then checking if the unit
tests noticed. If they do, then your unit tests have "killed" the mutation. If not,
the mutation has escaped detection. As unit tests are intended to prevent regressions,
having a real regression pass unnoticed would be a bad thing!

Whereas Code Coverage can tell you what code your tests are executing, Mutation
Testing is intended to help you judge how well your unit tests actually perform
and where they could be improved.

Usage
-----

Humbug is still under development so, to repeat, beware of rough edges. To ensure
a smooth ride, you should be using PHPUnit 4. You should have your phpunit
configuration file in the base of your project (same level as your source and tests
directories). If the configuration file contains a whitelist for code coverage, it
should at least cover your main source code.

In the base directory of your project create a humbug.json file:

```
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

```
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
then you can signal this also:

```
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

The magic command, while in your project's base directory (and assuming humbug
was cloned at same level as your project directory):

```
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

Humbug results are being logged as TEXT to: log.txt

Time: 2.26 minutes Memory: 8.75MB
```

Mutation Testing has traditionally been a slow process, however Humbug implements a number
of significant optimisations. It generates code coverage data so that only tests
applicable to a specific mutated line of code are run. It runs any tests in order of their
logged execution time (smallest first). It will not execute tests where code
coverage for a mutated line is nil. You may also adjust the timeout setting (if
too high for your project). The timeout exists to counteract infinite loops when
created through the mutation process.

These optimisations, while requiring some upfront execution time, make Humbug
quite fast once it gets going.

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

Installation
------------

You can clone and install Humbug's dependencies using Composer:

```
git clone https://github.com/padraic/humbug.git
cd humbug
/path/to/composer.phar install
```

The humbug command is now at bin/humbug.

Humbug will currently work on PHP 5.4 or greater.

Performance
-----------

Mutation Testing has traditionally been slow. The concept being to re-run your test
suite for each mutation generated. To speed things up significantly, Humbug does the
following:

* On each test run, it only uses those test classes which cover the specific file
and line on which the mutation was inserted.
* It orders test classes to run so that the slowest go last (hopefully the faster
tests will detect mutations early!).
* We use Runkit because...writing many files is the opposite of fast.
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

* It makes assumptions about local directories which may be incorrect (read Usage).
* Configuring Humbug is by command line; a configuration file is really needed.
* PHP file parsing has a few bugs: it makes assumptions about whitespace and likely will
explode when meeting a closure. This should never interrupt a MT run, however. At worst, it will report an "E".
* An error is logged when source code references a function that does not exist (e.g. 3rd party module)
* Humbug does initial test runs, logging and code coverage. Should allow user to do that optionally.
* Test classes (not tests) are run in a specific order, fastest first. Interdependent test classes may
therefore fail regularly which will skew the results.
* Need to finalise reporting formats: text is easy, XML in progress.
* Currently 100% PHPUnit specific, well 98.237%. There is an adapter where PHPUnit code is being shovelled.
* The list of supported mutations is awaiting expansion. Yes, we need to make those unit tests scream ;).



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
