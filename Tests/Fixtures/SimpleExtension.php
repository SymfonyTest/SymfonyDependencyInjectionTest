<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class SimpleExtension implements ExtensionInterface
{
    public function load(array $config, ContainerBuilder $container)
    {
    }

    public function getAlias()
    {
        return 'simple';
    }

    public function getNamespace()
    {
    }

    public function getXsdValidationBasePath()
    {
    }
}
