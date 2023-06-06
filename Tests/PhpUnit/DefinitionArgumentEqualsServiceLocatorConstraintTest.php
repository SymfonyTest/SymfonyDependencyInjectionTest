<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionArgumentEqualsServiceLocatorConstraint;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DefinitionArgumentEqualsServiceLocatorConstraintTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp(): void
    {
        if (!class_exists(ServiceLocator::class)) {
            $this->markTestSkipped('Requires the Symfony DependencyInjection component v3.4 or higher');
        }

        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * @test
     */
    public function it_fails_if_the_service_definition_is_not_a_service_locator(): void
    {
        $this->containerBuilder->setDefinition('not_a_service_locator', new Definition());
        $this->containerBuilder->setDefinition('using_service', new Definition(\stdClass::class, [new Reference('not_a_service_locator')]));

        $this->assertConstraintFails(new DefinitionArgumentEqualsServiceLocatorConstraint('using_service', 0, []));
    }

    /**
     * @test
     */
    public function if_fails_if_the_service_definition_value_references_a_missing_service(): void
    {
        $this->containerBuilder->setDefinition('using_service', new Definition(\stdClass::class, [new Reference('not_a_service_locator')]));

        $this->expectException(ServiceNotFoundException::class);

        $this->assertConstraintFails(new DefinitionArgumentEqualsServiceLocatorConstraint('using_service', 0, []));
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidServiceLocatorReferences
     */
    public function if_fails_if_the_service_definition_value_is_not_a_valid_reference($arguments): void
    {
        $this->containerBuilder->setDefinition('service_locator', new Definition(ServiceLocator::class, $arguments));
        $this->containerBuilder->setDefinition('using_service', new Definition(\stdClass::class, [new Reference('service_locator')]));

        $this->assertConstraintFails(new DefinitionArgumentEqualsServiceLocatorConstraint('using_service', 0, [new Reference('foo')]));
    }

    public static function provideInvalidServiceLocatorReferences()
    {
        yield [['']];
        yield [[null]];
        yield [[null, null]];
        yield [[new Reference('foo'), null]];
    }

    /**
     * @test
     */
    public function it_does_not_fail_if_the_service_definition_is_a_service_locator(): void
    {
        $definition = new Definition(ServiceLocator::class, [['bar' => new ServiceClosureArgument(new Reference('foo'))]]);
        $this->containerBuilder->setDefinition('using_service', new Definition(\stdClass::class, [null, '$l' => $definition]));

        $this->assertConstraintPasses(new DefinitionArgumentEqualsServiceLocatorConstraint('using_service', '$l', ['bar' => new Reference('foo')]));
    }

    /**
     * @test
     */
    public function it_does_not_fail_if_the_service_definition_is_a_service_locator_reference(): void
    {
        $id = ServiceLocatorTagPass::register($this->containerBuilder, ['bar' => new Reference('foo')]);
        $this->containerBuilder->setDefinition('using_service', new Definition(\stdClass::class, [$id]));

        $this->assertConstraintPasses(
            new DefinitionArgumentEqualsServiceLocatorConstraint('using_service', 0, ['bar' => new Reference('foo')])
        );
    }

    /**
     * @test
     */
    public function it_does_not_fail_if_the_service_definition_is_a_service_locator_with_a_with_a_context(): void
    {
        $id = ServiceLocatorTagPass::register($this->containerBuilder, ['bar' => new Reference('foo')], 'using_service');
        $this->containerBuilder->setDefinition('using_service', new Definition(\stdClass::class, [$id]));

        $this->assertConstraintPasses(
            new DefinitionArgumentEqualsServiceLocatorConstraint('using_service', 0, ['bar' => new Reference('foo')])
        );
    }

    private function assertConstraintFails(Constraint $constraint): void
    {
        $this->assertFalse($constraint->evaluate($this->containerBuilder, '', true));
    }

    private function assertConstraintPasses(Constraint $constraint): void
    {
        $this->assertTrue($constraint->evaluate($this->containerBuilder, '', false));
    }
}
