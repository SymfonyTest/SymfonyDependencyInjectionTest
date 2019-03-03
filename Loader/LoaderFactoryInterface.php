<?php

namespace Matthias\SymfonyDependencyInjectionTest\Loader;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface LoaderFactoryInterface
{
    public function createLoaderForSource(ContainerBuilder $container, $source): LoaderInterface;
}
