<?php

namespace Linotype\Bundle\LinotypeBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;

class LinotypeConfig
{

    static $config;

    private $projectDir;

    private $linotypePath;
    private $linotypeDirectory;

    private $blocks;
    private $blocksPath;
    private $blocksDirectory;

    private $fields;
    private $fieldsPath;
    private $fieldsDirectory;

    private $modules;
    private $modulesPath;
    private $modulesDirectory;

    private $helpers;
    private $helpersPath;
    private $helpersDirectory;

    private $templates;
    private $templatesPath;
    private $templatesDirectory;

    private $themes;
    private $themesPath;
    private $themesDirectory;

    private $customJs;
    private $customCss;

    function __construct(ContainerInterface $container, LinotypeMetaRepository $metaRepo)
    {
        
        $this->projectDir = $container->getParameter('kernel.project_dir');
        
        $this->linotypePath = 'linotype';
        $this->linotypeDirectory = $this->projectDir . '/' . $this->linotypePath;

        $this->blocks = [];
        $this->blocksSetting = [];
        $this->blocksPath = 'Block';
        $this->blocksDirectory = $this->projectDir . '/' . $this->blocksPath;

        $this->fields = [];
        $this->fieldsSetting = [];
        $this->fieldsPath = 'Field';
        $this->fieldsDirectory = $this->projectDir . '/' . $this->fieldsPath;

        $this->modules = [];
        $this->modulesSetting = [];
        $this->modulesPath = 'Module';
        $this->modulesDirectory = $this->projectDir . '/' . $this->modulesPath;

        $this->helpers = [];
        $this->helpersSetting = [];
        $this->helpersPath = 'Helper';
        $this->helpersDirectory = $this->projectDir . '/' . $this->helpersPath;

        $this->templates = [];
        $this->templatesSetting = [];
        $this->templatesPath = 'Template';
        $this->templatesDirectory = $this->projectDir . '/' . $this->templatesPath;

        $this->themes = [];
        $this->themesSetting = [];
        $this->themesPath = 'Theme';
        $this->themesDirectory = $this->projectDir . '/' . $this->themesPath;

        $this->customJs = [];
        $this->customCss = [];

        $this->metaRepo = $metaRepo;

        $this->load();

        $this->init();

        self::$config = $this->getLinotypeSettings();

        //dump( self::$config );
        //die;
    }

    static function getConfig($key = null)
    {   
        return $key ? self::$config[$key] : self::$config;
    }

    static function setContext($context)
    {
        self::$config['context'] = $context;
    }
    
    static function getContext()
    {
        return self::$config['context'];
    }

    private function getCurrentSettings($theme_id)
    {
        $current = [
            'theme' => null,
            'template' => null,
            'modules' => null,
            'blocks' => null,
            'fields' => null,
            'js' => $this->customJs,
            'css' => $this->customCss,
        ];
        $current['theme'] = isset($this->themes[$theme_id]) ? $this->themes[$theme_id] : [];
        return $current;
    }

    public function getLinotypeSettings()
    {
        $settings = ['linotype' => []];

        //get current config
        if (file_exists($this->linotypeDirectory . '/linotype.yml')) {
            $settings = Yaml::parse(file_get_contents($this->linotypeDirectory . '/linotype.yml'));
        }

        //set default config
        $defaults = [
            'linotype' => [
                'version' => '1.0',
                'debug' => false,
                'preview' => false,
                'theme' => 'default',
                'blocks' => [],
                'fields' => [],
                'helpers' => [],
                'modules' => [],
                'templates' => [],
                'themes' => [],
                'current' => [],
            ]
        ];

        //format config
        if (isset($settings['linotype'])) {
            $settings['linotype'] = array_merge($defaults['linotype'], $settings['linotype']);
        } else {
            $settings['linotype'] = $defaults['linotype'];
        }
        
        //format some values
        $settings['linotype']['version'] = strval( $settings['linotype']['version'] );

        //add custom settings
        $settings['linotype']['blocks'] = $this->blocks;
        $settings['linotype']['fields'] = $this->fields;
        $settings['linotype']['helpers'] = $this->helpers;
        $settings['linotype']['modules'] = $this->modules;
        $settings['linotype']['templates'] = $this->templates;
        $settings['linotype']['themes'] = $this->themes;

        //add current config
        $settings['linotype']['current'] = $this->getCurrentSettings($settings['linotype']['theme']);

        return $settings['linotype'];
    }

