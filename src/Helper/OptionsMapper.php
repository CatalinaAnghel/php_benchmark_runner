<?php

namespace MepProject\PhpBenchmarkRunner\Helper;

class OptionsMapper {
    private $sourceDir;

    # service types
    CONST HOOKS_MANAGER_TAG = 'hooks';
    CONST METRICS_GENERATOR_TAG = 'metrics';

    # options
    CONST REVOLUTIONS_OPTION = 'Revolutions';
    CONST ITERATIONS_OPTION = 'Iterations';
    CONST PARAMETER_PROVIDER_OPTION = 'ParameterProvider';

    # hooks
    CONST BEFORE_METHOD_HOOK = 'BeforeMethod';
    CONST AFTER_METHOD_HOOK = 'AfterMethod';
    CONST BENCHMARK_ORDER_HOOK = 'Order';
    CONST BEFORE_CLASS_HOOK = 'BeforeBenchmark';
    CONST AFTER_CLASS_HOOK = 'AfterBenchmark';

    protected $patterns = [
        '#@Revolutions([1-9]+[0-9]*)#' => self::REVOLUTIONS_OPTION,
        '#@Iterations([1-9]+[0-9]*)#' => self::ITERATIONS_OPTION,
        '#@ParameterProvider(("[a-zA-Z0-9_]+", ?)?[a-zA-Z0-9_]+)#' => self::PARAMETER_PROVIDER_OPTION,
        '#@BeforeMethod(("[a-zA-Z0-9_]+", ?)?[a-zA-Z0-9_]+)#' => self::BEFORE_METHOD_HOOK,
        '#@AfterMethod(("[a-zA-Z0-9_]+", ?)?[a-zA-Z0-9_]+)#' => self::AFTER_METHOD_HOOK,
        '#@BeforeBenchmark(("[a-zA-Z0-9_]+", ?)?[a-zA-Z0-9_]+)#' => self::BEFORE_CLASS_HOOK,
        '#@AfterBenchmark(("[a-zA-Z0-9_]+", ?)?[a-zA-Z0-9_]+)#' => self::AFTER_CLASS_HOOK,
        '#@Order([1-9]+[0-9]*)#' => self::BENCHMARK_ORDER_HOOK
    ];

    public function __construct($sourceDir, $locator)
    {
        dd($locator->getProvidedServices());
        $this->sourceDir = '../../../../../' . $sourceDir;
    }

    public function print(){
        dump(scandir($this->sourceDir));
        dd($this->sourceDir);
    }
}