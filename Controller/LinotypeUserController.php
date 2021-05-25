<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeMeta;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeTemplate;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Doctrine\ORM\EntityManagerInterface;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeFile;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeUserRepository;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeUserController extends AbstractController
{   
    
    function __construct( Linotype $linotype, LinotypeLoader $loader, UploadableManager $uploadableManager )
    {
        $this->linotype = $linotype;
        $this->config = $this->linotype->getConfig();
        $this->current = $this->config->getCurrent();
        $this->theme = $this->current->getTheme();
        $this->map = $this->theme ? $this->theme->getMap() : [];
        $this->blocks = $this->config->getBlocks();
        $this->fields = $this->config->getFields();
        $this->helpers = $this->config->getHelpers();
        $this->modules = $this->config->getModules();
        $this->templates = $this->config->getTemplates();
        $this->themes = $this->config->getThemes();
        $this->loader = $loader;
        $this->uploadableManager = $uploadableManager;
    }

    /**
     * Linotype
     * @Route("/admin/user/list", name="linotype_user_list")
     */
    public function userList( Request $request, LinotypeUserRepository $linotypeUserRepository ): Response
    {
        $users = $linotypeUserRepository->findAll();

        $items = [];
        if ( $users ){
            foreach( $users as $user ) {

                $items[ $user->getId() ] = [
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                    'link' => [
                        'edit' => '/admin/user/edit/' . $user->getId(),
                        'view' => '/admin/user/view/' . $user->getId(),
                        'delete' => '/admin/user/delete/' . $user->getId(),
                    ]
                ];
            }
        }
        

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => 'Users', 'link' => ''];

        return $this->loader->render('@Linotype/User/list.twig', [
            'breadcrumb' => $breadcrumb,
            'menu' => $this->linotype->getMenu('user'),
            'title' => 'Users',
            'current' => '/admin/user/list',
            'map' => $this->map,
            'items' => $items,
            'link_new' => '/admin/user/new'
        ]);
    }
    
}
