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

/**
 * PhpBenchmarkRunnerExtension class
 */
class PhpBenchmarkRunnerExtension extends Extension {
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container){
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        // set the arguments for the benchmark runner
        $definition = $container->findDefinition('php_benchmark_runner.runner');
        $definition->setArgument(0, new Reference(AnnotationMapper::class));

        // inject the service locator used for the analysed classes
        if(isset($config['locator'])){
            $definition->setArgument(1, new Reference($config['locator']));
        }else{
            $definition->setArgument(1, null);
        }

        // inject the service locator used for the provider classes
        if(isset($config['providers_locator'])){
            $providersLocatorReference = new Reference($config['providers_locator']);
            $definition->setArgument(2, $providersLocatorReference);
        }else{
            $definition->setArgument(2, null);
        }

        // prepare to inject the service locator used for the hooks classes
        if(isset($config['hooks_locator'])){
            $hooksLocatorReference = new Reference($config['hooks_locator']);
            $definition->setArgument(3, $hooksLocatorReference);
        }

        $container->registerForAutoconfiguration(PhpBenchmarkRunner::class);

        // set the arguments for the annotation mapper
        $annotationMapperDefinition = $container->findDefinition('php_benchmark_runner.annotation_mapper');
        if(isset($providersLocatorReference)){
            $annotationMapperDefinition->setArgument(0, $providersLocatorReference);
        }
        $container->registerForAutoconfiguration(AnnotationMapper::class);
    }
}