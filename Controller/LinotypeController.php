<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
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
    public function index( Request $request, LinotypeTemplateRepository $templateRepo ): Response
    {
        
        $this->linotype->setContext([
            'route' => $request->attributes->get('_route'),
        ]);

        $map_id = $request->get('map_id');

        $id = (int) $request->get('id');

        if ( $id ) {
            $templateEntityExist = $templateRepo->findOneBy(['id' => $id]);
        } else {
            $templateEntityExist = $templateRepo->findOneBy(['template_key' => $request->attributes->get('_route')]);
        }

        return $this->loader->render('index', [
        ]);
    }

}
