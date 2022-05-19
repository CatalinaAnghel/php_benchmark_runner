<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkResult;
use MepProject\PhpBenchmarkRunner\DTO\MethodBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\Exception\InvalidConfigurationException;
use MepProject\PhpBenchmarkRunner\Exception\ServiceConfigurationException;
use MepProject\PhpBenchmarkRunner\Helper\ExceptionMessages;
use MepProject\PhpBenchmarkRunner\Service\Contracts\AnnotationMapperInterface;
use MepProject\PhpBenchmarkRunner\Service\Contracts\PhpBenchmarkRunnerInterface;
use MepProject\PhpBenchmarkRunner\Traits\MemoryConvertorTrait;
use MepProject\PhpBenchmarkRunner\Traits\SubscribedServiceTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class PhpBenchmarkRunnerInterface.
 */
class PhpBenchmarkRunner implements PhpBenchmarkRunnerInterface {
    /**
     * @var AnnotationMapperInterface
     */
    private AnnotationMapperInterface $annotationMapper;

    /**
     * @var ServiceLocator $serviceLocator
     */
    private ServiceLocator $serviceLocator;

    /**
     * @var ServiceLocator|null $providersServiceLocator
     */
    private ?ServiceLocator $providersServiceLocator;

    /**
     * @var ServiceLocator|null $hooksServiceLocator
     */
    private ?ServiceLocator $hooksServiceLocator;

    use SubscribedServiceTrait;

    use MemoryConvertorTrait;

    /**
     * @param AnnotationMapperInterface $annotationMapper
     * @param array $parallelConfiguration
     * @param ServiceLocator|null $serviceLocator
     * @param ServiceLocator|null $providersLocator
     * @param ServiceLocator|null $hooksLocator
     */
    public function __construct(AnnotationMapperInterface $annotationMapper, ServiceLocator $serviceLocator = null, ServiceLocator $providersLocator = null, ServiceLocator $hooksLocator = null) {
        $this->annotationMapper = $annotationMapper;
        $this->serviceLocator = $serviceLocator;
        $this->providersServiceLocator = $providersLocator;
        $this->hooksServiceLocator = $hooksLocator;

        $this->validateConfiguration();
    }

    /**
     * Validate the configuration
     */
    private function validateConfiguration(): void {
        if (null === $this->serviceLocator) {
            throw new InvalidConfigurationException(ExceptionMessages::INVALID_SERVICE_LOCATOR_EXCEPTION_MESSAGE);
        }
    }

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws ContainerExceptionInterface
     */
    public function buildBenchmark(): array {
        $benchmarkCollection = $this->annotationMapper->buildBenchmarkRecipe();
        return $this->runBenchmark($benchmarkCollection);
    }

    /**
     * {@inheritDoc}
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection): array {
        $results = array();
        if (is_array($benchmarkCollection->getBenchmarks()) && count($benchmarkCollection->getBenchmarks())) {
            foreach ($benchmarkCollection->getBenchmarks() as $key =>$benchmark) {
                // run the before class hooks
                $this->runClassHooks($benchmark->getClassBenchmarkConfiguration()->getHooks());
                // method benchmark configuration
                foreach ($benchmark->getMethodBenchmarkConfigurations() as $methodBenchmarkConfiguration) {
                    $this->runMethodHooks($methodBenchmarkConfiguration->getHooks());
                    $results[$benchmark->getClassBenchmarkConfiguration()->getReflector()->name][$methodBenchmarkConfiguration->getReflector()->name] =
                        $this->runSequentialBenchmark($methodBenchmarkConfiguration);

                    $this->runMethodHooks($methodBenchmarkConfiguration->getHooks(), true);
                }

                // run the after class hooks
                $this->runClassHooks($benchmark->getClassBenchmarkConfiguration()->getHooks(), true);
            }
        }
        return $results;
    }

    /**
     * @param array $hooks
     * @param bool $runAfter
     */
    private function runClassHooks(array $hooks, bool $runAfter = false): void {
        foreach ($hooks as $classHook) {
            if ($runAfter === $classHook->isRunAfter()) {
                $method = $classHook->getMethodName();
                $classHook->getClassName()::$method();
            }
        }
    }

