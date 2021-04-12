<?php
 
namespace Linotype\Bundle\LinotypeBundle\Core;

use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeOptionRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
use Linotype\Core\LinotypeCore;
use Symfony\Component\DependencyInjection\ContainerInterface;


class Linotype
{
    
    private $container;

    private $config;

    private $context;

    private $loader;

    private $logs;

    private $projectDir;

    public function __construct( ContainerInterface $container, LinotypeCore $linotype, LinotypeMetaRepository $metaRepo, LinotypeTemplateRepository $templateRepo, LinotypeOptionRepository $optionRepo ) 
    {
        $this->container = $container;
        $this->projectDir = $this->container->getParameter('kernel.project_dir');
        $linotype->registerDoctrineMetaRepository($metaRepo);
        $linotype->registerDoctrineTemplateRepository($templateRepo);
        $linotype->registerDoctrineOptionRepository($optionRepo);
        $this->config = $linotype->getConfig();
    }

    public function getDir()
    {
        return $this->projectDir . '/linotype';
    }

    public function log( $title, $value = null )
    {
        $this->logs[] = [ 'title' => $title, 'value' => $value ];
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext($context = [])
    {
        $this->context = $context;

        return $this->context;
    }

}
