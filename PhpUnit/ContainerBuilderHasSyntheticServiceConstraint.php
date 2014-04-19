<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use SebastianBergmann\Exporter\Exporter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderHasSyntheticServiceConstraint extends \PHPUnit_Framework_Constraint
{
    private $serviceId;
    protected $exporter;

    public function __construct($serviceId)
    {
        if (!is_string($serviceId)) {
            throw new \InvalidArgumentException('The $serviceId argument should be a string');
        }

        $this->serviceId = $serviceId;
        $this->exporter = new Exporter;
    }

    public function toString()
    {
        return sprintf(
            'has a synthetic service "%s"',
            $this->serviceId
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

        if ($containerBuilder->hasDefinition($this->serviceId) || $containerBuilder->hasAlias($this->serviceId)) {
            $definition = $containerBuilder->findDefinition($this->serviceId);
            if (!$definition->isSynthetic()) {
                if ($returnResult) {
                    return false;
                }

                $this->fail(
                    $containerBuilder,
                    sprintf(
                        'The container builder has a service "%s", but it is not synthetic',
                        $this->serviceId
                    )
                );
            }
        }

        /*
         * Right now, the ContainerBuilder instance has the service, but no definition or alias for it.
         * This means that the synthetic service has been provided already using Container::set().
         */

        return true;
    }
}
