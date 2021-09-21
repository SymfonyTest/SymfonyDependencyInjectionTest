<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class DefinitionDecoratesConstraint extends Constraint
{
    private const INVALID_BEHAVIORS = [
        ContainerInterface::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE => 'RUNTIME_EXCEPTION_ON_INVALID_REFERENCE',
        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE         => 'EXCEPTION_ON_INVALID_REFERENCE',
        ContainerInterface::NULL_ON_INVALID_REFERENCE              => 'NULL_ON_INVALID_REFERENCE',
        ContainerInterface::IGNORE_ON_INVALID_REFERENCE            => 'IGNORE_ON_INVALID_REFERENCE',
        ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE      => 'IGNORE_ON_UNINITIALIZED_REFERENCE',
    ];

    private $serviceId;
    private $decoratedServiceId;
    private $renamedId;
    private $priority;
    private $invalidBehavior;

    public function __construct(string $serviceId, string $decoratedServiceId, ?string $renamedId = null, int $priority = 0, ?int $invalidBehavior = null)
    {
        $this->serviceId = $serviceId;
        $this->decoratedServiceId = $decoratedServiceId;
        $this->renamedId = $renamedId;
        $this->priority = $priority;
        $this->invalidBehavior = $invalidBehavior;
    }

    public function toString(): string
    {
        return sprintf(
            '"%s" decorates service "%s"%s with priority "%d" and "%s" behavior.',
            $this->serviceId,
            $this->decoratedServiceId,
            $this->renamedId !== null ? sprintf(' and renames it to "%s"', $this->renamedId) : '',
            $this->priority,
            self::INVALID_BEHAVIORS[$this->invalidBehavior ?? 0]
        );
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): bool
    {
        if (!($other instanceof ContainerBuilder)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerBuilder'
            );
        }

        return $this->evaluateServiceDefinition($other, $returnResult);
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

        $definition = $containerBuilder->findDefinition($this->serviceId);

        $decorated = $definition->getDecoratedService();

        if ($decorated === null) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has a service "%s", but it does not decorate any service',
                    $this->serviceId
                )
            );
        }

        if ($decorated[0] !== $this->decoratedServiceId) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has a decorator service "%s", but it does decorate service "%s".',
                    $this->serviceId,
                    $decorated[0]
                )
            );
        }

        if ($decorated[1] !== $this->renamedId) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has a decorator service "%s", but it does not rename decorated service to "%s".',
                    $this->serviceId,
                    $this->renamedId
                )
            );
        }

        if ($decorated[2] !== $this->priority) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has a decorator service "%s", but it does not decorate at expected "%d" priority.',
                    $this->serviceId,
                    $this->priority
                )
            );
        }

        if (($decorated[3] ?? null) !== $this->invalidBehavior) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has a decorator service "%s", but it does not decorate with expected "%s" behavior.',
                    $this->serviceId,
                    self::INVALID_BEHAVIORS[$this->invalidBehavior]
                )
            );
        }

        return true;
    }
}
