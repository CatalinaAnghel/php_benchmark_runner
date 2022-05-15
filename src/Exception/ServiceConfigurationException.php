<?php

namespace MepProject\PhpBenchmarkRunner\Exception;

use MepProject\PhpBenchmarkRunner\Traits\SubscribedServiceTrait;
use Throwable;

/**
 * Class ServiceConfigurationException
 */
class ServiceConfigurationException extends InvalidConfigurationException {
    /**
     * @var string $methodName
     */
    protected string $methodName;

    /**
     * @var string $normalizedServiceClassName
     */
    protected string $normalizedServiceClassName;

    use SubscribedServiceTrait;

    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param string $providedValue
     * @param string $methodName
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, string $providedValue = '', $methodName = '') {
        parent::__construct($message, $code, $previous, $providedValue);
        $this->normalizedServiceClassName = self::getIndex($providedValue);
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public function getMethodName(): string {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName(string $methodName): void {
        $this->methodName = $methodName;
    }
}