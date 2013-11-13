<?php

namespace Matthias\SymfonyDependencyInjectionTest\Loader;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface LoaderFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param $source
     * @return LoaderInterface
     */
    public function createLoaderForSource(ContainerBuilder $container, $source);
}
