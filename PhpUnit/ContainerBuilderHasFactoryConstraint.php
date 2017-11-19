<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContainerBuilderHasFactoryConstraint extends Constraint
{
    private $serviceId;
    private $expectedFactoryClass;
    private $expectedFactoryMethod;

    public function __construct($serviceId, $expectedFactoryClass = null, $expectedFactoryMethod = null)
    {
        parent::__construct();

        if (!is_string($serviceId)) {
            throw new \InvalidArgumentException('The $serviceId argument should be a string');
        }

        if ($expectedFactoryClass !== null && !is_string($expectedFactoryClass)) {
            throw new \InvalidArgumentException('The $expectedFactoryClass argument should be a string');
        }

        if( null !== $expectedFactoryMethod && null === $expectedFactoryClass ) {
            throw new \InvalidArgumentException('When argument $expectedFactoryMethod is set, must inform $expectedFactoryClass');
        }

        if( null !== $expectedFactoryMethod && !is_string($expectedFactoryMethod ) ) {
            throw new \InvalidArgumentException('The $expectedFactoryMethod argument should be a string');
        }

        $this->serviceId = $serviceId;
        $this->expectedFactoryClass = $expectedFactoryClass;
        $this->expectedFactoryMethod = $expectedFactoryMethod;
    }

    public function toString()
    {
        if( null === $this->expectedFactoryClass )
            return sprintf( '"%s" has factory', $this->serviceId );

        return sprintf( '"%s" has factory "@%s:%s"', $this->serviceId, $this->expectedFactoryClass, $this->expectedFactoryMethod );
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof ContainerBuilder)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerBuilder'
            );
        }

        if (!$this->evaluateServiceId($other, $returnResult)) {
            return false;
        }

        if (!$this->evaluateFactory($other, $returnResult)) {
            return false;
        }

        if ($this->expectedFactoryClass !== null && !$this->evaluateFactoryClass($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateServiceId(ContainerBuilder $containerBuilder, $returnResult)
    {
        if (!$containerBuilder->hasDefinition($this->serviceId)) {
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

    private function evaluateFactory(ContainerBuilder $containerBuilder, $returnResult)
    {
        /** @var Definition */
        $definition = $containerBuilder->getDefinition($this->serviceId);

        $factory = $definition->getFactory();

        if( !is_array( $factory ) ) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has service "%s" with not "%s" factory',
                    $this->serviceId, $this->expectedFactoryClass
                )
            );
        }

        return true;
    }

    private function evaluateFactoryClass(ContainerBuilder $containerBuilder, $returnResult)
    {
        /** @var Definition */
        $definition = $containerBuilder->getDefinition($this->serviceId);

        $factory = $definition->getFactory();

        list( $factoryDefinition, $factoryMethod ) = $factory;

        if( $factoryDefinition instanceof Reference ) {
            $factoryClass = (string)$factoryDefinition;
        } else if( is_string( $factoryDefinition ) ) {
            $factoryClass = $factoryDefinition;
        } else {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has service "%s" with not service "%s" factory',
                    $this->serviceId, $this->expectedFactoryClass
                )
            );
        }

        $constraint = new IsEqual($this->expectedFactoryClass);
        if( !$constraint->evaluate( $factoryClass, '', true ) ) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has service "%s" with not service class "%s" factory',
                    $this->serviceId, $this->expectedFactoryClass
                )
            );
        }

        if( $this->expectedFactoryMethod ) {
            $constraint = new IsEqual($this->expectedFactoryMethod);
            if( !$constraint->evaluate( $factoryMethod, '', true ) ) {
                if ($returnResult) {
                    return false;
                }

                $this->fail(
                    $containerBuilder,
                    sprintf(
                        'The container builder has service "%s" with not service class method "%s::%s" factory',
                        $this->serviceId, $this->expectedFactoryClass,
                        $this->expectedFactoryMethod
                    )
                );
            }
        }

        return true;
    }

}
