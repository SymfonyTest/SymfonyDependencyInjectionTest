<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DependableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        if ($container->hasParameter('parameter_from_non_dependable')) {
            $container->setParameter('dependable_parameter', 'dependable value');
        }
    }

    public function getAlias(): string
    {
        return 'dependable';
    }
}
