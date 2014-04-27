<?php

namespace Matthias\SymfonyDependencyInjectionTest\PhpUnit;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractCompilerPassTestCase extends AbstractContainerBuilderTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    abstract protected function registerCompilerPass(ContainerBuilder $container);

    /**
     * This test will run the compile method
     *
     * @test
     */
    public function compilation_should_not_fail_with_empty_container()
    {
        try {
            $this->compile();

            // Ensure assertion for strict mode
            $this->assertFalse($this->container->has('no_service'));
        } catch (\Exception $e) {
            $this->fail('The compiler pass should not fail with an empty container.');
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->registerCompilerPass($this->container);
    }
}
