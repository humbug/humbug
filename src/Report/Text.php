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
use Humbug\TestSuite\Mutant\Result;

class Text
{
    /**
     * @param Result[] $results
     * @param string $mutantsGroupName
     * @return string
     */
    public function prepareMutantsReport(array $results, $mutantsGroupName)
    {
        $out = [];

        $out[] =
            '------' . PHP_EOL .
            $mutantsGroupName . PHP_EOL .
            '------' . PHP_EOL .
            PHP_EOL;

        foreach ($results as $index => $result) {
            $out[] = $index + 1 . ') ' . $this->prepareReportForMutant($result);
        }

        return implode(PHP_EOL, $out);
    }

    /**
     * @param Result $result
     * @return string
     */
    public function prepareReportForMutant(Result $result)
    {
        $mutant = $result->getMutant();
        $mutation = $mutant->getMutation();

        $out =
            $mutation->getMutator() . PHP_EOL .
            'Diff on ' . $mutation->getClass() . '::' . $mutation->getMethod() . '() in ' . $mutation->getFile() . ':' . PHP_EOL .
            $mutant->getDiff() . PHP_EOL .
            PHP_EOL;

        $errorOutput = $result->getErrorOutput();

        if (!empty($errorOutput)) {
            $out .=
                'The following output was received on stderr:' . PHP_EOL .
                PHP_EOL .
                $errorOutput . PHP_EOL .
                PHP_EOL;
        }

        return $out;
    }
}
