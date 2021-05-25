<?php
 
namespace Linotype\Bundle\LinotypeBundle\Core;

use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeOptionRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTranslateRepository;
use Linotype\Core\LinotypeCore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Linotype
{
    
    private $container;

    private $config;

    private $context;

    private $loader;

    private $logs;

    private $projectDir;

    public function __construct( ContainerInterface $container, RequestStack $request, LinotypeCore $linotype, LinotypeMetaRepository $metaRepo, LinotypeTemplateRepository $templateRepo, LinotypeOptionRepository $optionRepo, LinotypeTranslateRepository $translateRepo ) 
    {
        $this->container = $container;
        $this->default_locale = $this->container->getParameter('locale');
        $this->locales = $this->container->getParameter('locales');
        $this->projectDir = $this->container->getParameter('kernel.project_dir');
        $this->locale = $request->getCurrentRequest() ? $request->getCurrentRequest()->getLocale() : $this->default_locale;
        $linotype->setLocale($this->locale);
        $linotype->registerDoctrineMetaRepository($metaRepo);
        $linotype->registerDoctrineTemplateRepository($templateRepo);
        $linotype->registerDoctrineOptionRepository($optionRepo);
        $linotype->registerDoctrineTranslateRepository($translateRepo);
        $this->config = $linotype->getConfig();
        $this->theme = $this->config->getCurrent()->getTheme();
        
        

    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getDefaultLocale()
    {
        return $this->default_locale;
    }
    public function getLocales()
    {
        return $this->locales;
    }

    public function getMenu($current_id = null)
    {
        $current_path = null;
        $menu = [ 'top'=>[], 'bottom'=>[], 'lang' => [ 'current' => '', 'list'=> [] ] ];
        foreach( $this->theme->getmap() as $menu_id => $menu_value ) {
            if ( $menu_value['type'] == 'post' ) {
                $path = '/content/' . $menu_id . '/list';
            } else {
                $path = '/content/' . $menu_id . '/edit';
            }
            $menu['top'][ $menu_id ] = [
                'name' => $menu_value['name'],
                'icon' => $menu_value['icon'],
                'path' => ( $this->locale !== $this->default_locale ? '/' . $this->locale : '' ) . '/admin' . $path,
                'current' => ( $current_id == $menu_id ? true : false )
            ];
            if ( $current_id == $menu_id ) $current_path = '/admin' . $path;
        }
        $menu['bottom'][ 'option' ] = [
            'name' => 'Options',
            'icon' => '@Linotype/Icons/settings.svg',
            'path' => ( $this->locale !== $this->default_locale ? '/' . $this->locale : '' ) . '/admin/options',
            'current' => ( $current_id == 'option' ? true : false )
        ];
        if ( $current_id == 'option' ) $current_path = '/admin/options';
        $menu['bottom'][ 'system' ] = [
            'name' => 'System',
            'icon' => '@Linotype/Icons/layers.svg',
            'path' => ( $this->locale !== $this->default_locale ? '/' . $this->locale : '' ) . '/admin/system',
            'current' => ( $current_id == 'system' ? true : false )
        ];
        if ( $current_id == 'system' ) $current_path = '/admin/system';
        $menu['bottom'][ 'user' ] = [
            'name' => 'Users',
            'icon' => '@Linotype/Icons/users.svg',
            'path' => ( $this->locale !== $this->default_locale ? '/' . $this->locale : '' ) . '/admin/user/list',
            'current' => ( $current_id == 'user' ? true : false )
        ];
        if ( $current_id == 'user' ) $current_path = '/admin/user/list';
        foreach( $this->locales as $locale_key => $locale_name ) {
            $menu['lang']['list'][ $locale_key ] = [
                'name' => $locale_name,
                'path' => ( $locale_key !== $this->default_locale ? '/' . $locale_key : '' ) . $current_path,
                'current' => ( $locale_key == $this->locale ? true : false )
            ];
        }
        $menu['lang']['current'] = $this->locales[ $this->locale ];
        return $menu;
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
