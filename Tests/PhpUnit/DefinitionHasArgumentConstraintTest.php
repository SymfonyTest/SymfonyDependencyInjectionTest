<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasArgumentConstraint;
use Symfony\Component\DependencyInjection\Definition;

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
        $argumentNumber = 1;
        $rightValue = 'the right value';
        $arguments = array($argumentNumber => $rightValue);
        $definitionWithArguments->setArguments($arguments);

        $wrongValue = 'the wrong value';

        return array(
            // the definition has no second argument
            array($definitionWithNoArguments, $argumentNumber, $rightValue, false),
            // the definition has a second argument, but with the wrong value
            array($definitionWithNoArguments, $argumentNumber, $wrongValue, false),
            // the definition has a second argument with the right value
            array($definitionWithArguments, $argumentNumber, $rightValue, true),
        );
    }
}
