<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

class DefinitionHasArgumentConstraint extends Constraint
{
    private $argumentIndex;
    private $expectedValue;
    private $checkExpectedValue;

    public function __construct($argumentIndex, $expectedValue, $checkExpectedValue = true)
    {
        parent::__construct();

        $this->argumentIndex = $argumentIndex;
        $this->expectedValue = $expectedValue;
        $this->checkExpectedValue = $checkExpectedValue;
    }

    public function toString()
    {
        return sprintf(
            'has an argument with index %s with the given value',
            $this->argumentIndex
        );
    }

    public function evaluate($other, $description = '', $returnResult = false)
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

    private function evaluateArgumentIndex(Definition $definition, $returnResult)
    {
        try {
            $definition->getArgument($this->argumentIndex);
        } catch (\Exception $exception) {
            // Older versions of Symfony throw \OutOfBoundsException
            // Newer versions throw Symfony\Component\DependencyInjection\Exception\OutOfBoundsException
            if (!($exception instanceof \OutOfBoundsException || $exception instanceof OutOfBoundsException)) {
                // this was not the expected exception
                throw $exception;
            }

            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The definition has no argument with index %d',
                    $this->argumentIndex
                )
            );
        }

        return true;
    }

    private function evaluateArgumentValue(Definition $definition, $returnResult)
    {
        $actualValue = $definition->getArgument($this->argumentIndex);

        $constraint = new IsEqual($this->expectedValue);

        if (!$constraint->evaluate($actualValue, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The value of argument with index %d (%s) is not equal to the expected value (%s)',
                    $this->argumentIndex,
                    $this->exporter->export($actualValue),
                    $this->exporter->export($this->expectedValue)
                )
            );
        }

        return true;
    }
}
