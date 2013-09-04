<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CollectServicesAndSetThemAsArgumentCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('collecting_service_id')) {
            return;
        }

        $service = $container->getDefinition('collecting_service_id');

        $collectedServices = array();
        foreach ($container->findTaggedServiceIds('collect_with_argument') as $serviceId => $tags) {
            $collectedServices[] = new Reference($serviceId);
        }

        $service->setArguments(array($collectedServices));
    }
}