    private function load()
    {
        foreach (['Field', 'Helper','Block', 'Module', 'Template', 'Theme'] as $settingKey) {
            if (file_exists($this->linotypeDirectory . '/' . $settingKey)) {
                $finder = new Finder();
                $finder->files()->name(['*.yml', '*.yaml'])->in($this->linotypeDirectory . '/' . $settingKey);
                if ($finder->hasResults()) {
                    foreach ($finder as $file) {
                        $id = $file->getFilenameWithoutExtension();
                        $path = $file->getPathname();
                        $fileContent = '';
                        if ( file_exists($path) ) $fileContent = file_get_contents($path);
                        $fileData = Yaml::parse($fileContent);
                        switch ($settingKey) {
                            case 'Field':
                                $this->fieldsSetting[$id] = isset($fileData['field']) ? $fileData['field'] : [];
                                break;
                            case 'Block':
                                $this->blocksSetting[$id] = isset($fileData['block']) ? $fileData['block'] : [];
                                break;
                            case 'Helper':
                                $this->helpersSetting[$id] = isset($fileData['helper']) ? $fileData['helper'] : [];
                                break;
                            case 'Module':
                                $this->modulesSetting[$id] = isset($fileData['module']) ? $fileData['module'] : [];
                                break;
                            case 'Template':
                                $this->templatesSetting[$id] = isset($fileData['template']) ? $fileData['template'] : [];
                                break;
                            case 'Theme':
                                $this->themesSetting[$id] = isset($fileData['theme']) ? $fileData['theme'] : [];
                                break;
                        }
                    }
                }
            }
        }
    }

    private function init()
    {
        foreach ($this->fieldsSetting as $id => $field) {
            $this->fields[$id] = $this->getFieldSettings($id);
        }
        foreach ($this->blocksSetting as $id => $block) {
            $this->blocks[$id] = $this->getBlockSettings($id);
        }
        foreach ($this->modulesSetting as $id => $module) {
            $this->modules[$id] = $this->getModuleSettings($id);
        }
        foreach ($this->helpersSetting as $id => $helper) {
            $this->helpers[$id] = $this->getHelperSettings($id);
        }
        foreach ($this->templatesSetting as $id => $template) {
            $this->templates[$id] = $this->getTemplateSettings($id);
        }
        foreach ($this->themesSetting as $id => $theme) {
            $this->themes[$id] = $this->getThemeSettings($id);
        }
    }

    private function getBlockSettings($id, $override = [], $childrens = [] ): array
    {
        //get original config from file
        $settings = $this->blocksSetting[$id];

        //set default config
        $defaults = [
            'version' => '1.0',
            'package' => 'unknow',
            'name' => 'Unknow',
            'desc' => 'Create unknow block',
            'id' => null,
            'key' => null,
            'path' => null,
            'dir' => null,
            'yml' => null,
            'twig' => null,
            'scss' => null,
            'js' => null,
            'context' => [],
        ];

        //format config
        if (isset($settings)) {
            $settings = array_merge($defaults, $settings);
        } else {
            $settings = $defaults;
        }

        //add custom settings
        $settings['id'] = $id;
        $settings['path'] = $this->blocksPath . '/' . $id;
        $settings['dir'] = $this->blocksDirectory . '/' . $id;
        $settings['yml'] = $this->blocksDirectory . '/' . $id . '/' . $id . '.yml';
        $settings['twig'] = $this->blocksPath . '/' . $id . '/' . $id . '.twig';

        // dump($settings);

        //overwrite on template or module demand
        if ($override) {
            foreach ($override as $override_context_key => $override_context_value) {
                if (isset($settings['context'][$override_context_key])) {
                    foreach ($override_context_value as $override_key => $override_value) {
                        if (isset($settings['context'][$override_context_key][$override_key])) {
                            $settings['context'][$override_context_key][$override_key] = $override_value;
                        }
                    }
                }
            }
        }
        // dump($settings);
        //format context to get unique block key for drupal block_content field key
        if (isset($settings['context'])) {
            foreach ($settings['context'] as $context_key => $context) {
                if ( ! isset( $context['save'] ) ) $context['save'] = 'meta';
                // if ($context['save'] !== 'static') {
                    $fieldConfig = $this->fieldsSetting[$context['field']];
                    $settings['context'][$context_key]['field'] = $fieldConfig;
                    $settings['context'][$context_key]['field_id'] = $context['field'];
                    $settings['context'][$context_key]['key'] = $context_key;
                    $settings['context'][$context_key]['field']['key'] = $id . '_' . $context_key;
                // }
            }
        }

        if ( $childrens ) $settings['childrens'] = $childrens;

        return $settings;
    }

