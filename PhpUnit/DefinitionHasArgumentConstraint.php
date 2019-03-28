<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

final class DefinitionHasArgumentConstraint extends Constraint
{
    /**
     * @var int|string
     */
    private $argumentIndex;
    private $expectedValue;
    private $checkExpectedValue;

    public function __construct($argumentIndex, $expectedValue, bool $checkExpectedValue = true)
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

        $this->argumentIndex = $argumentIndex;
        $this->expectedValue = $expectedValue;
        $this->checkExpectedValue = $checkExpectedValue;
    }

    public function toString(): string
    {
        if (is_string($this->argumentIndex)) {
            return sprintf(
                'has an argument named "%s" with the given value',
                $this->argumentIndex
            );
        }

        return sprintf(
            'has an argument with index %d with the given value',
            $this->argumentIndex
        );
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): bool
    {
        if (!($other instanceof Definition)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\Definition'
            );
        }

        if (!$this->evaluateArgumentIndex($other, $returnResult)) {
            return false;
        }

        if ($this->checkExpectedValue && !$this->evaluateArgumentValue($other, $returnResult)) {
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

            if (is_string($this->argumentIndex)) {
                $message = sprintf('The definition has no argument named "%s"', $this->argumentIndex);
            } else {
                $message = sprintf('The definition has no argument with index %d', $this->argumentIndex);
            }

            $this->fail($definition, $message);
        }

        return true;
    }

    private function evaluateArgumentValue(Definition $definition, bool $returnResult): bool
    {
        $actualValue = $definition->getArgument($this->argumentIndex);

        $constraint = new IsEqual($this->expectedValue);

        if (!$constraint->evaluate($actualValue, '', true)) {
            if ($returnResult) {
                return false;
            }

            if (is_string($this->argumentIndex)) {
                $message = sprintf(
                    'The value of argument named "%s" (%s) is not equal to the expected value (%s)',
                    $this->argumentIndex,
                    $this->exporter()->export($actualValue),
                    $this->exporter()->export($this->expectedValue)
                );
            } else {
                $message = sprintf(
                    'The value of argument with index %d (%s) is not equal to the expected value (%s)',
                    $this->argumentIndex,
                    $this->exporter()->export($actualValue),
                    $this->exporter()->export($this->expectedValue)
                );
            }

            $this->fail($definition, $message);
        }

        return true;
    }
}
