<?php

namespace Matthias\SymfonyDependencyInjectionTest\Loader;

use Matthias\SymfonyDependencyInjectionTest\Loader\Exception\UnknownConfigurationSourceException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class LoaderFactory implements LoaderFactoryInterface
{
    public function createLoaderForSource(ContainerBuilder $container, $source): LoaderInterface
    {
        if ($source instanceof \Closure) {
            return new ClosureLoader($container);
        }

        if (!is_string($source)) {
            throw new \InvalidArgumentException('Configuration source should be either a closure or a string');
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'yml':
            case 'yaml':
                return $this->createYamlFileLoader($container);
            case 'xml':
                return $this->createXmlFileLoader($container);
            case 'php':
                return $this->createPhpFileLoader($container);
        }

        throw new UnknownConfigurationSourceException(sprintf(
            'Could not create a loader for configuration source "%s"',
            $source
        ));
    }

    public function createYamlFileLoader($container): YamlFileLoader
    {
        return new YamlFileLoader($container, new FileLocator());
    }

    public function createXmlFileLoader(ContainerBuilder $container): XmlFileLoader
    {
        return new XmlFileLoader($container, new FileLocator());
    }

    public function createPhpFileLoader(ContainerBuilder $container): PhpFileLoader
    {
        return new PhpFileLoader($container, new FileLocator());
    }

    public function createIniFileLoader(ContainerBuilder $container): IniFileLoader
    {
        return new IniFileLoader($container, new FileLocator());
    }
}
