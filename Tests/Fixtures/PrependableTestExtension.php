<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class PrependableTestExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->setParameter('prepend_parameter_set', 'prepended value');
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function getAlias(): string
    {
        return 'prependable_test';
    }
}
