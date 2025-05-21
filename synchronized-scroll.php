<?php
/*
Plugin Name: Synchronized Scroll
Plugin URI: https://example.com/
Description: Create synchronized scrolling containers with complete control over direction, height, and speed.
Version: 1.0.0
Author: DevXX
Author URI: https://example.com/
Text Domain: synchronized-scroll
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

define('SYNC_SCROLL_VERSION', '1.0.0');
define('SYNC_SCROLL_URL', plugin_dir_url(__FILE__));
define('SYNC_SCROLL_PATH', plugin_dir_path(__FILE__));

final class Synchronized_Scroll_Extension {
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    public function init_plugin() {
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
        add_action('elementor/element/section/section_layout/after_section_end', [$this, 'register_controls'], 10, 2);
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'register_controls'], 10, 2);
        
        add_action('elementor/frontend/section/before_render', [$this, 'before_render']);
        add_action('elementor/frontend/container/before_render', [$this, 'before_render']);
        add_action('elementor/frontend/section/after_render', [$this, 'after_render']);
        add_action('elementor/frontend/container/after_render', [$this, 'after_render']);
        
        // Aggiungiamo un'azione per il piè di pagina per garantire che il nostro codice JS sia caricato
        add_action('wp_footer', [$this, 'debug_output'], 100);
    }
    
    public function debug_output() {
        // Questo aiuta a verificare se il plugin è attivo
        echo "<!-- Synchronized Scroll Plugin Active -->";
    }

    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'sync-scroll-elementor'),
            '<strong>' . esc_html__('Synchronized Scroll for Elementor', 'sync-scroll-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'sync-scroll-elementor') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function register_scripts() {
        wp_register_style(
            'sync-scroll-elementor-css',
            SYNC_SCROLL_URL . 'assets/css/synchronized-scroll.css',
            [],
            SYNC_SCROLL_VERSION
        );

        wp_register_script(
            'sync-scroll-elementor-js',
            SYNC_SCROLL_URL . 'assets/js/synchronized-scroll.js',
            ['jquery'],
            SYNC_SCROLL_VERSION,
            true
        );
        
        wp_enqueue_style('sync-scroll-elementor-css');
        wp_enqueue_script('sync-scroll-elementor-js');
    }

    public function register_controls($element, $section_id) {
        $element->start_controls_section(
            'section_sync_scroll',
            [
                'label' => __('Synchronized Scroll', 'sync-scroll-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_LAYOUT,
            ]
        );

        $element->add_control(
            'enable_sync_scroll',
            [
                'label' => __('Enable Synchronized Scroll', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'label_on' => __('Yes', 'sync-scroll-elementor'),
                'label_off' => __('No', 'sync-scroll-elementor'),
                'return_value' => 'yes',
                'prefix_class' => 'sync-scroll-',
            ]
        );
        
        $element->add_control(
            'sync_scroll_type',
            [
                'label' => __('Scroll Type', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', 'sync-scroll-elementor'),
                    'vertical' => __('Vertical', 'sync-scroll-elementor'),
                    'parallax' => __('Parallax', 'sync-scroll-elementor'),
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'prefix_class' => 'sync-scroll-type-',
            ]
        );
        
        $element->add_control(
            'sync_scroll_direction',
            [
                'label' => __('Scroll Direction', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => __('Normal', 'sync-scroll-elementor'),
                    'reverse' => __('Reverse', 'sync-scroll-elementor'),
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'prefix_class' => 'sync-scroll-direction-',
            ]
        );
        
        $element->add_control(
            'sync_scroll_speed',
            [
                'label' => __('Scroll Speed', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0.1,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
            ]
        );
        
        $element->add_control(
            'sync_scroll_sticky',
            [
                'label' => __('Make Section Sticky', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'label_on' => __('Yes', 'sync-scroll-elementor'),
                'label_off' => __('No', 'sync-scroll-elementor'),
                'return_value' => 'yes',
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'prefix_class' => 'sync-scroll-sticky-',
            ]
        );
        
        $element->add_control(
            'sync_scroll_height',
            [
                'label' => __('Section Height', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 2000,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 200,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 200,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'vh',
                    'size' => 100,
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'height: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $element->add_control(
            'sync_scroll_container_width',
            [
                'label' => __('Content Width', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 100,
                        'max' => 500,
                        'step' => 10,
                    ],
                    'px' => [
                        'min' => 500,
                        'max' => 5000,
                        'step' => 100,
                    ],
                    'vw' => [
                        'min' => 100,
                        'max' => 500,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 300,
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                    'sync_scroll_type' => 'horizontal',
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-container, {{WRAPPER}} > .e-con-inner' => 'width: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $element->add_control(
            'sync_scroll_transition',
            [
                'label' => __('Transition Duration (seconds)', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 2,
                        'step' => 0.05,
                    ],
                ],
                'default' => [
                    'size' => 0.1,
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-container, {{WRAPPER}} > .e-con-inner' => 'transition: transform {{SIZE}}s ease-out !important;',
                ],
            ]
        );
        
        $element->add_control(
            'sync_scroll_content_height',
            [
                'label' => __('Content Height', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px', 'vh'],
                'range' => [
                    '%' => [
                        'min' => 100,
                        'max' => 500,
                        'step' => 10,
                    ],
                    'px' => [
                        'min' => 100,
                        'max' => 2000,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 100,
                        'max' => 500,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 200,
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                    'sync_scroll_type' => 'vertical',
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-container, {{WRAPPER}} > .e-con-inner' => 'height: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $element->end_controls_section();
    }

    public function before_render($element) {
        $settings = $element->get_settings_for_display();
        
        if ('yes' === $settings['enable_sync_scroll']) {
            $element_type = $element->get_type();
            $element_id = $element->get_id();
            
            // Aggiungi attributi dati al wrapper per JavaScript
            $element->add_render_attribute('_wrapper', 'data-sync-scroll', 'true');
            $element->add_render_attribute('_wrapper', 'data-scroll-speed', $settings['sync_scroll_speed']['size']);
            
            if (isset($settings['sync_scroll_type'])) {
                $element->add_render_attribute('_wrapper', 'data-scroll-type', $settings['sync_scroll_type']);
            }
            
            if (isset($settings['sync_scroll_direction'])) {
                $element->add_render_attribute('_wrapper', 'data-scroll-direction', $settings['sync_scroll_direction']);
            }
            
            if (isset($settings['sync_scroll_container_width']['size']) && isset($settings['sync_scroll_container_width']['unit'])) {
                $width = $settings['sync_scroll_container_width']['size'] . $settings['sync_scroll_container_width']['unit'];
                $element->add_render_attribute('_wrapper', 'data-scroll-width', $width);
            }
            
            if (isset($settings['sync_scroll_content_height']['size']) && isset($settings['sync_scroll_content_height']['unit'])) {
                $height = $settings['sync_scroll_content_height']['size'] . $settings['sync_scroll_content_height']['unit'];
                $element->add_render_attribute('_wrapper', 'data-scroll-height', $height);
            }
            
            if (isset($settings['sync_scroll_transition']['size'])) {
                $transition = $settings['sync_scroll_transition']['size'];
                $element->add_render_attribute('_wrapper', 'data-scroll-transition', $transition);
            }
            
            // Aggiungiamo una classe CSS specifica per assicurarci che venga selezionata
            $element->add_render_attribute('_wrapper', 'class', 'sync-scroll-element');
        }
    }

    public function after_render($element) {
        $settings = $element->get_settings_for_display();
        
        if ('yes' === $settings['enable_sync_scroll']) {
            // No additional code needed for page scrolling mode
        }
    }
}

Synchronized_Scroll_Extension::instance();

function synchronized_scroll_extension_activate() {
    // Crea le cartelle necessarie
    if (!file_exists(plugin_dir_path(__FILE__) . 'assets')) {
        mkdir(plugin_dir_path(__FILE__) . 'assets', 0755);
    }
    
    if (!file_exists(plugin_dir_path(__FILE__) . 'assets/css')) {
        mkdir(plugin_dir_path(__FILE__) . 'assets/css', 0755);
    }
    
    if (!file_exists(plugin_dir_path(__FILE__) . 'assets/js')) {
        mkdir(plugin_dir_path(__FILE__) . 'assets/js', 0755);
    }
}
register_activation_hook(__FILE__, 'synchronized_scroll_extension_activate');
?>