    /**
     * @param AbstractHook[] $hooks
     * @param bool $runAfter
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function runMethodHooks(array $hooks, bool $runAfter = false): void {
        foreach ($hooks as $hook) {
            if ($runAfter === $hook->isRunAfter()) {
                $hookService = null;
                $normalizedClassName = self::getIndex($hook->getClassName());
                if ($this->hooksServiceLocator->has($normalizedClassName)) {
                    $hookService = $this->hooksServiceLocator->get($normalizedClassName);
                } else if ($this->serviceLocator->has($normalizedClassName)) {
                    $hookService = $this->serviceLocator->get($normalizedClassName);
                } else {
                    throw new ServiceNotFoundException($normalizedClassName);
                }
                $method = $hook->getMethodName();
                $hookService->$method();
            }
        }
    }

    /**
     * @param MethodBenchmarkConfiguration $methodBenchmarkConfiguration
     * @return BenchmarkResult
     */
    private function runSequentialBenchmark(MethodBenchmarkConfiguration $methodBenchmarkConfiguration): BenchmarkResult {
        $benchmarkResult = new BenchmarkResult();
        $benchmarkResult->setIterationsNumber($methodBenchmarkConfiguration->getNumberOfIterations());
        $benchmarkResult->setRevolutionsNumber($methodBenchmarkConfiguration->getNumberOfRevolutions());
        for ($iteration = 0; $iteration < $methodBenchmarkConfiguration->getNumberOfIterations(); $iteration++) {
            $revolutionResults = $this->executeIteration($methodBenchmarkConfiguration);
            $totalRevTime = 0;
            $totalRevMemory = 0;
            foreach ($revolutionResults as $result) {
                $totalRevTime += $result['execution_time'];
                if (isset($result['memory_data'])) {
                    foreach ($result['memory_data'] as $memoryData) {
                        $totalRevMemory += $memoryData['memory'];
                    }
                    $totalRevMemory /= count($result['memory_data']);
                }
            }
            $totalRevTime /= count($revolutionResults);
            $totalRevMemory /= count($revolutionResults);
            $benchmarkResult->addAverageExecutionTimeResult(round($totalRevTime, 6));
            $benchmarkResult->addMemoryResult($this->convert($totalRevMemory));
        }
        $benchmarkResult->setMemoryPeakValue($this->convert(memory_get_peak_usage()));
        return $benchmarkResult;
    }

    /**
     * @param MethodBenchmarkConfiguration $methodBenchmarkConfiguration
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function executeIteration(MethodBenchmarkConfiguration $methodBenchmarkConfiguration): array {
        $revolutionResults = array();
        for ($revolution = 0; $revolution < $methodBenchmarkConfiguration->getNumberOfRevolutions(); $revolution++) {
            $reflector = $methodBenchmarkConfiguration->getReflector();
            $providerInfo = $methodBenchmarkConfiguration->getParamProvider();
            $serviceInstance = $this->serviceLocator->get(self::getIndex($reflector->class));
            $methodName = $reflector->name;
            if (null !== $providerInfo) {
                $providerInstance = $this->providersServiceLocator->get(self::getIndex($providerInfo->getClassName()));
            }
            $revolutionResults[$revolution]['initial_memory'] = memory_get_usage();
            $profiler = new MemoryProfiler();
            $startTime = microtime(true);
            if (isset($providerInstance)) {
                // a parameter provider has been defined
                $providerMethod = $providerInfo->getMethodName();
                $paramsGenerator = $providerInstance->$providerMethod();
                $generatedParams = array();

                foreach ($paramsGenerator as $params) {
                    $generatedParams[] = $params;
                }
                $generatedParams[] = [$profiler, 'start'];
                call_user_func_array(array($serviceInstance, $methodName), $generatedParams);
            } else {
                $params = array();
                $expectedParameters = $reflector->getParameters();
                for ($iterator = 0; $iterator < count($expectedParameters) - 1; $iterator++) {
                    $params[] = $expectedParameters[$iterator]->getDefaultValue();
                }
                $params[] = [$profiler, 'start'];
                call_user_func_array(array($serviceInstance, $methodName), $params);
            }
            $profiler->stop();
            $revolutionResults[$revolution]['memory_data'] = $profiler->getMemoryProfileArray();
            $endTime = microtime(true);
            $revolutionResults[$revolution]['execution_time'] = ($endTime - $startTime) * 1000;
        }

        return $revolutionResults;
    }
}