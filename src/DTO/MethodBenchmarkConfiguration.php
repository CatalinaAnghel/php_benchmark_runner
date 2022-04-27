<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;

/**
 * class MethodBenchmarkConfiguration
 */
class MethodBenchmarkConfiguration extends AbstractBenchmarkConfiguration {
    /**
     * @var ParamProvider $paramProvider
     */
    protected $paramProvider;

    /**
     * MethodBenchmarkConfiguration constructor
     */
    public function __construct(){
        $this->init();
    }

    /**
     * @return ParamProvider
     */
    public function getParamProvider(): ParamProvider{
        return $this->paramProvider;
    }

    /**
     * @param ParamProvider $paramProvider
     */
    public function setParamProvider(ParamProvider $paramProvider): void{
        $this->paramProvider = $paramProvider;
    }

    /**
     * {@inheritDoc}
     * @param AbstractHook $hook
     * @return bool
     */
    public function validateHook(AbstractHook $hook): bool{
        return $hook->getHookType() === MethodHook::class;
    }
}