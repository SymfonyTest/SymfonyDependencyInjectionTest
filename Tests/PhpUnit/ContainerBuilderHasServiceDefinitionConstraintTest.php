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
    public function match(
      ContainerBuilder $containerBuilder,
      $serviceId,
      $expectedClass,
      $checkExpectedClass,
      $shouldMatch
    ) {
        $constraint = new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $expectedClass, $checkExpectedClass);

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

        $containerBuilderWithServiceDefinitionWithParameterClass = new ContainerBuilder();
        $containerBuilderWithServiceDefinitionWithParameterClass->setParameter(
            'service_id.class',
            $rightClass
        );
        $containerBuilderWithServiceDefinitionWithParameterClass->setDefinition($serviceId, new Definition('%service_id.class%'));

        $wrongClass = 'TheWrongClass';

        return array(
            // the container does not have the service definition
            array($emptyContainerBuilder, $serviceId, $rightClass, true, false),
            // the container has a service definition, but with the wrong class
            array($containerBuilderWithServiceDefinition, $serviceId, $wrongClass, true, false),
            // the container has a service definition with the right class
            array($containerBuilderWithServiceDefinition, $serviceId, $rightClass, true, true),
            // the container has a service definition with the right class, but it's a parameter
            array($containerBuilderWithServiceDefinitionWithParameterClass, $serviceId, $rightClass, true, true),
            // the container has an alias, but with the wrong class
            array($containerBuilderWithAlias, $aliasId, $wrongClass, true, false),
            // the container has an alias with the right class
            array($containerBuilderWithAlias, $aliasId, $rightClass, true, true),
            // giving a class is optional
            array($containerBuilderWithAlias, $aliasId, null, false, true),
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

    /**
     * @test
     */
    public function it_expects_a_string_for_service_id()
    {
        $this->setExpectedException('\InvalidArgumentException', 'string');
        new ContainerBuilderHasServiceDefinitionConstraint(new \stdClass(), 'class');
    }

    /**
     * @test
     */
    public function it_expects_a_string_for_class()
    {
        $this->setExpectedException('\InvalidArgumentException', 'string');
        new ContainerBuilderHasServiceDefinitionConstraint('service_id', new \stdClass());
    }
}