    private function getFieldSettings($id)
    {
        //get original config from file
        $settings = $this->fieldsSetting[$id];

        //set default config
        $defaults = [
            'version' => '1.0',
            'package' => 'unknow',
            'name' => 'Unknow',
            'desc' => 'Create unknow block',
            'id' => null,
            'key' => null,
            'title' => null,
            'info' => null,
            'field_id' => null,
            'path' => null,
            'dir' => null,
            'yml' => null,
            'twig' => null,
            'scss' => null,
            'js' => null,
            'options' => [],
        ];

        //format config
        if (isset($settings)) {
            $settings = array_merge($defaults, $settings);
        } else {
            $settings = $defaults;
        }

        //add custom settings
        $settings['id'] = $id;
        $settings['field_id'] = $id;
        $settings['path'] = $this->fieldsPath . '/' . $id;
        $settings['dir'] = $this->fieldsDirectory . '/' . $id;
        $settings['yml'] = $this->fieldsDirectory . '/' . $id . '/' . $id . '.yml';
        $settings['twig'] = $this->fieldsPath . '/' . $id . '/' . $id . '.twig';

        return $settings;
    }

    private function getHelperSettings($id)
    {
        //get original config from file
        $settings = $this->helpersSetting[$id];

        //set default config
        $defaults = [
            'version' => '1.0',
            'package' => 'unknow',
            'name' => 'Unknow',
            'desc' => 'Create unknow block',
            'id' => null,
            'key' => null,
            'path' => null,
            'dir' => null,
            'yml' => null,
            'twig' => null,
            'scss' => null,
            'js' => null,
            'controller' => null,
            'methodes' => null,
            'tags' => null,
        ];

        //format config
        if (isset($settings)) {
            $settings = array_merge($defaults, $settings);
        } else {
            $settings = $defaults;
        }

        //add custom settings
        $settings['id'] = $id;
        $settings['path'] = $this->helpersPath . '/' . $id;
        $settings['dir'] = $this->helpersDirectory . '/' . $id;
        $settings['yml'] = $this->helpersDirectory . '/' . $id . '/' . $id . '.yml';
        $settings['twig'] = $this->helpersPath . '/' . $id . '/' . $id . '.twig';

        return $settings;
    }

