<?php

namespace Linotype\Bundle\LinotypeBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;

class ExtraLoader extends Loader
{

    private $isLoaded = false;

    public function __construct( Linotype $linotype ){
        $this->linotype = $linotype;
        $this->map = $linotype->getConfig()->getCurrent()->getTheme()->getMap();
    }

    public function load($resource, string $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }
        
        $routes = new RouteCollection();
        foreach( $this->map as $map_key => $map ) 
        {
            if ( isset( $map['path'] ) && $map['path']  )
            {
                $routes->add( $map_key, new Route( $map['path'], 
                    [ '_controller' => 'Linotype\Bundle\LinotypeBundle\Controller\LinotypeController::index' ]
                ));
                
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
