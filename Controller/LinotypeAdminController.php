<?php

namespace Linotype\Bundle\SymfonyBundle\Controller;

use Linotype\Bundle\SymfonyBundle\Entity\LinotypeMeta;
use Linotype\Bundle\SymfonyBundle\Entity\LinotypeTemplate;
use Linotype\Bundle\SymfonyBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\SymfonyBundle\Repository\LinotypeTemplateRepository;
use Linotype\Bundle\SymfonyBundle\Service\LinotypeConfig;
use Linotype\Bundle\SymfonyBundle\Service\LinotypeLoader;
use Doctrine\ORM\EntityManagerInterface;
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
    
    /**
     * Linotype admin
     * @Route("/admin", name="admin")
     */
    public function admin( LinotypeLoader $linotype, Request $request ): Response
    {
        LinotypeConfig::setContext([
            'admin' => [
               
            ],
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

        return $linotype->render('admin');
    }

    /**
     * Linotype admin
     * @Route("/admin/{map_id}/edit", name="admin_edit")
     */
    public function adminEdit( LinotypeLoader $linotype, Request $request, EntityManagerInterface $em, LinotypeTemplateRepository $templateRepo, LinotypeMetaRepository $metaRepo ): Response
    {
        //get configs
        $templates = LinotypeConfig::getConfig('templates');
        $current = LinotypeConfig::getConfig('current');

        //get current template
        $current_template = $current['theme']['map'][ $request->get('map_id') ]['template'];
        
        //get current template conf
        $template_conf = $templates[ $current_template ];

        //check if template ref exist
        $templateEntityExist = $templateRepo->findOneBy(['template_key' => $request->get('map_id') ]);

        //create template database if not exist
        if( $templateEntityExist == null ) {
            $templateEntity = new LinotypeTemplate();
            $templateEntity->setTemplateKey($request->get('map_id'));
            $templateEntity->setTemplateType( $template_conf['type'] );
            $em->persist($templateEntity);
            $em->flush();
        } else {
            $templateEntity = $templateEntityExist;
        }

        //create form
        $defaultData = [];
        $formBuilder = $this->createFormBuilder($defaultData);
        
        //create fields from template context
        $fields = [];
        foreach( $template_conf['context'] as $context_key => $context ) {

            
            //only for dynamic values
            if ( ! isset( $context['save'] ) ) $context['save'] = 'meta';
            if ( $context['save'] !== 'static' ) {
                
                // dump($context);

                //get field data
                $fields[ $context_key ] = $context['field'];
                
                //get value if exist
                $value = $metaRepo->findOneBy([ 'context_key' => $context_key ]) ? $metaRepo->findOneBy([ 'context_key' => $context_key ])->getContextValue() : '';
                
                //replace field title, desc, require from block
                $fields[ $context_key ]['title'] = isset( $context['title'] ) && $context['title'] ? $context['title'] : '';
                $fields[ $context_key ]['info'] = isset( $context['info'] ) && $context['info'] ? $context['info'] : '';
                $fields[ $context_key ]['require'] = isset( $context['require'] ) && $context['require'] ? $context['require'] : false;
                $fields[ $context_key ]['value'] = $value;

                $formBuilder->add( $context_key, TextareaType::class, [
                    'label' => ( isset( $context['title'] ) && $context['title'] ? $context['title'] : false ),
                    'help' => ( isset( $context['info'] ) && $context['info'] ? $context['info'] : '' ),
                    'required' => ( isset( $context['required'] ) && $context['required'] ? $context['required'] : false ),
                    'data' => $value,
                ] );
            }

        }
        
        //add submit button
        $formBuilder->add('Save', SubmitType::class );

        //get form object
        $form = $formBuilder->getForm();

        //get form on submit
        $data = [];
        $form->handleRequest($request);
        if ( $form->isSubmitted() ) {
            
            if ( $form->isValid() ) {
                $data = $form->getData();

                foreach( $data as $context_key => $context_value ) {
                    
                    //check if template ref exist
                    $metaEntityExist = $metaRepo->findOneBy([ 'context_key' => $context_key ]);

                    //create template database if not exist
                    if( $metaEntityExist == null ) {
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
                    'success' => $form->isValid() ? 'true' : 'false'
                ]);

            } else {
                
                return $this->redirectToRoute('admin_edit', [ 
                    'map_id' => $request->get('map_id'), 
                    'success' => $form->isValid() ? 'true' : 'false'
                ]);

            }

        }
        
        $form_view = $form->createView();

        foreach( $fields as $field_key => $field_value ){
            $fields[$field_key]['id'] = $field_key;
            $fields[$field_key]['form'] = [
                'name' => $form_view[$field_key]->vars['full_name'],
                'value' => $form_view[$field_key]->vars['value'],
            ];
        }
        
        LinotypeConfig::setContext([
            'admin' => [
                'route' => 'admin_edit',
                'fields' => $fields,
                'submit' => $data
            ],
            'route' => $request->get('map_id'),
            'href' => $request->getSchemeAndHttpHost() . $request->getRequestUri(),
            'location' => $request->getRequestUri(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'port' => $request->getPort(),
            'base' => $request->getBaseUrl(),
            'pathname' => $request->getPathInfo(),
            'params' => $request->getQueryString(),
        ]);

        return $linotype->render('admin_edit', [
            'map_id' => $request->get('map_id'),
            'success' => $request->get('success'),
            'message' => $request->get('message'),
            'form' =>  $form_view,
            'fields' =>  $fields
        ]);
    }

    /**
     * Linotype admin
     * @Route("/admin/{map_id}/new", name="admin_new")
     */
    public function adminNew( LinotypeLoader $linotype, Request $request ): Response
    {
        LinotypeConfig::setContext([
            'admin' => [
                'map_id' => $request->get('map_id'),
            ],
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

        return $linotype->render('admin_new');
    }
    
}
