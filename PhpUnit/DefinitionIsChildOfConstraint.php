<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefinitionIsChildOfConstraint extends Constraint
{
    private $expectedParentServiceId;

    public function __construct($expectedParentServiceId)
    {
        parent::__construct();

        $this->expectedParentServiceId = $expectedParentServiceId;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!$other instanceof Definition) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\Definition'
            );
        }

        if (!$this->evaluateDefinitionIsDecorator($other, $returnResult)) {
            return false;
        }

        if (!$this->evaluateDefinitionHasExpectedParentService($other, $returnResult)) {
            return false;
        }

        return true;
    }

    public function toString(): string
    {
        return sprintf(
            'is a child of service "%s"',
            $this->expectedParentServiceId
        );
    }

    private function evaluateDefinitionIsDecorator(Definition $definition, $returnResult)
    {
        if (!$definition instanceof ChildDefinition && !$definition instanceof DefinitionDecorator) {
            if ($returnResult) {
                return false;
            }

            $this->fail($definition, 'The definition has no parent service');
        }

        return true;
    }

    private function evaluateDefinitionHasExpectedParentService($definition, $returnResult)
    {
        $actualParentService = $this->expectedParentServiceId;

        if ($definition->getParent() !== $actualParentService) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $definition,
                sprintf(
                    'The parent of this definition (%s) is not the expected service (%s)',
                    $actualParentService,
                    $this->expectedParentServiceId
                )
            );
        }

        return true;
    }
}
