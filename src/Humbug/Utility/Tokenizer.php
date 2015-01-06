<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Utility;

class Tokenizer {

    const T_NEWLINE = -1;

    /**
     * Get tokens use token_get_all() but post process to interpolate new line
     * markers so we can check the line number of each token.
     *
     * @param string $source
     * @return array
     */
    public static function getTokens($source)
    {
        $newline = 0;
        $tokens = token_get_all($source);
        foreach ($tokens as $token) {
            $tname = is_array($token) ? $token[0] : null;
            $tdata = is_array($token) ? $token[1] : $token;
            if ($tname == T_CONSTANT_ENCAPSED_STRING) {
                $ntokens[] = [$tname, $tdata];
                continue;
            } elseif (substr($tdata, 0, 2) == '/*') {
                $ntokens[] = [$tname, $tdata];
                $split = preg_split("%(\r\n|\n)%", $tdata, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                foreach ($split as $value) {
                    if ($value == "\r\n" || $value == "\n") {
                        $newline++;
                    }
                }
                continue;
            }
            $split = preg_split("%(\r\n|\n)%", $tdata, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($split as $data) {
                if ($data == "\r\n" || $data == "\n") {
                    $newline++;
                    $ntokens[] = [self::T_NEWLINE, $data, $newline];
                } else {
                    $ntokens[] = is_array($token) ? [$tname, $data] : $data;
                }
            }
        }
        return $ntokens;
    }

    /**
     * Reconstruct a string of source code from its constituent tokens
     *
     * @param array $tokens
     * @return string
     */
    public static function reconstructFromTokens(array $tokens)
    {
        $str = '';
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $str .= $token;
            } else {
                $str .= $token[1];
            }
        }
        return $str;
    }

}