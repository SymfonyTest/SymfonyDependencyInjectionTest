<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasServiceDefinitionConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ContainerBuilderHasServiceDefinitionConstraintTest extends TestCase
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
    ): void {
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

        return [
            // the container does not have the service definition
            [$emptyContainerBuilder, $serviceId, $rightClass, true, false],
            // the container has a service definition, but with the wrong class
            [$containerBuilderWithServiceDefinition, $serviceId, $wrongClass, true, false],
            // the container has a service definition with the right class
            [$containerBuilderWithServiceDefinition, $serviceId, $rightClass, true, true],
            // the container has a service definition with the right class, but it's a parameter
            [$containerBuilderWithServiceDefinitionWithParameterClass, $serviceId, $rightClass, true, true],
            // the container has an alias, but with the wrong class
            [$containerBuilderWithAlias, $aliasId, $wrongClass, true, false],
            // the container has an alias with the right class
            [$containerBuilderWithAlias, $aliasId, $rightClass, true, true],
            // giving a class is optional
            [$containerBuilderWithAlias, $aliasId, null, false, true],
        ];
    }

    /**
     * @test
     */
    public function it_has_a_string_representation(): void
    {
        $serviceId = 'some_service_id';
        $class = 'SomeClass';
        $constraint = new ContainerBuilderHasServiceDefinitionConstraint($serviceId, $class);
        $this->assertSame(
            'has a service definition "'.$serviceId.'" with class "'.$class.'"',
            $constraint->toString()
        );
    }
}
