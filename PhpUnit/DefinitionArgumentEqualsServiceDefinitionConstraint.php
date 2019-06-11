<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Reference;

final class DefinitionArgumentEqualsServiceDefinitionConstraint extends Constraint
{
    /**
     * @var int|string
     */
    private $argumentIndex;
    private $expectedValue;
    private $serviceId;

    public function __construct(string $serviceId, $argumentIndex, $expectedValue)
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
        $this->expectedValue = is_string($expectedValue) ? new Reference($expectedValue) : $expectedValue;
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

        if (!$actualValue instanceof Reference) {
            $this->fail(
                $definition,
                sprintf(
                    'The definition argument %s must be a Reference of %s',
                    $this->argumentIndex,
                    $this->expectedValue
                )
            );
        }

        return $returnResult & $this->expectedValue !== $actualValue;
    }
}
