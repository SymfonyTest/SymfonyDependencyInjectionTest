<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionIsChildOfConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefinitionIsChildOfConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $parentServiceId, $expectedToMatch): void
    {
        $constraint = new DefinitionIsChildOfConstraint($parentServiceId);

        $this->assertSame($expectedToMatch, $constraint->evaluate($definition, '', true));
    }

    public static function definitionProvider()
    {
        $definition = new Definition();
        $decoratedDefinition = new ChildDefinition('parent_service_id');

        return [
            // the provided definition has the same parent service id
            [$decoratedDefinition, 'parent_service_id', true],
            // the provided definition has another parent service id
            [$decoratedDefinition, 'invalid_parent_service_id', false],
            // the provided definition is no DefinitionDecorator
            [$definition, 'any_parent_service_id', false],
        ];
    }
}
