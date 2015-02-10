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
        $out = $this->prepareMutantsReport($mutantEscapes, 'Escapes');

        if (count($mutantTimeouts) > 0) {
            $out .= PHP_EOL . $this->prepareMutantsReport($mutantTimeouts, 'Timeouts');
        }

        if (count($mutantErrors) > 0) {
            $out .= PHP_EOL . $this->prepareMutantsReport($mutantTimeouts, 'Errors');
        }

        return $out;
    }

    public function prepareMutantsReport(array $mutants, $mutantsGroupName)
    {
        $out = [];

        $out[] =
            '------' . PHP_EOL .
            $mutantsGroupName . PHP_EOL .
            '------' . PHP_EOL .
            PHP_EOL;

        foreach ($mutants as $index => $mutant) {
            $out[] = $index + 1 . ') ' . $this->prepareReportForMutant($mutant);
        }

        return implode(PHP_EOL, $out);
    }

    public function prepareReportForMutant(Mutant $mutant)
    {
        $mutation = $mutant->getMutation();

        $out =
            $mutation['mutator'] . PHP_EOL .
            'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':' . PHP_EOL .
            $mutant->getDiff() . PHP_EOL .
            PHP_EOL;

        $errorOutput = $mutant->getProcess()->getErrorOutput();

        if ($errorOutput) {
            $out .=
                'The following output was received on stderr:' . PHP_EOL .
                PHP_EOL .
                $errorOutput . PHP_EOL .
                PHP_EOL;
        }

        return $out;
    }
}
