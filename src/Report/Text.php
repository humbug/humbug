<?php

namespace Humbug\Report;

use Humbug\Mutant;

class Text
{
    /**
     * @param Mutant[] $mutantEscapes
     * @param Mutant[] $mutantTimeouts
     * @param Mutant[] $mutantErrors
     * @return string
     */
    public function prepare($mutantEscapes, $mutantTimeouts, $mutantErrors)
    {
        $out = [PHP_EOL, '-------', 'Escapes', '-------'];
        foreach ($mutantEscapes as $index => $escaped) {
            $out[] = $index + 1 . ') ' . $this->prepareReportForMutant($escaped);
        }

        if (count($mutantTimeouts) > 0) {
            $out = array_merge($out, [PHP_EOL, '------', 'Timeouts', '------']);
            foreach ($mutantTimeouts as $index => $timeouted) {
                $out[] = $index + 1 . ') ' . $this->prepareReportForMutant($timeouted);
            }
        }

        if (count($mutantErrors) > 0) {
            $out = array_merge($out, [PHP_EOL, '------', 'Errors', '------']);
            foreach ($mutantErrors as $index => $errored) {
                $out[] = $index + 1 . ') ' . $this->prepareReportForMutant($errored);
                $out[] = 'The following output was received on stderr:';
                $out[] = PHP_EOL;
                $out[] = $errored->getProcess()->getErrorOutput();
                $out[] = PHP_EOL;
                $out[] = PHP_EOL;
            }
        }

        return implode(PHP_EOL, $out);
    }

    public function prepareReportForMutant(Mutant $mutant)
    {
        $mutation = $mutant->getMutation();

        return
            $mutation['mutator'] . PHP_EOL .
            'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':' . PHP_EOL .
            $mutant->getDiff() . PHP_EOL .
            PHP_EOL;
    }
}