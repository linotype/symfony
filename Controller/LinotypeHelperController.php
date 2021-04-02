<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Core\Service\LinotypeConfig;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeHelperController extends AbstractController
{   
    
    /**
     * Linotype helper
     * @Route("/linotype", name="helper")
     */
    public function helper( LinotypeLoader $linotype, Request $request ): Response
    {
        LinotypeConfig::setContext([
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

        return $linotype->render('helper');
    }
    
}
