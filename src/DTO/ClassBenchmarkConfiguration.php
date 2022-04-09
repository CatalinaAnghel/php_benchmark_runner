<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;
use MepProject\PhpBenchmarkRunner\DTO\ClassHook;

class ClassBenchmarkConfiguration extends AbstractBenchmarkConfiguration {
    /**
     * ClassBenchmarkConfiguration constructor.
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
        return $hook->getHookType() === ClassHook::class;
    }
}