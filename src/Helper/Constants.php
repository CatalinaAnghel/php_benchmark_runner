<?php

namespace MepProject\PhpBenchmarkRunner\Helper;

class Constants{
    CONST ANNOTATION_CHARACTER = '@';
    # service types
    CONST HOOKS_MANAGER_TAG = 'hooks';
    CONST METRICS_GENERATOR_TAG = 'metrics';

    # options
    CONST REVOLUTIONS_OPTION = 'Revolutions';
    CONST ITERATIONS_OPTION = 'Iterations';
    CONST PARAMETER_PROVIDER_OPTION = 'ParamProvider';

    # hooks
    CONST BEFORE_METHOD_HOOK = 'BeforeMethod';
    CONST AFTER_METHOD_HOOK = 'AfterMethod';
    CONST BEFORE_CLASS_HOOK = 'BeforeClass';
    CONST AFTER_CLASS_HOOK = 'AfterClass';

    CONST BENCHMARK_ANNOTATION = 'Benchmark';
    CONST BENCHMARK_METHOD_ANNOTATION = 'BenchmarkMethod';

    CONST UNITS = ['B','kB','MB','GB'];
}