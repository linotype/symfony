<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LinotypeController extends AbstractController
{   
    
    function __construct( Linotype $linotype, LinotypeLoader $loader )
    {
        $this->linotype = $linotype;
        $this->loader = $loader;
    }

    /**
     * Linotype index
     * Auto generated routes from linotype theme.yml
     */
    public function index( Request $request ): Response
    {
        
        $this->linotype->setContext([
            'route' => $request->attributes->get('_route'),
            'href' => $request->getSchemeAndHttpHost() . $request->getRequestUri(),
            'location' => $request->getRequestUri(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'port' => $request->getPort(),
            'base' => $request->getBaseUrl(),
            'pathname' => $request->getPathInfo(),
            'params' => $request->getQueryString(),
        ]);

        return $this->loader->render('index');
    }

}
