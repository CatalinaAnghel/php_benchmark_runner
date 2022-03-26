<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\Helper\OptionsMapper;
use MepProject\PhpBenchmarkRunner\Service\Abstractions\IPhpBenchmarkRunner;

class PhpBenchmarkRunner implements IPhpBenchmarkRunner
{
    protected $optionsMapper;

    public function __construct(OptionsMapper $optionsManager)
    {
        $this->optionsMapper = $optionsManager;
    }

    public function test()
    {
        $this->optionsMapper->print();
    }
}