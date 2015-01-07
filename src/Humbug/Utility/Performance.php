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

class Performance
{

    protected static $startTime;

    protected static $endTime;

    public static function start()
    {
        self::$startTime = microtime(true);
        self::$endTime = null;
    }

    public static function stop()
    {
        self::$endTime = microtime(true);
    }

    public static function getTime()
    {
        if (empty(self::$endTime)) {
            return microtime(true) - self::$startTime;
        }
        return self::$endTime - self::$startTime;
    }

    public static function getTimeString()
    {
        $horizons = [
            'hour'   => 3600000,
            'minute' => 60000,
            'second' => 1000
        ];
        $milliseconds = round(self::getTime() * 1000);
        foreach ($horizons as $unit => $value) {
            if ($milliseconds >= $value) {
                $time = floor($milliseconds / $value * 100.0) / 100.0;
                return $time . ' ' . ($time == 1 ? $unit : $unit . 's');
            }
        }
        return $milliseconds . ' milliseconds';
    }

    public static function getMemoryUsage()
    {
        return (memory_get_peak_usage(true) / 1048576);
    }

    public static function getMemoryUsageString()
    {
        return sprintf('%4.2fMB', self::getMemoryUsage());
    }
}