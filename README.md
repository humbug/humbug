Humbug: Mutation Testing for PHP
================================

Humbug is a Mutation Testing framework for PHP. It is currently in development and
so, while it does actually work quite well, it will have rough edges that a team
of minions are slaving to hammer out.

Mutation Testing is, in a nutshell, giving your unit tests a run for their money.
It involves injecting small defects into source code and then checking if the unit
tests noticed. If they do, then your unit tests have "killed" the mutation. If not,
the mutation has escaped detection. As unit tests are intended to prevent regressions,
having a real regression pass unnoticed might be a bad thing.

Not all mutations actually break your code - some will naturally create changes
which are equivalent to the original code.

While Code Coverage will tell you what code your unit tests actually execute,
Mutation Testing will help you to assess how well those unit tests actually monitor
the covered code.

Usage
-----

Humbug is still under development so be aware of the rough edges. Ensure that you are
using PHPUnit >3.7 (preferably the latest 4.4) and have a phpunit configuration file
in the root directory of your project. It's preferable to include a filter whitelist
in this configuration file for the code coverage collection. With this in place,
navigate to your project's root directory and check the help (adjust path as needed).

```
./bin/humbug -h
```

A few options will be automatically detected if they follow standard conventions.
Otherwise, set the --basedir and --srcdir options when running. Run humbug without
any arguments and it should work fine. It's not like anyone is rolling saving
throws...

Bear in mind that Mutation Testing runs your tests multiple times. While it will
make every effort to manage tests so that only relevant ones are actually run for
each mutation, it still means that as your source code increases, test count
increases and code coverage improves, the Mutation Testing execution time will
be extended.

Use it on something small while testing it out to get a feel for performance ;).
In very short order, minions will be adding more flexibility to target Humbug at
specific tests and subsets of tests. The magic command:

```
./bin/humbug
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

Humbug has completed the initial test run successfully.

Humbug is analysing source files...

Mutation Testing is commencing...
(.: killed, M: escaped, S: uncovered, E: fatal error, T: timed out)

.MMMSS.SSSM.SSMSS..SMSM.T.M.....S.......MMM.SS.SMMS...SMMSMS |   60
SSSSSSSS..S.M..S.MM...M.SSM.....MM.SEEE..M..M.ME...M..ESSSSS |  120
MSSMM.MMMMS..MMMMSSSSS.MMSMSSSSSS.......M..M

164 mutations were generated:
      63 mutants were killed
      43 mutants were never detected
       5 fatal errors were encountered
       1 time outs were encountered
      52 mutants were not covered by any test

Out of 112 testable mutants, 60% were detected.
Out of 164 total mutations, 32% were untestable.

Remember that some mutants will inevitably be harmless (i.e. false positives).

Time: 2.44 minutes Memory: 9.75MB
```

Additional detailed information about escaped mutations and errors is currently
next on the list. These will be logged to a file (given the amount of data).

Installation
------------

Humbug requires the installation of the runkit extension. Yes, that scares me too!
Use the updated version at https://github.com/padraic/runkit and NOT the one you
would normally get from PECL. This has minor modifications for PHP 5.6 support from
the one maintained by Dmitry Zenovich. For Ubuntu:

```
git clone https://github.com/padraic/runkit.git
cd runkit
phpize
./configure
make
sudo make install
sudo bash -c "echo 'extension=runkit.so' > /etc/php5/mods-available/runkit.ini"
sudo php5enmod runkit
```

We use Runkit to alter PHP methods without resorting to writing entire source code
copies all over the place. No, you are not required to install this on production
servers. Yes, it would be nice to have the PECL runkit synced to Dmitry's version.

Packagist registration will follow shortly, but for now:

```
git clone https://github.com/padraic/humbug.git
```

The humbug command is now at ./humbug/bin/humbug.

Humbug will currently work on PHP 5.4 or greater.


Did I Say Rough Edges?
----------------------

This is a short list of known issues:

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
