<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Benchmark;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;
use MepProject\PhpBenchmarkRunner\Service\AnnotationMapper;
use MepProject\PhpBenchmarkRunner\Service\Abstractions\IPhpBenchmarkRunner;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PhpBenchmarkRunner implements IPhpBenchmarkRunner{
    /**
     * @var \MepProject\PhpBenchmarkRunner\Service\AnnotationMapper
     */
    protected $annotationMapper;
    
    /**
     * @var ServiceLocator $serviceLocator
     */
    private $serviceLocator;

    /**
     * @var ServiceLocator $providersServiceLocator
     */
    private $providersServiceLocator;

    public function __construct(AnnotationMapper $annotationMapper, ServiceLocator $serviceLocator = null, ServiceLocator $providersLocator = null){
        $this->annotationMapper = $annotationMapper;
        $this->serviceLocator = $serviceLocator;
        $this->providersServiceLocator = $providersLocator;
    }

    /**
     * @throws \ReflectionException
     */
    public function buildBenchmark():void{
        if(null !== $this->serviceLocator){
            $this->buildBenchmarkRecipe();
        }else{
            throw new \Exception('The services cannot be instantiated: Invalid Service Locator configuration');
        }

    }

    /**
     * Builds the bencjmarking recipe based on the annotations provided within the registered services.
     * @throws \ReflectionException
     * @return BenchmarkCollection
     */
    public function buildBenchmarkRecipe(): BenchmarkCollection{
        $providedServices = $this->serviceLocator->getProvidedServices();
        $benchmarkCollection = new BenchmarkCollection();

        if(is_array($providedServices) && count($providedServices)){
            // there are services registered for benchmarking
            foreach($providedServices as $providedService){
                try{
                    $reflection = new \ReflectionClass($providedService);
                    $classDocs = $reflection->getDocComment();
                    if($classDocs){
                        $benchmark = new Benchmark();
                        $classBenchmarkConfiguration = $this->annotationMapper->parseClassAnnotations($classDocs, $reflection->getName());
                        $classBenchmarkConfiguration->setReflector($reflection);
                        $benchmark->setClassBenchmarkConfiguration($classBenchmarkConfiguration);
                        foreach ($reflection->getMethods() as $method){
                            $methodBenchmarkConfiguration = $this->annotationMapper->parseMethodAnnotations($method, $reflection->getName());

                            if($methodBenchmarkConfiguration){
                                $benchmark->addMethodBenchmarkConfiguration($methodBenchmarkConfiguration);
                            }
                        }
                        if(count($benchmark->getMethodBenchmarkConfigurations())){
                            $benchmarkCollection->addBenchmark($benchmark);
                        }
                    }
                }catch (\ReflectionException $exception){
                    throw $exception;
                }
            }
        }
        return $benchmarkCollection;
    }
}