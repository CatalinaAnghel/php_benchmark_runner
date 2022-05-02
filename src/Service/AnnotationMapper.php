<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\Benchmark;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\ClassBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\ClassHook;
use MepProject\PhpBenchmarkRunner\DTO\MethodBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\MethodHook;
use MepProject\PhpBenchmarkRunner\DTO\ParamProvider;
use MepProject\PhpBenchmarkRunner\Exception\ExceptionMessages;
use MepProject\PhpBenchmarkRunner\Helper\Constants;
use MepProject\PhpBenchmarkRunner\Traits\SubscribedServiceTrait;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ServiceLocator;

class AnnotationMapper {
    public CONST PARAMETERS_REPLACE_PATTERN = '~(\(|\)| |")~';

    /**
     * @var Lexer $lexer
     */
    protected $lexer;

    /**
     * @var ConstExprParser $parser
     */
    protected $parser;

    /**
     * @var BenchmarkCollection $benchmarkCollection
     */
    protected BenchmarkCollection $benchmarkCollection;

    /**
     * @var ServiceLocator $serviceLocator
     */
    protected ServiceLocator $serviceLocator;

    /**
     * @var ServiceLocator|null $providersLocator
     */
    protected ?ServiceLocator $providersLocator;

    /**
     * @var BenchmarkValidator $validator
     */
    protected BenchmarkValidator $validator;

    /**
     * @var array $limitConfig
     */
    protected array $limitConfig;

    use SubscribedServiceTrait;

    /**
     *
     * @param BenchmarkValidator $validator
     * @param array $limitConfig
     * @param ServiceLocator|null $serviceLocator
     * @param ServiceLocator|null $providersLocator
     */
    public function __construct(BenchmarkValidator $validator, array $limitConfig, ?ServiceLocator $serviceLocator = null, ?ServiceLocator $providersLocator = null){
        $this->lexer = new Lexer();
        $this->parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $this->validator = $validator;
        $this->limitConfig = $limitConfig;
        $this->serviceLocator = $serviceLocator;
        $this->providersLocator = $providersLocator;
    }

    /**
     * @param string $annotations
     * @param string $className
     * @return AbstractBenchmarkConfiguration|ClassBenchmarkConfiguration
     */
    public function parseClassAnnotations(string $annotations, string $className){
        $tokens = new TokenIterator($this->lexer->tokenize($annotations));
        $annotationsNode = $this->parser->parse($tokens);
        $matches = array_filter(
            $annotationsNode->children,
            function ($element) {
                return isset($element->name) &&
                    $element->name === Constants::ANNOTATION_CHARACTER . Constants::BENCHMARK_ANNOTATION;
            }
        );
        if(is_array($matches) && count($matches)){
            $benchmarkConfiguration = new ClassBenchmarkConfiguration();

            foreach ($annotationsNode->children as $childNode){
                if(isset($childNode->name)){
                    // annotation
                    $annotation = str_replace(Constants::ANNOTATION_CHARACTER, '', $childNode->name);
                    $params = $this->parseParameters($childNode->value);
                    switch ($annotation){
                        case Constants::REVOLUTIONS_OPTION:
                            // Revolutions
                            $benchmarkConfiguration = $this->setRevolutions($benchmarkConfiguration, $params);
                            break;
                        case Constants::ITERATIONS_OPTION:
                            $benchmarkConfiguration = $this->setIterations($benchmarkConfiguration, $params);
                            break;
                        case Constants::BEFORE_METHOD_HOOK:
                        case Constants::AFTER_METHOD_HOOK:
                            throw new Exception(ExceptionMessages::HOOK_CONFIGURATION_EXCEPTION_MESSAGE);
                            break;
                        case Constants::BEFORE_CLASS_HOOK:
                            if(is_array($params) && 1 === count($params)){
                                $params[1] = $params[0];
                                $params[0] = $className;
                            }
                            $benchmarkConfiguration = $this->setClassHook($benchmarkConfiguration, $params, Constants::BEFORE_CLASS_HOOK);
                            break;
                        case Constants::AFTER_CLASS_HOOK:
                            if(is_array($params) && 1 === count($params)){
                                $params[1] = $params[0];
                                $params[0] = $className;
                            }
                            $benchmarkConfiguration = $this->setClassHook($benchmarkConfiguration, $params, Constants::AFTER_CLASS_HOOK);
                            break;
                        case Constants::PARAMETER_PROVIDER_OPTION:
                            throw new Exception('Parameter provider invalid configuration');
                        default:
                            // do nothing
                    }
                }
            }
        }
        return $benchmarkConfiguration?? false;
    }

