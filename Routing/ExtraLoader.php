<?php

namespace Linotype\Bundle\LinotypeBundle\Routing;

use Linotype\Bundle\LinotypeBundle\Controller\LinotypeController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;

class ExtraLoader extends Loader
{

    private $isLoaded = false;

    public function __construct( Linotype $linotype ){
        $this->linotype = $linotype;
        $this->theme = $linotype->getConfig()->getCurrent()->getTheme();
        $this->map = $this->theme ? $this->theme->getMap() : [];
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
                    [ '_controller' => 'Linotype\Bundle\LinotypeBundle\Controller\LinotypeController::index' ],
                    [ '_locale' => 'en' ],
                    [ '_locale' => 'en|fr|de' ]
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
