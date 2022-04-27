<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use App\Service\BenchmarkHook\Method\Abstractions\AbstractMethodHook;
use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractHook;
use MepProject\PhpBenchmarkRunner\DTO\Benchmark;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;
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
    private ServiceLocator $providersServiceLocator;

    /**
     * @var ServiceLocator|null $hooksServiceLocator
     */
    private ServiceLocator $hooksServiceLocator;

    use SubscribedServiceTrait;

    /**
     * @param AnnotationMapper $annotationMapper
     * @param ServiceLocator|null $serviceLocator
     * @param ServiceLocator|null $providersLocator
     * @param ServiceLocator|null $hooksLocator
     */
    public function __construct(AnnotationMapper $annotationMapper, ServiceLocator $serviceLocator = null, ServiceLocator $providersLocator = null, ServiceLocator $hooksLocator = null){
        $this->annotationMapper = $annotationMapper;
        $this->serviceLocator = $serviceLocator;
        $this->providersServiceLocator = $providersLocator;
        $this->hooksServiceLocator = $hooksLocator;
        $this->validateConfiguration();
    }

    private function validateConfiguration(): void{
        if (null === $this->serviceLocator) {
            throw new Exception('The services cannot be instantiated: Invalid Service Locator configuration');
        }
    }

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     */
    public function buildBenchmark(): void{
        if (null !== $this->serviceLocator) {
            $benchmarkCollection = $this->buildBenchmarkRecipe();
            $results = $this->runBenchmark($benchmarkCollection);
        } else {
            throw new \Exception('The services cannot be instantiated: Invalid Service Locator configuration');
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return BenchmarkCollection
     * @throws \ReflectionException
     */
    public function buildBenchmarkRecipe(): BenchmarkCollection{
        $providedServices = $this->serviceLocator->getProvidedServices();
        $benchmarkCollection = new BenchmarkCollection();

        if (is_array($providedServices) && count($providedServices)) {
            // there are services registered for benchmarking
            foreach ($providedServices as $providedService) {
                $reflection = new \ReflectionClass($providedService);
                $classDocs = $reflection->getDocComment();
                if ($classDocs) {
                    $benchmark = new Benchmark();
                    $classBenchmarkConfiguration = $this->annotationMapper->parseClassAnnotations($classDocs, $reflection->getName());
                    if ($classBenchmarkConfiguration) {
                        $classBenchmarkConfiguration->setReflector($reflection);
                        $benchmark->setClassBenchmarkConfiguration($classBenchmarkConfiguration);
                        foreach ($reflection->getMethods() as $method) {
                            $methodBenchmarkConfiguration = $this->annotationMapper->parseMethodAnnotations($method, $reflection->getName());

                            if ($methodBenchmarkConfiguration) {
                                $benchmark->addMethodBenchmarkConfiguration($methodBenchmarkConfiguration);
                            }
                        }
                        if (count($benchmark->getMethodBenchmarkConfigurations())) {
                            $benchmarkCollection->addBenchmark($benchmark);
                        }
                    }
                }
            }
        }
        return $benchmarkCollection;
    }

    /**
     * {@inheritDoc}
     * @param BenchmarkCollection $benchmarkCollection
     * @return array
     */
    public function runBenchmark(BenchmarkCollection $benchmarkCollection): array{
        if (count($benchmarkCollection->getBenchmarks())) {
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
}