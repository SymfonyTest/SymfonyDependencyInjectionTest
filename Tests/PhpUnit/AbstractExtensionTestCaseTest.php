<?php

namespace Matthias\DependencyInjectionTests\Test\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\AutowiringDependencyInjectionTestExtension;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\ClassLoadedByServiceDefinition;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\MatthiasDependencyInjectionTestExtension;
use PHPUnit\Framework\ExpectationFailedException;

class AbstractExtensionTestCaseTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new MatthiasDependencyInjectionTestExtension(),
            new AutowiringDependencyInjectionTestExtension(),
        ];
    }

    /**
     * @test
     */
    public function if_load_is_successful_it_does_not_fail(): void
    {
        $this->load();

        // defined in services.xml
        $this->assertContainerBuilderHasService('loaded_service_id', 'stdClass');

        // defined in services.xml
        $this->assertContainerBuilderHasSyntheticService('synthetic_service');

        // manually defined parameter
        $this->assertContainerBuilderHasParameter('manual_parameter', 'parameter value');
        // Just check parameter exists, value will not be checked.
        $this->assertContainerBuilderHasParameter('manual_parameter');

        // manually defined service
        $this->assertContainerBuilderHasService('manual_service_id', 'stdClass');
        // Just check service exists, class will not be checked.
        $this->assertContainerBuilderHasService('manual_service_id');

        // manually created alias
        $this->assertContainerBuilderHasAlias('manual_alias', 'service_id');
        // Just check alias exists, service_id will not be checked.
        $this->assertContainerBuilderHasAlias('manual_alias');

        // manually overwritten argument
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 1, 'argument value');

        // check for existence of manually created arguments, not checking values.
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 1);
    }

    /**
     * @test
     */
    public function if_service_is_undefined_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);

        $this->assertContainerBuilderHasService('undefined', 'AnyClass');
    }

    /**
     * @test
     */
    public function if_synthetic_service_is_undefined_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('no service');

        $this->assertContainerBuilderHasSyntheticService('undefined');
    }

    /**
     * @test
     */
    public function if_service_is_defined_but_not_synthetic_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('synthetic');

        $this->assertContainerBuilderHasSyntheticService('loaded_service_id');
    }

    /**
     * @test
     */
    public function if_service_is_defined_but_has_another_class_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('stdClass');

        $this->assertContainerBuilderHasService('manual_service_id', 'SomeOtherClass');
    }

    /**
     * @test
     */
    public function if_alias_is_not_defined_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);

        $this->assertContainerBuilderHasAlias('undefined', 'any_service_id');
    }

    /**
     * @test
     */
    public function if_alias_exists_but_for_wrong_service_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('service_id');

        $this->assertContainerBuilderHasAlias('manual_alias', 'wrong');
    }

    /**
     * @test
     */
    public function if_parameter_does_not_exist_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('undefined');

        $this->assertContainerBuilderHasParameter('undefined', 'any value');
    }

    /**
     * @test
     */
    public function if_parameter_exists_but_has_wrong_value_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('parameter value');

        $this->assertContainerBuilderHasParameter('manual_parameter', 'wrong');
    }

    /**
     * @test
     */
    public function if_definition_does_not_have_argument_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('10');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 10, 'any value');
    }

    /**
     * @test
     */
    public function if_definition_has_argument_but_with_wrong_value_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 1, 'wrong value');
    }

    /**
     * @test
     */
    public function if_definition_is_decorated_and_argument_has_wrong_value_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('second argument');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('child_service_id', 1, 'wrong value');
    }

    /**
     * @test
     */
    public function if_definition_is_decorated_but_by_the_wrong_parent_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('parent_service_id');

        $this->assertContainerBuilderHasServiceDefinitionWithParent('child_service_id', 'wrong_parent_service_id');
    }

    /**
     * @test
     */
    public function if_definition_should_be_decorated_when_it_is_not_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('parent');

        $this->assertContainerBuilderHasServiceDefinitionWithParent('parent_service_id', 'any_other_service_id');
    }

    /**
     * @test
     */
    public function if_definition_should_have_a_method_call_and_it_has_not_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('wrongMethodName');

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'service_with_method_calls_id',
            'wrongMethodName',
            ['some argument']
        );
    }

    /**
     * @test
     */
    public function if_definition_should_have_a_certain_arguments_for_a_method_call_and_it_has_not_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('theRightMethodName');

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'service_with_method_calls_id',
            'theRightMethodName',
            ['a wrong argument']
        );
    }

    /**
     * @test
     */
    public function if_service_is_defined_it_fails(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);

        $this->assertContainerBuilderNotHasService('loaded_service_id');
    }

    /**
     * @test
     */
    public function if_service_is_not_defined_it_does_not_fail(): void
    {
        $this->load();

        $this->assertContainerBuilderNotHasService('undefined');
    }

    /**
     * @test
     */
    public function if_service_argument_is_a_valid_argument(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'class_without_autowiring_valid_argument_definition',
            '$loadedClass',
            ClassLoadedByServiceDefinition::class
        );
    }

    /**
     * @test
     */
    public function if_service_argument_is_a_valid_argument_even_not_being_a_reference(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'class_without_autowiring_invalid_argument_definition',
            '$loadedClass',
            ClassLoadedByServiceDefinition::class
        );
    }

    /**
     * @test
     */
    public function if_service_argument_is_a_valid_definition(): void
    {
        $this->load();

        $this->assertContainerBuilderHasServiceDefinitionWithServiceDefinitionArgument(
            'class_without_autowiring_valid_argument_definition',
            '$loadedClass',
            ClassLoadedByServiceDefinition::class
        );
    }

    /**
     * @test
     */
    public function if_service_argument_is_a_invalid_definition(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);

        $this->assertContainerBuilderHasServiceDefinitionWithServiceDefinitionArgument(
            'class_without_autowiring_invalid_argument_definition',
            '$loadedClass',
            ClassLoadedByServiceDefinition::class
        );
    }
}
