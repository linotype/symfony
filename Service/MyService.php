<?php

namespace Linotype\Bundle\SymfonyBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyService
{
    
    public function get( $debug = false, $preview = false ): String
    {
        $txt = 'My service !';
        $txt .= $debug ? ' debug: true' : ' debug: false'; 
        $txt .= $preview ? ' preview: true' : ' preview: false'; 
        return $txt;
    }
}
