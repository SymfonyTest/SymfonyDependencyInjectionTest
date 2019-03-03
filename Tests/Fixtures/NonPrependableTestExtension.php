<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class NonPrependableTestExtension implements ExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->setParameter('ignored_invocation', 'ignored value');
    }

    public function load(array $config, ContainerBuilder $container): void
    {
    }

    public function getAlias()
    {
        return 'non_prependable_test';
    }

    public function getNamespace(): void
    {
    }

    public function getXsdValidationBasePath(): void
    {
    }
}
