<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasTagConstraint;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasTagConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $tag, $attributes, $expectedToMatch)
    {
        $constraint = new DefinitionHasTagConstraint($tag, $attributes);

        $this->assertSame($expectedToMatch, $constraint->evaluate($definition, '', true));
    }

    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function evaluateThrowsExceptionOnFailure(Definition $definition, $tag, $attributes, $expectedToMatch)
    {
        $constraint = new DefinitionHasTagConstraint($tag, $attributes);

        if ($expectedToMatch) {
            $this->assertTrue($constraint->evaluate($definition));
        } else {
            try {
                $constraint->evaluate($definition);
                $this->fail('DefinitionHasTagConstraint doesn\'t throw expected exception');
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                $this->assertTrue(true, 'DefinitionHasTagConstraint throws expected exception');
            }
        }
    }

    public function definitionProvider()
    {
        $definitionWithoutTags = new Definition();
        $definitionWithTwoTags = new Definition();

        $tag = 'some_provider';

        $attributesOfFirstTag = array('name' => 'attribute of first tag');
        $definitionWithTwoTags->addTag($tag, $attributesOfFirstTag);

        $attributesOfSecondTag = array('name' => 'attributes of second tag');
        $definitionWithTwoTags->addTag($tag, $attributesOfSecondTag);

        $otherAttributes = array('name' => 'some other attribute');

        return array(
            // the definition has no tags
            array($definitionWithoutTags, $tag, array(), false),
            // the definition has this tag, attributes match with the first tag
            array($definitionWithTwoTags, $tag, $attributesOfFirstTag, true),
            // the definition has this tag, attributes match with the second tag
            array($definitionWithTwoTags, $tag, $attributesOfSecondTag, true),
            // the definition has this tag, but the attributes don't match
            array($definitionWithTwoTags, $tag, $otherAttributes, false),
        );
    }

    /**
     * @test
     */
    public function it_has_a_string_representation()
    {
        $tag = 'tagName';
        $constraint = new DefinitionHasTagConstraint($tag, array());

        $this->assertSame('has the "'.$tag.'" tag with the given attributes', $constraint->toString());
    }
}
