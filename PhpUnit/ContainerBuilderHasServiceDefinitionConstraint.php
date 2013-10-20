<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderHasServiceDefinitionConstraint extends \PHPUnit_Framework_Constraint
{
    private $serviceId;
    private $expectedClass;

    public function __construct($serviceId, $expectedClass)
    {
        $this->serviceId = $serviceId;
        $this->expectedClass = $expectedClass;
    }

    public function toString()
    {
        return sprintf(
            'has a service definition "%s" with class "%s"',
            $this->serviceId,
            $this->expectedClass
        );
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof ContainerBuilder)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerBuilder'
            );
        }

        if (!$this->evaluateServiceDefinition($other, $returnResult)) {
            return false;
        }

        if (!$this->evaluateClass($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateServiceDefinition(ContainerBuilder $containerBuilder, $returnResult)
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

    private function evaluateClass(ContainerBuilder $containerBuilder, $returnResult)
    {
        $definition = $containerBuilder->findDefinition($this->serviceId);

        $actualClass = $definition->getClass();

        $constraint = new \PHPUnit_Framework_Constraint_IsEqual($this->expectedClass);

        if (!$constraint->evaluate($actualClass, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($containerBuilder, sprintf(
                'The class of the service definition of "%s" (%s) does not match the expected value (%s)',
                $this->serviceId,
                \PHPUnit_Util_Type::export($actualClass),
                \PHPUnit_Util_Type::export($this->expectedClass)
            ));
        }

        return true;
    }
}
