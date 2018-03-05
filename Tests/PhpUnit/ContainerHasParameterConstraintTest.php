<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerHasParameterConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHasParameterConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider containerBuilderProvider
     */
    public function match(
      ContainerInterface $container,
      $parameterName,
      $parameterValue,
      $checkParameterValue,
      $expectedToMatch
    ) {
        $constraint = new ContainerHasParameterConstraint($parameterName, $parameterValue, $checkParameterValue);

        $this->assertSame($expectedToMatch, $constraint->evaluate($container, '', true));
    }

    public function containerBuilderProvider()
    {
        $emptyContainer = $this->createMockContainerWithParameters([]);

        $parameterName = 'parameter_name';
        $parameterValue = 'some value';
        $wrongParameterValue = 'some other value';

        return [
            // the container does not have the parameter
            [$emptyContainer, $parameterName, $parameterValue, true, false],
            // the container has the parameter but the values don't match
            [$this->createMockContainerWithParameters(array($parameterName => $parameterValue)), $parameterName, $wrongParameterValue, true, false],
            // the container has the parameter and the value matches
            [$this->createMockContainerWithParameters(array($parameterName => $parameterValue)), $parameterName, $parameterValue, true, true],
            // the container has the parameter and the value is optional
            [$this->createMockContainerWithParameters(array($parameterName => $parameterValue)), $parameterName, null, false, true],
        ];
    }

    private function createMockContainerWithParameters(array $parameters)
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects($this->any())
            ->method('hasParameter')
            ->willReturnCallback(function ($parameterName) use ($parameters) {
                return array_key_exists($parameterName, $parameters);
            });

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->willReturnCallback(function ($parameterName) use ($parameters) {
                return $parameters[$parameterName];
            });

        return $container;
    }
}
