<?php

namespace MepProject\PhpBenchmarkRunner\DTO\Contracts;

abstract class AbstractSerializableClass implements \JsonSerializable {
    public function jsonSerialize(): object {
        return (object)get_object_vars($this);
    }
}