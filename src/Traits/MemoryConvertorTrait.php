<?php

namespace MepProject\PhpBenchmarkRunner\Traits;

use MepProject\PhpBenchmarkRunner\DTO\MemoryResult;
use MepProject\PhpBenchmarkRunner\Helper\Constants;

trait MemoryConvertorTrait{
    /**
     * @param $size
     * @return MemoryResult
     */
    protected function convert($size):MemoryResult{
        $memoryResult = new MemoryResult();
        $memoryResult->setValue(round($size/ (1024 ** ($i = floor(log($size, 1024)))),6));
        $memoryResult->setUnit(Constants::UNITS[$i]);
        return $memoryResult;
    }
}