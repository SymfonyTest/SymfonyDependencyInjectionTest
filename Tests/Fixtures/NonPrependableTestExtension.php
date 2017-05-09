<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class NonPrependableTestExtension implements ExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $container->setParameter('ignored_invocation', 'ignored value');
    }

    public function load(array $config, ContainerBuilder $container)
    {
    }

    public function getAlias()
    {
        return 'non_prependable_test';
    }

    public function getNamespace()
    {
    }

    public function getXsdValidationBasePath()
    {
    }
}
