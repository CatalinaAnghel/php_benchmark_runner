<?php

namespace MepProject\PhpBenchmarkRunner\DependencyInjection;

use MepProject\PhpBenchmarkRunner\Helper\OptionsMapper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class PhpBenchmarkRunnerExtension extends Extension {
    public function load(array $configs, ContainerBuilder $container){
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
//        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
//        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->findDefinition('php_benchmark_runner.options_mapper');
        $definition->setArgument(0, new Reference($config['locator']));
        $container->registerForAutoconfiguration(OptionsMapper::class);
//        $definition->setArgument(1, $config['unicorns_are_real']);
//        $definition->setArgument(2, $config['min_sunshine']);

//        $container->registerForAutoconfiguration(WordProviderInterface::class)
//            ->addTag('knpu_ipsum_word_provider');
    }
}