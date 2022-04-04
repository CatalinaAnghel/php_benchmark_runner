<?php

namespace MepProject\PhpBenchmarkRunner\Service;

use MepProject\PhpBenchmarkRunner\Service\AnnotationMapper;
use MepProject\PhpBenchmarkRunner\Service\Abstractions\IPhpBenchmarkRunner;

class PhpBenchmarkRunner implements IPhpBenchmarkRunner{
    protected $annotationMapper;

    public function __construct(AnnotationMapper $annotationMapper){
        $this->annotationMapper = $annotationMapper;
    }

    public function buildBenchmark():void{
        $this->annotationMapper->buildBenchmarkRecipe();
    }
}