<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Roukmoute\SqidsBundle\ArgumentResolver\SqidsValueResolver;
use Sqids\Sqids;
use Sqids\SqidsInterface;

return function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(Sqids::class)->public();

    $services->set(SqidsInterface::class, Sqids::class);

    $services->set(SqidsValueResolver::class)
        ->args([service(SqidsInterface::class)])
        ->tag('controller.argument_value_resolver', ['priority' => 150])
    ;
};
