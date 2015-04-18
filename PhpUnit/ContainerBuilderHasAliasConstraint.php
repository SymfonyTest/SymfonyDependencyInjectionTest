<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use SebastianBergmann\Exporter\Exporter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerBuilderHasAliasConstraint extends \PHPUnit_Framework_Constraint
{
    private $aliasId;
    private $expectedServiceId;
    protected $exporter;

    public function __construct($aliasId, $expectedServiceId = null)
    {
        if (!is_string($aliasId)) {
            throw new \InvalidArgumentException('The $aliasId argument should be a string');
        }

        if ($expectedServiceId !== null && !is_string($expectedServiceId)) {
            throw new \InvalidArgumentException('The $expectedServiceId argument should be a string');
        }

        $this->aliasId = $aliasId;
        $this->expectedServiceId = $expectedServiceId;
        $this->exporter = new Exporter;
    }

    public function toString()
    {
        return 'has an alias "' . $this->aliasId . '" for service "' . $this->expectedServiceId . '"';
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof ContainerBuilder)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\ContainerBuilder'
            );
        }

        if (!$this->evaluateAliasId($other, $returnResult)) {
            return false;
        }

        if ($this->expectedServiceId !== null && !$this->evaluateServiceId($other, $returnResult)) {
            return false;
        }

        return true;
    }

    private function evaluateAliasId(ContainerBuilder $containerBuilder, $returnResult)
    {
        if (!$containerBuilder->hasAlias($this->aliasId)) {
            if ($returnResult) {
                return false;
            }

            $this->fail(
                $containerBuilder,
                sprintf(
                    'The container builder has no alias "%s"',
                    $this->aliasId
                )
            );
        }

        return true;
    }

    private function evaluateServiceId(ContainerBuilder $containerBuilder, $returnResult)
    {
        $alias = $containerBuilder->getAlias($this->aliasId);

        /*
         * The aliases service id can be retrieved by casting the alias to a string,
         * see Alias::__toString()
         */
        $actualServiceId = (string) $alias;

        $constraint = new \PHPUnit_Framework_Constraint_IsEqual(strtolower($this->expectedServiceId));
        if (!$constraint->evaluate($actualServiceId, '', true)) {
            if ($returnResult) {
                return false;
            }

            $this->fail($containerBuilder, sprintf(
                '"%s" is not an alias for "%s", but for "%s"',
                $this->aliasId,
                $this->expectedServiceId,
                $actualServiceId
            ));
        }

        return true;
    }
}
