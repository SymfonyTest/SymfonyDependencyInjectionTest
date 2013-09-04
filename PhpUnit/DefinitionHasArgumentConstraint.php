<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasArgumentConstraint extends \PHPUnit_Framework_Constraint
{
    private $argumentIndex;
    private $expectedValue;

    public function __construct($argumentIndex, $expectedValue)
    {
        $this->argumentIndex = (integer)$argumentIndex;
        $this->expectedValue = $expectedValue;
    }

    public function toString()
    {
        return sprintf(
            'has an argument with index %d with the given value',
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

        if (!$this->evaluateArgumentValue($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateArgumentIndex(Definition $definition, $returnResult)
    {
        $arguments = $definition->getArguments();

        if (!array_key_exists($this->argumentIndex, $arguments)) {
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
        $arguments = $definition->getArguments();
        $actualValue = $arguments[$this->argumentIndex];

        $constraint = new \PHPUnit_Framework_Constraint_IsEqual($this->expectedValue);

        return $constraint->evaluate(
            $actualValue,
            sprintf(
                'The value of argument with index %d is not equal to the expected value',
                $this->argumentIndex
            )
        );
    }
}
