<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use SebastianBergmann\Exporter\Exporter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderHasServiceDefinitionConstraint extends \PHPUnit_Framework_Constraint
{
    private $serviceId;
    private $expectedClass;
    private $checkExpectedClass;
    protected $exporter;

    public function __construct($serviceId, $expectedClass = null, $checkExpectedClass = true)
    {
        if (!is_string($serviceId)) {
            throw new \InvalidArgumentException('The $serviceId argument should be a string');
        }

        if ($checkExpectedClass && !is_string($expectedClass)) {
            throw new \InvalidArgumentException('The $expectedClass argument should be a string');
        }

        $this->serviceId = $serviceId;
        $this->expectedClass = $expectedClass;
        $this->checkExpectedClass = $checkExpectedClass;
        $this->exporter = new Exporter;
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

        if ($this->checkExpectedClass && !$this->evaluateClass($other, $returnResult)) {
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

        $actualClass = $containerBuilder->getParameterBag()->resolveValue($definition->getClass());

        $constraint = new \PHPUnit_Framework_Constraint_IsEqual($this->expectedClass);

        if (!$constraint->evaluate($actualClass, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($containerBuilder, sprintf(
                'The class of the service definition of "%s" (%s) does not match the expected value (%s)',
                $this->serviceId,
                $this->exporter->export($actualClass),
                $this->exporter->export($this->expectedClass)
            ));
        }

        return true;
    }
}
