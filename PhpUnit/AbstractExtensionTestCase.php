<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

abstract class AbstractExtensionTestCase extends AbstractContainerBuilderTestCase
{
    /**
     * @var bool denotes if load() method has been invoked.
     */
    private $loadMethodInvoked;

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

        $this->loadMethodInvoked = false;

        foreach ($this->getContainerExtensions() as $extension) {
            $this->container->registerExtension($extension);
        }
    }

    /**
     * Call this method from within your test after you have (optionally) modified the ContainerBuilder for this test
     * ($this->container).
     *
     * If your extension(s) implements \Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface, you may
     * set $withPrependInvocation to TRUE to invoke prepend() method prior to load() method of your extension.
     *
     * @param array $configurationValues
     * @param bool $withPrependInvocation
     */
    protected function load(array $configurationValues = array(), $withPrependInvocation = false)
    {
        $this->loadMethodInvoked = true;

        $configs = array($this->getMinimalConfiguration(), $configurationValues);

        foreach ($this->container->getExtensions() as $extension) {

            if ($withPrependInvocation && $extension instanceof PrependExtensionInterface) {
                $extension->prepend($this->container);
            }

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
