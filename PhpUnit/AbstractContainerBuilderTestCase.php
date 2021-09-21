<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractContainerBuilderTestCase extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->container->getCompilerPassConfig()->setRemovingPasses([]);
        $this->container->getCompilerPassConfig()->setAfterRemovingPasses([]);
    }

    protected function tearDown(): void
    {
        $this->container = null;
    }

    /**
     * Shortcut for quickly defining services. The returned Definition object can be further modified if necessary.
     */
    final protected function registerService(string $serviceId, string $class): Definition
    {
        $definition = new Definition($class);

        $this->container->setDefinition($serviceId, $definition);

        return $definition;
    }

    /**
     * Set a service definition you manually created.
     */
    final protected function setDefinition(string $serviceId, Definition $definition): void
    {
        $this->container->setDefinition($serviceId, $definition);
    }

    /**
     * Set a parameter.
     *
     * @param mixed $parameterValue
     */
    final protected function setParameter(string $parameterId, $parameterValue): void
    {
        $this->container->setParameter($parameterId, $parameterValue);
    }

    /**
     * Call this method to compile the ContainerBuilder, to test if any problems would occur at runtime.
     */
    final protected function compile(): void
    {
        $this->container->compile();
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id and class.
     */
    final protected function assertContainerBuilderHasService(
        string $serviceId,
        ?string $expectedClass = null
    ): void {
        $checkExpectedClass = (func_num_args() > 1);

        self::assertThat(
            $this->container,
            new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $expectedClass, $checkExpectedClass)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test does not have a service definition with the given id.
     */
    final protected function assertContainerBuilderNotHasService(string $serviceId): void
    {
        self::assertThat(
            $this->container,
            new LogicalNot(new ContainerBuilderHasServiceDefinitionConstraint($serviceId, null, false))
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a synthetic service with the given id.
     */
    final protected function assertContainerBuilderHasSyntheticService(string $serviceId): void
    {
        self::assertThat(
            $this->container,
            new ContainerBuilderHasSyntheticServiceConstraint($serviceId)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has an alias and that it is an alias for the given service id.
     */
    final protected function assertContainerBuilderHasAlias(
        string $aliasId,
        ?string $expectedServiceId = null
    ): void {
        self::assertThat(
            $this->container,
            new ContainerBuilderHasAliasConstraint($aliasId, $expectedServiceId)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a parameter and that its value is the given value.
     *
     * @param mixed $expectedParameterValue
     */
    final protected function assertContainerBuilderHasParameter(
        string $parameterName,
        $expectedParameterValue = null
    ): void {
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
     * @param mixed $expectedValue
     */
    final protected function assertContainerBuilderHasServiceDefinitionWithArgument(
        string $serviceId,
        $argumentIndex,
        $expectedValue = null
    ): void {
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
     * @param int|string $argumentIndex
     * @param array      $expectedServiceMap an array of service-id references and their key in the map
     */
    final protected function assertContainerBuilderHasServiceDefinitionWithServiceLocatorArgument(
        string $serviceId,
        $argumentIndex,
        array $expectedValue
    ): void {
        self::assertThat(
            $this->container,
            new DefinitionArgumentEqualsServiceLocatorConstraint($serviceId, $argumentIndex, $expectedValue)
        );
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has a method
     * call to the given method with the given arguments.
     *
     * @param int|null $index
     */
    final protected function assertContainerBuilderHasServiceDefinitionWithMethodCall(
        string $serviceId,
        string $method,
        array $arguments = [],
        $index = null
    ): void {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionHasMethodCallConstraint($method, $arguments, $index));
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id, which has a tag
     * with the given attributes.
     */
    final protected function assertContainerBuilderHasServiceDefinitionWithTag(
        string $serviceId,
        string $tag,
        array $attributes = []
    ): void {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionHasTagConstraint($tag, $attributes));
    }

    /**
     * Assert that the ContainerBuilder for this test has a service definition with the given id which is a decorated
     * service and it has the given parent service.
     */
    final protected function assertContainerBuilderHasServiceDefinitionWithParent(
        string $serviceId,
        string $parentServiceId
    ): void {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionIsChildOfConstraint($parentServiceId));
    }

    /**
     * Assert that the ContainerBuilder for this test has a ServiceLocator service definition with the given id.
     *
     * @param array $expectedServiceMap an array of service-id references and their key in the map
     */
    final protected function assertContainerBuilderHasServiceLocator(
        string $serviceId,
        array $expectedServiceMap = []
    ): void {
        $definition = $this->container->findDefinition($serviceId);

        // Service locator was provided as context (and therefor a factory)
        if (isset($definition->getFactory()[1])) {
            $definition = $this->container->findDefinition((string) $definition->getFactory()[0]);
        }

        self::assertThat($definition, new DefinitionEqualsServiceLocatorConstraint($expectedServiceMap));
    }

    final protected function assertContainerBuilderServiceDecoration(
        string $serviceId,
        string $decoratedServiceId,
        ?string $renamedId = null,
        int $priority = 0,
        ?int $invalidBehavior = null
    ): void {
        $definition = $this->container->findDefinition($serviceId);

        self::assertThat($definition, new DefinitionDecoratesConstraint($serviceId, $decoratedServiceId, $renamedId, $priority, $invalidBehavior));
    }
}
