<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractSerializableClass;

class MemoryResult extends AbstractSerializableClass {
    /**
     * @var float $value
     */
    protected float $value;

    /**
     * @var string $unit
     */
    protected string $unit;

    /**
     * @return float
     */
    public function getValue(): float {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue(float $value): void {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getUnit(): string {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit(string $unit): void {
        $this->unit = $unit;
    }
}