<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SimpleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function getAlias(): string
    {
        return 'simple';
    }
}
