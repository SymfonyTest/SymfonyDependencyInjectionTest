<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionEqualsServiceLocatorConstraint;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DefinitionEqualsServiceLocatorTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        if (!class_exists(ServiceLocator::class)) {
            $this->markTestSkipped('Requires the Symfony DependencyInjection component v3.4 or higher');
        }

        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * @test
     */
    public function it_fails_if_the_service_definition_is_not_a_service_locator()
    {
        $this->assertConstraintFails(new DefinitionEqualsServiceLocatorConstraint([]), new Definition());
        $this->assertConstraintFails(new DefinitionEqualsServiceLocatorConstraint([]), new Definition(\stdClass::class));
    }

    /**
     * @test
     *
     * @dataProvider provideInvalidServiceLocatorReferences
     */
    public function if_fails_if_the_service_definition_value_is_not_a_valid_reference($arguments)
    {
        $this->assertConstraintFails(
            new DefinitionEqualsServiceLocatorConstraint([]),
            new Definition(ServiceLocator::class, $arguments)
        );
    }

    public function provideInvalidServiceLocatorReferences()
    {
        yield [['']];
        yield [[null]];
        yield [[null, null]];
        yield [[new Reference('foo'), null]];
    }

    /**
     * @test
     *
     * @dataProvider provideValidServiceLocatorDefs
     */
    public function it_does_not_fail_if_the_service_definition_is_a_service_locator(array $defArguments, array $expected)
    {
        $this->assertConstraintPasses(
            new DefinitionEqualsServiceLocatorConstraint($expected),
            new Definition(ServiceLocator::class, [$defArguments])
        );
    }

    public function provideValidServiceLocatorDefs()
    {
        // Data providers get called before setUp?
        if (!class_exists(ServiceLocator::class)) {
            return [[], []];
        }

        yield [
            ['bar' => new ServiceClosureArgument(new Reference('foo'))],
            ['bar' => new Reference('foo')],
        ];

        yield [
            ['bar' => new ServiceClosureArgument(new Reference('foo'))],
            ['bar' => new ServiceClosureArgument(new Reference('foo'))],
        ];

        yield [
            ['bar' => new ServiceClosureArgument(new Reference('foo'))],
            ['bar' => 'foo'],
        ];

        yield [
            [
                'bar' => new ServiceClosureArgument(new Reference('foo')),
                'foo' => new ServiceClosureArgument(new Reference('bar')),
            ],
            ['bar' => 'foo', 'foo' => 'bar'],
        ];
    }

    private function assertConstraintFails(Constraint $constraint, $value)
    {
        $this->assertFalse($constraint->evaluate($value, '', true));
    }

    private function assertConstraintPasses(Constraint $constraint, $value)
    {
        $this->assertTrue($constraint->evaluate($value, '', true));
    }
}
