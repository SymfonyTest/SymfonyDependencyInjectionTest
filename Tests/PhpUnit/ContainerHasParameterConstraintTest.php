<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerHasParameterConstraint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHasParameterConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider containerBuilderProvider
     */
    public function match(ContainerInterface $container, $parameterName, $parameterValue, $expectedToMatch)
    {
        $constraint = new ContainerHasParameterConstraint($parameterName, $parameterValue);

        $this->assertSame($expectedToMatch, $constraint->evaluate($container, '', true));
    }

    public function containerBuilderProvider()
    {
        $emptyContainer = $this->createMockContainerWithParameters(array());

        $parameterName = 'parameter_name';
        $parameterValue = 'some value';
        $containerWithParameter = $this->createMockContainerWithParameters(
            array(
                $parameterName => $parameterValue
            )
        );

        $wrongParameterValue = 'some other value';

        return array(
            // the container does not have the parameter
            array($emptyContainer, $parameterName, $parameterValue, false),
            // the container has the parameter but the values don't match
            array($containerWithParameter, $parameterName, $wrongParameterValue, false),
            // the container has the parameter and the value matches
            array($containerWithParameter, $parameterName, $parameterValue, true),
        );
    }

    private function createMockContainerWithParameters(array $parameters)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container
            ->expects($this->any())
            ->method('hasParameter')
            ->will(
                $this->returnCallback(
                    function ($parameterName) use ($parameters) {
                        return array_key_exists($parameterName, $parameters);
                    }
                )
            );

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnCallback(
                    function ($parameterName) use ($parameters) {
                        return $parameters[$parameterName];
                    }
                )
            );

        return $container;
    }
}
