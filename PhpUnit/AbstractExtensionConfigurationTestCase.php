<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Matthias\SymfonyConfigTest\PhpUnit\ProcessedConfigurationEqualsConstraint;
use Matthias\SymfonyDependencyInjectionTest\Loader\ExtensionConfigurationBuilder;
use Matthias\SymfonyDependencyInjectionTest\Loader\LoaderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

abstract class AbstractExtensionConfigurationTestCase extends TestCase
{
    /**
     * Return an instance of the container extension that you are testing.
     */
    abstract protected function getContainerExtension(): ExtensionInterface;

    /**
     * Return an instance of the configuration class that you are testing.
     */
    abstract protected function getConfiguration(): ConfigurationInterface;

    final protected function assertProcessedConfigurationEquals(
        array $expectedConfiguration,
        array $sources
    ): void {
        $extensionConfigurationBuilder = new ExtensionConfigurationBuilder(new LoaderFactory());
        $extensionConfiguration = $extensionConfigurationBuilder
            ->setExtension($this->getContainerExtension())
            ->setSources($sources)
            ->getConfiguration();

        $constraint = new ProcessedConfigurationEqualsConstraint(
            $this->getConfiguration(),
            $extensionConfiguration
        );

        self::assertThat($expectedConfiguration, $constraint);
    }
}
