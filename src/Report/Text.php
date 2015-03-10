<?php

/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     rafal.wartalski@gmail.com
 */

namespace Humbug\Report;

use Humbug\Mutant;

class Text
{
    /**
     * @param Mutant[] $mutants
     * @param string $mutantsGroupName
     * @return string
     */
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

    /**
     * @param Mutant $mutant
     * @return string
     */
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
