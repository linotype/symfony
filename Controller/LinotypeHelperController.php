<?php

namespace Linotype\Bundle\LinotypeBundle\Controller;

use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LinotypeHelperController extends AbstractController
{   
    
    function __construct( Linotype $linotype, LinotypeLoader $loader )
    {
        $this->linotype = $linotype;
        $this->config = $this->linotype->getConfig();
        $this->blocks = $this->config->getBlocks()->getAll();
        $this->fields = $this->config->getFields()->getAll();
        $this->helpers = $this->config->getHelpers()->getAll();
        $this->modules = $this->config->getModules()->getAll();
        $this->templates = $this->config->getTemplates()->getAll();
        $this->themes = $this->config->getThemes()->getAll();
        $this->loader = $loader;
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
        foreach( $this->blocks as $item ) {
            $block['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/blocks/' . $item->getSlug(),
            ];
        }

        $field = [ 'link' => '/linotype/fields', 'items' => [] ];
        foreach( $this->fields as $item ) {
            $field['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/fields/' . $item->getSlug(),
            ];
        }

        $helper = [ 'link' => '/linotype/helpers', 'items' => [] ];
        foreach( $this->helpers as $item ) {
            $helper['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/helpers/' . $item->getSlug(),
            ];
        }

        $module = [ 'link' => '/linotype/modules', 'items' => [] ];
        foreach( $this->modules as $item ) {
            $module['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/modules/' . $item->getSlug(),
            ];
        }

        $template = [ 'link' => '/linotype/templates', 'items' => [] ];
        foreach( $this->templates as $item ) {
            $template['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/templates/' . $item->getSlug(),
            ];
        }

        $theme = [ 'link' => '/linotype/themes', 'items' => [] ];
        foreach( $this->themes as $item ) {
            $theme['items'][] = [
                'name' => $item->getName(),
                'desc' => $item->getDesc(),
                'version' => $item->getVersion(),
                'author' => $item->getAuthor(),
                'link' => '/linotype/themes/' . $item->getSlug(),
            ];
        }

        return $this->loader->render('helper', [
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

        return $this->loader->render('helper_list', [
            
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

        return $this->loader->render('helper_view', [
            
        ]);
    }
    
}
