<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\Abstractions\AbstractBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\BenckmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\ClassBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\ClassHook;
use MepProject\PhpBenchmarkRunner\DTO\MethodBenchmarkConfiguration;
use MepProject\PhpBenchmarkRunner\DTO\MethodHook;
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
    public function __construct(){
        $this->lexer = new Lexer();
        $this->parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
    }

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
                            if(is_array($params) && count($params) === 1){
                                $params[1] = $params[0];
                                $params[0] = $className;
                            }
                            $benchmarkConfiguration = $this->setClassHook($benchmarkConfiguration, $params, Constants::BEFORE_CLASS_HOOK);
                            break;
                        case Constants::AFTER_CLASS_HOOK:
                            if(is_array($params) && count($params) === 1){
                                $params[1] = $params[0];
                                $params[0] = $className;
                            }
                            $benchmarkConfiguration = $this->setClassHook($benchmarkConfiguration, $params, Constants::AFTER_CLASS_HOOK);
                            break;
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

    private function parseParameters($params):array{
        $parsedParameters = explode(',',
            preg_replace(self::PARAMETERS_REPLACE_PATTERN, '', $params));
        return $parsedParameters;
    }

    private function validate($params):bool{
        return (is_array($params) && count($params) === 1 && isset($params[0]) && is_numeric($params[0]));
    }

    private function validateHook($params):bool{
        return (is_array($params) && (count($params) === 2 && method_exists($params[0], $params[1]) &&
                    $this->checkStaticMethod($params[0], $params[1])));
    }

    private function checkStaticMethod($className, $methodName){
        $reflectionMethod = new ReflectionMethod($className, $methodName);

        return $reflectionMethod->isStatic();
    }

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
                                if(is_array($params) && count($params) === 1){
                                    $params[1] = $params[0];
                                    $params[0] = $reflectionMethod->getDeclaringClass()->getName();

                                }
                                $benchmarkConfiguration = $this->setMethodHook($benchmarkConfiguration, $params, Constants::BEFORE_METHOD_HOOK);
                            break;
                            case Constants::AFTER_METHOD_HOOK:
                                if(is_array($params) && count($params) === 1){
                                    $params[1] = $params[0];
                                    $params[0] = $reflectionMethod->getDeclaringClass()->getName();
                                }
                                $benchmarkConfiguration = $this->setMethodHook($benchmarkConfiguration, $params, Constants::AFTER_METHOD_HOOK);
                                break;
                            case Constants::BEFORE_CLASS_HOOK:
                            case Constants::AFTER_CLASS_HOOK:
                                throw new Exception(ExceptionMessages::HOOK_CONFIGURATION_EXCEPTION_MESSAGE);
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

    protected function setRevolutions(AbstractBenchmarkConfiguration &$benchmarkConfiguration, array $params): void{
        if($this->validate($params)){
            // the annotation is correct
            $benchmarkConfiguration->setNumberOfRevolutions((int) $params[0]);
        }else{
            // TODO: throw an exception
        }
    }

    protected function setIterations(AbstractBenchmarkConfiguration  &$benchmarkConfiguration, array $params):void{
        if($this->validate($params)){
            // the annotation is correct
            $benchmarkConfiguration->setNumberOfIterations((int) $params[0]);
        }else{
            // TODO: throw an exception
        }
    }

    protected function setClassHook(
        AbstractBenchmarkConfiguration $benchmarkConfiguration,
        array $params,
        string $type): AbstractBenchmarkConfiguration{
        if($this->validateHook($params)){
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

    protected function setMethodHook(
        AbstractBenchmarkConfiguration $benchmarkConfiguration,
        array $params,
        string $type): AbstractBenchmarkConfiguration{
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
}