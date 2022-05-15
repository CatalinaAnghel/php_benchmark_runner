<?php

namespace MepProject\PhpBenchmarkRunner\Exception;

use Throwable;

/**
 * InvalidConfigurationException - exception thrown when the bundle configuration is not properly made.
 */
class InvalidConfigurationException extends \Exception {
    /**
     * @var string $providedValue
     */
    protected string $providedValue;

    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, string $providedValue = '') {
        parent::__construct($message, $code, $previous);
        $this->providedValue = $providedValue;
    }

    /**
     * Gets the provided value (as seen in the PHPDoc block)
     *
     * @return string
     */
    public function getProvidedValue(): string {
        return $this->providedValue;
    }

    /**
     * Sets the provided value based on the provided PHPDoc block
     *
     * @param string $providedValue
     */
    public function setProvidedValue(string $providedValue): void {
        $this->providedValue = $providedValue;
    }
}