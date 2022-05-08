<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;

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