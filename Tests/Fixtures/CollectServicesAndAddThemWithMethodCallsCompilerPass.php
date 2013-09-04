<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CollectServicesAndAddThemWithMethodCallsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('collecting_service_id')) {
            return;
        }

        $service = $container->getDefinition('collecting_service_id');

        foreach ($container->findTaggedServiceIds('collect_with_method_calls') as $serviceId => $tags) {
            $service->addMethodCall('add', array(new Reference($serviceId)));
        }
    }
}
