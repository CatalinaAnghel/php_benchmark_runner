<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\DTO\BenckmarkCollection;
use MepProject\PhpBenchmarkRunner\DTO\BenchmarkConfiguration;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\DependencyInjection\ServiceLocator;

class AnnotationMapper {
    /**
     * @var ServiceLocator $serviceLocator
     */
    private $serviceLocator;

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

    # service types
    CONST HOOKS_MANAGER_TAG = 'hooks';
    CONST METRICS_GENERATOR_TAG = 'metrics';

    # options
    CONST REVOLUTIONS_OPTION = 0;
    CONST ITERATIONS_OPTION = 1;
    CONST PARAMETER_PROVIDER_OPTION = 2;

    # hooks
    CONST BEFORE_METHOD_HOOK = 3;
    CONST AFTER_METHOD_HOOK = 4;
    CONST BENCHMARK_ORDER_HOOK = 5;
    CONST BEFORE_CLASS_HOOK = 6;
    CONST AFTER_CLASS_HOOK = 7;

    protected $patterns = [
        '#@Revolutions([1-9]+[0-9]*)#' => self::REVOLUTIONS_OPTION,
        '#@Iterations([1-9]+[0-9]*)#' => self::ITERATIONS_OPTION,
        '#@ParameterProvider(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => self::PARAMETER_PROVIDER_OPTION,
        '#@BeforeMethod(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => self::BEFORE_METHOD_HOOK,
        '#@AfterMethod(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => self::AFTER_METHOD_HOOK,
        '#@BeforeBenchmark(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => self::BEFORE_CLASS_HOOK,
        '#@AfterBenchmark(("[a-zA-Z0-9_]+", ?)?"[a-zA-Z0-9_]+")#' => self::AFTER_CLASS_HOOK,
        '#@Order([1-9]+[0-9]*)#' => self::BENCHMARK_ORDER_HOOK
    ];

    /**
     *
     * @param $locator
     */
    public function __construct(ServiceLocator $locator){
        $this->serviceLocator = $locator;
        $this->lexer = new Lexer();
        $this->parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
    }

    /**
     * Builds the bencjmarking recipe based on the annotations provided within the registered services.
     * @throws \ReflectionException
     */
    public function buildBenchmarkRecipe(){
        $providedServices = $this->serviceLocator->getProvidedServices();
        $benchmarkCollection = new BenckmarkCollection();

        if(is_array($providedServices) && count($providedServices)){
            // there are services registered for benchmarking
            foreach($providedServices as $providedService){
                try{
                    $reflection = new \ReflectionClass($providedService);
                    $classDocs = $reflection->getDocComment();
                    $benchmarkConfig = $this->parseAnnotations($classDocs);
//                    foreach ($reflection->getMethods() as $method){
////                        dump($method->getDocComment());
//                    }
                }catch (\ReflectionException $exception){
                    throw $exception;
                }
            }
        }
        return $benchmarkCollection;
    }

    private function parseAnnotations(string $annotations, BenchmarkConfiguration $benchmarkConfig = null){
        $benchmarkConfiguration = empty($benchmarkConfig)? new BenchmarkConfiguration(): $benchmarkConfig;

        $tokens = new TokenIterator($this->lexer->tokenize($annotations));
        $annotationsNode = $this->parser->parse($tokens);
        foreach ($annotationsNode->children as $childNode){
            
        }
        dd($annotationsNode->children);

        return $benchmarkConfiguration;
    }
}