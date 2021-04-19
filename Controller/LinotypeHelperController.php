<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeHelperController extends AbstractController
{   
    
    function __construct( Linotype $linotype, LinotypeLoader $loader, Profiler $profiler )
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
        $this->profiler = $profiler;
    }

    /**
     * Linotype helper
     * @Route("/linotype", name="helper")
     */
    public function helper( Request $request ): Response
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
        
        $block = [ 'link' => '/linotype/blocks', 'items' => [] ];
        foreach( $this->blocks->getAll() as $item ) {
            $block['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/blocks/' . $item->getSlug(),
            ];
        }

        $field = [ 'link' => '/linotype/fields', 'items' => [] ];
        foreach( $this->fields->getAll() as $item ) {
            $field['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/fields/' . $item->getSlug(),
            ];
        }

        $helper = [ 'link' => '/linotype/helpers', 'items' => [] ];
        foreach( $this->helpers->getAll() as $item ) {
            $helper['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/helpers/' . $item->getSlug(),
            ];
        }

        $module = [ 'link' => '/linotype/modules', 'items' => [] ];
        foreach( $this->modules->getAll() as $item ) {
            $module['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/modules/' . $item->getSlug(),
            ];
        }

        $template = [ 'link' => '/linotype/templates', 'items' => [] ];
        foreach( $this->templates->getAll() as $item ) {
            $template['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/templates/' . $item->getSlug(),
            ];
        }

        $theme = [ 'link' => '/linotype/themes', 'items' => [] ];
        foreach( $this->themes->getAll() as $item ) {
            $theme['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/themes/' . $item->getSlug(),
            ];
        }
        
        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => 'linotype', 'link' => ''];

        return $this->loader->render('helper', [
            'breadcrumb' => $breadcrumb,
            'map' => $this->map,
            'block' => $block,
            'field' => $field,
            'helper' => $helper,
            'module' => $module,
            'template' => $template,
            'theme' => $theme,
        ]);
    }

    /**
     * Linotype helper
     * @Route("/linotype/{type}", name="helper_list")
     */
    public function list( Request $request ): Response
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

        switch( $request->get('type') ) {
            case 'blocks':
                $title = 'Blocks';
                $items = $this->blocks;
                break;
            case 'fields':
                $title = 'Fields';
                $items = $this->fields;
                break;
            case 'helpers':
                $title = 'Helpers';
                $items = $this->helpers;
                break;
            case 'modules':
                $title = 'Modules';
                $items = $this->modules;
                break;
            case 'templates':
                $title = 'Templates';
                $items = $this->templates;
                break;
            case 'themes':
                $title = 'Themes';
                $items = $this->themes;
                break;
        }
        

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => 'linotype', 'link' => '/linotype'];
        $breadcrumb[] = ['title' => $title, 'link' => ''];

        return $this->loader->render('helper_list', [
            'breadcrumb' => $breadcrumb,
            'map' => $this->map,
        ]);
    }

    /**
     * Linotype helper
     * @Route("/linotype/{type}/{slug}", name="helper_view")
     */
    public function view( Request $request ): Response
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

        $type = null;
        $title = 'error';
        $link = '/error';
        $items = [];
        $object = null;
        $object_title = '';
        
        switch( $request->get('type') ) {
            case 'blocks':
                $type = $request->get('type');
                $title = 'Blocks';
                $link = '/linotype/blocks';
                $items = $this->blocks;
                $object = $items->findBySlug( $request->get('slug') );
                $object_title = $object->getName();
                break;
            case 'fields':
                $type = $request->get('type');
                $title = 'Fields';
                $link = '/linotype/fields';
                $items = $this->fields;
                $object = $items->findBySlug( $request->get('slug') );
                $object_title = $object->getName();
                break;
            case 'helpers':
                $type = $request->get('type');
                $title = 'Helpers';
                $link = '/linotype/helpers';
                $items = $this->helpers;
                $object = $items->findBySlug( $request->get('slug') );
                $object_title = $object->getName();
                break;
            case 'modules':
                $type = $request->get('type');
                $title = 'Modules';
                $link = '/linotype/modules';
                $items = $this->modules;
                $object = $items->findBySlug( $request->get('slug') );
                $object_title = $object->getName();
                break;
            case 'templates':
                $type = $request->get('type');
                $title = 'Templates';
                $link = '/linotype/templates';
                $items = $this->templates;
                $object = $items->findBySlug( $request->get('slug') );
                $object_title = $object->getName();
                break;
            case 'themes':
                $type = $request->get('type');
                $title = 'Themes';
                $link = '/linotype/themes';
                $items = $this->themes;
                $object = $items->findBySlug( $request->get('slug') );
                $object_title = $object->getName();
                break;
        }

        $breadcrumb = [];
        $breadcrumb[] = ['title' => 'linotype.dev', 'link' => '/'];
        $breadcrumb[] = ['title' => 'linotype', 'link' => '/linotype'];
        $breadcrumb[] = ['title' => $title, 'link' => $link];
        $breadcrumb[] = ['title' => $object_title, 'link' => ''];

        return $this->loader->render('helper_view', [
            'breadcrumb' => $breadcrumb,
            'map' => $this->map,
            'type' => $type,
            'id' => $object->getSlug(),
            'name' => $object->getName(),
            'desc' => $object->getDesc(),
        ]);
    }


    /**
     * Linotype helper
     * @Route("/linotype/{type}/{slug}/viewer", name="helper_viewer")
     */
    public function viewer( Request $request ): Response
    {
        if ( $request->get('mode') == 'iframe' ) $this->profiler->disable();
   
        switch( $request->get('type') ) {
            case 'blocks':
                $type = 'block';
                $items = $this->blocks;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'fields':
                $type = 'field';
                $items = $this->fields;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'helpers':
                $type = 'helper';
                $items = $this->helpers;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'modules':
                $type = 'module';
                $items = $this->modules;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'templates':
                $type = 'template';
                $items = $this->templates;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'themes':
                $type = 'theme';
                $items = $this->themes;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
        }
        
        return $this->loader->render('viewer', [
            'type' => $type,
            'id' => $id,
            'object' => $object,
        ]);
    }

    /**
     * Linotype helper
     * @Route("/linotype/{type}/{slug}/preview", name="helper_preview")
     */
    public function preview( Request $request ): Response
    {
        if ( $request->get('mode') == 'iframe' ) $this->profiler->disable();
   
        switch( $request->get('type') ) {
            case 'blocks':
                $type = 'block';
                $items = $this->blocks;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'fields':
                $type = 'field';
                $items = $this->fields;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'helpers':
                $type = 'helper';
                $items = $this->helpers;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'modules':
                $type = 'module';
                $items = $this->modules;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'templates':
                $type = 'template';
                $items = $this->templates;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
            case 'themes':
                $type = 'theme';
                $items = $this->themes;
                $object = $items->findBySlug( $request->get('slug') );
                $id = $object->getId();
                break;
        }
        
        return $this->loader->render('preview', [
            'type' => $type,
            'id' => $id,
            'object' => $object,
        ]);
    }
    
}
