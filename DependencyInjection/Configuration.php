<?php

namespace Linotype\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('linotype');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->booleanNode('debug')->defaultTrue()->info('Display debug tool')->end()
                ->booleanNode('preview')->defaultTrue()->info('Use preview data')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}