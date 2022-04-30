<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;
use MepProject\PhpBenchmarkRunner\DTO\Benchmark;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\MethodBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\Service\Abstractions\IPhpBenchmarkRunner;
use MepProject\PhpBenchmarkRunner\Traits\SubscribedServiceTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PhpBenchmarkRunner implements IPhpBenchmarkRunner{
    /**
     * @var AnnotationMapper
     */
    protected AnnotationMapper $annotationMapper;

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

    /**
     * @param AnnotationMapper $annotationMapper
     * @param ServiceLocator|null $serviceLocator
     * @param ServiceLocator|null $providersLocator
     * @param ServiceLocator|null $hooksLocator
     * @param array|null $parallelConfiguration
     */
    public function __construct(AnnotationMapper $annotationMapper, ServiceLocator $serviceLocator = null, ServiceLocator $providersLocator = null, ServiceLocator $hooksLocator = null, array $parallelConfiguration = null){
        $this->annotationMapper = $annotationMapper;
        $this->serviceLocator = $serviceLocator;
        $this->providersServiceLocator = $providersLocator;
        $this->hooksServiceLocator = $hooksLocator;
        $this->parallelConfiguration = $parallelConfiguration;
        $this->validateConfiguration();
    }

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
     */
    public function buildBenchmark(): void{
        if (null !== $this->serviceLocator) {
            $benchmarkCollection = $this->annotationMapper->buildBenchmarkRecipe();
            $results = $this->runBenchmark($benchmarkCollection);
        } else {
            throw new \Exception('The services cannot be instantiated: Invalid Service Locator configuration');
        }
    }

    /**
     * {@inheritDoc}
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection): array{
        if (is_array($benchmarkCollection->getBenchmarks()) && count($benchmarkCollection->getBenchmarks())) {
            foreach ($benchmarkCollection->getBenchmarks() as $benchmark) {
                // run the before class hooks
                $this->runClassHooks($benchmark->getClassBenchmarkConfiguration()->getHooks());

                // method benchmark configuration
                foreach ($benchmark->getMethodBenchmarkConfigurations() as $methodBenchmarkConfiguration){
                    try {
                        $this->runMethodHooks($methodBenchmarkConfiguration->getHooks());
                    } catch (NotFoundExceptionInterface $e) {
                    } catch (ContainerExceptionInterface $e) {
                    }

                    if(isset($this->parallelConfiguration['enabled']) && $this->parallelConfiguration['enabled']){
                        //$this->runParallelBenchmark($methodBenchmarkConfiguration, $benchmark->getClassBenchmarkConfiguration());
                    }else{
                        $this->runSequentialBenchmark($methodBenchmarkConfiguration);
                    }

                    try {
                        $this->runMethodHooks($methodBenchmarkConfiguration->getHooks(), true);
                    } catch (NotFoundExceptionInterface $e) {
                    } catch (ContainerExceptionInterface $e) {
                    }
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
    protected function runClassHooks(array $hooks, bool $runAfter = false):void{
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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function runMethodHooks(array $hooks, bool $runAfter = false): void{
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
     * @return array
     */
    private function runSequentialBenchmark(MethodBenchmarkConfiguration $methodBenchmarkConfiguration): array{
        $iterationResults = array();

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

                $revolutionResults[$revolution]['execution_time'] = $endTime - $startTime;
                $revolutionResults[$revolution]['memory'] = memory_get_usage();
            }
            $totalRevTime = 0;
            $totalRevMemory = 0;
            foreach ($revolutionResults as $result){
                $totalRevTime += $result['execution_time'];
                $totalRevMemory += $result['memory'];
            }
            $totalRevTime /= count($revolutionResults);
            $totalRevMemory /= count($revolutionResults);
            $iterationResults[$iteration] = [
                'execution_time' => $totalRevTime,
                'memory' => $totalRevMemory,
                'max_memory' => memory_get_peak_usage()
            ];
        }
        return $iterationResults;
    }

}