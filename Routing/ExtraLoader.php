<?php

namespace Linotype\Bundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;
use Linotype\Bundle\Core\Linotype;

class ExtraLoader extends Loader
{

    private $isLoaded = false;

    public function __construct( Linotype $linotype ){
        $this->linotype = $linotype;
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }
        
        $routes = new RouteCollection();

        $linotypeDir = $this->linotype->getDir();

        if ( file_exists( $linotypeDir . '/linotype.yml' ) ) 
        {
            $settings = Yaml::parse( file_get_contents( $linotypeDir . '/linotype.yml' ) );
    
            if ( isset( $settings['linotype']['theme'] ) && $settings['linotype']['theme'] && file_exists( $linotypeDir . '/Theme/' . $settings['linotype']['theme'] . '.yml' ) )
            {
                $theme = Yaml::parse( file_get_contents( $linotypeDir . '/Theme/' . $settings['linotype']['theme'] . '.yml' ) );
                
                if( isset( $theme['theme']['map'] ) ) 
                {
                    foreach( $theme['theme']['map'] as $map_key => $map ) 
                    {
                        if ( isset( $map['path'] ) && $map['path']  )
                        {
                            
                            $routes->add( $map_key, new Route( $map['path'], [
                                '_controller' => 'Linotype\Bundle\Controller\LinotypeController::index',
                            ]));
                            
                            //TODO: check with parameters
                            // $routes->add( $map_key, new Route( '/extra/{parameter}', 
                            //     [
                            //         '_controller' => 'Linotype\Bundle\Controller\MyController::index',
                            //     ], 
                            //     [
                            //         'parameter' => '\d+',
                            //     ]
                            // ) );

                            //$routes->add( $map_key, $map['path'] )->controller([LinotypeController::class, 'index']);
                        }
                    }
                }
            }
    
        }

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, string $type = null)
    {
        return 'extra' === $type;
    }
}
