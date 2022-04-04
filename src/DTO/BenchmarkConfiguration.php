<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;

/**
 * class BenchmarkConfiguration
 */
class BenchmarkConfiguration{
    /**
     * The number of revolutions that will be performed
     * @var int $numberOfRevolutions
     */
    protected $numberOfRevolutions;

    /**
     * The number of iterations that will be performed on a benchmark
     * @var int $numberOfIterations
     */
    protected $numberOfIterations;

    /**
     * The hooks that will be applied while running the benchmark
     * @var AbstractHook[]|null $hooks
     */
    protected $hooks;

    /**
     * @return int
     */
    public function getNumberOfRevolutions(): int{
        return $this->numberOfRevolutions;
    }

    /**
     * @param int $numberOfRevolutions
     */
    public function setNumberOfRevolutions(int $numberOfRevolutions): void{
        $this->numberOfRevolutions = $numberOfRevolutions;
    }

    /**
     * @return int
     */
    public function getNumberOfIterations(): int{
        return $this->numberOfIterations;
    }

    /**
     * @param int $numberOfIterations
     */
    public function setNumberOfIterations(int $numberOfIterations): void{
        $this->numberOfIterations = $numberOfIterations;
    }

    /**
     * @return AbstractHook[]|null
     */
    public function getHooks(): ?array{
        return $this->hooks;
    }

    /**
     * @param AbstractHook[]|null $hooks
     */
    public function setHooks(?array $hooks): void{
        $this->hooks = $hooks;
    }

    /**
     * @param AbstractHook $hook
     */
    public function addHook(AbstractHook $hook):void{
        $this->hooks[] = $hook;
    }
}