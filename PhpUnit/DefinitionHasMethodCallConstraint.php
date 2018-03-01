<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasMethodCallConstraint extends Constraint
{
    private $methodName;
    private $arguments;
    private $index;

    public function __construct($methodName, array $arguments = array(), $index = null)
    {
        if ($index !== null && !is_int($index)) {
            throw new \InvalidArgumentException(sprintf('Expected value of integer type for method call index, "%s" given.', is_object($index) ? get_class($index) : gettype($index)));
        }

        parent::__construct();

        $this->methodName = $methodName;
        $this->arguments = $arguments;
        $this->index = $index;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof Definition)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\Definition'
            );
        }

        $methodCalls = $other->getMethodCalls();

        for ($currentIndex = 0; $currentIndex < count($methodCalls); $currentIndex++) {
            list($method, $arguments) = $methodCalls[$currentIndex];

            if ($method !== $this->methodName) {
                continue;
            }

            if (null !== $this->index && $currentIndex !== $this->index) {
                continue;
            }

            if ($this->equalArguments($this->arguments, $arguments)) {
                return true;
            }
        }

        if (!$returnResult) {
            $this->fail(
                $other,
                sprintf(
                    'None of the method calls matched the expected method "%s" with arguments %s with %s invocation order index',
                    $this->methodName,
                    $this->exporter->export($this->arguments),
                    (null === $this->index) ? 'any' : sprintf('"%s"', $this->index)
                )
            );
        }

        return false;
    }

    public function toString(): string
    {
        if (null !== $this->index) {
            return sprintf(
                'has a method call to "%s" with the given arguments on invocation order index with value of %s.',
                $this->methodName,
                $this->index
            );
        }

        return sprintf(
            'has a method call to "%s" with the given arguments.',
            $this->methodName
        );
    }

    private function equalArguments($expectedArguments, $actualArguments)
    {
        $constraint = new IsEqual(
            $expectedArguments
        );

        return $constraint->evaluate($actualArguments, '', true);
    }
}
