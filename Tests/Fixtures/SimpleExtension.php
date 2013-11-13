<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SimpleExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
    }

    public function getAlias()
    {
        return 'simple';
    }
}
