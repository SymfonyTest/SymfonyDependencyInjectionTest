<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerHasParameterConstraint extends Constraint
{
    private $parameterName;
    private $expectedParameterValue;
    private $checkParameterValue;

    public function __construct(
        string $parameterName,
        $expectedParameterValue = null,
        bool $checkParameterValue = false
    ) {
        $this->parameterName = $parameterName;
        $this->expectedParameterValue = $expectedParameterValue;
        $this->checkParameterValue = $checkParameterValue;
    }

    public function toString(): string
    {
        return sprintf(
            'has a parameter "%s" with the given value',
            $this->parameterName
        );
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): bool
    {
        if (!($other instanceof ContainerInterface)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerInterface'
            );
        }

        if (!$this->evaluateParameterName($other, $returnResult)) {
            return false;
        }

        if ($this->checkParameterValue && !$this->evaluateParameterValue($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateParameterName(ContainerInterface $container, bool $returnResult): bool
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

    private function evaluateParameterValue(ContainerInterface $container, bool $returnResult): bool
    {
        $actualValue = $container->getParameter($this->parameterName);

        $constraint = new IsEqual($this->expectedParameterValue);

        if (!$constraint->evaluate($actualValue, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($container, sprintf(
                'The value of parameter "%s" (%s) does not match the expected value (%s)',
                $this->parameterName,
                $this->exporter()->export($actualValue),
                $this->exporter()->export($this->expectedParameterValue)
            ));
        }

        return true;
    }
}
