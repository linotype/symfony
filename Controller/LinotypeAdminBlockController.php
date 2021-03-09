<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeMeta;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeTemplate;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeConfig;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeAdminBlockController extends AbstractController
{   
    
    /**
     * Linotype admin: block edit
     * @Route("/admin/block/{map_id}/edit", name="admin_block_edit")
     */
    public function adminBlockEdit( LinotypeLoader $linotype, Request $request ): Response
    {

        $blocks = LinotypeConfig::getConfig('blocks');

        $block_conf = $blocks[ $request->get('map_id') ];

        //create form
        $defaultData = [];
        $formBuilder = $this->createFormBuilder($defaultData);
        
        //create fields from template context
        $fields = [];
        foreach( $block_conf['context'] as $context_key => $context ) {

            //only for dynamic values
            if ( ! isset( $context['save'] ) ) $context['save'] = 'meta';
            if ( $context['save'] !== 'static' ) {
                
                //get field data
                $fields[ $context_key ] = $context['field'];
                
                //get value if exist
                $value = '';//$metaRepo->findOneBy([ 'context_key' => $context_key ]) ? $metaRepo->findOneBy([ 'context_key' => $context_key ])->getContextValue() : '';
                
                //replace field title, desc, require from block
                $fields[ $context_key ]['title'] = isset( $context['title'] ) && $context['title'] ? $context['title'] : '';
                $fields[ $context_key ]['desc'] = isset( $context['desc'] ) && $context['desc'] ? $context['desc'] : '';
                $fields[ $context_key ]['require'] = isset( $context['require'] ) && $context['require'] ? $context['require'] : false;
                $fields[ $context_key ]['value'] = $value;

                $formBuilder->add( $context_key, TextareaType::class, [
                    'label' => ( isset( $context['title'] ) && $context['title'] ? $context['title'] : false ),
                    'help' => ( isset( $context['desc'] ) && $context['desc'] ? $context['desc'] : '' ),
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

        return $linotype->render('admin_block_edit', [
            'map_id' => $request->get('map_id'),
            'success' => $request->get('success'),
            'message' => $request->get('message'),
            'form' =>  $form_view,
            'fields' =>  $fields
        ]);

    }
    
}
