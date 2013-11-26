<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractContainerBuilderTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->getCompilerPassConfig()->setOptimizationPasses(array());
        $this->container->getCompilerPassConfig()->setRemovingPasses(array());
    }

    protected function tearDown()
    {
        $this->container = null;
    }

    /**
     * Shortcut for quickly defining services. The returned Definition object can be further modified if necessary.
     *
     * @param $serviceId
     * @param $class
     * @return Definition
     */
    protected function registerService($serviceId, $class)
    {
        $definition = new Definition($class);

        $this->container->setDefinition($serviceId, $definition);

        return $definition;
    }

    /**
     * Set a service definition you manually created.
     *
     * @param $serviceId
     * @param Definition $definition
     */
    protected function setDefinition($serviceId, Definition $definition)
    {
        $this->container->setDefinition($serviceId, $definition);
    }

    /**
     * Set a parameter.
     *
     * @param $parameterId
     * @param $parameterValue
     */
    protected function setParameter($parameterId, $parameterValue)
    {
        $this->container->setParameter($parameterId, $parameterValue);
    }

    /**
     * Call this method to compile the ContainerBuilder, to test if any problems would occur at runtime.
     */
    protected function compile()
    {
        $this->container->compile();
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id and class.
     *
     * @param $serviceId
     * @param $expectedClass
     */
    protected function assertContainerBuilderHasService($serviceId, $expectedClass)
    {
        self::assertThat(
            $this->container,
            new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $expectedClass)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has an alias and that it is an alias for the given service id.
     *
     * @param $aliasId
     * @param $expectedServiceId
     */
    protected function assertContainerBuilderHasAlias($aliasId, $expectedServiceId)
    {
        self::assertThat(
            $this->container,
            new ContainerBuilderHasAliasConstraint($aliasId, $expectedServiceId)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a parameter and that its value is the given value.
     *
     * @param $parameterName
     * @param $expectedParameterValue
     */
    protected function assertContainerBuilderHasParameter($parameterName, $expectedParameterValue)
    {
        self::assertThat(
            $this->container,
            new ContainerHasParameterConstraint($parameterName, $expectedParameterValue)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has an argument
     * at the given index, and its value is the given value.
     *
     * @param $serviceId
     * @param $argumentIndex
     * @param $expectedValue
     */
    protected function assertContainerBuilderHasServiceDefinitionWithArgument(
        $serviceId,
        $argumentIndex,
        $expectedValue
    ) {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat(
            $definition,
            new DefinitionHasArgumentConstraint($argumentIndex, $expectedValue)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has a method
     * call to the given method with the given arguments.
     *
     * @param string $serviceId
     * @param string $method
     * @param array $arguments
     */
    protected function assertContainerBuilderHasServiceDefinitionWithMethodCall(
        $serviceId,
        $method,
        array $arguments
    ) {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionHasMethodCallConstraint($method, $arguments));
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id which is a decorated
     * service and it has the given parent service.
     *
     * @param $serviceId
     * @param $parentServiceId
     */
    protected function assertContainerBuilderHasServiceDefinitionWithParent($serviceId, $parentServiceId)
    {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionIsChildOfConstraint($parentServiceId));
    }
}
