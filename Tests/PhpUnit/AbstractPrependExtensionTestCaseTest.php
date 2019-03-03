<?php

namespace Matthias\DependencyInjectionTests\Test\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\NonPrependableTestExtension;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\PrependableTestExtension;
use PHPUnit\Framework\ExpectationFailedException;

class AbstractPrependExtensionTestCaseTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new PrependableTestExtension(),
            new NonPrependableTestExtension(),
        ];
    }

    /**
     * @test
     */
    public function prepend_invoked_only_if_prepend_interface_is_implemented(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('prepend_parameter_set', 'prepended value');
    }

    /**
     * @test
     */
    public function if_prepend_interface_is_not_implemented_prepend_is_not_invoked(): void
    {
        $this->load();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('ignored_invocation');

        $this->assertContainerBuilderHasParameter('ignored_invocation', 'ignored value');
    }
}
