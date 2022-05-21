<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;

/**
 * class ClassHook
 */
class ClassHook extends AbstractHook {
    /**
     * {@inheritDoc}
     * @return bool
     */
    public function validate(): bool {
        return method_exists($this->getClassName(), $this->getMethodName()) &&
            $this->checkStaticMethod($this->getClassName(), $this->getMethodName());
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return bool
     * @throws \ReflectionException
     */
    private function checkStaticMethod(string $className, string $methodName): bool {
        return (new \ReflectionMethod($className, $methodName))->isStatic();
    }
}