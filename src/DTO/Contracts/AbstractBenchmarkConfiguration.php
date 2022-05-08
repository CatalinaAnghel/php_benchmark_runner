<?php

namespace MepProject\PhpBenchmarkRunner\DTO\Contracts;

use Symfony\Component\Config\Definition\Exception\Exception;
use Reflector;

abstract class AbstractBenchmarkConfiguration{
    /**
     * The number of revolutions that will be performed
     * @var int $numberOfRevolutions
     */
    protected int $numberOfRevolutions;

    /**
     * The number of iterations that will be performed on a benchmark
     * @var int $numberOfIterations
     */
    protected int $numberOfIterations;

    /**
     * The hooks that will be applied while running the benchmark
     * @var AbstractHook[]|null $hooks
     */
    protected ?array $hooks;

    /**
     * @var Reflector $reflector
     */
    protected Reflector $reflector;

    /**
     * Init
     */
    public function init(){
        $this->numberOfRevolutions = 1;
        $this->numberOfIterations = 1;
        $this->hooks = array();
    }

    /**
     * @return int
     */
    public function getNumberOfRevolutions(): int{
        return $this->numberOfRevolutions;
    }

    /**
     * @param int $numberOfRevolutions
     */
    public function setNumberOfRevolutions(int $numberOfRevolutions): void{
        $this->numberOfRevolutions = $numberOfRevolutions;
    }

    /**
     * @return int
     */
    public function getNumberOfIterations(): int{
        return $this->numberOfIterations;
    }

    /**
     * @param int $numberOfIterations
     */
    public function setNumberOfIterations(int $numberOfIterations): void{
        $this->numberOfIterations = $numberOfIterations;
    }

    /**
     * @return AbstractHook[]|null
     */
    public function getHooks(): ?array{
        return $this->hooks;
    }

    /**
     * @param AbstractHook $hook
     */
    public function addHook(AbstractHook $hook):void{
        if($this->validateHook($hook)){
            $this->hooks[] = $hook;
        }else{
            throw new Exception('Invalid hook configuration');
        }
    }

    /**
     * @return Reflector
     */
    public function getReflector(): Reflector{
        return $this->reflector;
    }

    /**
     * @param Reflector $reflector
     */
    public function setReflector(Reflector $reflector): void{
        $this->reflector = $reflector;
    }


    /**
     * Validates the hook configuration
     *
     * @param AbstractHook $hook
     * @return bool
     */
    abstract public function validateHook(AbstractHook $hook):bool;
}