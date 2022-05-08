<?php

namespace MepProject\PhpBenchmarkRunner\Service\Contracts;

use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;

interface AnnotationMapperInterface {
    /**
     * Builds the BenchmarkCollection which includes the recipes for the registered benchmarks.
     * @return BenchmarkCollection
     */
    public function buildBenchmarkRecipe(): BenchmarkCollection;
}