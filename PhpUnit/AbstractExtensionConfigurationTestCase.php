<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Matthias\SymfonyConfigTest\PhpUnit\ProcessedConfigurationEqualsConstraint;
use Matthias\SymfonyDependencyInjectionTest\Loader\ExtensionConfigurationBuilder;
use Matthias\SymfonyDependencyInjectionTest\Loader\LoaderFactory;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

abstract class AbstractExtensionConfigurationTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Return an instance of the container extension that you are testing.
     *
     * @return ExtensionInterface
     */
    abstract protected function getContainerExtension();

    /**
     * Return an instance of the configuration class that you are testing.
     *
     * @return ConfigurationInterface
     */
    abstract protected function getConfiguration();

    protected function assertProcessedConfigurationEquals($expectedConfiguration, array $sources)
    {
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
