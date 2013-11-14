<?php

namespace Matthias\DependencyInjectionTests\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\CollectServicesAndAddThemWithMethodCallsCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\CollectServicesAndSetThemAsArgumentCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AbstractCompilerPassTestCaseTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CollectServicesAndAddThemWithMethodCallsCompilerPass());
        $container->addCompilerPass(new CollectServicesAndSetThemAsArgumentCompilerPass());
    }

    /**
     * @test
     */
    public function if_compiler_pass_collects_services_by_adding_method_calls_these_can_be_asserted_to_exist()
    {
        $collectingService = new Definition();
        $this->setDefinition('collecting_service_id', $collectingService);

        $collectedService1 = new Definition();
        $collectedService1->addTag('collect_with_method_calls');
        $this->setDefinition('collected_service_1', $collectedService1);

        $collectedService2 = new Definition();
        $collectedService2->addTag('collect_with_method_calls');
        $this->setDefinition('collected_service_2', $collectedService2);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'collecting_service_id',
            'add',
            array(
                new Reference('collected_service_1')
            )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'collecting_service_id',
            'add',
            array(
                new Reference('collected_service_2')
            )
        );
    }

    /**
     * @test
     */
    public function if_compiler_pass_collects_services_by_setting_constructor_argument_it_can_be_asserted_to_exist()
    {
        $collectingService = new Definition();
        $this->setDefinition('collecting_service_id', $collectingService);

        $collectedService1 = new Definition();
        $collectedService1->addTag('collect_with_argument');
        $this->setDefinition('collected_service_1', $collectedService1);

        $collectedService2 = new Definition();
        $collectedService2->addTag('collect_with_argument');
        $this->setDefinition('collected_service_2', $collectedService2);

        $this->compile();

        $expectedReferences = array(
            new Reference('collected_service_1'),
            new Reference('collected_service_2')
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('collecting_service_id', 0, $expectedReferences);
    }
}
