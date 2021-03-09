<?php
 
namespace Linotype\SymfonyBundle\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Linotype\SymfonyBundle\Service\LinotypeConfig;

class Linotype
{
    
    private $container;

    private $config;

    private $logs;

    private $projectDir;

    public function __construct( ContainerInterface $container, LinotypeConfig $config ) 
    {
        $this->container = $container;
        $this->config = $config;
        $this->projectDir = $this->container->getParameter('kernel.project_dir');
        $this->log('Linotype core');
        // dump( $this->config->getLinotypeSettings() );
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

    public function getConfig()
    {
        return $this->config;
    }

}
