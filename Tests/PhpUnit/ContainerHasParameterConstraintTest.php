<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerHasParameterConstraint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHasParameterConstraintTest extends TestCase
{
    #[Test]
    #[DataProvider('containerBuilderProvider')]
    public function match(
        array $containerParameters,
        $parameterName,
        $parameterValue,
        $checkParameterValue,
        $expectedToMatch
    ): void {
        $container = $this->createMockContainerWithParameters($containerParameters);
        $constraint = new ContainerHasParameterConstraint($parameterName, $parameterValue, $checkParameterValue);

        $this->assertSame($expectedToMatch, $constraint->evaluate($container, '', true));
    }

    #[Test]
    #[DataProvider('typeAwareContainerBuilderProvider')]
    public function matchWithType(
        array $containerParameters,
        $parameterName,
        $parameterValue,
        $checkParameterValue,
        $expectedToMatch
    ): void {
        $container = $this->createMockContainerWithParameters($containerParameters);
        $constraint = new ContainerHasParameterConstraint($parameterName, $parameterValue, $checkParameterValue, true);

        $this->assertSame($expectedToMatch, $constraint->evaluate($container, '', true));
    }

    public static function containerBuilderProvider(): array
    {
        $parameterName = 'parameter_name';
        $parameterValue = 'some value';
        $wrongParameterValue = 'some other value';

        return [
            // the container does not have the parameter
            [[], $parameterName, $parameterValue, true, false],
            // the container has the parameter but the values don't match
            [[$parameterName => $parameterValue], $parameterName, $wrongParameterValue, true, false],
            // the container has the parameter and the value matches
            [[$parameterName => $parameterValue], $parameterName, $parameterValue, true, true],
            // the container has the parameter and the value is optional
            [[$parameterName => $parameterValue], $parameterName, null, false, true],
        ];
    }

    public static function typeAwareContainerBuilderProvider(): array
    {
        $parameterName = 'parameter_name';
        $parameterValue = '123123';
        $wrongParameterValue = 123123;

        return [
            // the container has the parameter but the type don't match
            [[$parameterName => $parameterValue], $parameterName, $wrongParameterValue, true, false],
            // the container has the parameter and the value matches
            [[$parameterName => $parameterValue], $parameterName, $parameterValue, true, true],
        ];
    }

    private function createMockContainerWithParameters(array $parameters)
    {
        $container = $this->createStub(ContainerInterface::class);

        $container
            ->method('hasParameter')
            ->willReturnCallback(function ($parameterName) use ($parameters) {
                return array_key_exists($parameterName, $parameters);
            });

        $container
            ->method('getParameter')
            ->willReturnCallback(function ($parameterName) use ($parameters) {
                return $parameters[$parameterName];
            });

        return $container;
    }
}
