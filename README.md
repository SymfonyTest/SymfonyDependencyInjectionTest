# SymfonyDependencyInjectionTest

By Matthias Noback

[![Build Status](https://secure.travis-ci.org/matthiasnoback/SymfonyDependencyInjectionTest.png)](http://travis-ci.org/matthiasnoback/SymfonyDependencyInjectionTest)

This library contains several PHPUnit test case classes and many semantic [assertions](#list-of-assertions) which
you can use to functionally test your [container extensions](#testing-a-container-extension) (or "bundle extensions")
and [compiler passes](#testing-a-compiler-pass). It also provides the tools to functionally test your container
extension (or "bundle") configuration by verifying processed values from different types of configuration files.

Besides verifying their correctness, this library will also help you to adopt a TDD approach when developing
these classes.

## Installation

Using Composer:

    php composer.phar require --dev matthiasnoback/symfony-dependency-injection-test 0.*

## Usage

### Testing a container extension

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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('services.xml');

        // maybe process the configuration values in $config, then:

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
        $this->load(array('my' => array('enabled' => 'false'));

        ...
    }
}
```

To prevent duplication of required configuration values, you can provide some minimal configuration, by overriding
the ``getMinimalConfiguration()`` method of the test case.

## Testing a compiler pass

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

### Test different configuration file formats

The Symfony DependencyInjection component supports many different types of configuration loaders: Yaml, XML, and
PHP files, but also closures. When you create a ``Configuration`` class for your bundle, you need to make sure that each
of these formats is supported. Special attention needs to be given to XML files.

In order to verify that any type of configuration file will be correctly loaded by your bundle, you must install the
[SymfonyConfigTest](https://github.com/matthiasnoback/SymfonyConfigTest) library and create a test class that extends
from ``AbstractExtensionConfigurationTestCase``:

```php
<?php

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension()
    {
        return new TwigExtension();
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
```

Now inside each test method you can use the ``assertProcessedConfigurationEquals($expectedConfiguration, $sources)``
method to verify that after loading the given sources the processed configuration equals the expected array of values:

```yaml
# in Fixtures/config.yml
twig:
    extensions: ['twig.extension.foo']
```

```xml
<!-- in Fixtures/config.xml -->
<container>
    <twig:config>
        <twig:extension>twig.extension.bar</twig:extension>
    </twig:config>
</container>
```

```php
<?php
...

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    ...

    /**
     * @test
     */
    public function it_converts_extension_elements_to_extensions()
    {
        $expectedConfiguration = array(
            'extensions' => array('twig.extension.foo', 'twig.extension.bar')
        );

        $sources = array(
            __DIR__ . '/Fixtures/config.yml',
            __DIR__ . '/Fixtures/config.xml',
        )

        $this->assertProcessedConfigurationEquals($expectedConfiguration, $sources);
    }
}
```

## List of assertions

These are the available semantic assertions for each of the test cases shown above:

<dl>
<dt><code>assertContainerBuilderHasService($serviceId)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id.</dd>
<dt><code>assertContainerBuilderHasService($serviceId, $expectedClass)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id and class.</dd>
<dt><code>assertContainerBuilderNotHasService($serviceId)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test does not have a service definition with the given id.</dd>
<dt><code>assertContainerBuilderHasSyntheticService($serviceId)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a synthetic service with the given id.</dd>
<dt><code>assertContainerBuilderHasAlias($aliasId)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has an alias.</dd>
<dt><code>assertContainerBuilderHasAlias($aliasId, $expectedServiceId)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has an alias and that it is an alias for the given service id.</dd>
<dt><code>assertContainerBuilderHasParameter($parameterName)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a parameter.</dd>
<dt><code>assertContainerBuilderHasParameter($parameterName, $expectedParameterValue)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a parameter and that its value is the given value.</dd>
<dt><code>assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, $argumentIndex)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id, which has an argument at
the given index.</dd>
<dt><code>assertContainerBuilderHasServiceDefinitionWithArgument($serviceId, $argumentIndex, $expectedValue)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id, which has an argument at
the given index, and its value is the given value.</dd>
<dt><code>assertContainerBuilderHasServiceDefinitionWithMethodCall($serviceId, $method, array $arguments = array())</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id, which has a method call to
the given method with the given arguments.</dd>
<dt><code>assertContainerBuilderHasServiceDefinitionWithTag($serviceId, $tag, array $attributes = array())</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id, which has the given tag with the given arguments.</dd>
<dt><code>assertContainerBuilderHasServiceDefinitionWithParent($serviceId, $parentServiceId)</code></dt>
<dd>Assert that the <code>ContainerBuilder</code> for this test has a service definition with the given id which is a decorated service and it has the given parent service.</dd>
</dl>

## Available methods to set up container

In all test cases shown above, you have access to some methods to set up the
container:

<dl>
<dt><code>setDefinition($serviceId, $definition)</code></dt>
<dd>Set a definition. The second parameter is a <code>Definition</code> class</dd>
<dt><code>registerDefinition($serviceId, $class)</code></dt>
<dd>A shortcut for <code>setDefinition</code>. It returns a <code>Definition</code> object that can be modified if necessary.</dd>
<dt><code>setParameter($parameterId, $parameterValue)</code></dt>
<dd>Set a parameter.</dd>
</dl>
