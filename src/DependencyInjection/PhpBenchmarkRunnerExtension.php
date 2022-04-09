<?php

namespace MepProject\PhpBenchmarkRunner\DependencyInjection;

use MepProject\PhpBenchmarkRunner\Helper\OptionsMapper;
use MepProject\PhpBenchmarkRunner\Service\AnnotationMapper;
use MepProject\PhpBenchmarkRunner\Service\PhpBenchmarkRunner;
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

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->findDefinition('php_benchmark_runner.runner');
        $definition->setArgument(0, new Reference(AnnotationMapper::class));
        $definition->setArgument(1, new Reference($config['locator']));
        $container->registerForAutoconfiguration(PhpBenchmarkRunner::class);
    }
}