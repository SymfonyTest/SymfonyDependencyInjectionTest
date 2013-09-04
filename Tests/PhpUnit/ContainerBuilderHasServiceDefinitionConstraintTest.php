<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasServiceDefinitionConstraint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ContainerBuilderHasServiceDefinitionConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider containerBuilderProvider
     */
    public function match(ContainerBuilder $containerBuilder, $serviceId, $expectedClass, $shouldMatch)
    {
        $constraint = new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $expectedClass);

        $this->assertSame($shouldMatch, $constraint->evaluate($containerBuilder, '', true));
    }

    public function containerBuilderProvider()
    {
        $emptyContainerBuilder = new ContainerBuilder();

        $serviceId = 'service_id';
        $rightClass = 'TheRightClass';
        $definition = new Definition($rightClass);
        $containerBuilderWithServiceDefinition = new ContainerBuilder();
        $containerBuilderWithServiceDefinition->setDefinition($serviceId, $definition);

        $aliasId = 'alias_id';
        $containerBuilderWithAlias = new ContainerBuilder();
        $containerBuilderWithAlias->setDefinition($serviceId, $definition);
        $containerBuilderWithAlias->setAlias($aliasId, $serviceId);

        $wrongClass = 'TheWrongClass';

        return array(
            // the container does not have the service definition
            array($emptyContainerBuilder, $serviceId, $rightClass, false),
            // the container has a service definition, but with the wrong class
            array($containerBuilderWithServiceDefinition, $serviceId, $wrongClass, false),
            // the container has a service definition with the right class
            array($containerBuilderWithServiceDefinition, $serviceId, $rightClass, true),
            // the container has an alias, but with the wrong class
            array($containerBuilderWithAlias, $aliasId, $wrongClass, false),
            // the container has an alias with the right class
            array($containerBuilderWithAlias, $aliasId, $rightClass, true)
        );
    }

    /**
     * @test
     */
    public function it_has_a_string_representation()
    {
        $serviceId = 'some_service_id';
        $class = 'SomeClass';
        $constraint = new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $class);
        $this->assertSame(
            'has a service definition "' . $serviceId . '" with class "' . $class . '"',
            $constraint->toString()
        );
    }
}
