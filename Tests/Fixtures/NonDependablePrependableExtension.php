<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class NonDependablePrependableExtension implements ExtensionInterface, PrependExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
    }

    public function getAlias()
    {
        return 'non_dependable';
    }

    public function getNamespace(): void
    {
    }

    public function getXsdValidationBasePath(): void
    {
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->setParameter('parameter_from_non_dependable', 'non-dependable value');
    }
}
