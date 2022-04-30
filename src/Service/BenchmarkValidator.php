<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * BenchmarkValidator service
 */
class BenchmarkValidator{
    /**
     * @param $params
     * @return bool
     */
    public function validate($params):bool{
        return (is_array($params) && 1 === count($params) && isset($params[0]) && is_numeric($params[0]));
    }

    /**
     * @param $params
     * @param bool $checkStatic
     * @return bool
     * @throws \ReflectionException
     */
    public function validateHook($params, bool $checkStatic = false):bool{
        return (is_array($params) && (2 === count($params) && method_exists($params[0], $params[1]) &&
                (($this->checkStaticMethod($params[0], $params[1]) && $checkStatic) || !$checkStatic)));
    }

    /**
     * @param $className
     * @param $methodName
     * @return bool
     * @throws \ReflectionException
     */
    protected function checkStaticMethod($className, $methodName):bool{
        return (new \ReflectionMethod($className, $methodName))->isStatic();
    }

    /**
     *
     * @param \Reflector $reflectionMethod
     * @param \Generator $generator
     * @return bool
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