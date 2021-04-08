<?php

namespace Linotype\Bundle\LinotypeBundle\Linotype\Service\Menu;

use Linotype\Bundle\LinotypeBundle\Linotype\Service\Menu\inc\MenuHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;

class MenuService extends AbstractController
{

    public function __construct( MenuHelper $helper ){
    
        $this->helper = $helper;
    }

    public function getMenuItems( $context = [] )
    {

        //return static items if exist
        if ( isset( $context['items'] ) && ! empty( $context['items'] ) ) {
            return $context['items'];
        }

        //return preset if menu_id exist
        if ( isset( $context['menu_id'] ) ) {
            switch ( $context['menu_id'] ) {

                case 'admin':
                    return [
                        'home' => [
                            'name' => $this->helper->format('home'),
                            'path' => '/',
                        ],
                        'about' => [
                            'name' => 'About',
                            'path' => '/about',
                        ],
                        'articles' => [
                            'name' => 'Articles',
                            'path' => '/articles',
                        ],
                        'test' => [
                            'name' => 'Test',
                            'path' => '/test',
                        ],
                        'contact' => [
                            'name' => 'Contact',
                            'path' => '/contact',
                        ],
                    ];
                    break;
                
                case 'loggedin':
                    return [];
                    break;

                case 'loggedout':
                    return [];
                    break;
            }
        }
    
    }

}