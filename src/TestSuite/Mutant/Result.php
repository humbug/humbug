<?php

/**
 * Status of an executed mutant
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 * @author     Thibaud Fabre
 */
namespace Humbug\TestSuite\Mutant;

class Result
{

    const KILL = 0;

    const ESCAPE = 1;

    const ERROR = 2;

    const TIMEOUT = 3;

    private $status;

    private $stdErr = '';

    public static function getStatusCode($passFlag, $successFlag, $timeoutFlag)
    {
        $status = Result::ESCAPE;

        if ($timeoutFlag === true) {
            $status = Result::TIMEOUT;
        } elseif ($successFlag === false) {
            $status = Result::ERROR;
        } elseif ($passFlag === false) {
            $status = Result::KILL;
        }

        return $status;
    }

    public function __construct($status, $stdOut, $stdErr)
    {
        if ($status < 0 || $status > 3) {
            throw new \InvalidArgumentException('Invalid result code.');
        }

        $this->status = $status;
        $this->stdErr = $stdErr;
        $this->stdOut = $stdOut;
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
