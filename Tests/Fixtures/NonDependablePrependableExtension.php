<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class NonDependablePrependableExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function getAlias(): string
    {
        return 'non_dependable';
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->setParameter('parameter_from_non_dependable', 'non-dependable value');
    }
}
