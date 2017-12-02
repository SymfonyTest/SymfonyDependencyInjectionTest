<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasAliasConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderHasAliasConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider containerBuilderProvider
     */
    public function match(ContainerBuilder $containerBuilder, $alias, $expectedServiceId, $shouldMatch)
    {
        $constraint = new ContainerBuilderHasAliasConstraint($alias, $expectedServiceId);

        $this->assertSame($shouldMatch, $constraint->evaluate($containerBuilder, null, true));
    }

    public function containerBuilderProvider()
    {
        $emptyContainerBuilder = new ContainerBuilder();

        $aliasId = 'alias';

        $rightServiceId = 'some_service_id';
        $builderWithAlias = new ContainerBuilder();
        $builderWithAlias->setAlias($aliasId, $rightServiceId);

        $wrongServiceId = 'other_service_id';

        return array(
            // the container does not have the alias
            array($emptyContainerBuilder, $aliasId, $rightServiceId, false),
            // the container has the alias, but for another service
            array($emptyContainerBuilder, $aliasId, $wrongServiceId, false),
            // the container has the alias for the right service id
            array($builderWithAlias, $aliasId, $rightServiceId, true),
            // service id is optional
            array($builderWithAlias, $aliasId, null, true),
        );
    }

    /**
     * @test
     */
    public function it_has_a_string_representation()
    {
        $aliasId = 'alias_id';
        $serviceId = 'service_id';
        $constraint = new ContainerBuilderHasAliasConstraint($aliasId, $serviceId);
        $this->assertSame(
            'has an alias "' . $aliasId . '" for service "' . $serviceId . '"',
            $constraint->toString()
        );
    }

    /**
     * @test
     */
    public function it_expects_a_string_for_alias_id()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');

        new ContainerBuilderHasAliasConstraint(new \stdClass(), 'service_id');
    }

    /**
     * @test
     */
    public function it_expects_a_string_for_service_id()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');

        new ContainerBuilderHasAliasConstraint('alias_id', new \stdClass());
    }

    /**
     * @test
     */
    public function it_does_not_change_case_of_aliased_service_ids()
    {
        if (!class_exists(ChildDefinition::class)) {
            $this->markTestSkipped('This test requires at least Symfony 3.3');
        }

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setAlias('Interface', 'InterfaceImplementationService');
        $constraint = new ContainerBuilderHasAliasConstraint('Interface', 'InterfaceImplementationService');

        $this->assertTrue($constraint->evaluate($containerBuilder, null, true));
    }
}
