<?php

namespace Roukmoute\SqidsBundle\DependencyInjection;

use Sqids\Sqids;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('roukmoute_sqids');
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
                        ->then(static function (string $path): array {
                            $contents = file_get_contents($path);
                            if ($contents === false) {
                                throw new \RuntimeException(sprintf('Could not read blocklist file: %s', $path));
                            }
                            /** @var array<int, string> */
                            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
                        })
                    ->end()
                    ->info('A list of sqids to block')
                    ->defaultValue(null)
                ->end()
                ->booleanNode('passthrough')
                    ->info('If true, sets decoded value in request attributes for the next resolver (useful for Doctrine entity conversion)')
                    ->defaultFalse()
                ->end()
                ->booleanNode('auto_convert')
                    ->info('If true, automatically attempts to decode all string parameters. Use "_sqid_" prefix in routing for explicit conversion.')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
