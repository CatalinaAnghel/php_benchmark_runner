<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

/**
 * class BenchmarkCollection
 * @author Catalina Anghel
 */
class BenchmarkCollection {
    /**
     * @var Benchmark[]|null $benchmarks
     */
    protected ?array $benchmarks;

    /**
     * BenchmarkCollection constructor
     */
    public function __construct() {
        $this->benchmarks = array();
    }

    /**
     * @return Benchmark[]|null
     */
    public function getBenchmarks(): ?array {
        return $this->benchmarks;
    }

    /**
     * @param Benchmark[]|null $benchmarks
     */
    public function setBenchmarks(?array $benchmarks): void {
        $this->benchmarks = $benchmarks;
    }

    /**
     * @param Benchmark $benchmark
     * @param int|false $position
     */
    public function addBenchmark(Benchmark $benchmark, $position = false): void {
        if (false !== $position) {
            $this->benchmarks[$position] = $benchmark;
        } else {
            $this->benchmarks[] = $benchmark;
        }
    }
}