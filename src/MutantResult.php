<?php

namespace Humbug;

/**
 * Status of an executed mutant
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */

class MutantResult
{

    const KILL = 0;

    const ESCAPE = 1;

    const ERROR = 2;

    const TIMEOUT = 3;

    private $status;

    private $stdErr = '';

    public function __construct($passFlag, $successFlag, $timeoutFlag, $stdOut, $stdErr)
    {
        $this->status = self::ESCAPE;
        $this->stdErr = $stdErr;
        $this->stdOut = $stdOut;

        if ($timeoutFlag === true) {
            $this->status = self::TIMEOUT;
        } elseif ($successFlag === false) {
            $this->status = self::ERROR;
        } elseif ($passFlag === false) {
            $this->status = self::KILL;
        }
    }

    public function isTimeout()
    {
        return $this->status === self::TIMEOUT;
    }

    public function isError()
    {
        return $this->status === self::ERROR;
    }

    public function isKill()
    {
        return $this->status === self::KILL;
    }

    public function isEscape()
    {
        return $this->status === self::ESCAPE;
    }

    public function getResult()
    {
        return $this->status;
    }

    public function getErrorOutput()
    {
        return $this->stdErr;
    }
}
