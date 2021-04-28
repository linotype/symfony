<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeFile;
use Linotype\Bundle\LinotypeBundle\Entity\LinotypeOption;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeOptionRepository;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Linotype\Core\Render\ThemeRender;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeOptionController extends AbstractController
{   
    
    function __construct( Linotype $linotype, LinotypeLoader $loader, UploadableManager $uploadableManager, Profiler $profiler )
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
        $this->profiler = $profiler;
    }

    /**
     * Linotype option
     * @Route("/admin/options", name="linotype_option")
     */
    public function options( Request $request, EntityManagerInterface $em, LinotypeOptionRepository $optionRepo ): Response
    {
        
        if ( $request->getMethod() == 'POST' ) {

            foreach( $request->request->all() as $option_key => $option_value ) {
                
                //check if template ref exist
                $optionEntityExist = $optionRepo->findOneBy([ 'option_key' => $option_key ]);

                //create option if not exist
                if ( $optionEntityExist == null ) {
                    $optionEntity = new LinotypeOption();
                } else {
                    $optionEntity = $optionEntityExist;
                }
                
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

                if ( ! $option_value ) $option_value = '';

                $optionEntity->setOptionKey($option_key);
                $optionEntity->setOptionValue($option_value);
                $em->persist($optionEntity);
            
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
            'map' => $this->map,
            'current' => 'option',
            'title' => 'Options',
            'success' => $request->get('success')
        ]);
    }
    
}
