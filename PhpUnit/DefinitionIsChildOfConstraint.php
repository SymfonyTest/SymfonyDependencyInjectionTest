<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use SebastianBergmann\Exporter\Exporter;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefinitionIsChildOfConstraint extends \PHPUnit_Framework_Constraint
{
    private $expectedParentServiceId;
    protected $exporter;

    public function __construct($expectedParentServiceId)
    {
        $this->expectedParentServiceId = $expectedParentServiceId;
        $this->exporter = new Exporter;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof Definition)) {
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

    public function toString()
    {
        return sprintf(
            'is a child of service "%s"',
            $this->expectedParentServiceId
        );
    }

    private function evaluateDefinitionIsDecorator(Definition $definition, $returnResult)
    {
        if (!($definition instanceof DefinitionDecorator)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($definition, 'The definition has no parent service');
        }

        return true;
    }

    private function evaluateDefinitionHasExpectedParentService(DefinitionDecorator $definition, $returnResult)
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
