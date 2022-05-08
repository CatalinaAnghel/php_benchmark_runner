<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\Service\Contracts\BenchmarkValidatorInterface;

/**
 * BenchmarkValidator service
 */
class BenchmarkValidator implements BenchmarkValidatorInterface {
    /**
     * @param string $className
     * @param string $methodName
     * @return bool
     * @throws \ReflectionException
     */
    private function checkStaticMethod(string $className, string $methodName):bool{
        return (new \ReflectionMethod($className, $methodName))->isStatic();
    }

    /**
     * {@inheritDoc)
     */
    public function validate($params, $limit):bool{
        return (is_array($params) && 1 === count($params) && isset($params[0]) &&
            is_numeric($params[0]) && $params[0] <= (int)$limit);
    }

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     */
    public function validateHook($params, bool $checkStatic = false):bool{
        return (is_array($params) && (2 === count($params) && method_exists($params[0], $params[1]) &&
                (($this->checkStaticMethod($params[0], $params[1]) && $checkStatic) || !$checkStatic)));
    }

    /**
     * {@inheritDoc}
     */
    public function validateProvider(\Reflector $reflectionMethod, \Generator $generator): bool{
        $generatedParams = array();
        foreach ($generator as $param){
            $generatedParams[] = $param;
        }
        foreach ($reflectionMethod->getParameters() as $key => $parameter){
            if((isset($generatedParams[$key]) &&
                get_debug_type($generatedParams[$key]) !== $parameter->getType()->getName()) || (
                    !isset($generatedParams[$key]) && !$parameter->isOptional()
                )){
                return false;
            }
        }

        return true;
    }
}