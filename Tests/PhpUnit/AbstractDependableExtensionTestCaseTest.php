<?php

namespace Matthias\DependencyInjectionTests\Test\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\DependableExtension;
use Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures\NonDependablePrependableExtension;

class AbstractDependableExtensionTestCaseTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new DependableExtension(),
            new NonDependablePrependableExtension(),
        ];
    }

    /**
     * @test
     */
    public function prepend_invoked_before_any_load(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('dependable_parameter', 'dependable value');
    }
}