    /**
     * @param $params
     * @return array
     */
    private function parseParameters($params):array{
        return explode(',',
            preg_replace(self::PARAMETERS_REPLACE_PATTERN, '', $params));
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param ClassBenchmarkConfiguration $classBenchmarkConfiguration
     * @return false|AbstractBenchmarkConfiguration|MethodBenchmarkConfiguration
     * @throws \ReflectionException
     */
    public function parseMethodAnnotations(\ReflectionMethod $reflectionMethod, ClassBenchmarkConfiguration $classBenchmarkConfiguration){
        $methodDocs = $reflectionMethod->getDocComment();
        if($methodDocs){
            $tokens = new TokenIterator($this->lexer->tokenize($methodDocs));
            $annotationsNode = $this->parser->parse($tokens);
            $matches = array_filter(
                $annotationsNode->children,
                function ($element) {
                    return isset($element->name) &&
                        $element->name === Constants::ANNOTATION_CHARACTER . Constants::BENCHMARK_METHOD_ANNOTATION;
                }
            );
            if(is_array($matches) && count($matches)){
                $benchmarkConfiguration  = new MethodBenchmarkConfiguration();
                $benchmarkConfiguration->setReflector($reflectionMethod);
                foreach ($annotationsNode->children as $childNode){
                    if(isset($childNode->name)){
                        // annotation
                        $annotation = str_replace(Constants::ANNOTATION_CHARACTER, '', $childNode->name);
                        $params = $this->parseParameters($childNode->value);
                        switch ($annotation){
                            case Constants::REVOLUTIONS_OPTION:
                                // Revolutions
                                $benchmarkConfiguration = $this->setRevolutions($benchmarkConfiguration, $params);
                                break;
                            case Constants::ITERATIONS_OPTION:
                                $benchmarkConfiguration = $this->setIterations($benchmarkConfiguration, $params);
                                break;
                            case Constants::BEFORE_METHOD_HOOK:
                                if(is_array($params) && 1 === count($params)){
                                    $params[1] = $params[0];
                                    $params[0] = $reflectionMethod->getDeclaringClass()->getName();

                                }
                                $benchmarkConfiguration = $this->setMethodHook($benchmarkConfiguration, $params, Constants::BEFORE_METHOD_HOOK);
                                break;
                            case Constants::AFTER_METHOD_HOOK:
                                if(is_array($params) && 1 === count($params)){
                                    $params[1] = $params[0];
                                    $params[0] = $reflectionMethod->getDeclaringClass()->getName();
                                }
                                $benchmarkConfiguration = $this->setMethodHook($benchmarkConfiguration, $params, Constants::AFTER_METHOD_HOOK);
                                break;
                            case Constants::BEFORE_CLASS_HOOK:
                            case Constants::AFTER_CLASS_HOOK:
                                throw new Exception(ExceptionMessages::HOOK_CONFIGURATION_EXCEPTION_MESSAGE);
                                break;
                            case Constants::PARAMETER_PROVIDER_OPTION:
                                $benchmarkConfiguration = $this->setProvider($benchmarkConfiguration, $params);
                                break;
                            default:
                                // do nothing
                        }

                    }
                }

                $iterationsMatches = array_filter(
                    $annotationsNode->children,
                    function ($element) {
                        return isset($element->name) &&
                            $element->name === Constants::ANNOTATION_CHARACTER . Constants::ITERATIONS_OPTION;
                    }
                );

                if(empty($iterationsMatches)){
                    // set the iterations based on the class configuration
                    $benchmarkConfiguration->setNumberOfIterations($classBenchmarkConfiguration->getNumberOfIterations());
                }

                $revolutionsMatches = array_filter(
                    $annotationsNode->children,
                    function ($element) {
                        return isset($element->name) &&
                            $element->name === Constants::ANNOTATION_CHARACTER . Constants::REVOLUTIONS_OPTION;
                    }
                );

                if(empty($revolutionsMatches)){
                    // set the iterations based on the class configuration
                    $benchmarkConfiguration->setNumberOfRevolutions($classBenchmarkConfiguration->getNumberOfRevolutions());
                }
            }
            //dump($benchmarkConfiguration);
        }

        return $benchmarkConfiguration ?? false;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     * @return AbstractBenchmarkConfiguration
     */
    protected function setRevolutions(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params): AbstractBenchmarkConfiguration{
        if($this->validator->validate($params, $this->limitConfig['revolutions'])){
            // the annotation is correct
            $benchmarkConfiguration->setNumberOfRevolutions((int) $params[0]);
        }else{
            throw new Exception('Invalid revolutions configuration');
        }

        return $benchmarkConfiguration;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     */
    protected function setIterations(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params):AbstractBenchmarkConfiguration{
        if($this->validator->validate($params, $this->limitConfig['iterations'])){
            // the annotation is correct
            $benchmarkConfiguration->setNumberOfIterations((int) $params[0]);
        }else{
            throw new Exception('Invalid iterations configuration');
        }

        return $benchmarkConfiguration;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     * @param string $type
     * @return AbstractBenchmarkConfiguration
     * @throws \ReflectionException
     */
    protected function setClassHook(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params, string $type): AbstractBenchmarkConfiguration{
        if($this->validator->validateHook($params, true)){
            $hook = new ClassHook();
            $hook->setClassName($params[0]);
            $hook->setMethodName($params[1]);
            $hook->setRunAfter($type === Constants::AFTER_CLASS_HOOK);
            $benchmarkConfiguration->addHook($hook);
        }else{
            throw new Exception('Invalid class hook configuration');
        }
        return $benchmarkConfiguration;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     * @param string $type
     * @return AbstractBenchmarkConfiguration
     * @throws \ReflectionException
     */
    protected function setMethodHook(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params, string $type): AbstractBenchmarkConfiguration{
        if($this->validator->validateHook($params)){
            $hook = new MethodHook();
            $hook->setClassName($params[0]);
            $hook->setMethodName($params[1]);
            $hook->setRunAfter($type === Constants::AFTER_METHOD_HOOK);
            $benchmarkConfiguration->addHook($hook);
        }else{
            throw new Exception('Invalid method hook configuration');
        }
        return $benchmarkConfiguration;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     * @return AbstractBenchmarkConfiguration
     */
    public function setProvider(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params): AbstractBenchmarkConfiguration{
        if(null !== $this->providersLocator &&
            isset($params[0], $params[1], $this->providersLocator->getProvidedServices()[self::getIndex($params[0])]) &&
            method_exists($this->providersLocator->getProvidedServices()[self::getIndex($params[0])], $params[1])
        ){
            $methodName = $params[1];
            $generator = ($this->providersLocator->get(self::getIndex($params[0])))->$methodName();
            if($this->validator->validateProvider($benchmarkConfiguration->getReflector(), $generator)){
                // the class and the method exist and the class is registered as a provider service
                $paramProvider = new ParamProvider();
                $paramProvider->setClassName($params[0]);
                $paramProvider->setMethodName($params[1]);
                $benchmarkConfiguration->setParamProvider($paramProvider);
            }else{
                throw new Exception("Invalid provider");
            }
        }
        return $benchmarkConfiguration;
    }

    /**
     * Builds the benchmarking recipe based on the annotations provided within the registered services.
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
                    $classBenchmarkConfiguration = $this->parseClassAnnotations($classDocs, $reflection->getName());
                    if ($classBenchmarkConfiguration) {
                        $classBenchmarkConfiguration->setReflector($reflection);
                        $benchmark->setClassBenchmarkConfiguration($classBenchmarkConfiguration);
                        foreach ($reflection->getMethods() as $method) {
                            $methodBenchmarkConfiguration = $this->parseMethodAnnotations($method, $classBenchmarkConfiguration);
                            if ($methodBenchmarkConfiguration) {
                                $benchmark->addMethodBenchmarkConfiguration($methodBenchmarkConfiguration);
                            }
                        }
                        if (is_array($benchmark->getMethodBenchmarkConfigurations()) && count($benchmark->getMethodBenchmarkConfigurations())) {
                            $benchmarkCollection->addBenchmark($benchmark);
                        }
                    }
                }
            }
        }
        return $benchmarkCollection;
    }
}