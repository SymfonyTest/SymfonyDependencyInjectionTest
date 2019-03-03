<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DefinitionEqualsServiceLocatorConstraint extends Constraint
{
    private $expectedValue;

    public function __construct($expectedValue)
    {
        $this->expectedValue = array_map(
            function ($serviceId) {
                if (is_string($serviceId)) {
                    return new ServiceClosureArgument(new Reference($serviceId));
                }

                if (!$serviceId instanceof ServiceClosureArgument) {
                    return new ServiceClosureArgument($serviceId);
                }

                return $serviceId;
            },
            $expectedValue
        );
    }

    public function toString(): string
    {
        return sprintf('service definition is a service locator');
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): bool
    {
        if (!($other instanceof Definition)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\Definition'
            );
        }

        return $this->evaluateServiceDefinition($other, $returnResult);
    }

    private function evaluateServiceDefinition(Definition $definition, bool $returnResult): bool
    {
        if (!$this->evaluateServiceDefinitionClass($definition, $returnResult)) {
            return false;
        }

        return $this->evaluateArgumentIndex($definition, $returnResult);
    }

    private function evaluateServiceDefinitionClass(Definition $definition, bool $returnResult): bool
    {
        if (is_a($definition->getClass(), ServiceLocator::class, true)) {
            return true;
        }

        if ($returnResult) {
            return false;
        }

        $this->fail(
            $definition,
            sprintf(
                'class %s was expected as service definition class, found %s instead',
                $this->exporter()->export(ServiceLocator::class),
                $this->exporter()->export($definition->getClass())
            )
        );
    }

    private function evaluateArgumentIndex(Definition $definition, bool $returnResult): bool
    {
        $actualValue = $definition->getArgument(0);
        $constraint = new IsEqual($this->expectedValue);

        if (!$constraint->evaluate($actualValue, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The service-map %s does not equal to the expected service-map (%s)',
                    $this->exporter()->export($actualValue),
                    $this->exporter()->export($this->expectedValue)
                )
            );
        }

        return true;
    }
}
