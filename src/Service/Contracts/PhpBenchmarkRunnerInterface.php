<?php

namespace MepProject\PhpBenchmarkRunner\Service\Contracts;

use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;

interface PhpBenchmarkRunnerInterface{
    /**
     * Start the benchmarking process
     */
    public function buildBenchmark():void;

//    /**
//     * Builds the benchmarking recipe based on the annotations provided within the registered services.
//     * @return BenchmarkCollection
//     */
//    public function buildBenchmarkRecipe(): BenchmarkCollection;

    /**
     * Benchmark based on the benchmarking plan
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection):array;
}