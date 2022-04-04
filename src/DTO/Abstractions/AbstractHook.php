<?php

namespace MepProject\PhpBenchmarkRunner\DTO\Abstractions;

abstract class AbstractHook{
    /**
     * @var string $className
     */
    protected $className;

    /**
     * @var string $methodName
     */
    protected $methodName;

    /**
     * If the hook will be run before or after the class
     * @var bool $runAfter
     */
    protected $runAfter;

    /**
     * @return string
     */
    public function getClassName(): string{
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void{
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getMethodName(): string{
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName(string $methodName): void{
        $this->methodName = $methodName;
    }

    /**
     * @return bool
     */
    public function isRunAfter(): bool{
        return $this->runAfter;
    }

    /**
     * @param bool $runAfter
     */
    public function setRunAfter(bool $runAfter): void{
        $this->runAfter = $runAfter;
    }

    /**
     * Validate if the hook is correctly created
     * @return bool
     */
    public abstract function validate():bool;
}