<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;
use MepProject\PhpBenchmarkRunner\Service\Contracts\BenchmarkValidatorInterface;

/**
 * BenchmarkValidator service
 */
class BenchmarkValidator implements BenchmarkValidatorInterface {
    /**
     * {@inheritDoc)
     */
    public function validate($params, $limit): bool {
        return (is_array($params) && 1 === count($params) && isset($params[0]) &&
            is_numeric($params[0]) && $params[0] <= (int)$limit && $params[0] >= 1);
    }

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     */
    public function validateHookConfiguration($params): bool {
        return is_array($params) && 2 === count($params);
    }

    /**
     * {@inheritDoc}
     */
    public function validateHook(AbstractHook $hook): bool {
        return $hook->validate();
    }

    /**
     * {@inheritDoc}
     */
    public function validateProvider(\Reflector $reflectionMethod, \Generator $generator): bool {
        $generatedParams = array();
        foreach ($generator as $param) {
            $generatedParams[] = $param;
        }
        foreach ($reflectionMethod->getParameters() as $key => $parameter) {
            if ((isset($generatedParams[$key]) &&
                    gettype($generatedParams[$key]) !== $parameter->getType()->getName()) || (
                    !isset($generatedParams[$key]) && !$parameter->isOptional()
                )) {
                return false;
            }
        }

        return true;
    }
}