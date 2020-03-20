<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class DependableExtension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container): void
    {
        if ($container->hasParameter('parameter_from_non_dependable')) {
            $container->setParameter('dependable_parameter', 'dependable value');
        }
    }

    public function getAlias()
    {
        return 'dependable';
    }

    public function getNamespace(): void
    {
    }

    public function getXsdValidationBasePath(): void
    {
    }
}
