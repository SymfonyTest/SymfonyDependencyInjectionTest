<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractContainerBuilderTestCase extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->container->getCompilerPassConfig()->setRemovingPasses([]);
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
     *
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
            new LogicalNot(new ContainerBuilderHasServiceDefinitionConstraint($serviceId, null, false))
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
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has an argument
     * at the given index, and its value is a ServiceLocator with a reference-map equal to the given value.
     *
     * @param string     $serviceId
     * @param int|string $argumentIndex
     * @param array      $expectedServiceMap an array of service-id references and their key in the map
     */
    protected function assertContainerBuilderHasServiceDefinitionWithServiceLocatorArgument(
        $serviceId,
        $argumentIndex,
        array $expectedValue
    ) {
        self::assertThat(
            $this->container,
            new DefinitionArgumentEqualsServiceLocatorConstraint($serviceId, $argumentIndex, $expectedValue)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has a method
     * call to the given method with the given arguments.
     *
     * @param string   $serviceId
     * @param string   $method
     * @param array    $arguments
     * @param int|null $index
     */
    protected function assertContainerBuilderHasServiceDefinitionWithMethodCall(
        $serviceId,
        $method,
        array $arguments = [],
        $index = null
    ) {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionHasMethodCallConstraint($method, $arguments, $index));
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
        array $attributes = []
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

    /**
     * Assert that the ContainerBuilder for this test has a ServiceLocator service definition with the given id.
     *
     * @param string $serviceId
     * @param array  $expectedServiceMap an array of service-id references and their key in the map
     */
    protected function assertContainerBuilderHasServiceLocator(string $serviceId, array $expectedServiceMap = [])
    {
        $definition = $this->container->findDefinition($serviceId);

        // Service locator was provided as context (and therefor a factory)
        if (isset($definition->getFactory()[1])) {
            $definition = $this->container->findDefinition((string) $definition->getFactory()[0]);
        }

        self::assertThat($definition, new DefinitionEqualsServiceLocatorConstraint($expectedServiceMap));
    }
}
