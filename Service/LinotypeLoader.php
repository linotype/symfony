<?php

namespace Linotype\Bundle\LinotypeBundle\Service;

use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class LinotypeLoader
{

    private $twig;

    function __construct( ContainerInterface $container, Environment $twig, Linotype $linotype )
    { 
        $this->linotype = $linotype;

        $this->config = $this->linotype->getConfig();

        $this->theme = $this->config->getCurrent()->getTheme();
        $this->index = $this->theme ? $this->theme->getInfo()->getTemplate() : '@Linotype/index.twig';

        $this->container = $container;
        $this->twig = $twig;
    }

    public function render( string $interface = 'index', array $context = [], Response $response = null ): Response
    {

        if ( isset( $this->index ) && $this->index ) {

            switch( $interface ) {
                case 'index':
                    $template = $this->index;
                    break;
                case 'helper':
                    $template = '@Linotype/Helper/helper.twig';
                    break;
                case 'helper_list':
                    $template = '@Linotype/Helper/helper-list.twig';
                    break;
                case 'helper_view':
                    $template = '@Linotype/Helper/helper-view.twig';
                    break;
                case 'admin':
                    $template = '@Linotype/Admin/admin.twig';
                    break;
                case 'admin_edit':
                    $template = '@Linotype/Admin/admin-edit.twig';
                    break;
                case 'admin_block_edit':
                    $template = '@Linotype/Admin/admin-block-edit.twig';
                    break;
                case 'admin_new':
                    $template = '@Linotype/Admin/admin-new.twig';
                    break;
                default:
                    $template = $interface;
                    break;
            }

            $content = $this->renderView( $template, $context );

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