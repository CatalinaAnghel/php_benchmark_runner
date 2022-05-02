<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

class BenchmarkResult{
    /**
     * @var int $iterationsNumber
     */
    protected int $iterationsNumber;

    /**
     * @var int $revolutionsNumber
     */
    protected int $revolutionsNumber;

    /**
     * @var float[] $averageExecutionTimeResults
     */
    protected array $averageExecutionTimeResults;

    /**
     * @var MemoryResult[] $memoryResults
     */
    protected array $memoryResults;

    /**
     * @var MemoryResult $memoryPeakValue
     */
    protected MemoryResult $memoryPeakValue;

    /**
     * @return int
     */
    public function getIterationsNumber(): int{
        return $this->iterationsNumber;
    }

    /**
     * @param int $iterationsNumber
     */
    public function setIterationsNumber(int $iterationsNumber): void{
        $this->iterationsNumber = $iterationsNumber;
    }

    /**
     * @return int
     */
    public function getRevolutionsNumber(): int{
        return $this->revolutionsNumber;
    }

    /**
     * @param int $revolutionsNumber
     */
    public function setRevolutionsNumber(int $revolutionsNumber): void{
        $this->revolutionsNumber = $revolutionsNumber;
    }

    /**
     * @return float[]
     */
    public function getAverageExecutionTimeResults(): array{
        return $this->averageExecutionTimeResults;
    }

    /**
     * @param float $averageExecutionTimeResult
     */
    public function addAverageExecutionTimeResult(float $averageExecutionTimeResult): void{
        $this->averageExecutionTimeResults[] = $averageExecutionTimeResult;
    }

    /**
     * @return MemoryResult[]
     */
    public function getMemoryResults(): array{
        return $this->memoryResults;
    }

    /**
     * @param MemoryResult $memoryResult
     */
    public function addMemoryResult(MemoryResult $memoryResult): void{
        $this->memoryResults[] = $memoryResult;
    }

    /**
     * @return MemoryResult
     */
    public function getMemoryPeakValue(): MemoryResult{
        return $this->memoryPeakValue;
    }

    /**
     * @param MemoryResult $memoryPeakValue
     */
    public function setMemoryPeakValue(MemoryResult $memoryPeakValue): void{
        $this->memoryPeakValue = $memoryPeakValue;
    }
}