<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasSyntheticServiceConstraint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ContainerBuilderHasSyntheticServiceConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * @test
     */
    public function it_fails_if_the_service_definition_is_a_regular_definition()
    {
        $this->containerBuilder->setDefinition('synthetic_service', new Definition());

        $constraint = new ContainerBuilderHasSyntheticServiceConstraint('synthetic_service');

        $this->assertConstraintFails($constraint);
    }

    /**
     * @test
     */
    public function it_does_not_fail_if_the_synthetic_service_definition_exists()
    {
        $this->containerBuilder->setDefinition('synthetic_service', $this->createSyntheticDefinition());

        $constraint = new ContainerBuilderHasSyntheticServiceConstraint('synthetic_service');

        $this->assertConstraintPasses($constraint);
    }

    /**
     * @test
     */
    public function it_does_not_fail_if_the_synthetic_service_has_been_provided_already()
    {
        $this->containerBuilder->setDefinition('synthetic_service', $this->createSyntheticDefinition());
        $this->containerBuilder->set('synthetic_service', new \stdClass());

        $constraint = new ContainerBuilderHasSyntheticServiceConstraint('synthetic_service');

        $this->assertConstraintPasses($constraint);
    }

    private function createSyntheticDefinition()
    {
        $syntheticDefinition = new Definition();
        $syntheticDefinition->setSynthetic(true);

        return $syntheticDefinition;
    }

    private function assertConstraintFails(\PHPUnit_Framework_Constraint $constraint)
    {
        $this->assertFalse($constraint->evaluate($this->containerBuilder, '', true));
    }

    private function assertConstraintPasses(\PHPUnit_Framework_Constraint $constraint)
    {
        $this->assertTrue($constraint->evaluate($this->containerBuilder, '', true));
    }
}
