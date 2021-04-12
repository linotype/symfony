<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeMeta;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeTemplate;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Doctrine\ORM\EntityManagerInterface;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeAdminController extends AbstractController
{   
    
    function __construct( Linotype $linotype, LinotypeLoader $loader )
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
    }

    /**
     * Linotype admin
     * @Route("/admin", name="admin")
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

        return $this->loader->render('admin', [
            'map' => $this->map
        ]);
    }

    /**
     * Linotype admin
     * @Route("/admin/{map_id}/edit", name="admin_edit")
     */
    public function adminEdit( Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo ): Response
    {
     
        $map_id = $this->map[ $request->get('map_id') ]['template'];
        $template = $this->templates->findById( $map_id );
        $blocks = $this->current->render( $template );

        $data = [];
        $fields = [];
        foreach( $blocks as $block ) {
            foreach( $block->getContext()->getAll() as $context ) {
                if ( $context->getPersist() == 'meta' ) {
                    
                    $field = $context->getFieldEntity();

                    $fields[ $field->getKey() ] = $field;
                        
                    $data[ $field->getKey() ] = $request->get( $field->getKey() );

                }
            }
        }

        if ( $request->getMethod() == 'POST' ) {

            //check if template ref exist
            $templateEntityExist = $templateRepo->findOneBy(['template_key' => $request->get('map_id') ]);

            //create template database if not exist
            if( $templateEntityExist == null ) {
                $templateEntity = new LinotypeTemplate();
                $templateEntity->setTemplateKey($request->get('map_id'));
                $templateEntity->setTemplateType( 'single' );
                $em->persist($templateEntity);
                $em->flush();
            } else {
                $templateEntity = $templateEntityExist;
            }
            
            foreach( $data as $context_key => $context_value ) {
                    
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

            return $this->redirectToRoute('admin_edit', [ 
                'map_id' => $request->get('map_id'), 
                'success' => 'true'
            ]);
            
        }

        return $this->loader->render('admin_edit', [
            'form_action' => '/admin/' . $request->get('map_id') . '/edit',
            'form_data' => [
                'field_custom_name' => $request->get('field_custom_name'),
            ],
            'fields' => $fields,
            'success' => $request->get('success')
        ]);

    }

    /**
     * Linotype admin
     * @Route("/admin/{map_id}/new", name="admin_new")
     */
    public function adminNew( Request $request ): Response
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

        return $this->loader->render('admin_new');
    }
    
}
