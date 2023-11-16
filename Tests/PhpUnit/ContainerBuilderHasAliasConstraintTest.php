<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasAliasConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderHasAliasConstraintTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider containerBuilderProvider
     */
    public function match(ContainerBuilder $containerBuilder, $alias, $expectedServiceId, $shouldMatch): void
    {
        $constraint = new ContainerBuilderHasAliasConstraint($alias, $expectedServiceId);

        $this->assertSame($shouldMatch, $constraint->evaluate($containerBuilder, '', true));
    }

    public static function containerBuilderProvider()
    {
        $emptyContainerBuilder = new ContainerBuilder();

        $aliasId = 'alias';

        $rightServiceId = 'some_service_id';
        $builderWithAlias = new ContainerBuilder();
        $builderWithAlias->setAlias($aliasId, $rightServiceId);

        $wrongServiceId = 'other_service_id';

        return [
            // the container does not have the alias
            [$emptyContainerBuilder, $aliasId, $rightServiceId, false],
            // the container has the alias, but for another service
            [$emptyContainerBuilder, $aliasId, $wrongServiceId, false],
            // the container has the alias for the right service id
            [$builderWithAlias, $aliasId, $rightServiceId, true],
            // service id is optional
            [$builderWithAlias, $aliasId, null, true],
        ];
    }

    /**
     * @test
     */
    public function it_has_a_string_representation(): void
    {
        $aliasId = 'alias_id';
        $serviceId = 'service_id';
        $constraint = new ContainerBuilderHasAliasConstraint($aliasId, $serviceId);
        $this->assertSame(
            'has an alias "'.$aliasId.'" for service "'.$serviceId.'"',
            $constraint->toString()
        );
    }

    /**
     * @test
     */
    public function it_does_not_change_case_of_aliased_service_ids(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setAlias('Interface', 'InterfaceImplementationService');
        $constraint = new ContainerBuilderHasAliasConstraint('Interface', 'InterfaceImplementationService');

        $this->assertTrue($constraint->evaluate($containerBuilder, '', true));
    }
}
