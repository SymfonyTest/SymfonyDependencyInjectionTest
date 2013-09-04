# SymfonyDependencyInjectionTest

By Matthias Noback

[![Build Status](https://secure.travis-ci.org/matthiasnoback/SymfonyDependencyInjectionTest.png)](http://travis-ci.org/matthiasnoback/SymfonyDependencyInjectionTest)

This library contains several base PHPUnit test case classes and many semantic [assertions](#assertions) which you can
use to functionally test your container extensions (or "bundle extensions") and compiler passes. It will also help you
you to adopt a TDD approach for developing these classes.

## Installation

Using Composer:

    php composer.phar require matthiasnoback/symfony-dependency-injection-test 0.*

## Usage

### Testing a container extension class

To test your own container extension class ``MyExtension`` create a class and extend from
``Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase``. Then implement the
``getContainerExtensions()`` method:

```php
<?php

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class MyExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new MyExtension()
        );
    }
}
```

Basically you will be testing your extension's load method, which will look something like this:

```php
<?php
class MyExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        // things you usually do in the load() method:

        // load service definitions from a file
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.xml');

        // maybe process configuration

        $container->setParameter('parameter_name', 'some value');
    }
}
```

So in the test case you should test that after loading the container, the parameter has been set correctly:

```php
<?php

class MyExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('parameter_name', 'some value');
    }
}
```

To test the effect of different configuration values, use the first argument of ``load()``:

```php
<?php

class MyExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load(array('my_bundle' => array('enabled' => 'false'));

        ...
    }
}
```

To prevent duplication of required configuration values, you can provide some minimal configuration, by overriding
the ``getMinimalConfiguration()`` method of the test case.

## Testing compiler passes

To test a compiler pass, create a test class and extend from
``Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase``. Then implement the ``registerCompilerPass()`` method:

```php
<?php

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;

class MyCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MyCompilerPass());
    }
}
```

In each test you can first set up the ``ContainerBuilder`` instance properly, depending on what your compiler pass is
expected to do. For instance you can add some definitions with specific tags you will collect. Then after the "arrange"
phase of your test, you need to "act", by calling the ``compile()``method. Finally you may enter the "assert" stage and
you should verify the correct behavior of the compiler pass by making assertions about the ``ContainerBuilder``
instance.

```php
<?php

class MyCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function if_compiler_pass_collects_services_by_adding_method_calls_these_will_exist()
    {
        $collectingService = new Definition();
        $this->setDefinition('collecting_service_id', $collectingService);

        $collectedService = new Definition();
        $collectedService->addTag('collect_with_method_calls');
        $this->setDefinition('collected_service', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'collecting_service_id',
            'add',
            array(
                new Reference('collected_service')
            )
        );
    }
}
```

### Standard test for unobtrusiveness

The ``AbstractCompilerPassTestCase`` class always executes one specific test -
``compilation_should_not_fail_with_empty_container()`` - which makes sure that the compiler pass is unobtrusive. For
example, when your compiler pass assumes the existence of a service, an exception will be thrown, and this test will
fail:

```php
<?php

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MyCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('some_service_id');

        ...
    }
}
```

So you need to always add one or more guard clauses inside the ``process()`` method:

```php
<?php

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MyCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('some_service_id')) {
            return;
        }

        $definition = $container->getDefinition('some_service_id');

        ...
    }
}
```

> #### Use ``findDefinition()`` instead of ``getDefinition()``
>
> You may not know in advance if a service id stands for a service definition, or for an alias. So instead of
> ``hasDefinition()`` and ``getDefinition()`` you may consider using ``has()`` and ``findDefinition()``. These methods
> recognize both aliases and definitions.

## List of assertions {#assertions}

These are the available semantic assertions for each of the test cases shown above:

``assertContainerBuilderHasService($serviceId, $expectedClass)``
: Assert that the ContainerBuilder for this test has a service definition with the given id and class.

``assertContainerBuilderHasAlias($aliasId, $expectedServiceId)``
: Assert that the ContainerBuilder for this test has an alias and that it is an alias for the given service id.

``assertContainerBuilderHasParameter($parameterName, $expectedParameterValue)``
: Assert that the ContainerBuilder for this test has a parameter and that its value is the given value.

``assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, $argumentIndex, $expectedValue)``
: Assert that the ContainerBuilder for this test has a service definition with the given id, which has an argument at
the given index, and its value is the given value.

``assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, $method, array $arguments)``
: Assert that the ContainerBuilder for this test has a service definition with the given id, which has a method call to
the given method with the given arguments.
