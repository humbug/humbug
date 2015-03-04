<?php

namespace Humbug;

class Collector
{

    private $totalCount = 0;

    private $shadowCount = 0;

    private $killedCount = 0;

    private $killed = [];

    private $timeoutCount = 0;

    private $timeouts = [];

    private $errorCount = 0;

    private $errors = [];

    private $escapeCount = 0;

    private $escaped = [];

    public function collect(Mutant $mutant, $result)
    {
        $this->totalCount++;

        if ($result['timeout'] === true) {
            $this->timeoutCount++;
            $this->timeouts[] = $mutant;
        } elseif ($result['successful'] === false) {
            $this->errorCount++;
            $this->errors[] = $mutant;
        } elseif ($result['passed'] === false) {
            $this->killedCount++;
            $this->killed[] = $mutant;
        } else {
            $this->escapeCount++;
            $this->escaped[] = $mutant;
        }
    }

    public function collectShadow()
    {
        $this->totalCount++;
        $this->shadowCount++;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function getMeasurableTotal()
    {
        return $this->totalCount - $this->shadowCount;
    }

    public function getVanquishedTotal()
    {
        return $this->killedCount + $this->timeoutCount + $this->errorCount;
    }

    /**
     * @return int
     */
    public function getShadowCount()
    {
        return $this->shadowCount;
    }

    /**
     * @return array
     */
    public function getShadows()
    {
        return $this->shadows;
    }

    /**
     * @return int
     */
    public function getKilledCount()
    {
        return $this->killedCount;
    }

    /**
     * @return array
     */
    public function getKilled()
    {
        return $this->killed;
    }

    /**
     * @return int
     */
    public function getTimeoutCount()
    {
        return $this->timeoutCount;
    }

    /**
     * @return array
     */
    public function getTimeouts()
    {
        return $this->timeouts;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getEscapeCount()
    {
        return $this->escapeCount;
    }

    /**
     * @return array
     */
    public function getEscaped()
    {
        return $this->escaped;
    }

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
            $group[] = $mutant->toArray();
        }

        return $group;
    }
}
