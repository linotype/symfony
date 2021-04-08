<?php

namespace Linotype\Bundle\LinotypeBundle\DependencyInjection;

use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class LinotypeExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        
        $loader = new XmlFileLoader($container, new FileLocator( dirname( __DIR__ ) .'/Resources/config' ));
        $loader->load('services.xml');

        $loaderCustom = new PhpFileLoader($container, new FileLocator( dirname( __DIR__ ) .'/Resources/config' ));
        $loaderCustom->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        
    }

    public function getAlias()
    {
        return 'linotype';
    }

}