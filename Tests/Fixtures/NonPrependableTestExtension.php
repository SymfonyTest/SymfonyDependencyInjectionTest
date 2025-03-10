<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class NonPrependableTestExtension extends Extension
{
    public function prepend(ContainerBuilder $container): void
    {
        $container->setParameter('ignored_invocation', 'ignored value');
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function getAlias(): string
    {
        return 'non_prependable_test';
    }
}
