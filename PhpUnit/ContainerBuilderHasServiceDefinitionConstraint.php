<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ContainerBuilderHasServiceDefinitionConstraint extends Constraint
{
    private $serviceId;
    private $expectedClass;
    private $checkExpectedClass;

    public function __construct(
        string $serviceId,
        ?string $expectedClass = null,
        bool $checkExpectedClass = true
    ) {
        $this->serviceId = $serviceId;
        $this->expectedClass = $expectedClass;
        $this->checkExpectedClass = $checkExpectedClass;
    }

    public function toString(): string
    {
        return sprintf(
            'has a service definition "%s" with class "%s"',
            $this->serviceId,
            $this->expectedClass
        );
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): bool
    {
        if (!($other instanceof ContainerBuilder)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerBuilder'
            );
        }

        if (!$this->evaluateServiceDefinition($other, $returnResult)) {
            return false;
        }

        if ($this->checkExpectedClass && !$this->evaluateClass($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateServiceDefinition(ContainerBuilder $containerBuilder, bool $returnResult): bool
    {
        if (!$containerBuilder->has($this->serviceId)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has no service "%s"',
                    $this->serviceId
                )
            );
        }

        return true;
    }

    private function evaluateClass(ContainerBuilder $containerBuilder, bool $returnResult): bool
    {
        $definition = $containerBuilder->findDefinition($this->serviceId);

        $actualClass = $containerBuilder->getParameterBag()->resolveValue($definition->getClass());

        $constraint = new IsEqual($this->expectedClass);

        if (!$constraint->evaluate($actualClass, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($containerBuilder, sprintf(
                'The class of the service definition of "%s" (%s) does not match the expected value (%s)',
                $this->serviceId,
                $this->exporter()->export($actualClass),
                $this->exporter()->export($this->expectedClass)
            ));
        }

        return true;
    }
}
