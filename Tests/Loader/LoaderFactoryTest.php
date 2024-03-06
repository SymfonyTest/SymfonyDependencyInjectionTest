<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Loader;

use Matthias\SymfonyDependencyInjectionTest\Loader\LoaderFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LoaderFactoryTest extends TestCase
{
    #[Test]
    #[DataProvider('fileProvider')]
    public function it_creates_the_appropriate_file_loader_based_on_the_extension($file, $expectedClass): void
    {
        $factory = new LoaderFactory();
        $loader = $factory->createLoaderForSource($this->createMockContainerBuilder(), $file);

        $this->assertInstanceOf($expectedClass, $loader);
    }

    #[Test]
    public function it_creates_a_closure_loader_when_source_is_a_closure(): void
    {
        $source = function (): void {
        };
        $factory = new LoaderFactory();

        $loader = $factory->createLoaderForSource($this->createMockContainerBuilder(), $source);
        $this->assertInstanceOf(ClosureLoader::class, $loader);
    }

    public static function fileProvider()
    {
        return [
            ['file.xml', XmlFileLoader::class],
            ['file.yml', YamlFileLoader::class],
            ['file.yaml', YamlFileLoader::class],
            ['file.php', PhpFileLoader::class],
        ];
    }

    private function createMockContainerBuilder(): MockObject&ContainerBuilder
    {
        return $this->createMock(ContainerBuilder::class);
    }
}
