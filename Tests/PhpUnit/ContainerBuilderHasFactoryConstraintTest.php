<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasFactoryConstraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContainerBuilderHasFactoryConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider containerBuilderProvider
     */
    public function match(ContainerBuilder $containerBuilder, $serviceId, $expectedFactoryClass, $expectedFactoryMethod, $shouldMatch)
    {
        $constraint = new ContainerBuilderHasFactoryConstraint($serviceId, $expectedFactoryClass, $expectedFactoryMethod);

        $this->assertSame($shouldMatch, $constraint->evaluate($containerBuilder, null, true));
    }

    public function containerBuilderProvider()
    {
        $rightServiceId = 'some_service_id';
        $wrongServiceId = 'other_service_id';
        $factoryClass = 'factory_class_service';
        $invalidFactoryClass = 'invalid_class_service';
        $factoryMethod = 'someMethod';
        $invalidFactoryMethod = 'invalidMethod';

        $emptyContainerBuilder = new ContainerBuilder();

        $builderWithFactory = new ContainerBuilder();
        $factoryReference = new Reference($factoryClass);

        if (ContainerBuilderHasFactoryConstraint::isLegacySymfonyDI()) {
            $builderWithFactory->register($rightServiceId)
                ->setFactoryService($factoryReference)
                ->setFactoryMethod($factoryMethod);
        } else {
            $builderWithFactory->register($rightServiceId)
                ->setFactory([$factoryReference,$factoryMethod]);
        }

        $builderWithFactory->register($wrongServiceId);

        return array(
            // the container does not have the service
            array($emptyContainerBuilder, $rightServiceId, null, null, false),

            // the container has service created by factory
            array($builderWithFactory, $rightServiceId, null, null, true ),

            // the container has service, but they has not factory
            array($builderWithFactory, $wrongServiceId, null, null, false ),

            // the container has service created by factory, but factory is invalid
            array($builderWithFactory, $rightServiceId, $invalidFactoryClass, $factoryMethod, false ),

            // the container has service created by factory, but factory method is invalid
            array($builderWithFactory, $rightServiceId, $factoryClass, $invalidFactoryMethod, false ),

            // the container has service created by factory, and whole arguments are valid
            array($builderWithFactory, $rightServiceId, $factoryClass, $factoryMethod, true ),

            // the container has service created by factory, and whole arguments are valid
            array($builderWithFactory, $wrongServiceId, $factoryClass, $factoryMethod, false ),

        );
    }

    /**
     * @test
     */
    public function it_has_a_string_representation()
    {
        $serviceId = 'service_id';
        $factoryClass = 'SomeFactoryClass';
        $factoryMethod = 'someMethod';
        $constraint = new ContainerBuilderHasFactoryConstraint($serviceId, $factoryClass, $factoryMethod);
        $this->assertSame(
            '"service_id" has factory "@SomeFactoryClass:someMethod"',
            $constraint->toString()
        );
    }

    /**
     * @test
     */
    public function it_expects_a_string_for_service_id()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');

        new ContainerBuilderHasFactoryConstraint(new \stdClass(), 'service_id');
    }

    /**
     * @test
     */
    public function it_expects_a_string_for_factory_class()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');

        new ContainerBuilderHasFactoryConstraint('service_id', new \stdClass(), '');
    }

    /**
     * @test
     */
    public function it_expects_a_string_for_factory_method()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('string');

        new ContainerBuilderHasFactoryConstraint('service_id', 'FactoryClass', new \stdClass());
    }

    /**
     * @test
     */
    public function it_expects_factory_class_be_informed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('inform');

        new ContainerBuilderHasFactoryConstraint('service_id', null, 'factoryMethod');
    }
}
