<?php
/**
 * Class collecting source and file data to track changes over time.
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\Log;

/**
 * Class JsonLogParser
 * @package app\models
 */
class JsonLogParser
{
    private $data;
    private $classesToFilter = [];

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setClassList($classList)
    {
        $this->classesToFilter = $classList;
    }

    public function getFilteredStats($verbose = false)
    {
        $result = $this->getOverallStats($verbose);
        $sections = $this->getSectionsList();
        $shouldFilter = count($this->classesToFilter) > 0;

        foreach ($sections as $section) {
            $count = 0;
            foreach ($result[$section] as $key => $value) {
                if ($shouldFilter && !in_array($key, $this->classesToFilter)) {
                    unset ($result[$section][$key]);
                } else {
                    $count += $value['count'];
                }
            }
            $result['total'][$section] = $count;
        }
        return $result;
    }

    public function getOverallStats($verbose = true)
    {
        $result = array_combine($this->getSectionsList(), [[], [], [], []]);

        $sections = $this->getSectionsList();
        foreach ($sections as $section) {
            if (isset($this->data[$section]) && is_array($this->data[$section])) {
                foreach ($this->data[$section] as $item) {
                    $className = str_replace('\\\\', '\\', $item['class']);
                    if (isset($result[$section][$className])) {
                        $result[$section][$className]['count']++;
                    } else {
                        $result[$section][$className]['count'] = 1;
                        $result[$section][$className]['classHash'] = md5($item['class'] . time());
                    }
                    if ($verbose) {
                        $result[$section][$className]['items'][] = [
                            'method' => $item['method'],
                            'line' => $item['line'],
                            'tests' => $item['tests'],
                            'mutator' => $item['mutator'],
                            'diff' => $item['diff'],
                            'diffProcessed' => $this->processDiff($item['diff']),
                        ];
                    }
                }
            }
        }

        return $result;
    }

    public function getSectionsList()
    {
        return ["killed", "escaped", "errored", "timeouts"];
    }

    public function getSummary()
    {
        return isset($this->data['summary']) ? $this->data['summary'] : [];
    }

    public function generateListSummary($list)
    {
        $this->setClassList($list);
        $rawData = $this->getFilteredStats(true);
        $stats = [];
        $total = 0;
        foreach ($rawData as $section => $items) {
            $stats[$section . '_classes'] = count($items);
            $stats[$section] = 0;
            if (count($items)) {
                foreach ($items as $item) {
                    $stats[$section] += $item['count'];
                    $total += $item['count'];
                }
            }
        }
        $stats['total'] = $total;
        $percent = [];
        foreach ($stats as $section => $amount) {
            $percents[$section] = round((float)$amount / (float)$stats['total'] * 100);
        }
        $percents['killed'] = 100 - $percents['escaped'] - $percents['errored'] - $percents['timeouts'];

        return [
            'amounts' => $stats,
            'percents' => $percents
        ];
    }

    private function processDiff($diff)
    {
        $diff = preg_split('/[\n\r]+/', $diff);
        unset($diff[0]);
        unset($diff[1]);
        unset($diff[2]);

        $lineNumNew = null;
        $lineNumOld = null;

        foreach ($diff as $lineNum => $line) {
            if (!in_array(substr($line, 0, 1), ['+', '-'])) {
                continue;
            }

            $firstSymbol = substr($line, 0, 1);
            if ($firstSymbol === '+') {
                $lineNumNew = $lineNum;
            } else {
                $lineNumOld = $lineNum;
            }
        }

        if ($lineNumOld !== null) {
            $diff[$lineNumOld] = $this->generateDiffString($diff[$lineNumOld], $diff[$lineNumNew]);
        }
        if ($lineNumNew !== null) {
            $diff[$lineNumNew] = $this->generateDiffString($diff[$lineNumOld], $diff[$lineNumNew], false);
        }

        return $diff;
    }

    private function generateDiffString($old, $new, $isOld = true)
    {
        $old = substr($old, 1);
        $new = substr($new, 1);
        $fromStart = strspn($old ^ $new, "\0");
        $fromEnd = strspn(strrev($old) ^ strrev($new), "\0");

        $oldEnd = strlen($old) - $fromEnd;
        $newEnd = strlen($new) - $fromEnd;

        $start = substr($new, 0, $fromStart);
        $end = substr($new, $newEnd);
        $newDiff = substr($new, $fromStart, $newEnd - $fromStart);
        $oldDiff = substr($old, $fromStart, $oldEnd - $fromStart);

        if ($isOld) {
            return '-' . $start . '%start%' . $oldDiff . '%end%' . $end;
        }

        return '+' . $start . '%start%' . $newDiff . '%end%' . $end;
    }
}
