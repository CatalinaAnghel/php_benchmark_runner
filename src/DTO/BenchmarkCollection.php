<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;

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
     * @param int|false $postition
     */
    public function addBenchmark(Benchmark $benchmark, $postition = false):void {
        if(false !== $postition){
            $this->benchmarks[$position] = $benchmark;
        }else{
            $this->benchmarks[] = $benchmark;
        }
    }
}