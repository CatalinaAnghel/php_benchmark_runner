<?php

namespace MepProject\PhpBenchmarkRunner\Traits;

trait SubscribedServiceTrait{
    /**
     * @return string
     */
    public static function getIndex($className): string{
        $normalizedClassName = explode('\\', $className);
        return strtolower(end($normalizedClassName));
    }
}