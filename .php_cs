<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('vendor')
    ->exclude('features')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->level('psr2')
    ->finder($finder);