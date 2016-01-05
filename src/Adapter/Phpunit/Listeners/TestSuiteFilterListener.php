<?php

namespace Humbug\Adapter\Phpunit\Listeners;

use ReflectionClass;
use Humbug\Phpunit\Listener\FilterListener;
use Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter;
use Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter;

class TestSuiteFilterListener extends FilterListener
{
    /**
     * @param int $rootSuiteNestingLevel
     * @param string $filterStatsPath path used by fastesFirstFilter
     * @param string ...$filterTestSuites
     */
    public function __construct($rootSuiteNestingLevel = 0, $filterStatsPath = null)
    {
        $filterTestSuites = array_slice(func_get_args(), 2);
        $includeOnlyFilter = $this->createIncludeOnlyFilter($filterTestSuites);

        $fastestFirstFilter = new FastestFirstFilter($filterStatsPath);

        parent::__construct(
            $rootSuiteNestingLevel,
            $includeOnlyFilter,
            $fastestFirstFilter
        );
    }

    /**
     * Create the IncludeOnlyFilter based on an array of suites. We're using
     * a ReflectionClass to instantiate the filter with the provided arguments.
     */
    private function createIncludeOnlyFilter($filterTestSuites)
    {
        $reflection = new ReflectionClass('Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter');
        $includeOnlyFilter = $reflection->newInstanceArgs($filterTestSuites);

        return $includeOnlyFilter;
    }
}
