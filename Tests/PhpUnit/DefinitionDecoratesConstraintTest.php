<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionDecoratesConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionDecoratesConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider containerBuilderProvider
     */
    public function match(ContainerBuilder $containerBuilder, bool $expectedToMatch, string $serviceId, string $decoratedServiceId, ?string $renamedId, int $priority, ?int $invalidBehavior): void
    {
        $constraint = new DefinitionDecoratesConstraint($serviceId, $decoratedServiceId, $renamedId, $priority, $invalidBehavior);

        $this->assertSame($expectedToMatch, $constraint->evaluate($containerBuilder, '', true));
    }

    public static function containerBuilderProvider(): iterable
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition('decorated1', new Definition('DecoratedClass1'));
        $containerBuilder->setDefinition('decorated2', new Definition('DecoratedClass2'));
        $containerBuilder->setDefinition('decorated3', new Definition('DecoratedClass3'));
        $containerBuilder->setDefinition('decorated4', new Definition('DecoratedClass4'));
        $containerBuilder->setDefinition('decorated5', new Definition('DecoratedClass5'));

        $containerBuilder->setDefinition('decorator1', (new Definition('DecoratorClass1'))->setDecoratedService('decorated1'));
        $containerBuilder->setDefinition('decorator2', (new Definition('DecoratorClass2'))->setDecoratedService('decorated2', 'decorated2_0'));
        $containerBuilder->setDefinition('decorator3', (new Definition('DecoratorClass3'))->setDecoratedService('decorated3', null, 10));
        $containerBuilder->setDefinition('decorator4', (new Definition('DecoratorClass4'))->setDecoratedService('decorated4', null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
        $containerBuilder->setDefinition('decorator5', (new Definition('DecoratorClass5'))->setDecoratedService('decorated5', 'decorated5_0', -3, ContainerInterface::NULL_ON_INVALID_REFERENCE));

        yield [$containerBuilder, false, 'not_decorator', 'decorated', null, 0, null];
        yield [$containerBuilder, true, 'decorator1', 'decorated1', null, 0, null];
        yield [$containerBuilder, true, 'decorator2', 'decorated2', 'decorated2_0', 0, null];
        yield [$containerBuilder, true, 'decorator3', 'decorated3', null, 10, null];
        yield [$containerBuilder, true, 'decorator4', 'decorated4', null, 0, 3];
        yield [$containerBuilder, true, 'decorator5', 'decorated5', 'decorated5_0', -3, 2];
    }

    /**
     * @test
     * @dataProvider stringRepresentationProvider
     */
    public function it_has_a_string_representation(DefinitionDecoratesConstraint $constraint, string $expectedRepresentation): void
    {
        $this->assertSame($expectedRepresentation, $constraint->toString());
    }

    public static function stringRepresentationProvider(): iterable
    {
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated'), '"decorator" decorates service "decorated" with priority "0" and "RUNTIME_EXCEPTION_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0'), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "0" and "RUNTIME_EXCEPTION_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0', -3), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "-3" and "RUNTIME_EXCEPTION_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0', -3, 0), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "-3" and "RUNTIME_EXCEPTION_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0', -3, 1), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "-3" and "EXCEPTION_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0', -3, 2), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "-3" and "NULL_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0', -3, 3), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "-3" and "IGNORE_ON_INVALID_REFERENCE" behavior.'];
        yield [new DefinitionDecoratesConstraint('decorator', 'decorated', 'decorated_0', -3, 4), '"decorator" decorates service "decorated" and renames it to "decorated_0" with priority "-3" and "IGNORE_ON_UNINITIALIZED_REFERENCE" behavior.'];
    }
}
