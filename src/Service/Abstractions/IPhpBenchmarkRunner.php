<?php

namespace MepProject\PhpBenchmarkRunner\Service\Abstractions;

use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;

interface IPhpBenchmarkRunner{
    /**
     * Start the benchmarking process
     */
    public function buildBenchmark():void;


    /**
     * Benchmark based on the benchmarking plan
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection):array;
}