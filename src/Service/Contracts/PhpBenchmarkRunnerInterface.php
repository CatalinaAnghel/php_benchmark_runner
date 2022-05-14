<?php

namespace MepProject\PhpBenchmarkRunner\Service\Contracts;

use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;

interface PhpBenchmarkRunnerInterface {
    /**
     * Start the benchmarking process
     */
    public function buildBenchmark(): array;

    /**
     * Benchmark based on the benchmarking plan
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection): array;
}