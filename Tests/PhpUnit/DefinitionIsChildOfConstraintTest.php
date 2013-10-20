<?php


namespace Matthias\DependencyInjectionTests\Test\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionIsChildOfConstraint;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefinitionIsChildOfConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $parentServiceId, $expectedToMatch)
    {
        $constraint = new DefinitionIsChildOfConstraint($parentServiceId);

        $this->assertSame($expectedToMatch, $constraint->evaluate($definition, '', true));
    }

    public function definitionProvider()
    {
        $definition = new Definition();
        $decoratedDefinition = new DefinitionDecorator('parent_service_id');

        return array(
            // the provided definition has the same parent service id
            array($decoratedDefinition, 'parent_service_id', true),
            // the provided definition has another parent service id
            array($decoratedDefinition, 'invalid_parent_service_id', false),
            // the provided definition is no DefinitionDecorator
            array($definition, 'any_parent_service_id', false)
        );
    }
}
