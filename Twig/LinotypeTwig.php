<?php

namespace Linotype\Bundle\LinotypeBundle\Twig;

use Linotype\Bundle\LinotypeBundle\Entity\LinotypeMeta;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeMetaRepository;
use Linotype\Bundle\LinotypeBundle\Service\LinotypeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;

class LinotypeTwig extends AbstractExtension
{
    public $twig;

    public $currentTheme;
    public $currentTemplate;
    public $currentModule;
    public $currentHelper;
    public $currentField;
    public $currentBlock;

    public function __construct( ContainerInterface $container, Environment $twig, Linotype $linotype, LinotypeMetaRepository $metaRepo )
    {
        $this->linotype = $linotype;
        $this->container = $container;
        $this->twig = $twig;
        $this->metaRepo = $metaRepo;

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
                $theme = isset( LinotypeConfig::$config['current']['theme'] ) ? LinotypeConfig::$config['current']['theme'] : LinotypeConfig::$config['themes'][$id];
                return $this->renderTheme( $theme, $context );
                break;
            
            case 'template':
                return $this->renderTemplate( LinotypeConfig::$config['templates'][$id], $context );
                break;
        
            case 'module':
                return $this->renderModule( LinotypeConfig::$config['modules'][$id], $context );
                break;
    
            case 'block':
                return $this->renderBlock( LinotypeConfig::$config['blocks'][$id], $context, $field_key );
                break;
                
            case 'field':
                return $this->renderField( LinotypeConfig::$config['fields'][$id], $context );
                break;

            case 'helper':
                return $this->getHelper( $id, $context );
                break;

            default:
                return '[error]';
                break;
        }
    }

    public function linotype_admin(string $type, string $id = '', array $context = [], $field_key = null )
    {
        switch ($type) {

            case 'block':
                return $this->renderAdminBlock( LinotypeConfig::$config['blocks'][$id], $context, $field_key );
                break;
    
            default:
                return '[error]';
                break;
        }
    }

    public function linotype_render(string $type, array $item = [], array $context = [])
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

    public function renderTheme( $theme, $context )
    {
        $this->currentTheme = $theme;
        $template_current = $theme['map'][ LinotypeConfig::$config['context']['route'] ]['template'];
        return $this->renderTemplate( LinotypeConfig::$config['templates'][ $template_current ], $context );
    }

    public function renderTemplate($template)
    {
        $this->currentTemplate = $template;
        $render = '';
        if (isset($template['blocks'])) {
            foreach ($template['blocks'] as $block_key => $block) {
                $render .= $this->renderBlock($block, [], $block_key );
            }
        }
        return $render;
    }

    public function renderModule($module)
    {
        $this->currentModule = $module;
        $render = '';
        if (isset($module['blocks'])) {
            foreach ($module['blocks'] as $block_key => $block) {
                $render .= $this->renderBlock($block, [], $block_key );
            }
        }
        return $render;
    }

    public function renderBlock($block, $context_overwrite = [], $block_key = null)
    {
        $this->currentBlock = $block;
        $context = $this->renderBlockContext($block, $context_overwrite, $block_key);
        $render = $this->twig->render($block['twig'], $context);
        return $render;
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

                    $render .= $this->renderField( LinotypeConfig::$config['fields'][ $context['field_id'] ], [
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
                    // $render .= $this->renderField( LinotypeConfig::$config['fields'][ $context['field']['field_id'] ], $context );
                }
                // $render .= json_encode( $context_key );
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

    public function renderBlockContext($block, $context_overwrite = [], $block_key = '')
    {
        
        $context = [];
        $context['block'] = [];
        
        //create uid from template id, module_key and block_key
        $uid = $this->currentTemplate['id'] ? $this->currentTemplate['id'] . '__' . $block_key : $block_key;

        //create hashed uid
        $context['block']['uid'] = md5( $uid, false );

        //sanitize for block id 
        $block_uid = strtolower( str_replace( '_', '-', $uid ) );

        //define block id 
        $context['block']['id'] = $block_uid;

        //add class
        $context['block']['class'] = 'block--' . strtolower( preg_replace('/([a-z])([A-Z])/s','$1-$2', $block['id'] ) );

        //add default values to block context
        $context['block']['path'] = $block['path'];
        $context['block']['dir'] = $block['dir'];

        $customCss = [];
        //add context value to context
        if (isset($block['context'])) {
            foreach ($block['context'] as $context_item_key => $context_item_value) {

                //initialise
                if (!isset($context_item_value['context'])) $context_item_value['context'] = [];
                
                //get value from template
                if ( isset( $this->currentTemplate['context'][ $block_key  . '__' . $context_item_key ]['value'] ) ) {
                    $context[$context_item_key] = $this->currentTemplate['context'][ $block_key  . '__' . $context_item_key ]['value'];
                }

                //if variable not set, create empty value
                if (!isset($context[$context_item_key])) $context[$context_item_key] = '';

                //if overwrite data exist
                if (!$context[$context_item_key] && isset($context_overwrite[$context_item_key])) {
                    $context[$context_item_key] = $context_overwrite[$context_item_key];
                }

                //if empty use value
                if (!$context[$context_item_key] && isset($context_item_value['value'])) {
                    $context[$context_item_key] = $context_item_value['value'];
                }

                //if empty use default
                if (!$context[$context_item_key] && isset($context_item_value['default'])) {
                    $context[$context_item_key] = $context_item_value['default'];
                }

                //overwrite if preview mode enabled
                if (isset(LinotypeConfig::$config['preview']) && LinotypeConfig::$config['preview'] == true && isset($context_item_value['preview'])) {
                    $context[$context_item_key] = $context_item_value['preview'];
                }

                
                if( isset( $context_item_value['css'] ) ){
                    if ( isset( $context_item_value['css'] ) && $context_item_value['css'] == true && isset( $context_item_value['value'] ) && $context_item_value['value'] ) {
                        if ( ! isset( $customCss[ '#' . $block_key ] ) ) $customCss[ '#' . $block_key ] = [];
                        $customCss[ '#' . $block_key ][ '--' . $context_item_key ] =  $context_item_value['value'];
                    }
                }
              
                if (isset($context_item_value['dump']) && $context_item_value['dump'] == true) {
                    dump($context_item_value);
                }

            }
        }

        //add custom css
        $this->linotype_style_add( $customCss );

        //set childrends
        if (isset($block['childrens'])) {
            $context['childrens'] = $block['childrens'];
        } else {
            $context['childrens'] = [];
        }

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

        if (isset(LinotypeConfig::$config['debug']) && LinotypeConfig::$config['debug'] === true) {
            dump($context);
        }

        return $context;
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

        if (isset(LinotypeConfig::$config['debug']) && LinotypeConfig::$config['debug'] === true) {
            dump($context);
        }
        
        return $context;
    }

    public function getHelper( $id, $context = [] )
    {
        $target = explode('.', $id );
        $helper_id = isset($target[0]) ? $target[0] : false;
        $function = isset($target[1]) ? $target[1] : 'get';
        if ( isset( LinotypeConfig::$config['helpers'][$helper_id]['methodes'][$function]['controller'] ) ) {
            $controller = explode('::', LinotypeConfig::$config['helpers'][$helper_id]['methodes'][$function]['controller'] );
            $class = isset($controller[0]) ? $controller[0] : false;
            $function = isset($controller[1]) ? $controller[1] : 'get';
            if ( $class ) {
                return $this->container->get( $class )->$function( $context );   
            }
        }
    }

    public function linotype_style_add($styles)
    {
        foreach($styles as $style_key => $style){
            LinotypeConfig::$config['current']['css'][$style_key] = $style; 
        }
    }

    public function linotype_style()
    {
        $css = '';
        foreach( LinotypeConfig::$config['current']['css'] as $cssId => $cssVar ) {
            $css .=  $cssId . ' {' . PHP_EOL;
            foreach( $cssVar as $cssVarKey => $cssVarVal ) {
                $css .=  '  ' . $cssVarKey . ': ' . $cssVarVal . ';' . PHP_EOL;
            }
            $css .=  '}' . PHP_EOL;
        };
        return '<style id="linotype-variable-css">' . PHP_EOL . '' . $css . '</style>';
    }

    public function linotype_script()
    {
        return '<script id="linotype-variable-js" type="text/javascript">' . PHP_EOL . 'var linotype = ' . json_encode( LinotypeConfig::$config['current']['js'], JSON_PRETTY_PRINT ) . ';' . PHP_EOL . '</script>';
    }

}
