<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class PrependableTestExtension implements ExtensionInterface, PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $container->setParameter('prepend_parameter_set', 'prepended value');
    }

    public function load(array $config, ContainerBuilder $container)
    {
    }

    public function getAlias()
    {
        return 'prependable_test';
    }

    public function getNamespace()
    {
    }

    public function getXsdValidationBasePath()
    {
    }
}
