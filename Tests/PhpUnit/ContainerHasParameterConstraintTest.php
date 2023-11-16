<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

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

    public static function containerBuilderProvider()
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

    private function createMockContainerWithParameters(array $parameters)
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects(self::any())
            ->method('hasParameter')
            ->willReturnCallback(function ($parameterName) use ($parameters) {
                return array_key_exists($parameterName, $parameters);
            });

        $container
            ->expects(self::any())
            ->method('getParameter')
            ->willReturnCallback(function ($parameterName) use ($parameters) {
                return $parameters[$parameterName];
            });

        return $container;
    }
}
