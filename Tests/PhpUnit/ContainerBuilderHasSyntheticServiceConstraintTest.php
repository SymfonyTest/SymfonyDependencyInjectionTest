<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasSyntheticServiceConstraint;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ContainerBuilderHasSyntheticServiceConstraintTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp(): void
    {
        $this->containerBuilder = new ContainerBuilder();
    }

    #[Test]
    public function it_fails_if_the_service_definition_is_a_regular_definition(): void
    {
        $this->containerBuilder->setDefinition('synthetic_service', new Definition());

        $constraint = new ContainerBuilderHasSyntheticServiceConstraint('synthetic_service');

        $this->assertConstraintFails($constraint);
    }

    #[Test]
    public function it_does_not_fail_if_the_synthetic_service_definition_exists(): void
    {
        $this->containerBuilder->setDefinition('synthetic_service', $this->createSyntheticDefinition());

        $constraint = new ContainerBuilderHasSyntheticServiceConstraint('synthetic_service');

        $this->assertConstraintPasses($constraint);
    }

    #[Test]
    public function it_does_not_fail_if_the_synthetic_service_has_been_provided_already(): void
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

    private function assertConstraintFails(Constraint $constraint): void
    {
        $this->assertFalse($constraint->evaluate($this->containerBuilder, '', true));
    }

    private function assertConstraintPasses(Constraint $constraint): void
    {
        $this->assertTrue($constraint->evaluate($this->containerBuilder, '', true));
    }
}
