<?php

namespace MepProject\PhpBenchmarkRunner\Traits;

trait SubscribedServiceTrait {
    /**
     * @param $className
     * @return string
     */
    public static function getIndex($className): string {
        $normalizedClassName = explode('\\', $className);
        return strtolower(end($normalizedClassName));
    }
}