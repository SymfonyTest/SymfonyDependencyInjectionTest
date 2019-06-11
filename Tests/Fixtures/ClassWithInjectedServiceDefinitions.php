<?php

declare(strict_types=1);

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

class ClassWithInjectedServiceDefinitions
{
    private $loadedClass;

    public function __construct(ClassLoadedByServiceDefinition $loadedClass)
    {
        $this->loadedClass = $loadedClass;
    }

    public function getLoadedClass(): ClassLoadedByServiceDefinition
    {
        return $this->loadedClass;
    }
}
