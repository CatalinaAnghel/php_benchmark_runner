<?php

namespace MepProject\PhpBenchmarkRunner\DTO\Contracts;

abstract class AbstractHook {
    /**
     * @var string $className
     */
    protected string $className;

    /**
     * @var string $methodName
     */
    protected string $methodName;

    /**
     * If the hook will be run before or after the class
     * @var bool $runAfter
     */
    protected bool $runAfter;

    /**
     * @return string
     */
    public function getClassName(): string {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getMethodName(): string {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName(string $methodName): void {
        $this->methodName = $methodName;
    }

    /**
     * @return bool
     */
    public function isRunAfter(): bool {
        return $this->runAfter;
    }

    /**
     * @param bool $runAfter
     */
    public function setRunAfter(bool $runAfter): void {
        $this->runAfter = $runAfter;
    }

    /**
     * Validate if the hook is correctly created
     * @return bool
     */
    abstract public function validate(): bool;

    /**
     * Returns the type of the hook (class or method)
     *
     * @return string
     */
    public function getHookType(): string {
        return get_class($this);
    }
}