<?php

namespace Linotype\Bundle\LinotypeBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;
use Linotype\Bundle\LinotypeBundle\Core\Linotype;
use Linotype\Bundle\LinotypeBundle\Repository\LinotypeTemplateRepository;
use Linotype\Core\Entity\BlockEntity;
use Linotype\Core\Entity\FieldEntity;
use Linotype\Core\Entity\ModuleEntity;
use Linotype\Core\Entity\TemplateEntity;
use Linotype\Core\Entity\ThemeEntity;
use Symfony\Component\HttpFoundation\RequestStack;

class LinotypeTwig extends AbstractExtension
{
    public $twig;
    
    public $currentJs = [];

    public $currentCss = [];

    public function __construct( ContainerInterface $container, RequestStack $request, Environment $twig, Linotype $linotype, LinotypeTemplateRepository $templateRepo )
    {
        
        //get current database id
        $this->database_id = $request->getCurrentRequest()->get('id');
        if ( $this->database_id == null ) {
            $template_key = $request->getCurrentRequest()->get('map_id');
            if ( $template_key == null ) $template_key = $request->getCurrentRequest()->get('_route');
            $template = $templateRepo->findOneBy(['template_key' => $template_key ]);
            if ( $template ) $this->database_id = $template->getId();
        }

        $this->linotype = $linotype;
        $this->config = $linotype->getConfig();
        $this->current = $this->config->getCurrent();
        $this->theme = $this->current->getTheme();
        $this->map = $this->theme ? $this->theme->getMap() : [];
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
                return 'no data';
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
                return 'no data';
                break;
        }
    }

    public function renderTheme( ThemeEntity $theme, $context = [] )
    {
        $render = '';

        //get current route from controller
        if ( isset( $this->linotype->getContext()['route'] ) ) {

            //check if current route has template
            if( isset( $this->map[ $this->linotype->getContext()['route'] ]['template'] ) ) {
                
                //get current route template
                $current_template = $this->map[ $this->linotype->getContext()['route'] ]['template'];

                //get template object
                if ( $template = $this->config->getTemplates()->findById( $current_template ) ) {

                    //set map key as unique template key (used to find doctrine ref)
                    $template->setKey( $this->linotype->getContext()['route'] );

                    //render template
                    $render .= $this->renderTemplate( $template, $context );
                
                }
            }
        }
        return $render;
    }

    public function renderTemplate(TemplateEntity $template, $context = [] )
    {
        $render = '';

        //render template object
        if ( $templateRender = $this->current->render($template, $this->database_id) ) {
            
            //loop blocks from template render
            foreach ( $templateRender as $block) {

                //render block
                $render .= $this->renderBlock($block);
            }
        }
        return $render;
    }

    public function renderModule(ModuleEntity $module)
    {
        //TODO: render block from rendered module
    }

    public function renderField(FieldEntity $field, $context_overwrite = [], $field_key = null)
    {   
        //render field context
        $context = $this->renderFieldContext($field, $context_overwrite, $field_key);

        //render field template with context 
        $render = $this->twig->render( $field->getInfo()->getTemplate(), $context);

        return $render;
    }

    public function renderBlock(BlockEntity $block, $context_overwrite = [], $block_key = null)
    {
        //render block context
        $context = $this->renderBlockContext( $block, $context_overwrite, $block_key );

        //render block template with context 
        $render = $this->twig->render( $block->getInfo()->getTemplate(), $context);

        return $render;
    }

    public function renderBlockContext(BlockEntity $block, $context_overwrite = [], $block_key = '')
    {
        $context = [];

        $context['block'] = [];
        
        //define key
        $context['block']['key'] = $block->getKey();

        //define uid 
        $context['block']['uid'] = $block->getHash();

        //define block css id 
        $context['block']['id'] = $block->getCssId();

        //add class
        $context['block']['class'] = 'block--' . $block->getCssClass();

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

    public function renderFieldContext(FieldEntity $field, $context_overwrite = [], $field_key = '')
    {
        $context = [];

        $context['field'] = [];
        
        //define value
        $context['field']['value'] = $field->getValue();

        //define default
        $context['field']['default'] = $field->getDefault();

        //define key
        $context['field']['key'] = $field->getKey();

        //define uid 
        $context['field']['uid'] = $field->getHash();

        //define field css id 
        $context['field']['id'] = $field->getCssId();

        //add class
        $context['field']['class'] = 'field--' .$field->getCssClass();

        //add default values to field context
        $context['field']['path'] = $field->getInfo()->getPath();
        $context['field']['dir'] = $field->getInfo()->getDir();

        //define require context title
        $context['title'] = $field->getTitle();

        //define require context help
        $context['help'] = $field->getHelp();

        //define require context require
        $context['require'] = $field->getRequire();

        //add context value to twig variables
        foreach ( $field->getOption() as $context_key => $context_value ) {
            $context[$context_key] = $context_value;
        }

        //options variable to scripts and styles
        $this->currentJs = $field->getCustomJs() ? array_merge( $this->currentJs, $field->getCustomJs() ) : $this->currentJs;
        $this->currentCss = $field->getCustomCss() ? array_merge( $this->currentCss, $field->getCustomCss() ) : $this->currentCss;

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

    public function linotype_admin(string $type, string $id = '', array $context = [] )
    {
        switch ($type) {

            case 'template':
                return $this->renderTemplateAdmin( $this->templates->findById($id), $context );
                break;

            case 'block':
                return $this->renderBlockAdmin( $this->blocks->findById($id), $context );
                break;

            default:
                return '[error]';
                break;
        }
    }

    public function renderTemplateAdmin( TemplateEntity $template, $context = [] )
    {
        $render = '';

        //render template object
        if ( $templateRender = $this->current->render($template, $this->database_id) ) {
            
            //loop blocks from template render
            foreach ( $templateRender as $block) {

                //render block
                $block_render = $this->renderBlockAdmin($block);
                if ( $block_render ) {
                    $render .= '<div class="panel-block">';
                        if ( $block->getTitle() ) $render .= '<h4 class="text-primary mb-0 mt-2">' . $block->getTitle() . '</h4>';
                        if ( $block->getHelp() ) $render .= '<p class="text-secondary mb-1">' . $block->getHelp() . '</p>';
                        $render .= '<div class="">';
                            $render .= $block_render;
                        $render .= '</div>';
                    $render .= '</div>';
                }

            }
        }
        return $render;
    }

    public function renderBlockAdmin( BlockEntity $block, $context_overwrite = [], $field_key = null)
    {
        $render = '';
        $context = $this->renderBlockContext($block, $context_overwrite);
        
        foreach( $block->getContext()->getAll() as $context ) {
            if ( $context->getPersist() == 'meta' ) {
                $field = $context->getFieldEntity();
                $render .= $this->renderField( $field, [] );
            }
        }

        $children = '';
        if ( $block->getChildren() ) {
            foreach( $block->getChildren() as $child_key => $child ) {
                $children .= $this->renderBlockAdmin($child);
            }
        }
        $render .= $children;

        return $render;
    }

    

    

}
