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

use Humbug\Mutant;

class Result
{

    const KILL = 0;

    const ESCAPE = 1;

    const ERROR = 2;

    const TIMEOUT = 3;

    /**
     * @var Mutant
     */
    private $mutant;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $stdErr = '';

    /**
     * @var string
     */
    private $stdOut = '';

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

    /**
     * @param Mutant $mutant
     * @param int $status
     * @param string $stdOut
     * @param string $stdErr
     */
    public function __construct(Mutant $mutant, $status, $stdOut, $stdErr)
    {
        if ($status < 0 || $status > 3) {
            throw new \InvalidArgumentException('Invalid result code.');
        }

        $this->mutant = $mutant;
        $this->status = $status;
        $this->stdErr = $stdErr;
        $this->stdOut = $stdOut;
    }

    /**
     * @return bool
     */
    public function isTimeout()
    {
        return $this->status === self::TIMEOUT;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->status === self::ERROR;
    }

    /**
     * @return bool
     */
    public function isKill()
    {
        return $this->status === self::KILL;
    }

    /**
     * @return bool
     */
    public function isEscape()
    {
        return $this->status === self::ESCAPE;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->status;
    }

    /**
     * @return Mutant
     */
    public function getMutant()
    {
        return $this->mutant;
    }

    /**
     * @return string
     */
    public function getErrorOutput()
    {
        return $this->stdErr;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->stdOut;
    }

    public function toArray()
    {
        $data = $this->mutant->toArray();

        $data['stderr'] = $this->stdErr;
        $data['stdout'] = $this->stdOut;

        return $data;
    }
}
