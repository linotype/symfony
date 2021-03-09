<?php

namespace Linotype\Bundle\SymfonyBundle\DependencyInjection;

use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Linotype\Bundle\SymfonyBundle\Core\Linotype;
use Linotype\Bundle\SymfonyBundle\DataCollector\LinotypeCollector;
use Linotype\Bundle\SymfonyBundle\Service\MyService;

class LinotypeExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        
        $loader = new XmlFileLoader($container, new FileLocator( dirname( __DIR__ ) .'/Resources/config' ));
        $loader->load('services.xml');

        // $loaderCustom = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        // $loaderCustom->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        
        // $definition = $container->getDefinition('linotype.my_service');
        // $definition->setArgument(0, $config['debug']);
        // $definition->setArgument(1, $config['preview']);

        // $definition = $container->getDefinition('linotype.config');
        // $definition->setArgument(0, $container->getParameter('kernel.project_dir') );

        // $config['rootDir'] = $container->getParameter('kernel.project_dir');
        // $config['linotypeDir'] = $config['rootDir'] . '/linotype';


    }

    public function getAlias()
    {
        return 'linotype';
    }

}