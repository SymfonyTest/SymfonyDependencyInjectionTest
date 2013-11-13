<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\SimpleExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\SimpleConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AbstractExtensionConfigurationTestCaseTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension()
    {
        return new SimpleExtension();
    }

    protected function getConfiguration()
    {
        return new SimpleConfiguration();
    }

    /**
     * @test
     */
    public function it_compares_expected_configuration_values_with_values_loaded_from_files()
    {
        $sources = array(
            __DIR__ . '/../Fixtures/simple.php',
            function (ContainerBuilder $container) {
                $container->loadFromExtension(
                    'simple',
                    array(
                        'types' => array('closure')
                    )
                );
            },
            __DIR__ . '/../Fixtures/simple.yml',
            __DIR__ . '/../Fixtures/simple.xml',
        );

        $expectedConfiguration = array('types' => array('php', 'closure', 'yml', 'xml'));

        $this->assertProcessedConfigurationEquals($expectedConfiguration, $sources);
    }
}
