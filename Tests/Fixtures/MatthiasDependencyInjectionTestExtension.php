<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class MatthiasDependencyInjectionTestExtension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container)
    {
        // load some service definitions
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.xml');

        // set a parameter manually
        $container->setParameter('manual_parameter', 'parameter value');

        // manually add a service definition
        $definition = new Definition('stdClass');
        $definition->setArguments(array('first', 'second'));
        $container->setDefinition('manual_service_id', $definition);

        // replace an argument of a previously defined service definition
        $container
            ->getDefinition('manual_service_id')
            ->replaceArgument(1, 'argument value');

        // add an alias to an existing service
        $container->setAlias('manual_alias', 'service_id');
    }

    public function getAlias()
    {
        return 'matthias_dependency_injection_test';
    }

    public function getNamespace()
    {
    }

    public function getXsdValidationBasePath()
    {
    }
}
