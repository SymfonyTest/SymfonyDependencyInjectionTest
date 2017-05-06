<?php

namespace Matthias\DependencyInjectionTests\Test\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\MatthiasDependencyInjectionTestExtension;
use PHPUnit\Framework\ExpectationFailedException;

class AbstractPrependExtensionTestCaseTest extends AbstractExtensionTestCase
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
    public function if_prepend_invoked_it_does_not_fails()
    {
        $this->load([], true);

        $this->assertContainerBuilderHasParameter('prepend_extension_interface.successfully_invoked', 'prepended value');
    }

    /**
     * @test
     */
    public function if_prepend_is_not_invoked_it_does_not_fails()
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('prepend_extension_interface.successfully_invoked');

        $this->assertContainerBuilderHasParameter('prepend_extension_interface.successfully_invoked', 'prepended value');
    }
}
