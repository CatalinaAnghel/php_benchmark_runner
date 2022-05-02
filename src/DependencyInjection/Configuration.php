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
            ->scalarNode('hooks_locator')
            ->defaultNull()
            ->end()
            ->arrayNode('limit')
                ->children()
                    ->integerNode('revolutions')
                    ->defaultValue(50)
                    ->end()
                    ->integerNode('iterations')
                    ->defaultValue(50)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('parallel')
                ->children()
                    ->booleanNode('enabled')
                    ->defaultFalse()
                    ->end()
                    ->integerNode('threads_number')
                    ->defaultValue(100)
                    ->end()
            ->end()
        ->end();

        return $threeBuilder;
    }
}