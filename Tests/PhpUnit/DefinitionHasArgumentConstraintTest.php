<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\DefinitionHasArgumentConstraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class DefinitionHasArgumentConstraintTest extends TestCase
{
    /**
     * @test
     * @dataProvider definitionProvider
     */
    public function match(Definition $definition, $argumentIndex, $expectedValue, $shouldMatch): void
    {
        $constraint = new DefinitionHasArgumentConstraint($argumentIndex, $expectedValue);

        $this->assertSame($shouldMatch, $constraint->evaluate($definition, '', true));
    }

    public function definitionProvider()
    {
        $definitionWithNoArguments = new Definition();

        $definitionWithArguments = new Definition();
        $rightValue = 'the right value';
        $wrongValue = 'the wrong value';
        $arguments = [0 => 'first argument', 1 => $rightValue];
        $definitionWithArguments->setArguments($arguments);

        $parentServiceId = 'parent_service_id';
        if (class_exists(ChildDefinition::class)) {
            $decoratedDefinitionWithArguments = new ChildDefinition($parentServiceId);
        } else {
            $decoratedDefinitionWithArguments = new DefinitionDecorator($parentServiceId);
        }

        $decoratedDefinitionWithArguments->setArguments([0 => 'first argument', 1 => $wrongValue]);
        $decoratedDefinitionWithArguments->replaceArgument(1, $rightValue);

        return [
            // the definition has no second argument
            [$definitionWithNoArguments, 1, $rightValue, false],
            // the definition has a second argument, but with the wrong value
            [$definitionWithNoArguments, 1, $wrongValue, false],
            // the definition has a second argument with the right value
            [$definitionWithArguments, 1, $rightValue, true],
            // the definition is a decorated definition
            [$decoratedDefinitionWithArguments, 1, $rightValue, true],
        ];
    }

    /**
     * @test
     * @dataProvider invalid_definition_indexes
     *
     * @param mixed  $argument
     * @param string $exceptionMessage
     */
    public function validates_definitionIndex($argument, $exceptionMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new DefinitionHasArgumentConstraint($argument, 0);
    }

    /**
     * @return \Generator
     */
    public function invalid_definition_indexes()
    {
        yield [
            new \stdClass(), 'Expected either a string or a positive integer for $argumentIndex.',
        ];

        yield [
            1.0, 'Expected either a string or a positive integer for $argumentIndex.',
        ];

        yield [
            '1', 'Unknown argument "1". Did you mean "$1"?',
        ];

        yield [
            'a', 'Unknown argument "a". Did you mean "$a"?',
        ];

        yield [
            '', 'A named argument must begin with a "$".',
        ];
    }

    /**
     * @test
     * @dataProvider indexed_arguments
     *
     * @param int $argumentIndex
     */
    public function supports_indexed_arguments($argumentIndex): void
    {
        $expectedValue = 'bar';

        $constraint = new DefinitionHasArgumentConstraint($argumentIndex, $expectedValue);
        $definition = new Definition(stdClass::class, array_fill(0, $argumentIndex + 1, $expectedValue));

        self::assertTrue($constraint->evaluate($definition));
        self::assertSame("has an argument with index $argumentIndex with the given value", $constraint->toString());

        $failingExpectation = $expectedValue.$expectedValue;
        $constraint = new DefinitionHasArgumentConstraint($argumentIndex, $failingExpectation);

        try {
            $constraint->evaluate($definition);
            $this->fail('The expression above should throw an exception.');
        } catch (ExpectationFailedException $e) {
            self::assertStringStartsWith(
                sprintf(
                    'The value of argument with index %d (\'%s\') is not equal to the expected value (\'%s\')',
                    $argumentIndex,
                    $expectedValue,
                    $failingExpectation
                ),
                $e->getMessage()
            );
        }
    }

    /**
     * @return \Generator
     */
    public function indexed_arguments()
    {
        // yield [0];
        yield [1];
        yield [2];
        yield [3];
    }

    /**
     * @test
     * @dataProvider named_arguments
     *
     * @param string $argument
     */
    public function supports_named_arguments($argument): void
    {
        $expectedValue = 'bar';

        $constraint = new DefinitionHasArgumentConstraint($argument, $expectedValue);
        $definition = new Definition(stdClass::class, [
            $argument => $expectedValue,
        ]);

        self::assertTrue($constraint->evaluate($definition));
        self::assertSame(sprintf('has an argument named "%s" with the given value', $argument), $constraint->toString());

        $failingExpectation = $expectedValue.$expectedValue;
        $constraint = new DefinitionHasArgumentConstraint($argument, $failingExpectation);

        try {
            $constraint->evaluate($definition);
            $this->fail('The expression above should throw an exception.');
        } catch (ExpectationFailedException $e) {
            self::assertStringStartsWith(
                sprintf(
                    'The value of argument named "%s" (\'%s\') is not equal to the expected value (\'%s\')',
                    $argument,
                    $expectedValue,
                    $failingExpectation
                ),
                $e->getMessage()
            );
        }
    }

    /**
     * @return \Generator
     */
    public function named_arguments()
    {
        yield ['$foo'];
        yield ['$bar'];
        yield ['$a'];
    }
}
