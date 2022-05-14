<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\Service\Contracts\MemoryProfilerInterface;

class MemoryProfiler implements MemoryProfilerInterface {
    /**
     * @var array $memoryProfileArray
     */
    private array $memoryProfileArray;

    /**
     * @var float $initialMemory
     */
    private float $initialMemory;

    /**
     * @var bool $showBacktrace
     */
    private bool $showBacktrace;

    /**
     * MemoryProfiler constructor
     *
     * @param bool $includeBacktrace
     */
    public function __construct(bool $includeBacktrace = false) {
        $this->showBacktrace = $includeBacktrace;
        $this->memoryProfileArray = array();
        $this->initialMemory = memory_get_usage();
    }

    /**
     * {@inheritDoc}
     */
    public function start(): void {
        register_tick_function(array($this, "tick"), true);
        declare(ticks=1);
    }

    /**
     * {@inheritDoc}
     */
    public function tick(): void {
        $data = array(
            "initial_memory" => $this->initialMemory,
            "memory" => memory_get_usage() - $this->initialMemory,
            "time" => microtime(TRUE)
        );

        if ($this->showBacktrace) {
            $data['backtrace'] = debug_backtrace(FALSE);
        }

        $this->memoryProfileArray[] = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void {
        unregister_tick_function(array($this, "tick"));
    }

    /**
     * Get the profiling data
     *
     * @return array
     */
    public function getMemoryProfileArray(): array {
        return $this->memoryProfileArray;
    }
}