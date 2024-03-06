<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\PhpUnit;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\SimpleConfiguration;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\SimpleExtension;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class AbstractExtensionConfigurationTestCaseTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension(): ExtensionInterface
    {
        return new SimpleExtension();
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new SimpleConfiguration();
    }

    #[Test]
    public function it_compares_expected_configuration_values_with_values_loaded_from_files(): void
    {
        $sources = [
            __DIR__.'/../Fixtures/simple.php',
            function (ContainerBuilder $container): void {
                $container->loadFromExtension(
                    'simple',
                    [
                        'types' => ['closure'],
                    ]
                );
            },
            __DIR__.'/../Fixtures/simple.yml',
            __DIR__.'/../Fixtures/simple.xml',
        ];

        $expectedConfiguration = ['types' => ['php', 'closure', 'yml', 'xml']];

        $this->assertProcessedConfigurationEquals($expectedConfiguration, $sources);
    }
}
