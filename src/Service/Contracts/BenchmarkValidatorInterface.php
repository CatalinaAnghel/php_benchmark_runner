<?php

namespace MepProject\PhpBenchmarkRunner\Service\Contracts;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;

interface BenchmarkValidatorInterface {
    /**
     * Validates the benchmark configuration from the PHPDoc blocks (e.g. the number of iterations and revolutions)
     *
     * @param $params
     * @param $limit
     * @return bool
     */
    public function validate($params, $limit): bool;

    /**
     * Validates the provided hook configuration (from the PHPDoc blocks)
     *
     * @param $params
     * @return bool
     */
    public function validateHookConfiguration($params): bool;

    /**
     * Validates the provided hook configuration (from the PHPDoc blocks)
     *
     * @param AbstractHook $hook
     * @return bool
     */
    public function validateHook(AbstractHook $hook): bool;

    /**
     * Validates the configuration provided for the parameter provider
     *
     * @param \Reflector $reflectionMethod
     * @param \Generator $generator
     * @return bool
     */
    public function validateProvider(\Reflector $reflectionMethod, \Generator $generator): bool;
}