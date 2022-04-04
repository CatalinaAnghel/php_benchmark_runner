<?php

namespace MepProject\PhpBenchmarkRunner\DTO;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;

class MethodHook extends AbstractHook{
    /**
     * {@inheritDoc}
     * @return bool
     */
    public function validate():bool{
        return true;
    }
}