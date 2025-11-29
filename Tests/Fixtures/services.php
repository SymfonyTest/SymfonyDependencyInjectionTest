<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('loaded_service_id', \stdClass::class)

        ->set('parent_service_id', \stdClass::class)

        ->set('child_service_id')
            ->parent('parent_service_id')
            ->args([
                'first argument',
                'second argument',
            ])

        ->set('service_with_method_calls_id')
            ->call('theRightMethodName', ['first argument', 'second argument'])

        ->set('synthetic_service')
            ->synthetic();
};