    private function getModuleSettings($id)
    {
        //get original config from file
        $settings = $this->modulesSetting[$id];

        //set default config
        $defaults = [
            'version' => '1.0',
            'package' => 'unknow',
            'name' => 'Unknow',
            'desc' => 'Create unknow block',
            'id' => null,
            'key' => null,
            'path' => null,
            'dir' => null,
            'yml' => null,
            'layout' => [],
        ];

        //format config
        if (isset($settings)) {
            $settings = array_merge($defaults, $settings);
        } else {
            $settings = $defaults;
        }

        //add custom settings
        $settings['id'] = $id;
        $settings['path'] = $this->modulesPath . '/' . $id;
        $settings['dir'] = $this->modulesDirectory . '/' . $id;
        $settings['yml'] = $this->modulesDirectory . '/' . $id . '/' . $id . '.yml';

        //get all blocks
        $blocks = [];
        if (isset($settings['layout'])) {
            foreach ($settings['layout'] as $item_key => $item) {
                if (isset($item['block'])) {
                    if (!isset($item['override'])) $item['override'] = [];
                    $blocks[$item_key] = $this->getBlockSettings($item['block'], $item['override']);
                } else if (isset($item['module'])) {
                    //$blocks[] = 
                }
            }
        }
        $settings['blocks'] = $blocks;

        //get all context
        $module_context = [];
        if (isset($settings['blocks'])) {
            foreach ($settings['blocks'] as $item_key => $item) {
                if (isset($item['context'])) {
                    foreach ($item['context'] as $context_key => $context) {
                        if (isset($context['field'])) {
                            $module_context[$item_key . '__' . $context_key] = $context;
                        }
                    }
                }
            }
        }
        $settings['context'] = $module_context;

        return $settings;
    }

    private function getTemplateSettings($id)
    {
        //get original config from file
        $settings = $this->templatesSetting[$id];

        //set default config
        $defaults = [
            'version' => '1.0',
            'package' => 'unknow',
            'name' => 'Unknow',
            'desc' => 'Create unknow block',
            'id' => null,
            'key' => null,
            'path' => null,
            'dir' => null,
            'yml' => null,
            'layout' => [],
        ];

        //format config
        if (isset($settings)) {
            $settings = array_merge($defaults, $settings);
        } else {
            $settings = $defaults;
        }

        //add custom settings
        $settings['id'] = $id;
        $settings['path'] = $this->templatesPath . '/' . $id;
        $settings['dir'] = $this->templatesDirectory . '/' . $id;
        $settings['yml'] = $this->templatesDirectory . '/' . $id . '/' . $id . '.yml';

        //get all blocks
        $blocks = [];
        if (isset($settings['layout'])) {
            foreach ($settings['layout'] as $item_key => $item ) {
                
                //if item is block
                if (isset($item['block'])) {
                    
                    //check if block has children
                    $childrens = [];
                    if ( isset( $item['childrens'] ) ) {
                        
                        //itinerate childrends to extract blocks
                        foreach( $item['childrens'] as $child_key => $child ) {
                            
                            //check context overide
                            if (!isset($child['override'])) $child['override'] = [];
                            
                            //////////
                            //check if block has children
                            $childrens_sub = [];
                            if ( isset( $child['childrens'] ) ) {
                                
                                //itinerate childrends to extract blocks
                                foreach( $child['childrens'] as $child_sub_key => $child_sub ) {
                                    
                                    //check context overide
                                    if (!isset($child_sub['override'])) $child_sub['override'] = [];
                                    
                                    //get block settings
                                    $childrens_sub[ $child_key . '_' . $child_sub_key . '__child__' . $child_sub_key ] = $this->getBlockSettings($child_sub['block'], $child_sub['override']);
                                    
                                }
                                
                            }
                            /////////
                            //get block settings
                            $childrens[ $item_key . '__child__' . $child_key ] = $this->getBlockSettings($child['block'], $child['override'], $childrens_sub );
                            
                        }
                        
                    }

                    //check context overide
                    if (!isset($item['override'])) $item['override'] = [];
                    
                    //get block settings
                    $blocks[ $item_key ] = $this->getBlockSettings($item['block'], $item['override'], $childrens );

                //if item is module load module blocks
                } else if (isset($item['module'])) {

                    //itinerate module layout
                    if (isset($this->modules[$item['module']]['layout'])) {
                        foreach ($this->modules[$item['module']]['layout'] as $module_item_key => $module_item) {

                            //if item is block
                            if (isset($module_item['block'])) {

                                //check context overide
                                if (!isset($module_item['override'])) $module_item['override'] = [];

                                //get block settings
                                $blocks[ $item['module'] . '__' . $module_item_key] = $this->getBlockSettings($module_item['block'], $module_item['override']);

                            }

                        }
                    }

                }

            }
        }
        $settings['blocks'] = $blocks;

        //get all context
        $template_context = [];
        if (isset($settings['blocks'])) {
            foreach ($settings['blocks'] as $item_key => $item) {
                if (isset($item['context'])) {
                    foreach ($item['context'] as $context_key => $context) {
                        if (isset($context['field'])) {

                            // get value from db
                            try {
                                if ( isset( $context['field'] ) && is_array( $context['field'] ) ) {
                                    $metaEntity = $this->metaRepo->findOneBy([ 'context_key' => $item_key . '__' . $context_key ]);
                                    $context['value'] = $metaEntity ? $metaEntity->getContextValue() : '';
                                }
                            } 
                            catch(\Exception $e){
                                $errorMessage = $e->getMessage();
                            }

                            $template_context[$item_key . '__' . $context_key] = $context;
                            
                            //get custom js/css
                            if ( ( ! isset( $context['value'] ) || empty( $context['value'] ) ) && isset( $context['default'] ) && $context['default'] ) $context['value'] = $context['default'];
                            $domId = strtolower( str_replace( '_', '-', $settings['id'] . '__' . $item_key ) );
                            if ( isset( $context['css'] ) && $context['css'] == true && isset( $context['value'] ) && $context['value'] ) {
                                if ( ! isset( $this->customCss[ '#' . $domId ] ) ) $this->customCss[ '#' . $domId ] = [];
                                $this->customCss[ '#' . $domId ][ '--' . $context_key ] =  $context['value'];
                                //$this->customCss[] = '#' . strtolower( str_replace( '_', '-', $settings['id'] . '__' . $item_key ) ) . ' { --' . $context_key .':' . $context['value'] . ';}';
                            }
                            if ( isset( $context['js'] ) && $context['js'] == true && isset( $context['value'] ) ) {
                                if ( ! isset( $this->customJs[ $domId ] ) ) $this->customJs[ $domId ] = [];
                                $this->customJs[ $domId ][$context_key] =  $context['value'];
                            }

                        }
                    }
                }
            }
        }
        $settings['context'] = $template_context;


        //TODO: get "current" template (query only the display template) and check if has data from fields (db). list theme and create 1 query (replace line 562 to 565 )
        // $xxx = $this->metaRepo->findByKeys( ['presentation__text', 'intro__text'] );
        // or quicker query by template_id relation.
        // $xxx = $this->metaRepo->findByTemplateId( 2 );
        // dump($xxx);

        return $settings;
    }

