<?php

namespace Linotype\Bundle\SymfonyBundle\Service;

use Linotype\Bundle\SymfonyBundle\Service\LinotypeConfig;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class LinotypeLoader
{

    private $twig;

    function __construct( ContainerInterface $container, Environment $twig, LinotypeConfig $linotypeConfig )
    { 
        $this->linotypeConfig = $linotypeConfig;
        $this->container = $container;
        $this->twig = $twig;
    }

    public function render( string $interface = 'index', array $context = [], Response $response = null ): Response
    {
        
        if ( isset( LinotypeConfig::$config['current']['theme']['twig'] ) && isset( LinotypeConfig::$config['current']['theme']['twig_admin'] ) ) {

            switch( $interface ) {
                case 'index':
                    $template = LinotypeConfig::$config['current']['theme']['twig'];
                    break;
                case 'helper':
                    //$template = LinotypeConfig::$config['current']['theme']['twig_helper'];
                    $template = '@Linotype/Helper/helper.twig';
                    break;
                case 'admin':
                    //$template = LinotypeConfig::$config['current']['theme']['twig_admin'];
                    $template = '@Linotype/Admin/admin.twig';
                    break;
                case 'admin_edit':
                    //$template = LinotypeConfig::$config['current']['theme']['twig_admin_edit'];
                    $template = '@Linotype/Admin/admin-edit.twig';
                    break;
                case 'admin_block_edit':
                    //$template = LinotypeConfig::$config['current']['theme']['twig_admin_edit'];
                    $template = '@Linotype/Admin/admin-block-edit.twig';
                    break;
                case 'admin_new':
                    // $template = LinotypeConfig::$config['current']['theme']['twig_admin_new'];
                    $template = '@Linotype/Admin/admin-new.twig';
                    break;
            }

            $content = $this->renderView( $template, ['linotype' => LinotypeConfig::$config ] + $context );

            if (null === $response) {
                $response = new Response();
            }

            $response->setContent($content);

        } else {

            throw new \LogicException('Linotype has no theme template.');
        
        }

        return $response;
    }

    public function renderView( string $template, array $context = [] ): string
    {
        if ( ! $this->container->has('twig') ) {
            throw new \LogicException('You can not use the "renderView" method if the Twig Bundle is not available. Try running "composer require symfony/twig-bundle".');
        }

        return $this->twig->render( $template, $context );
        
    }

}