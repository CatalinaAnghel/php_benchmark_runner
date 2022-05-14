<?php

namespace MepProject\PhpBenchmarkRunner;

use MepProject\PhpBenchmarkRunner\DependencyInjection\PhpBenchmarkRunnerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class PhpBenchmarkRunnerBundle
 */
class PhpBenchmarkRunnerBundle extends Bundle {
    /**
     * {@inheritDoc}
     * @return ExtensionInterface|null
     */
    public function getContainerExtension(): ?ExtensionInterface {
        return new PhpBenchmarkRunnerExtension();
    }
}