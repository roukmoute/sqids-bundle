<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Roukmoute\SqidsBundle\Twig\SqidsExtension;
use Roukmoute\SqidsBundle\ValueResolver\SqidsValueResolver;
use Sqids\Sqids;
use Sqids\SqidsInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(Sqids::class)->public();

    $services->set(SqidsInterface::class, Sqids::class);

    $services->set(SqidsValueResolver::class)
        ->args([
            service(SqidsInterface::class),
            param('sqids.passthrough'),
            param('sqids.auto_convert'),
            param('sqids.alphabet'),
        ])
        ->tag('controller.argument_value_resolver', ['priority' => 150])
    ;

    $services->set('sqids.twig.extension', SqidsExtension::class)
        ->args([service(SqidsInterface::class)])
        ->tag('twig.extension')
    ;
};
