<?php

namespace MepProject\PhpBenchmarkRunner;

use MepProject\PhpBenchmarkRunner\DependencyInjection\PhpBenchmarkRunnerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PhpBenchmarkRunnerBundle extends Bundle{
    public function getContainerExtension():?ExtensionInterface{
        return new PhpBenchmarkRunnerExtension();
    }
}