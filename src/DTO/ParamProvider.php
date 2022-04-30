<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

class ParamProvider{
    /**
     * @var string $className
     */
    protected string $className;

    /**
     * @var string $methodName
     */
    protected string $methodName;

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
}