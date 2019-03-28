<?php

namespace Matthias\SymfonyDependencyInjectionTest\Loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class ExtensionConfigurationBuilder
{
    private $extension;
    private $sources = [];
    private $loaderFactory;

    public function __construct(LoaderFactoryInterface $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }

    public function setExtension(ExtensionInterface $extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function getExtension(): ExtensionInterface
    {
        if (!($this->extension instanceof ExtensionInterface)) {
            throw new \LogicException('You need to call setExtension() first');
        }

        return $this->extension;
    }

    public function addSource($source)
    {
        $this->sources[] = $source;

        return $this;
    }

    public function setSources(array $sources)
    {
        $this->sources = $sources;

        return $this;
    }

    public function getSources(): array
    {
        if (count($this->sources) === 0) {
            throw new \LogicException('You need to call setSources() or addSource() first');
        }

        return $this->sources;
    }

    public function getConfiguration(): array
    {
        $container = new ContainerBuilder();

        $container->registerExtension($this->getExtension());

        $this->loadSources($container, $this->getSources());

        return $this->getExtensionConfiguration($container);
    }

    private function loadSources(ContainerBuilder $container, array $sources): void
    {
        foreach ($sources as $source) {
            $loader = $this->loaderFactory->createLoaderForSource($container, $source);
            $loader->load($source);
        }
    }

    private function getExtensionConfiguration(ContainerBuilder $container): array
    {
        $extensionAlias = $this->getExtension()->getAlias();

        $extensionConfiguration = $container->getExtensionConfig($extensionAlias);

        return $extensionConfiguration;
    }
}
