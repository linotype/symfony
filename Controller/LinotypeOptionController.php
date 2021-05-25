<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeFile;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeOption;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeTranslate;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeOptionRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTranslateRepository;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Linotype\Core\Render\ThemeRender;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeOptionController extends AbstractController
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
     * Linotype option
     * @Route("/admin/options", name="linotype_option")
     */
    public function options( Request $request, EntityManagerInterface $em, LinotypeOptionRepository $optionRepo, LinotypeTranslateRepository $translateRepo ): Response
    {
        $locale = $request->getLocale();
        
        if ( $request->getMethod() == 'POST' ) {

            foreach( $request->request->all() as $option_key => $option_value ) {
                
                if ( ! $option_value ) $option_value = '';

                if ( $request->files->has( $option_key ) ) {
                        
                    $file = $request->files->get( $option_key );
                    
                    if ( $file instanceof UploadedFile ) {

                        $fileEntity = new LinotypeFile();
                        
                        $em->persist($fileEntity);
    
                        $this->uploadableManager->markEntityToUpload($fileEntity, $file);

                        $em->flush();
                        
                        $option_value = $fileEntity->getId();
                    
                    }
                    
                }

                if ( $locale == 'en' ) {

                    //check if option ref exist
                    $optionEntityExist = $optionRepo->findOneBy([ 'option_key' => $option_key ]);

                    //create option if not exist
                    if ( $optionEntityExist == null ) {
                        $optionEntity = new LinotypeOption();
                    } else {
                        $optionEntity = $optionEntityExist;
                    }
                    
                    $optionEntity->setOptionKey($option_key);
                    $optionEntity->setOptionValue($option_value);
                    $em->persist($optionEntity); 

                } else {

                    $optionTransEntityExist = $translateRepo->findOneBy([ 
                        'type' => 'option',
                        'trans_id' => $option_key,
                        'lang' => $locale,
                    ]);

                    //create option if not exist
                    if ( $optionTransEntityExist == null ) {
                        $optionTransEntity = new LinotypeTranslate();
                    } else {
                        $optionTransEntity = $optionTransEntityExist;
                    }

                    $optionTransEntity->setType('option');
                    $optionTransEntity->setLang($locale);
                    $optionTransEntity->setTransId($option_key);
                    $optionTransEntity->setTransValue($option_value);
                    $em->persist($optionTransEntity);
                    
                }
            
            }
            
            $em->flush();
            
            return $this->redirectToRoute('linotype_option', [ 
                'success' => 'true'
            ]);
            
        }

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => 'Options', 'link' => ''];

        return $this->loader->render('admin_option', [
            'breadcrumb' => $breadcrumb,
            'menu' => $this->linotype->getMenu('option'),
            'map' => $this->map,
            'current' => 'option',
            'title' => 'Options',
            'success' => $request->get('success')
        ]);
    }
    
}
