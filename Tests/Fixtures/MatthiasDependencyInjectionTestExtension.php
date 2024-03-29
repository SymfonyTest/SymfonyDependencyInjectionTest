<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class MatthiasDependencyInjectionTestExtension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        // load some service definitions
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.xml');

        // set a parameter manually
        $container->setParameter('manual_parameter', 'parameter value');
        $container->setParameter('manual_number_parameter', 123123);
        $container->setParameter('manual_array_parameter', ['key1' => 'value1', 'key2' => 'value2']);

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

        // add a reference to an existing service
        $definition = new Definition('manual_with_reference');
        $definition->addArgument(new Reference('manual_service_id'));
        $container->setDefinition('manual_with_reference', $definition);
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
