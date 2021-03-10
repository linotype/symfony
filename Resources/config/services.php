<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
            ->autowire()     
            ->autoconfigure() 
    ;

    if( file_exists( __DIR__  . './../../../../linotype/Helper' ) ) {
        $services->load('Linotype\\Helper\\', './../../../../linotype/Helper/*');
    }

};