    private function getThemeSettings($id)
    {
        //get original config from file
        $settings = $this->themesSetting[$id];

        //set default config
        $defaults = [
            'version' => '1.0',
            'package' => 'unknow',
            'name' => 'Unknow',
            'desc' => 'Create unknow block',
            'id' => null,
            'key' => null,
            'path' => null,
            'dir' => null,
            'yml' => null,
            'twig' => null,
            'twig_helper' => null,
            'twig_admin' => null,
            'twig_admin_edit' => null,
            'twig_admin_new' => null,
            'map' => [],
        ];

        //format config
        if (isset($settings)) {
            $settings = array_merge($defaults, $settings);
        } else {
            $settings = $defaults;
        }

        //add custom settings
        $settings['id'] = $id;
        $settings['path'] = $this->themesPath . '/Base';
        $settings['dir'] = $this->themesDirectory . '/Base';
        $settings['yml'] = $this->themesDirectory . '/' . $id . '.yml';
        $settings['twig'] = '/Base/index.twig';
        $settings['twig_helper'] = '/Base/linotype.twig';
        $settings['twig_admin'] = '/Base/admin/admin.twig';
        $settings['twig_admin_edit'] = '/Base/admin/admin-edit.twig';
        $settings['twig_admin_new'] = '/Base/admin/admin-new.twig';

        return $settings;
    }
}
