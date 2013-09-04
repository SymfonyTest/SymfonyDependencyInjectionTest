<?php

namespace Matthias\DependencyInjectionTests\Test\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\MatthiasDependencyInjectionTestExtension;

class AbstractExtensionTestCaseTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new MatthiasDependencyInjectionTestExtension()
        );
    }

    /**
     * @test
     */
    public function if_load_is_successful_it_does_not_fail()
    {
        $this->load();

        // defined in services.xml
        $this->assertContainerBuilderHasService('loaded_service_id', 'stdClass');

        // manually defined parameter
        $this->assertContainerBuilderHasParameter('manual_parameter', 'parameter value');

        // manually defined service
        $this->assertContainerBuilderHasService('manual_service_id', 'stdClass');

        // manually created alias
        $this->assertContainerBuilderHasAlias('manual_alias', 'service_id');

        //
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 1, 'argument value');
    }

    /**
     * @test
     */
    public function if_service_is_undefined_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasService('undefined', 'AnyClass');
    }

    /**
     * @test
     */
    public function if_service_is_defined_but_has_another_class_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasService('manual_service_id', 'SomeOtherClass');
    }

    /**
     * @test
     */
    public function if_alias_is_not_defined_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasAlias('undefined', 'any_service_id');
    }

    /**
     * @test
     */
    public function if_alias_exists_but_for_wrong_service_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasAlias('manual_alias', 'wrong');
    }

    /**
     * @test
     */
    public function if_parameter_does_not_exist_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasParameter('undefined', 'any value');
    }

    /**
     * @test
     */
    public function if_parameter_exists_but_has_wrong_value_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasParameter('manual_parameter', 'wrong');
    }

    /**
     * @test
     */
    public function if_definition_does_not_have_argument_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 10, 'any value');
    }

    /**
     * @test
     */
    public function if_definition_has_argument_but_with_wrong_value_it_fails()
    {
        $this->load();

        $this->setExpectedException('\PHPUnit_Framework_ExpectationFailedException');

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('manual_service_id', 1, 'wrong value');
    }
}
