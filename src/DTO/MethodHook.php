<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;

class MethodHook extends AbstractHook {
    /**
     * {@inheritDoc}
     * @return bool
     */
    public function validate(): bool {
        return method_exists($this->getClassName(), $this->getMethodName());
    }
}