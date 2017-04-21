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

use SebastianBergmann\Diff\Differ;

class Diff
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected $differ;

    private function __construct()
    {
        $this->differ = new Differ();
    }

    public function difference($from, $to, $lines = 12)
    {
        return $this->split(
            $this->differ->diff($from, $to),
            "\n",
            $lines
        );
    }

    protected function split($string, $needle, $nth)
    {
        $max = strlen($string);
        $n = 0;
        for ($i=0; $i < $max; $i++) {
            if ($string[$i] == $needle) {
                $n++;
                if ($n >= $nth) {
                    break;
                }
            }
        }
        return substr($string, 0, $i);
    }
}
