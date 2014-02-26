<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

abstract class AbstractExtensionTestCase extends AbstractContainerBuilderTestCase
{
    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    abstract protected function getContainerExtensions();

    /**
     * Optionally override this method to return an array that will be used as the minimal configuration for loading
     * the container extension under test, to prevent a test from failing because of a missing required
     * configuration value for the container extension.
     */
    protected function getMinimalConfiguration()
    {
        return array();
    }

    /**
     * Setup for each test: creates a new ContainerBuilder,
     * registers the ValidateServiceDefinitionsPass which will validate all defined services when
     * the container is compiled
     *
     * @see AbstractExtensionTestCase::tearDown()
     */
    protected function setUp()
    {
        parent::setUp();

        foreach ($this->getContainerExtensions() as $extension) {
            $this->container->registerExtension($extension);
        }
    }

    /**
     * Call this method from within your test after you have (optionally) modified the ContainerBuilder for this test
     * ($this->container).
     *
     * @param array $specificConfiguration
     */
    protected function load(array $configurationValues = array())
    {
        $configs = array($this->getMinimalConfiguration(), $configurationValues);

        foreach ($this->container->getExtensions() as $extension) {
            $extension->load($configs, $this->container);
        }
    }

    /**
     * Call this method if you want to compile the ContainerBuilder *inside* the test instead of *after* the test.
     */
    protected function compile()
    {
        $this->container->compile();
    }
}
