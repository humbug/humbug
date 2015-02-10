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
            $mutation = $escaped->getMutation();
            $out[] = $index + 1 . ') ' . $mutation['mutator'];
            $out[] = 'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':';
            $out[] = $escaped->getDiff();
            $out[] = PHP_EOL;
        }

        if (count($mutantTimeouts) > 0) {
            $out = array_merge($out, [PHP_EOL, '------', 'Timeouts', '------']);
            foreach ($mutantTimeouts as $index => $timeouted) {
                $mutation = $timeouted->getMutation();
                $out[] = $index + 1 . ') ' . $mutation['mutator'];
                $out[] = 'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':';
                $out[] = $timeouted->getDiff();
                $out[] = PHP_EOL;
            }
        }

        if (count($mutantErrors) > 0) {
            $out = array_merge($out, [PHP_EOL, '------', 'Errors', '------']);
            foreach ($mutantErrors as $index => $errored) {
                $mutation = $errored->getMutation();
                $out[] = $index + 1 . ') ' . $mutation['mutator'];
                $out[] = 'Diff on ' . $mutation['class'] . '::' . $mutation['method'] . '() in ' . $mutation['file'] . ':';
                $out[] = $errored->getDiff();
                $out[] = PHP_EOL;
                $out[] = 'The following output was received on stderr:';
                $out[] = PHP_EOL;
                $out[] = $errored->getProcess()->getErrorOutput();
                $out[] = PHP_EOL;
                $out[] = PHP_EOL;
            }
        }

        return implode(PHP_EOL, $out);
    }
} 