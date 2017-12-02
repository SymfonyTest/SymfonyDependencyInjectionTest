<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasMethodCallConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasMethodCallConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $method, $arguments, $index, $expectedToMatch)
    {
        $constraint = new DefinitionHasMethodCallConstraint($method, $arguments, $index);

        $this->assertSame($expectedToMatch, $constraint->evaluate($definition, '', true));
    }

    public function definitionProvider()
    {
        $definitionWithNoMethodCalls = new Definition();

        $definitionWithTwoMethodCalls = new Definition();

        $argumentsOfFirstCall = array('argument of first call');
        $definitionWithTwoMethodCalls->addMethodCall('methodCallOne', $argumentsOfFirstCall);

        $argumentsOfSecondCall = array('argument of second call');
        $definitionWithTwoMethodCalls->addMethodCall('methodCallTwo', $argumentsOfSecondCall);

        $otherArguments = array('some other argument');

        return array(
            // the definition has no method call
            array($definitionWithNoMethodCalls, 'noMethodCall', array(), null, false),
            // the definition has a call to this method on any invocation index, arguments match with the first call
            array($definitionWithTwoMethodCalls, 'methodCallOne', $argumentsOfFirstCall, null, true),
            // the definition has a call to this method on first invocation index, arguments match with the second call
            array($definitionWithTwoMethodCalls, 'methodCallTwo', $argumentsOfSecondCall, 1, true),
            // the definition has a call to this method, but the arguments don't match
            array($definitionWithTwoMethodCalls, 'methodCallOne', $otherArguments, null, false),
            // the definition has a call to this method, arguments match with the first call, but invocation index is wrong
            array($definitionWithTwoMethodCalls, 'methodCallOne', $argumentsOfFirstCall, 1, false),
            // the definition has a call to this method, has arguments, but they are not checked
            array($definitionWithTwoMethodCalls, 'methodCallOne', null, null, true),
        );
    }

    /**
     * @test
     */
    public function it_has_a_string_representation()
    {
        $method = 'methodName';
        $constraint = new DefinitionHasMethodCallConstraint($method, array());

        $this->assertSame('has a method call to "'.$method.'" with the given arguments.', $constraint->toString());

        $invocationIndex = 2;
        $constraint = new DefinitionHasMethodCallConstraint($method, array(), $invocationIndex);

        $this->assertSame('has a method call to "'.$method.'" with the given arguments on invocation order index with value of '.$invocationIndex.'.', $constraint->toString());
    }
}
