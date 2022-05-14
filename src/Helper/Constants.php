<?php

namespace MepProject\PhpBenchmarkRunner\Helper;

class Constants {
    const ANNOTATION_CHARACTER = '@';
    # service types
    const HOOKS_MANAGER_TAG = 'hooks';
    const METRICS_GENERATOR_TAG = 'metrics';

    # options
    const REVOLUTIONS_OPTION = 'Revolutions';
    const ITERATIONS_OPTION = 'Iterations';
    const PARAMETER_PROVIDER_OPTION = 'ParamProvider';

    # hooks
    const BEFORE_METHOD_HOOK = 'BeforeMethod';
    const AFTER_METHOD_HOOK = 'AfterMethod';
    const BEFORE_CLASS_HOOK = 'BeforeClass';
    const AFTER_CLASS_HOOK = 'AfterClass';

    const BENCHMARK_ANNOTATION = 'Benchmark';
    const BENCHMARK_METHOD_ANNOTATION = 'BenchmarkMethod';

    const UNITS = ['B', 'kB', 'MB', 'GB'];
}