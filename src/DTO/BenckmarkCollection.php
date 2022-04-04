<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

/**
 * class BenchmarkCollection
 * @author Catalina Anghel
 */
class BenckmarkCollection {
    /**
     * @var BenchmarkConfiguration[]|null $benchmarks
     */
    protected $benchmarks;

    /**
     * BenchmarkCollection constructor
     */
    public function __construct() {
        $this->benchmarks = array();
    }

    /**
     * @return BenchmarkConfiguration[]|null
     */
    public function getBenchmarks(): ?array {
        return $this->benchmarks;
    }

    /**
     * @param BenchmarkConfiguration[]|null $benchmarks
     */
    public function setBenchmarks(?array $benchmarks): void {
        $this->benchmarks = $benchmarks;
    }

    /**
     * @param BenchmarkConfiguration $benchmarkConfiguration
     * @param int|false $postition
     */
    public function addBenchmark(BenchmarkConfiguration $benchmarkConfiguration, $postition = false):void {
        if($postition !== false){
            $this->benchmarks[$position] = $benchmarkConfiguration;
        }else{
            $this->benchmarks[] = $benchmarkConfiguration;
        }
    }
}