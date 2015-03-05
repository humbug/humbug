<?php

namespace Humbug;

/**
 * Class collecting all mutants and their results.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
class Collector
{

    /**
     * @var int Total mutant count collected
     */
    private $totalCount = 0;

    /**
     * @var int Count of mutants not covered by a test case.
     */
    private $shadowCount = 0;

    /**
     * @var int Count of mutants killed by a test case.
     */
    private $killedCount = 0;

    /**
     * @var Mutant[] Mutants killed by a test case.
     */
    private $killed = [];

    /**
     * @var int Count of mutants that timed out.
     */
    private $timeoutCount = 0;

    /**
     * @var Mutant[] Mutants that resulted in a timeout.
     */
    private $timeouts = [];

    /**
     * @var int Count of mutants that triggered an error.
     */
    private $errorCount = 0;

    /**
     * @var Mutant[] Mutants that triggered an error.
     */
    private $errors = [];

    /**
     * @var int Count of mutants that escaped tests.
     */
    private $escapeCount = 0;

    /**
     * @var Mutant[] Mutants that escaped tests.
     */
    private $escaped = [];

    /**
     * Collects a mutant and its result.
     *
     * @param Mutant $mutant
     * @param MutantResult $result
     */
    public function collect(Mutant $mutant, MutantResult $result)
    {
        $this->totalCount++;

        if ($result->isTimeout()) {
            $this->timeoutCount++;
            $this->timeouts[] = $mutant;
        } elseif ($result->isError()) {
            $this->errorCount++;
            $this->errors[] = $mutant;
        } elseif ($result->isKill()) {
            $this->killedCount++;
            $this->killed[] = $mutant;
        } else {
            $this->escapeCount++;
            $this->escaped[] = $mutant;
        }
    }

    /**
     * Collects a shadow mutant.
     */
    public function collectShadow()
    {
        $this->totalCount++;
        $this->shadowCount++;
    }

    /**
     * @return int Total count of collected mutants.
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return int Measurable count of mutants.
     */
    public function getMeasurableTotal()
    {
        return $this->totalCount - $this->shadowCount;
    }

    /**
     * @return int Count of mutants that were covered by a test.
     */
    public function getVanquishedTotal()
    {
        return $this->killedCount + $this->timeoutCount + $this->errorCount;
    }

    /**
     * @return int Count of mutants that were not covered by a test
     */
    public function getShadowCount()
    {
        return $this->shadowCount;
    }

    /**
     * @return int Count of mutants successfully killed by tests.
     */
    public function getKilledCount()
    {
        return $this->killedCount;
    }

    /**
     * @return Mutant[] List of mutants successfully killed by tests.
     */
    public function getKilled()
    {
        return $this->killed;
    }

    /**
     * @return int Count of mutants that resulted in a timeout.
     */
    public function getTimeoutCount()
    {
        return $this->timeoutCount;
    }

    /**
     * @return Mutant[] List of mutants that resulted in a timeout.
     */
    public function getTimeouts()
    {
        return $this->timeouts;
    }

    /**
     * @return int Count of mutants that resulted in an error.
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * @return Mutant[] List of mutants that triggered an error.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int Count of mutants that escaped test cases.
     */
    public function getEscapeCount()
    {
        return $this->escapeCount;
    }

    /**
     * @return Mutant[] List of mutants that escaped test cases.
     */
    public function getEscaped()
    {
        return $this->escaped;
    }

    /**
     * Returns all collected mutants as arrays, grouped by their result status.
     *
     * @return array
     */
    public function toGroupedMutantArray()
    {
        return [
            'escaped' => $this->createGroup($this->escaped),
            'errored' => $this->createGroup($this->errors),
            'timeouts' => $this->createGroup($this->timeouts),
            'killed' => $this->createGroup($this->killed)
        ];
    }

    private function createGroup(array $mutants)
    {
        $group = [];

        foreach ($mutants as $mutant) {
            $mutantData = $mutant->toArray();

            $stderr = explode(PHP_EOL, $mutantData['stderr'], 2);
            $mutantData['stderr'] = $stderr[0];

            $group[] = $mutantData;
        }

        return $group;
    }
}
