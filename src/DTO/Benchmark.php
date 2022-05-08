<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\ClassBenchmarkConfiguration;
class Benchmark{
    /**
     * @var ClassBenchmarkConfiguration $classBenchmarkConfiguration
     */
    protected ClassBenchmarkConfiguration $classBenchmarkConfiguration;

    /**
     * @var MethodBenchmarkConfiguration[]|null $methodBenchmarkConfigurations
     */
    protected ?array $methodBenchmarkConfigurations;

    /**
     * @return ClassBenchmarkConfiguration
     */
    public function getClassBenchmarkConfiguration(): ClassBenchmarkConfiguration{
        return $this->classBenchmarkConfiguration;
    }

    /**
     * @param ClassBenchmarkConfiguration $classBenchmarkConfiguration
     */
    public function setClassBenchmarkConfiguration(ClassBenchmarkConfiguration $classBenchmarkConfiguration): void{
        $this->classBenchmarkConfiguration = $classBenchmarkConfiguration;
    }

    /**
     * @return MethodBenchmarkConfiguration[]|null
     */
    public function getMethodBenchmarkConfigurations(): ?array{
        return $this->methodBenchmarkConfigurations;
    }

    /**
     * @param MethodBenchmarkConfiguration $methodBenchmarkConfiguration
     */
    public function addMethodBenchmarkConfiguration(MethodBenchmarkConfiguration $methodBenchmarkConfiguration): void{
        $this->methodBenchmarkConfigurations[] = $methodBenchmarkConfiguration;
    }
}