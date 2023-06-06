<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasTagConstraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;

class DefinitionHasTagConstraintTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $tag, $attributes, $expectedToMatch): void
    {
        $constraint = new DefinitionHasTagConstraint($tag, $attributes);

        $this->assertSame($expectedToMatch, $constraint->evaluate($definition, '', true));
    }

    /**
     * @test
     *
     * @dataProvider definitionProvider
     */
    public function evaluateThrowsExceptionOnFailure(Definition $definition, $tag, $attributes, $expectedToMatch): void
    {
        $constraint = new DefinitionHasTagConstraint($tag, $attributes);

        if ($expectedToMatch) {
            $this->assertTrue($constraint->evaluate($definition));
        } else {
            try {
                $constraint->evaluate($definition);
                $this->fail('DefinitionHasTagConstraint doesn\'t throw expected exception');
            } catch (ExpectationFailedException $e) {
                $this->assertTrue(true, 'DefinitionHasTagConstraint throws expected exception');
            }
        }
    }

    public static function definitionProvider()
    {
        $definitionWithoutTags = new Definition();
        $definitionWithTwoTags = new Definition();

        $tag = 'some_provider';

        $attributesOfFirstTag = ['name' => 'attribute of first tag'];
        $definitionWithTwoTags->addTag($tag, $attributesOfFirstTag);

        $attributesOfSecondTag = ['name' => 'attributes of second tag'];
        $definitionWithTwoTags->addTag($tag, $attributesOfSecondTag);

        $otherAttributes = ['name' => 'some other attribute'];

        return [
            // the definition has no tags
            [$definitionWithoutTags, $tag, [], false],
            // the definition has this tag, attributes match with the first tag
            [$definitionWithTwoTags, $tag, $attributesOfFirstTag, true],
            // the definition has this tag, attributes match with the second tag
            [$definitionWithTwoTags, $tag, $attributesOfSecondTag, true],
            // the definition has this tag, but the attributes don't match
            [$definitionWithTwoTags, $tag, $otherAttributes, false],
        ];
    }

    /**
     * @test
     */
    public function it_has_a_string_representation(): void
    {
        $tag = 'tagName';
        $constraint = new DefinitionHasTagConstraint($tag, []);

        $this->assertSame('has the "'.$tag.'" tag with the given attributes', $constraint->toString());
    }
}
