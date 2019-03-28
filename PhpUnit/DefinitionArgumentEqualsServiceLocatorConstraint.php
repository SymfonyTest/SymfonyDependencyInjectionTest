<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DefinitionArgumentEqualsServiceLocatorConstraint extends Constraint
{
    /**
     * @var int|string
     */
    private $argumentIndex;
    private $expectedValue;
    private $serviceId;

    public function __construct(string $serviceId, $argumentIndex, array $expectedValue)
    {
        if (!(is_string($argumentIndex) || (is_int($argumentIndex) && $argumentIndex >= 0))) {
            throw new \InvalidArgumentException('Expected either a string or a positive integer for $argumentIndex.');
        }

        if (is_string($argumentIndex)) {
            if ('' === $argumentIndex) {
                throw new \InvalidArgumentException('A named argument must begin with a "$".');
            }

            if ('$' !== $argumentIndex[0]) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown argument "%s". Did you mean "$%s"?', $argumentIndex, $argumentIndex)
                );
            }
        }

        $this->serviceId = $serviceId;
        $this->argumentIndex = $argumentIndex;
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
        if (is_string($this->argumentIndex)) {
            return sprintf(
                'has an argument named "%s" with the ServiceLocator',
                $this->argumentIndex
            );
        }

        return sprintf(
            'has an argument with index %d with the ServiceLocator',
            $this->argumentIndex
        );
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): bool
    {
        if (!($other instanceof ContainerBuilder)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerBuilder'
            );
        }

        if (!$this->evaluateArgumentIndex($other->findDefinition($this->serviceId), $returnResult)) {
            return false;
        }

        if (!$this->evaluateArgumentValue($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateArgumentIndex(Definition $definition, bool $returnResult): bool
    {
        try {
            $definition->getArgument($this->argumentIndex);
        } catch (OutOfBoundsException $exception) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf('The definition has no argument with index %s', $this->argumentIndex)
            );
        }

        return true;
    }

    private function evaluateArgumentValue(ContainerBuilder $container, bool $returnResult): bool
    {
        $definition = $container->findDefinition($this->serviceId);
        $actualValue = $definition->getArgument($this->argumentIndex);
        $serviceLocatorDef = $actualValue;

        if ($actualValue instanceof Reference) {
            $serviceLocatorDef = $container->findDefinition((string) $actualValue);
        } elseif (!($actualValue instanceof Definition)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The value of argument with index %s (%s) was expected to an instance of Symfony\Component\DependencyInjection\Reference or \Symfony\Component\DependencyInjection\Definition',
                    $this->argumentIndex,
                    $this->exporter()->export($actualValue)
                )
            );
        }

        if (!is_a($serviceLocatorDef->getClass(), ServiceLocator::class, true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The referenced service class of argument with index %s (%s) was expected to be an instance of Symfony\Component\DependencyInjection\ServiceLocator',
                    $this->argumentIndex,
                    $this->exporter()->export($serviceLocatorDef->getClass())
                )
            );
        }

        // Service locator was provided as context (and therefor a factory)
        if (isset($serviceLocatorDef->getFactory()[1])) {
            $serviceLocatorDef = $container->findDefinition((string) $serviceLocatorDef->getFactory()[0]);
        }

        return $this->evaluateServiceDefinition($serviceLocatorDef, $definition, $returnResult);
    }

    private function evaluateServiceDefinition(
        Definition $serviceLocatorDef,
        Definition $definition,
        bool $returnResult
    ): bool {
        $actualValue = $serviceLocatorDef->getArgument(0);
        $constraint = new IsEqual($this->expectedValue);

        if (!$constraint->evaluate($actualValue, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The value of argument with index %s (%s) does not equal to the expected ServiceLocator service-map (%s)',
                    $this->argumentIndex,
                    $this->exporter()->export($actualValue),
                    $this->exporter()->export($this->expectedValue)
                )
            );
        }

        return true;
    }
}
