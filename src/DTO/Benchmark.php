<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractBenchmarkConfiguration;

class Benchmark{
    /**
     * @var AbstractBenchmarkConfiguration $classBenchmarkConfiguration
     */
    protected AbstractBenchmarkConfiguration $classBenchmarkConfiguration;

    /**
     * @var AbstractBenchmarkConfiguration[]|null $methodBenchmarkConfigurations
     */
    protected ?array $methodBenchmarkConfigurations;

    /**
     * @return AbstractBenchmarkConfiguration
     */
    public function getClassBenchmarkConfiguration(): AbstractBenchmarkConfiguration{
        return $this->classBenchmarkConfiguration;
    }

    /**
     * @param AbstractBenchmarkConfiguration $classBenchmarkConfiguration
     */
    public function setClassBenchmarkConfiguration(AbstractBenchmarkConfiguration $classBenchmarkConfiguration): void{
        $this->classBenchmarkConfiguration = $classBenchmarkConfiguration;
    }

    /**
     * @return AbstractBenchmarkConfiguration[]|null
     */
    public function getMethodBenchmarkConfigurations(): ?array{
        return $this->methodBenchmarkConfigurations;
    }

    /**
     * @param AbstractBenchmarkConfiguration $methodBenchmarkConfiguration
     */
    public function addMethodBenchmarkConfiguration(AbstractBenchmarkConfiguration $methodBenchmarkConfiguration): void{
        $this->methodBenchmarkConfigurations[] = $methodBenchmarkConfiguration;
    }
}