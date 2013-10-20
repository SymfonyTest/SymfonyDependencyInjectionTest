<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasArgumentConstraint;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefinitionHasArgumentConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $argumentIndex, $expectedValue, $shouldMatch)
    {
        $constraint = new DefinitionHasArgumentConstraint($argumentIndex, $expectedValue);

        $this->assertSame($shouldMatch, $constraint->evaluate($definition, '', true));
    }

    public function definitionProvider()
    {
        $definitionWithNoArguments = new Definition();

        $definitionWithArguments = new Definition();
        $rightValue = 'the right value';
        $wrongValue = 'the wrong value';
        $arguments = array(0 => 'first argument', 1 => $rightValue);
        $definitionWithArguments->setArguments($arguments);

        $decoratedDefinitionWithArguments = new DefinitionDecorator('parent_service_id');
        $decoratedDefinitionWithArguments->setArguments(array(0 => 'first argument', 1 => $wrongValue));
        $decoratedDefinitionWithArguments->replaceArgument(1, $rightValue);

        return array(
            // the definition has no second argument
            array($definitionWithNoArguments, 1, $rightValue, false),
            // the definition has a second argument, but with the wrong value
            array($definitionWithNoArguments, 1, $wrongValue, false),
            // the definition has a second argument with the right value
            array($definitionWithArguments, 1, $rightValue, true),
            // the definition is a decorated definition
            array($decoratedDefinitionWithArguments, 1, $rightValue, true),
        );
    }
}
