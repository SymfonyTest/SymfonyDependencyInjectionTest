<?php

namespace Matthias\SymfonyDependencyInjectionTest\Tests\Fixtures;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SimpleConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('simple');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->fixXmlConfig('type', 'types')
            ->children()
                ->arrayNode('types') // values from different config files will be combined
                ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
