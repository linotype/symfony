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
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeAdminController extends AbstractController
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
     * Linotype admin
     * @Route("/admin", name="linotype_admin")
     */
    public function admin( Request $request ): Response
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

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => 'Dashboard', 'link' => ''];

        return $this->loader->render('admin', [
            'breadcrumb' => $breadcrumb,
            'map' => $this->map,
            'current' => 'dashboard',
        ]);
    }

    /**
     * Linotype admin
     * @Route("/admin/content/{map_id}/edit", name="linotype_admin_edit")
     */
    public function adminEdit( Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo  ): Response
    {
     
        $map_id = $request->get('map_id');

        $template_id = $this->map[ $map_id ]['template'];
        $template_path = $this->map[ $map_id ]['path'];
        $template = $this->templates->findById( $template_id );
        $template->setKey($map_id);
        $blocks = $this->current->renderTemplate( $template );

        //check if template ref exist
        $templateEntityExist = $templateRepo->findOneBy(['template_key' => $map_id ]);
        
        //create template database if not exist
        if( $templateEntityExist == null ) {
            $templateEntity = new LinotypeTemplate();
            $templateEntity->setTemplateKey($map_id);
            $templateEntity->setTemplateType( 'single' );
            $em->persist($templateEntity);
            $em->flush();
        } else {
            $templateEntity = $templateEntityExist;
        }

        if ( $request->getMethod() == 'POST' ) {
            
            foreach( $request->request->all() as $context_key => $context_value ) {
                
                //check if template ref exist
                $metaEntityExist = $metaRepo->findOneBy([ 'context_key' => $context_key, 'template_id' => $templateEntity->getId() ]);

                //create meta if not exist
                if ( $metaEntityExist == null ) {
                    $metaEntity = new LinotypeMeta();
                } else {
                    $metaEntity = $metaEntityExist;
                }
                
                if ( $request->files->has( $context_key ) ) {
                    
                    $file = $request->files->get( $context_key );
                    
                    if ( $file instanceof UploadedFile ) {

                        $fileEntity = new LinotypeFile();
                    
                        $em->persist($fileEntity);

                        $this->uploadableManager->markEntityToUpload($fileEntity, $file);

                        $em->flush();
                    
                        $context_value = $fileEntity->getId();
                    
                    }

                }

                if ( ! $context_value ) $context_value = '';

                $metaEntity->setContextKey($context_key);
                $metaEntity->setContextValue($context_value);
                $metaEntity->setTemplateId( $templateEntity->getId() );
                $em->persist($metaEntity);
            
            }

            $em->flush();

            return $this->redirectToRoute('linotype_admin_edit', [ 
                'map_id' => $map_id, 
                'success' => 'true'
            ]);
            
        }

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => $template->getName(), 'link' => ''];

        return $this->loader->render('admin_edit', [
            'breadcrumb' => $breadcrumb,
            'template_id' => $template_id,
            'template_path' => $template_path,
            'current' => $map_id,
            'map' => $this->map,
            'title' => $template->getName(),
            'form_action' => '/admin/content/' . $map_id . '/edit',
            
            'success' => $request->get('success')
        ]);

    }

    /**
     * Linotype admin
     * @Route("/admin/content/{map_id}/edit/{id}", name="linotype_admin_edit_id")
     */
    public function adminEditId( Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo ): Response
    {
     
        $map_id = $request->get('map_id');
        $database_id = $request->get('id');
        
        $template_id = $this->map[ $map_id ]['template'];
        $template_path = str_replace('{id}', $database_id, $this->map[ $map_id ]['path'] );
        $template = $this->templates->findById( $template_id );
        $template->setKey($map_id);
        $blocks = $this->current->renderTemplate( $template );

        //check if template ref exist
        $templateEntityExist = $templateRepo->findOneBy(['id' => $database_id ]);
            
        //create template database if not exist
        if( $templateEntityExist == null ) {
            $templateEntity = new LinotypeTemplate();
            $templateEntity->setTemplateKey($map_id);
            $templateEntity->setTemplateType( 'post' );
            $em->persist($templateEntity);
            $em->flush();
        } else {
            $templateEntity = $templateEntityExist;
        }
        
        if ( $request->getMethod() == 'POST' ) {
            
            foreach( $request->request->all() as $context_key => $context_value ) {
                
                //check if template ref exist
                $metaEntityExist = $metaRepo->findOneBy([ 'context_key' => $context_key, 'template_id' => $templateEntity->getId() ]);

                //create meta if not exist
                if ( $metaEntityExist == null ) {
                    $metaEntity = new LinotypeMeta();
                } else {
                    $metaEntity = $metaEntityExist;
                }
                
                if ( $request->files->has( $context_key ) ) {
                    
                    $file = $request->files->get( $context_key );

                    if ( $file instanceof UploadedFile ) {

                        $fileEntity = new LinotypeFile();
                        
                        $em->persist($fileEntity);

                        $this->uploadableManager->markEntityToUpload($fileEntity, $file);

                        $em->flush();
                        
                        $context_value = $fileEntity->getId();

                    }
                    
                }

                if ( ! $context_value ) $context_value = '';

                $metaEntity->setContextKey($context_key);
                $metaEntity->setContextValue($context_value);
                $metaEntity->setTemplateId( $templateEntity->getId() );
                $em->persist($metaEntity);
            
            }

            $em->flush();

            return $this->redirectToRoute('linotype_admin_edit_id', [ 
                'map_id' => $map_id, 
                'id' => $templateEntity->getId(),
                'success' => 'true'
            ]);
            
        }

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => $template->getName(), 'link' => '/admin/content/' . $template->getKey() . '/list' ];
        $breadcrumb[] = ['title' => 'ID: ' . $templateEntity->getId(), 'link' => ''];

        return $this->loader->render('admin_edit', [
            'breadcrumb' => $breadcrumb,
            'template_id' => $template_id,
            'template_path' => $template_path,
            'current' => $map_id,
            'map' => $this->map,
            'title' => $template->getName(),
            'success' => $request->get('success')
        ]);

    }

    /**
     * Linotype admin
     * @Route("/admin/content/{map_id}/delete/{id}", name="linotype_admin_delete")
     */
    public function admindelete( Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo ): Response
    {
     
        $map_id = $request->get('map_id');
        $database_id = $request->get('id');
        
        $template = $templateRepo->findOneBy(['id' => $database_id ]);
        
        $template_metas = $metaRepo->findBy([ 'template_id' => $template->getId() ]);

        foreach( $template_metas as $template_meta ) {
            $em->remove($template_meta);
        }
        
        $em->remove($template);

        $em->flush();

        return $this->redirectToRoute('linotype_admin_list', [ 
            'map_id' => $map_id, 
        ]);

    }

    /**
     * Linotype admin
     * @Route("/admin/content/{map_id}/new", name="linotype_admin_new")
     */
    public function adminNew( Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo ): Response
    {

        $map_id = $request->get('map_id');
        

        $template_id = $this->map[ $map_id ]['template'];
        // $template_path = str_replace('{id}', $database_id, $this->map[ $map_id ]['path'] );
        $template = $this->templates->findById( $template_id );
        $template->setKey($map_id);

        if ( $request->getMethod() == 'POST' ) {

            //create template database if not exist
            $templateEntity = new LinotypeTemplate();
            $templateEntity->setTemplateKey($map_id);
            $templateEntity->setTemplateType( 'post' );
            $em->persist($templateEntity);
            $em->flush();
            
            foreach( $request->request->all() as $context_key => $context_value ) {
                
                //check if template ref exist
                $metaEntityExist = $metaRepo->findOneBy([ 'context_key' => $context_key, 'template_id' => $templateEntity->getId() ]);

                //create meta if not exist
                if ( $metaEntityExist == null ) {
                    $metaEntity = new LinotypeMeta();
                } else {
                    $metaEntity = $metaEntityExist;
                }
                
                if ( ! $context_value ) $context_value = '';

                $metaEntity->setContextKey($context_key);
                $metaEntity->setContextValue($context_value);
                $metaEntity->setTemplateId( $templateEntity->getId() );
                $em->persist($metaEntity);
            
            }

            $em->flush();

            return $this->redirectToRoute('linotype_admin_edit_id', [ 
                'map_id' => $map_id, 
                'id' => $templateEntity->getId(),
                'success' => 'true'
            ]);
            
        }

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => $template->getName(), 'link' => '/admin/content/' . $template->getKey() . '/list' ];
        $breadcrumb[] = ['title' => 'New', 'link' => ''];

        return $this->loader->render('admin_new', [
            'breadcrumb' => $breadcrumb,
            'template_id' => $template_id,
            // 'template_path' => $template_path,
            'current' => $map_id,
            'map' => $this->map,
            'title' => $template->getName(),
            'success' => false,
        ]);
    }

    /**
     * Linotype admin
     * @Route("/admin/content/{map_id}/list", name="linotype_admin_list")
     */
    public function adminList( Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo ): Response
    {
        $map_id = $request->get('map_id');

        $template_ids = $templateRepo->findBy(['template_key' => $map_id ]);
        

        $items = [];
        if ( $template_ids ){
            foreach( $template_ids as $template ) {

                $templateObject = $this->templates->findById( $this->map[ $template->getTemplateKey() ]['template'] );

                $blocks = $this->current->renderTemplate( $templateObject, $template->getId() );

                $preview_data = ['title'=>'No title','info'=>'','desc'=>''];
                if (isset( $this->map[ $template->getTemplateKey() ]['preview'] ) ) {
                    foreach( $this->map[ $template->getTemplateKey() ]['preview'] as $preview_type => $preview_key ) {
                        if ( $preview_key ) {
                            $preview_keys = explode('__', $preview_key );
                            if ( isset( $preview_keys[0] ) && isset( $preview_keys[1] ) ) {
                                $preview_data[$preview_type] = $blocks[ $preview_keys[0] ]->getContext()->getKey($preview_keys[1])->getValue();
                            }
                        }
                    }
                }
                $item = null;
                if (isset( $this->map[ $template->getTemplateKey() ]['preview'] ) ) {
                    foreach( $this->map[ $template->getTemplateKey() ]['preview'] as $preview_type => $preview_key ) {
                        if ( $preview_key ) {
                            $preview_keys = explode('__', $preview_key );
                            if ( isset( $preview_keys[0] ) && isset( $preview_keys[1] ) ) {
                                $item = $blocks[ $preview_keys[0] ];
                            }
                        }
                    }
                }
                
                $items[ $template->getId() ] = [
                    'title' => $preview_data['title'],
                    'info' => $preview_data['info'],
                    'desc' => $preview_data['desc'],
                    'block' => $item,
                    'link' => [
                        'edit' => '/admin/content/' . $map_id . '/edit/' . $template->getId(),
                        'view' => '/admin/content/' . $map_id . '/view/' . $template->getId(),
                        'delete' => '/admin/content/' . $map_id . '/delete/' . $template->getId(),
                    ]
                ];
            }
        }
        
        $template_id = $this->map[ $map_id ]['template'];
        $template = $this->templates->findById( $template_id );
        $template->setKey($map_id);

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => $template->getName(), 'link' => ''];

        return $this->loader->render('admin_list',[
            'breadcrumb' => $breadcrumb,
            'current' => $map_id,
            'map' => $this->map,
            'title' => $template->getName(),
            'items' => $items,
            'link_new' => '/admin/content/' . $map_id . '/new'
        ]);
    }
    
}
