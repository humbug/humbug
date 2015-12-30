<?php

namespace Humbug\Adapter\Phpunit\Listeners;

use Humbug\Phpunit\Listener\FilterListener;
use Humbug\Phpunit\Filter\TestSuite\IncludeOnlyFilter;
use Humbug\Phpunit\Filter\TestSuite\FastestFirstFilter;

class TestSuiteFilterListener extends FilterListener
{
    public function __construct($rootSuiteNestingLevel = 0, $filterTestSuites = [], $filterStatsPath = [])
    {
        $includeOnlyFilter = new IncludeOnlyFilter($filterTestSuites);
        $fastestFirstFilter = new FastestFirstFilter($filterStatsPath);

        parent::__construct(
            $rootSuiteNestingLevel,
            $includeOnlyFilter,
            $fastestFirstFilter
        );
    }
}
