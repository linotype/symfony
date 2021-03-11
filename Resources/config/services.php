<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
            ->autowire()     
            ->autoconfigure() 
    ;

    $helperDir = __DIR__  . '/../../../../../linotype/Helper';
    
    if ( file_exists( $helperDir ) ) {
        $services->load('Linotype\\Helper\\', $helperDir . '/*' );
    }

};