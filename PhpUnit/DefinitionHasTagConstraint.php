<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasTagConstraint extends Constraint
{
    private $name;
    private $attributes;

    public function __construct($name, array $attributes = array())
    {
        parent::__construct();
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!($other instanceof Definition)) {
            throw new \InvalidArgumentException(
                'Expected an instance of Symfony\Component\DependencyInjection\Definition'
            );
        }

        foreach ($other->getTags() as $tagName => $tagsAttributes) {
            if ($tagName !== $this->name) {
                continue;
            }

            foreach ($tagsAttributes as $tagAttributes) {
                if ($this->equalAttributes($this->attributes, $tagAttributes)) {
                    return true;
                }
            }

            if (!$returnResult) {
                $this->fail(
                    $other,
                    sprintf(
                        'None of the tags matched the expected name "%s" with attributes %s',
                        $this->name,
                        $this->exporter->export($this->attributes)
                    )
                );
            }

            if (!$returnResult) {
                $this->fail($other, $description);
            }

            return false;
        }

        if (!$returnResult) {
            $this->fail($other, $description);
        }

        return false;
    }

    public function toString(): string
    {
        return sprintf(
            'has the "%s" tag with the given attributes',
            $this->name
        );
    }

    private function equalAttributes($expectedAttributes, $actualAttributes)
    {
        $constraint = new IsEqual(
            $expectedAttributes
        );

        return $constraint->evaluate($actualAttributes, '', true);
    }
}
