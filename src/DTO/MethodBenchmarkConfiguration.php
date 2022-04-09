<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;

/**
 * class MethodBenchmarkConfiguration
 */
class MethodBenchmarkConfiguration extends AbstractBenchmarkConfiguration {
    /**
     * MethodBenchmarkConfiguration constructor
     */
    public function __construct(){
        $this->init();
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