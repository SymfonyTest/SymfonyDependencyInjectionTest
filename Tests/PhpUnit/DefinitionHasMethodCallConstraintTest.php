<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasMethodCallConstraint;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasMethodCallConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $method, $arguments, $expectedToMatch)
    {
        $constraint = new DefinitionHasMethodCallConstraint($method, $arguments);

        $this->assertSame($expectedToMatch, $constraint->evaluate($definition, '', true));
    }

    public function definitionProvider()
    {
        $definitionWithNoMethodCalls = new Definition();

        $definitionWithTwoMethodCalls = new Definition();

        $method = 'add';

        $argumentsOfFirstCall = array('argument of first call');
        $definitionWithTwoMethodCalls->addMethodCall($method, $argumentsOfFirstCall);

        $argumentsOfSecondCall = array('argument of second call');
        $definitionWithTwoMethodCalls->addMethodCall($method, $argumentsOfSecondCall);

        $otherArguments = array('some other argument');

        return array(
            // the definition has no method call
            array($definitionWithNoMethodCalls, $method, array(), false),
            // the definition has a call to this method, arguments match with the first call
            array($definitionWithTwoMethodCalls, $method, $argumentsOfFirstCall, true),
            // the definition has a call to this method, arguments match with the second call
            array($definitionWithTwoMethodCalls, $method, $argumentsOfSecondCall, true),
            // the definition has a call to this method, but the arguments don't match
            array($definitionWithTwoMethodCalls, $method, $otherArguments, false),
        );
    }

    /**
     * @test
     */
    public function it_has_a_string_representation()
    {
        $method = 'methodName';
        $constraint = new DefinitionHasMethodCallConstraint($method, array());

        $this->assertSame('has a method call to "'.$method.'" with the given arguments', $constraint->toString());
    }
}
