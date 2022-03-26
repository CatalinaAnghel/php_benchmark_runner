<?php

namespace MepProject\PhpBenchmarkRunner\Helper;

class Option{
    /**
     * @var array $arguments
     */
    protected $arguments;

    /**
     * @var string $serviceTag
     */
    protected $serviceTag;

    /**
     * @var string $matchedOption
     */
    protected $matchedOption;

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getServiceTag(): string
    {
        return $this->serviceTag;
    }

    /**
     * @param string $serviceTag
     */
    public function setServiceTag(string $serviceTag): void
    {
        $this->serviceTag = $serviceTag;
    }

    /**
     * @return string
     */
    public function getMatchedOption(): string
    {
        return $this->matchedOption;
    }

    /**
     * @param string $matchedOption
     */
    public function setMatchedOption(string $matchedOption): void
    {
        $this->matchedOption = $matchedOption;
    }
}