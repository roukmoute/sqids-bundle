<?php

declare(strict_types=1);

namespace Roukmoute\SqidsBundle\DependencyInjection;

use Sqids\Sqids;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class RoukmouteSqidsExtension extends ConfigurableExtension
{
    /**
     * @param array{alphabet: string, min_length: int, blocklist: array<int, string>|null} $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__ . '/../../Resources/config'));
        $loader->load('services.php');

        $container->getDefinition(Sqids::class)
            ->setPublic(true)
            ->setArgument(0, $mergedConfig['alphabet'])
            ->setArgument(1, $mergedConfig['min_length'])
            ->setArgument(2, $mergedConfig['blocklist'])
        ;
    }
}
