<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Linotype\Bundle\LinotypeBundle\Service\MyService;

class MyController extends AbstractController
{
    function __construct( MyService $service )
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->json([
            'MyController > Myservice' => $this->service,
         
            'id' => $request,
        ]);
    }
}