<?php

namespace Roukmoute\SqidsBundle\DependencyInjection;

use Sqids\Sqids;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sqids');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('alphabet')
                    ->defaultValue(Sqids::DEFAULT_ALPHABET)
                    ->info('The alphabet to use for generating sqids')
                ->end()
                ->scalarNode('min_length')
                    ->defaultValue(Sqids::DEFAULT_MIN_LENGTH)
                    ->info('The minimum length of sqids to generate')
                ->end()
                ->scalarNode('blocklist')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function (string $path): array { return json_decode(file_get_contents($path), false, 512, JSON_THROW_ON_ERROR); })
                    ->end()
                    ->info('A list of sqids to block')
                    ->defaultValue(null)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
