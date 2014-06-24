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
    protected function assertContainerBuilderHasService($serviceId, $expectedClass = null)
    {
        $checkExpectedClass = (func_num_args() > 1);

        self::assertThat(
            $this->container,
            new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $expectedClass, $checkExpectedClass)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test does not have a service definition with the given id.
     *
     * @param $serviceId
     */
    protected function assertContainerBuilderNotHasService($serviceId)
    {
        self::assertThat(
            $this->container,
            new \PHPUnit_Framework_Constraint_Not(new ContainerBuilderHasServiceDefinitionConstraint($serviceId, null, false))
        );
    }


    /**
     * Assert that the ContainerBuilder for this test has a synthetic service with the given id.
     *
     * @param $serviceId
     */
    protected function assertContainerBuilderHasSyntheticService($serviceId)
    {
        self::assertThat(
            $this->container,
            new ContainerBuilderHasSyntheticServiceConstraint($serviceId)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has an alias and that it is an alias for the given service id.
     *
     * @param $aliasId
     * @param $expectedServiceId
     */
    protected function assertContainerBuilderHasAlias($aliasId, $expectedServiceId = null)
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
    protected function assertContainerBuilderHasParameter($parameterName, $expectedParameterValue = null)
    {
        $checkParameterValue = (func_num_args() > 1);

        self::assertThat(
            $this->container,
            new ContainerHasParameterConstraint($parameterName, $expectedParameterValue, $checkParameterValue)
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
        $expectedValue = null
    ) {
        $definition = $this->container->findDefinition($serviceId);
        $checkValue = (func_num_args() > 2);

        self::assertThat(
            $definition,
            new DefinitionHasArgumentConstraint($argumentIndex, $expectedValue, $checkValue)
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
        array $arguments = array()
    ) {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionHasMethodCallConstraint($method, $arguments));
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has a tag
     * with the given attributes.
     *
     * @param string $serviceId
     * @param string $tag
     * @param array  $attributes
     */
    protected function assertContainerBuilderHasServiceDefinitionWithTag(
        $serviceId,
        $tag,
        array $attributes = array()
    ) {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionHasTagConstraint($tag, $attributes));
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
