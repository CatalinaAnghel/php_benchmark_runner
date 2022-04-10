<?php

namespace MepProject\PhpBenchmarkRunner\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder():TreeBuilder{
        $threeBuilder = new TreeBuilder('php_benchmark_runner');
        $rootNode = $threeBuilder->getRootNode();
        $rootNode->children()
            ->scalarNode('locator')
            ->defaultNull()
            ->end()
            ->scalarNode('providers_locator')
            ->defaultNull()
            ->end()
        ->end();

        return $threeBuilder;
    }
}