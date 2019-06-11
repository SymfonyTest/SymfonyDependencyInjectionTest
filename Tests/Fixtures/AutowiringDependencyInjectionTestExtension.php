<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AutowiringDependencyInjectionTestExtension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        // load some service definitions
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.yaml');
    }

    public function getAlias()
    {
        return 'autowiring_dependency_injection_test';
    }

    public function getNamespace(): void
    {
    }

    public function getXsdValidationBasePath(): void
    {
    }
}
