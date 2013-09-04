<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHasParameterConstraint extends \PHPUnit_Framework_Constraint
{
    private $parameterName;
    private $expectedParameterValue;

    public function __construct($parameterName, $expectedParameterValue)
    {
        $this->parameterName = $parameterName;
        $this->expectedParameterValue = $expectedParameterValue;
    }

    public function toString()
    {
        return sprintf(
            'has a parameter "%s" with the given value',
            $this->parameterName
        );
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof ContainerInterface)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerInterface'
            );
        }

        if (!$this->evaluateParameterName($other, $returnResult)) {
            return false;
        }

        if (!$this->evaluateParameterValue($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateParameterName(ContainerInterface $container, $returnResult)
    {
        if (!$container->hasParameter($this->parameterName)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($container, sprintf(
                'The container has no "%s" parameter',
                $this->parameterName
            ));
        }

        return true;
    }

    private function evaluateParameterValue(ContainerInterface $container, $returnResult)
    {
        $actualValue = $container->getParameter($this->parameterName);

        $constraint = new \PHPUnit_Framework_Constraint_IsEqual($this->expectedParameterValue);

        return $constraint->evaluate(
            $actualValue,
            sprintf(
                'The value of parameter "%s" does not match the expected value',
                $this->parameterName
            ),
            $returnResult
        );
    }
}
