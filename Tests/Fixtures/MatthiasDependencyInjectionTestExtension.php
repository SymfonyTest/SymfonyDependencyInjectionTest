<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasFactoryConstraint;

class MatthiasDependencyInjectionTestExtension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        // load some service definitions
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.xml');

        // load factory services definitions
        if (ContainerBuilderHasFactoryConstraint::isLegacySymfonyDI()) {
            $loader->load('services-factory-legacy.xml');
        } else {
            $loader->load('services-factory.xml');

            // Load old syntax for services in YML files
            $ymlLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
            $ymlLoader->load('services-factory-old-syntax.yml');
        }

        // set a parameter manually
        $container->setParameter('manual_parameter', 'parameter value');

        // manually add a service definition
        $definition = new Definition('stdClass');
        $definition->setArguments(['first', 'second']);
        $container->setDefinition('manual_service_id', $definition);

        // replace an argument of a previously defined service definition
        $container
            ->getDefinition('manual_service_id')
            ->replaceArgument(1, 'argument value');

        // add an alias to an existing service
        $container->setAlias('manual_alias', 'service_id');

        // add an factory service
        $container->register('manual_factory_service', 'stdClass');

        if (ContainerBuilderHasFactoryConstraint::isLegacySymfonyDI()) {
            $container
                ->register('manual_created_by_factory_service', 'stdClass')
                ->setFactoryService(new Reference('manual_factory_service'))
                ->setFactoryMethod('factoryMethod');
            ;
        } else {
            $container
                ->register('manual_created_by_factory_service', 'stdClass')
                ->setFactory('manual_factory_service:factoryMethod')
                ;
        }
    }

    public function getAlias()
    {
        return 'matthias_dependency_injection_test';
    }

    public function getNamespace(): void
    {
    }

    public function getXsdValidationBasePath(): void
    {
    }
}
