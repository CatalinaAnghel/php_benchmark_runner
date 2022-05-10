<?php

namespace MepProject\PhpBenchmarkRunner\Service\Contracts;

interface MemoryProfilerInterface {
    /**
     * Starts the profiling process.
     */
    public function start():void;

    /**
     * Tick method used to gather the required information
     */
    public function tick():void;

    /**
     * Stopping the profiling process.
     */
    public function stop():void;
}