<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\BenckmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\ClassBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\ClassHook;
use MepProject\PhpBenchmarkRunner\DTO\MethodBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\MethodHook;
use MepProject\PhpBenchmarkRunner\DTO\ParamProvider;
use MepProject\PhpBenchmarkRunner\Exception\ExceptionMessages;
use MepProject\PhpBenchmarkRunner\Helper\Constants;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ServiceLocator;

class AnnotationMapper {
    CONST PARAMETERS_REPLACE_PATTERN = '~(\(|\)| |")~';

    /**
     * @var Lexer $lexer
     */
    private $lexer;

    /**
     * @var ConstExprParser $parser
     */
    private $parser;

    /**
     * @var BenckmarkCollection $benchmarkCollection
     */
    private $benchmarkCollection;

    /**
     * @var ServiceLocator $providersLocator
     */
    private $providersLocator;

    protected $patterns = [
        '#@Revolutions([1-9]+[0-9]*)#' => Constants::REVOLUTIONS_OPTION,
        '#@Iterations([1-9]+[0-9]*)#' => Constants::ITERATIONS_OPTION,
        '#@ParameterProvider(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => Constants::PARAMETER_PROVIDER_OPTION,
        '#@BeforeMethod(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => Constants::BEFORE_METHOD_HOOK,
        '#@AfterMethod(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => Constants::AFTER_METHOD_HOOK,
        '#@BeforeBenchmark(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => Constants::BEFORE_CLASS_HOOK,
        '#@AfterBenchmark(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => Constants::AFTER_CLASS_HOOK
    ];

    /**
     *
     * @param $locator
     */
    public function __construct(ServiceLocator $providersLocator = null){
        $this->lexer = new Lexer();
        $this->parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $this->providersLocator = $providersLocator;
    }

    /**
     * @param string $annotations
     * @param string $className
     * @return AbstractBenchmarkConfiguration|ClassBenchmarkConfiguration
     */
    public function parseClassAnnotations(string $annotations, string $className){
        $benchmarkConfiguration = new ClassBenchmarkConfiguration();

        $tokens = new TokenIterator($this->lexer->tokenize($annotations));

        try{
            $annotationsNode = $this->parser->parse($tokens);
            foreach ($annotationsNode->children as $childNode){
                if(isset($childNode->name)){
                    // annotation
                    $annotation = str_replace(Constants::ANNOTATION_CHARACTER, '', $childNode->name);
                    $params = $this->parseParameters($childNode->value);
                    switch ($annotation){
                        case Constants::REVOLUTIONS_OPTION:
                            // Revolutions
                            $this->setRevolutions($benchmarkConfiguration, $params);
                            break;
                        case Constants::ITERATIONS_OPTION:
                            $this->setIterations($benchmarkConfiguration, $params);
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
        }catch(ParserException $exception){
            dump($exception->getMessage());
        }
        return $benchmarkConfiguration;
    }

    /**
     * @param $params
     * @return array
     */
    private function parseParameters($params):array{
        $parsedParameters = explode(',',
            preg_replace(self::PARAMETERS_REPLACE_PATTERN, '', $params));
        return $parsedParameters;
    }

    /**
     * @param $params
     * @return bool
     */
    private function validate($params):bool{
        return (is_array($params) && 1 === count($params) && isset($params[0]) && is_numeric($params[0]));
    }

    /**
     * @param $params
     * @return bool
     */
    private function validateHook($params, $checkStatic = false):bool{
        return (is_array($params) && (2 === count($params) && method_exists($params[0], $params[1]) &&
                (($this->checkStaticMethod($params[0], $params[1]) && $checkStatic) || !$checkStatic)));
    }

    /**
     * @param $className
     * @param $methodName
     * @return mixed
     */
    private function checkStaticMethod($className, $methodName){
        $reflectionMethod = new \ReflectionMethod($className, $methodName);

        return $reflectionMethod->isStatic();
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @return false|AbstractBenchmarkConfiguration|MethodBenchmarkConfiguration
     */
    public function parseMethodAnnotations(\ReflectionMethod $reflectionMethod){
        $methodDocs = $reflectionMethod->getDocComment();
        if($methodDocs){
            $benchmarkConfiguration  = new MethodBenchmarkConfiguration();
            $benchmarkConfiguration->setReflector($reflectionMethod);
            $tokens = new TokenIterator($this->lexer->tokenize($methodDocs));

            try {
                $annotationsNode = $this->parser->parse($tokens);
                foreach ($annotationsNode->children as $childNode){
                    if(isset($childNode->name)){
                        // annotation
                        $annotation = str_replace(Constants::ANNOTATION_CHARACTER, '', $childNode->name);
                        $params = $this->parseParameters($childNode->value);
                        switch ($annotation){
                            case Constants::REVOLUTIONS_OPTION:
                                // Revolutions
                                $this->setRevolutions($benchmarkConfiguration, $params);
                                break;
                            case Constants::ITERATIONS_OPTION:
                                $this->setIterations($benchmarkConfiguration, $params);
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
            }catch (ParserException $exception){
                dd($exception->getMessage());
            }
            //dump($benchmarkConfiguration);
        }

        return isset($benchmarkConfiguration)?$benchmarkConfiguration:false;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     */
    protected function setRevolutions(AbstractBenchmarkConfiguration &$benchmarkConfiguration, array $params): void{
        if($this->validate($params)){
            // the annotation is correct
            $benchmarkConfiguration->setNumberOfRevolutions((int) $params[0]);
        }else{
            // TODO: throw an exception
        }
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     */
    protected function setIterations(AbstractBenchmarkConfiguration  &$benchmarkConfiguration, array $params):void{
        if($this->validate($params)){
            // the annotation is correct
            $benchmarkConfiguration->setNumberOfIterations((int) $params[0]);
        }else{
            // TODO: throw an exception
        }
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     * @param string $type
     * @return AbstractBenchmarkConfiguration
     */
    protected function setClassHook(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params, string $type): AbstractBenchmarkConfiguration{
        if($this->validateHook($params, true)){
            $hook = new ClassHook();
            $hook->setClassName($params[0]);
            $hook->setMethodName($params[1]);
            $hook->setRunAfter($type === Constants::AFTER_CLASS_HOOK);
            $benchmarkConfiguration->addHook($hook);
        }else{
            // TODO: throw a custom exception
        }
        return $benchmarkConfiguration;
    }

    /**
     * @param AbstractBenchmarkConfiguration $benchmarkConfiguration
     * @param array $params
     * @param string $type
     * @return AbstractBenchmarkConfiguration
     */
    protected function setMethodHook(AbstractBenchmarkConfiguration $benchmarkConfiguration, array $params, string $type): AbstractBenchmarkConfiguration{
        if($this->validateHook($params)){
            $hook = new MethodHook();
            $hook->setClassName($params[0]);
            $hook->setMethodName($params[1]);
            $hook->setRunAfter($type === Constants::AFTER_METHOD_HOOK);
            $benchmarkConfiguration->addHook($hook);
        }else{
            // TODO: throw a custom exception
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
            isset($params[0], $params[1], $this->providersLocator->getProvidedServices()[strtolower($params[0])]) &&
            method_exists($this->providersLocator->getProvidedServices()[strtolower($params[0])], $params[1])){
            // the class and the method exist and the class is registered as a provider service
            $paramProvider = new ParamProvider();
            $paramProvider->setClassName($params[0]);
            $paramProvider->setMethodName($params[1]);
            $benchmarkConfiguration->setParamProvider($paramProvider);
        }
        return $benchmarkConfiguration;
    }
}