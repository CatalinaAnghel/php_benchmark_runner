<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Contracts\AbstractHook;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkResult;
use MepProject\PhpBenchmarkRunner\DTO\MethodBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\Service\Contracts\AnnotationMapperInterface;
use MepProject\PhpBenchmarkRunner\Service\Contracts\PhpBenchmarkRunnerInterface;
use MepProject\PhpBenchmarkRunner\Traits\MemoryConvertorTrait;
use MepProject\PhpBenchmarkRunner\Traits\SubscribedServiceTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Class PhpBenchmarkRunnerInterface.
 */
class PhpBenchmarkRunner implements PhpBenchmarkRunnerInterface{
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

    /**
     * @var array|null $parallelConfiguration
     */
    private ?array $parallelConfiguration;

    use SubscribedServiceTrait;

    use MemoryConvertorTrait;

    /**
     * @param AnnotationMapperInterface $annotationMapper
     * @param array $parallelConfiguration
     * @param ServiceLocator|null $serviceLocator
     * @param ServiceLocator|null $providersLocator
     * @param ServiceLocator|null $hooksLocator
     */
    public function __construct(AnnotationMapperInterface $annotationMapper, array $parallelConfiguration, ServiceLocator $serviceLocator = null, ServiceLocator $providersLocator = null, ServiceLocator $hooksLocator = null){
        $this->annotationMapper = $annotationMapper;
        $this->parallelConfiguration = $parallelConfiguration;
        $this->serviceLocator = $serviceLocator;
        $this->providersServiceLocator = $providersLocator;
        $this->hooksServiceLocator = $hooksLocator;

        $this->validateConfiguration();
    }

    /**
     * Validate the configuration
     */
    private function validateConfiguration(): void{
        if (null === $this->serviceLocator) {
            throw new Exception('The services cannot be instantiated: Invalid Service Locator configuration');
        }

        if(isset($this->parallelConfiguration['enabled']) && $this->parallelConfiguration['enabled'] && !extension_loaded('parallel')){
            throw new Exception('The parallel PHP extension is not installed.');
        }
    }

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws ContainerExceptionInterface
     */
    public function buildBenchmark(): void{
        if (null !== $this->serviceLocator) {
            $benchmarkCollection = $this->annotationMapper->buildBenchmarkRecipe();
            $results = $this->runBenchmark($benchmarkCollection);
        } else {
            throw new \RuntimeException('The services cannot be instantiated: Invalid Service Locator configuration');
        }
    }

    /**
     * {@inheritDoc}
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection): array{
        if (is_array($benchmarkCollection->getBenchmarks()) && count($benchmarkCollection->getBenchmarks())) {
            foreach ($benchmarkCollection->getBenchmarks() as $benchmark) {
                // run the before class hooks
                $this->runClassHooks($benchmark->getClassBenchmarkConfiguration()->getHooks());
                // method benchmark configuration
                foreach ($benchmark->getMethodBenchmarkConfigurations() as $methodBenchmarkConfiguration){
                    $this->runMethodHooks($methodBenchmarkConfiguration->getHooks());

                    if(isset($this->parallelConfiguration['enabled']) && $this->parallelConfiguration['enabled']){
                    }else{
                        $this->runSequentialBenchmark($methodBenchmarkConfiguration);
                    }

                    $this->runMethodHooks($methodBenchmarkConfiguration->getHooks(), true);
                }

                // run the after class hooks
                $this->runClassHooks($benchmark->getClassBenchmarkConfiguration()->getHooks(), true);
            }
        }

        return [];
    }

    /**
     * @param array $hooks
     * @param bool $runAfter
     */
    private function runClassHooks(array $hooks, bool $runAfter = false):void{
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
    private function runMethodHooks(array $hooks, bool $runAfter = false): void{
        foreach ($hooks as $hook){
            if($runAfter === $hook->isRunAfter()){
                $hookService = null;
                $normalizedClassName = self::getIndex($hook->getClassName());
                if($this->hooksServiceLocator->has($normalizedClassName)){
                    $hookService = $this->hooksServiceLocator->get($normalizedClassName);
                }elseif($this->serviceLocator->has($normalizedClassName)){
                    $hookService = $this->serviceLocator->get($normalizedClassName);
                }else{
                    throw new Exception('Service not defined');
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
    private function runSequentialBenchmark(MethodBenchmarkConfiguration $methodBenchmarkConfiguration): BenchmarkResult{
        $iterationResults = array();
        $benchmarkResult = new BenchmarkResult();
        $benchmarkResult->setIterationsNumber($methodBenchmarkConfiguration->getNumberOfIterations());
        $benchmarkResult->setRevolutionsNumber($methodBenchmarkConfiguration->getNumberOfRevolutions());
        for($iteration = 0; $iteration < $methodBenchmarkConfiguration->getNumberOfIterations(); $iteration++){
            $revolutionResults = array();
            for($revolution = 0; $revolution < $methodBenchmarkConfiguration->getNumberOfRevolutions(); $revolution++){
                $reflector = $methodBenchmarkConfiguration->getReflector();
                $providerInfo = $methodBenchmarkConfiguration->getParamProvider();
                $serviceInstance = $this->serviceLocator->get(self::getIndex($reflector->class));
                $methodName = $reflector->name;
                if(null !== $providerInfo){
                    $providerInstance = $this->providersServiceLocator->get(self::getIndex($providerInfo->getClassName()));
                }

                $startTime = microtime(true);
                if(isset($providerInstance)){
                    // a parameter provider has been defined
                    $providerMethod = $providerInfo->getMethodName();
                    $paramsGenerator = $providerInstance->$providerMethod();
                    $generatedParams = array();
                    foreach ($paramsGenerator as $params){
                        $generatedParams[] = $params;
                    }

                    call_user_func_array(array($serviceInstance, $methodName), $generatedParams);
                }else{
                    $serviceInstance->$methodName();
                }
                $endTime = microtime(true);
                $revolutionResults[$revolution]['memory'] = memory_get_usage();
                $revolutionResults[$revolution]['execution_time'] = $endTime - $startTime;

            }
            $totalRevTime = 0;
            $totalRevMemory = 0;
            foreach ($revolutionResults as $result){
                $totalRevTime += $result['execution_time'];
                $totalRevMemory += $result['memory'];
            }
            $totalRevTime /= count($revolutionResults);
            $totalRevMemory /= count($revolutionResults);
            $benchmarkResult->addAverageExecutionTimeResult(round($totalRevTime, 6));
            $benchmarkResult->addMemoryResult($this->convert($totalRevMemory));
        }

        $benchmarkResult->setMemoryPeakValue($this->convert(memory_get_peak_usage()));
        return $benchmarkResult;
    }

}