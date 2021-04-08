<?php

namespace Linotype\Bundle\LinotypeBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Core\Entity\BlockEntity;
use Linotype\Core\Entity\ModuleEntity;
use Linotype\Core\Entity\TemplateEntity;
use Linotype\Core\Entity\ThemeEntity;

class LinotypeTwig extends AbstractExtension
{
    public $twig;

    public $currentTheme;

    public $currentTemplate;
    
    public $currentModule;
    
    public $currentHelper;
    
    public $currentField;
    
    public $currentBlock;

    public $currentJs = [];

    public $currentCss = [];

    public function __construct( ContainerInterface $container, Environment $twig, Linotype $linotype )
    {
        $this->linotype = $linotype;
        $this->config = $linotype->getConfig();
        $this->current = $this->config->getCurrent();
        $this->theme = $this->current->getTheme();
        $this->routes = $this->theme->getMap();
        $this->templates = $this->config->getTemplates();
        $this->modules = $this->config->getModules();
        $this->blocks = $this->config->getBlocks();
        $this->fields = $this->config->getFields();
        $this->container = $container;
        $this->twig = $twig;
        $this->linotype->log('twig');
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('linotype', [$this, 'linotype'], ['is_safe' => ['html']]),
            new TwigFunction('linotype_admin', [$this, 'linotype_admin'], ['is_safe' => ['html']]),
            new TwigFunction('linotype_render', [$this, 'linotype_render'], ['is_safe' => ['html']]),
            new TwigFunction('linotype_style', [$this, 'linotype_style'], ['is_safe' => ['html']]),
            new TwigFunction('linotype_script', [$this, 'linotype_script'], ['is_safe' => ['html']]),
        ];
    }

    public function linotype(string $type, string $id = null, $context = [], $field_key = null)
    {
        
        $this->linotype->log('twig:linotype', [ $type, $id, $context ] );

        switch ($type) {

            case 'theme':
                return $this->renderTheme( $this->theme, $context );
                break;
            
            case 'template':
                return $this->renderTemplate( $this->templates->findById($id), $context );
                break;
        
            case 'module':
                return $this->renderModule( $this->modules->findById($id), $context );
                break;
    
            case 'block':
                return $this->renderBlock( $this->blocks->findById($id), $context, $field_key );
                break;
                
            case 'field':
                return $this->renderField( $this->fields->findById($id), $context );
                break;

            case 'helper':
                return $this->getHelper( $id, $context );
                break;

            default:
                return '[error]';
                break;
        }
    }

    public function linotype_render(string $type, $item, array $context = [])
    {
        switch ($type) {

            case 'block':
                return $this->renderBlock( $item, $context );
                break;

            case 'field':
                return $this->renderField( $item, $context );
                break;
    
            default:
                return '[error]';
                break;
        }
    }

    public function renderTheme( ThemeEntity $theme, $context = [] )
    {
        $render = '';
        $this->currentTheme = $theme;
        if ( isset( $this->linotype->getContext()['route'] ) ) {
            if( isset( $this->routes[ $this->linotype->getContext()['route'] ]['template'] ) ) {
                $current_template = $this->routes[ $this->linotype->getContext()['route'] ]['template'];
                if ( $this->config->getTemplates()->findById( $current_template ) ) {
                    $render .= $this->renderTemplate( $this->config->getTemplates()->findById( $current_template ), $context );
                }
            }
        }
        return $render;
    }

    public function renderTemplate(TemplateEntity $template)
    {
        $render = '';
        $this->currentTemplate = $template;
        if ( $templateRender = $this->current->render($template) ) {
            foreach ( $templateRender as $block) {
                $render .= $this->renderBlock($block);
            }
        }
        return $render;
    }

    public function renderModule(ModuleEntity $module)
    {
        $render = '';
        $this->currentModule = $module;
        if (isset($module['blocks'])) {
            foreach ($module['blocks'] as $block_key => $block) {
                $render .= $this->renderBlock($block, [], $block_key );
            }
        }
        return $render;
    }

    public function renderField($field, $context_overwrite = [], $field_key = null)
    {   
        $this->currentField = $field;
        $context = $this->renderFieldContext($field, $context_overwrite, $field_key);
        $render = $this->twig->render($field['twig'], $context);
        return $render;
    }

    public function renderBlock(BlockEntity $block, $context_overwrite = [], $block_key = null)
    {
        $this->currentBlock = $block;
        $context = $this->renderBlockContext( $block, $context_overwrite, $block_key );
        $render = $this->twig->render( $block->getInfo()->getTemplate(), $context);
        return $render;
    }

    public function renderBlockContext(BlockEntity $block, $context_overwrite = [], $block_key = '')
    {
        $context = [];
        $customCss = [];

        $context['block'] = [];
        
        //define key
        $context['block']['key'] = $block->getKey();

        //define uid 
        $context['block']['uid'] = $block->getHash();

        //define block css id 
        $context['block']['id'] = $block->getCssId();

        //add class
        $context['block']['class'] = $block->getCssClass();

        //add default values to block context
        $context['block']['path'] = $block->getInfo()->getPath();
        $context['block']['dir'] = $block->getInfo()->getDir();

        //set context
        foreach ( $block->getContext()->getAll() as $context_key => $context_value ) {

            //add context value to twig variables
            $context[$context_key] = $context_value->getValue();

            //use overwrite context exist
            if ( isset( $context_overwrite[$context_key] ) && $context_overwrite[$context_key] ) {
                $context[$context_key] = $context_overwrite[$context_key];
            }

        }

        $this->currentJs = $block->getCustomJs() ? array_merge( $this->currentJs, $block->getCustomJs() ) : $this->currentJs;
        $this->currentCss = $block->getCustomCss() ? array_merge( $this->currentCss, $block->getCustomCss() ) : $this->currentCss;

        //set childrends
        $children = '';
        if ( $block->getChildren() ) {
            foreach( $block->getChildren() as $child_key => $child ) {
                $children .= $this->renderBlock($child);
            }
        }
        $context['children'] = $children;

        //proccess default variable from context values if require
        foreach ($context as $context_key => $context_value) {
            if (is_string($context_value) && strpos($context_value, '{{') !== false) {

                $context[$context_key] = str_replace(
                    [
                        '{{path}}', '{{ path }}',
                        '{{dir}}', '{{ dir }}',
                    ],
                    [
                        $context['block']['path'], $context['block']['path'],
                        $context['block']['dir'], $context['block']['dir'],
                    ],
                    $context_value
                );
            }
        }

        return $context;
    }

    public function getHelper( $id, $context = [] )
    {
        $target = explode('.', $id );
        $helper_id = isset($target[0]) ? $target[0] : false;
        $function = isset($target[1]) ? $target[1] : 'get';
        if ( isset( $this->config->getHelpers()->gethelper($helper_id)->getmethode()[$function]['controller'] ) ) {
            $controller = explode('::', $this->config->getHelpers()->gethelper($helper_id)->getmethode()[$function]['controller'] );
            $class = isset($controller[0]) ? $controller[0] : false;
            $function = isset($controller[1]) ? $controller[1] : 'get';
            if ( $class ) {
                return $this->container->get( $class )->$function( $context );   
            }
        }
    }

    public function linotype_style()
    {
        if ( $this->currentCss ) {
            $css = '';
            foreach( $this->currentCss as $cssId => $cssVar ) {
                $css .=  $cssId . ' {' . PHP_EOL;
                foreach( $cssVar as $cssVarKey => $cssVarVal ) {
                    $css .=  '  ' . $cssVarKey . ': ' . $cssVarVal . ';' . PHP_EOL;
                }
                $css .=  '}' . PHP_EOL;
            };
            return '<style id="linotype-variable-css">' . PHP_EOL . '' . $css . '</style>';
        } else {
            return '';
        }
    }

    public function linotype_script()
    {
        if ( $this->currentJs ) {
            return '<script id="linotype-variable-js" type="text/javascript">' . PHP_EOL . 'var linotype = ' . json_encode( $this->currentJs, JSON_PRETTY_PRINT ) . ';' . PHP_EOL . '</script>';
        } else {
            return '';
        }
    }














    /* ADMIN */


    public function linotype_admin(string $type, string $id = '', array $context = [], $field_key = null )
    {
        switch ($type) {

            case 'block':
                return $this->renderAdminBlock( $this->blocks->findById($id), $context, $field_key );
                break;
    
            default:
                return '[error]';
                break;
        }
    }

    public function renderAdminBlock($block, $context_overwrite = [], $field_key = null)
    {
        $render = '';
        $context = $this->renderBlockContext($block, $context_overwrite);
        
        if (isset($block['context'])) {
            foreach ($block['context'] as $context_key => $context) {
                if ( isset( $context['field_id'] ) ) {

                    $value = isset( $context_overwrite[$context_key]['value'] ) ? $context_overwrite[$context_key]['value'] : '';
                    if ( is_array( $value ) ) $value = json_encode( $value );

                    $render .= $this->renderField( $this->fields->findById( $context['field_id'] ), [
                        'title' => $context['title'],
                        'info' => "",
                        'id' => 'field-' . $field_key . '-' . $context_key,
                        'key' => $context_key,
                        'uid' => 'field-' . $field_key . '-' . $context_key,
                        'form' => [
                            'name' => 'field-' . $field_key . '-' . $context_key,
                            'value' => $value,
                        ]
                    ] );
                }
                // $render .= json_encode( $context_key );
            }
        }
        return $render;
    }

    

    public function renderFieldContext($field, $context_overwrite = [], $field_key = '')
    {
        // dump( [$field, $context_overwrite ] );
        $context = [];
        $context['field'] = [];
        
        if ( isset( $context_overwrite['id'] ) ) {
            $field_key = $context_overwrite['id'];
        }

        if ( isset( $context_overwrite['title'] ) ) {
            $context['title'] = $context_overwrite['title'];
        }
        if ( isset( $context_overwrite['info'] ) ) {
            $context['info'] = $context_overwrite['info'];
        }
        if ( isset( $context_overwrite['require'] ) ) {
            $context['require'] = $context_overwrite['require'];
        }
        if ( isset( $context_overwrite['key'] ) ) {
            $context['field']['key'] = $context_overwrite['key'];
        }
        
        if ( isset( $context_overwrite['form'] ) ) {
            $context['field']['form'] = $context_overwrite['form'];
        }
        
        //add context value to context
        if ( isset( $context_overwrite['option'] ) ) {
            $context['option'] = $field['option'];
            foreach ( $context_overwrite['option'] as $option_key => $option_value ) {
                $context['option'][$option_key] = $option_value;
            }
        } else {
            $context['option'] = $field['option'];
        }

        //create uid from template id, module_key and field_key
        $uid = $this->currentTemplate['id'] ? $this->currentTemplate['id'] . '__' . $field_key : $field_key;

        //create hashed uid
        $context['field']['uid'] = md5( $uid, false );

        //sanitize for field id 
        $field_uid = strtolower( str_replace( '_', '-', $uid ) );

        //define field id 
        $context['field']['id'] = $field_uid;

        //add class
        $context['field']['class'] = 'field--' . strtolower( preg_replace('/([a-z])([A-Z])/s','$1-$2', $field['id'] ) );

        //add default values to field context
        $context['field']['path'] = $field['path'];
        $context['field']['dir'] = $field['dir'];

        //proccess default variable from context values if require
        foreach ($context as $context_key => $context_value) {
            if (is_string($context_value) && strpos($context_value, '{{') !== false) {

                $context[$context_key] = str_replace(
                    [
                        '{{path}}', '{{ path }}',
                        '{{dir}}', '{{ dir }}',
                    ],
                    [
                        $context['field']['path'], $context['field']['path'],
                        $context['field']['dir'], $context['field']['dir'],
                    ],
                    $context_value
                );
            }
        }

       
        
        return $context;
    }

}
