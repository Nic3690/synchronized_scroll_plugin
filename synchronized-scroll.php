<?php
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
            SYNC_SCROLL_URL . 'assets/css/sync-scroll.css',
            [],
            SYNC_SCROLL_VERSION
        );

        wp_register_script(
            'sync-scroll-elementor-js',
            SYNC_SCROLL_URL . 'assets/js/sync-scroll.js',
            ['jquery'],
            SYNC_SCROLL_VERSION,
            true
        );
        
        wp_register_script(
            'page-sync-scroll-js',
            SYNC_SCROLL_URL . 'assets/js/page-sync-scroll.js',
            [],
            SYNC_SCROLL_VERSION,
            true
        );
        
        wp_enqueue_style('sync-scroll-elementor-css');
        wp_enqueue_script('page-sync-scroll-js');
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
            ]
        );
        
        $element->end_controls_section();
    }

    public function before_render($element) {
        $settings = $element->get_settings_for_display();
        
        if ('yes' === $settings['enable_sync_scroll']) {
            $element_type = $element->get_type();
            $element_id = $element->get_id();
            
            $element->add_render_attribute('_wrapper', 'data-scroll-speed', $settings['sync_scroll_speed']['size']);
            
            if (isset($settings['sync_scroll_container_width']['size']) && isset($settings['sync_scroll_container_width']['unit'])) {
                $width = $settings['sync_scroll_container_width']['size'] . $settings['sync_scroll_container_width']['unit'];
                $element->add_render_attribute('_wrapper', 'data-scroll-width', $width);
            }
            
            if (isset($settings['sync_scroll_transition']['size'])) {
                $transition = $settings['sync_scroll_transition']['size'];
                $element->add_render_attribute('_wrapper', 'data-scroll-transition', $transition);
            }
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
    if (!file_exists(plugin_dir_path(__FILE__) . 'assets')) {
        mkdir(plugin_dir_path(__FILE__) . 'assets', 0755);
    }
    
    if (!file_exists(plugin_dir_path(__FILE__) . 'assets/css')) {
        mkdir(plugin_dir_path(__FILE__) . 'assets/css', 0755);
    }
    
    if (!file_exists(plugin_dir_path(__FILE__) . 'assets/js')) {
        mkdir(plugin_dir_path(__FILE__) . 'assets/js', 0755);
    }
    
    $css_file = plugin_dir_path(__FILE__) . 'assets/css/sync-scroll.css';
    if (!file_exists($css_file)) {
        $css_content = '.sync-scroll-yes {
    overflow: visible !important;
    position: relative;
}

.sync-scroll-yes > .elementor-container,
.sync-scroll-yes > .e-con-inner {
    overflow: hidden !important;
}

.sync-scroll-sticky-yes {
    position: sticky !important;
    top: 0;
    z-index: 10;
}

.sync-scroll-type-horizontal > .elementor-container,
.sync-scroll-type-horizontal > .e-con-inner {
    display: flex !important;
    flex-wrap: nowrap !important;
    width: 300% !important;
}

.sync-scroll-type-horizontal > .elementor-container > .elementor-column,
.sync-scroll-type-horizontal > .elementor-container > .elementor-widget,
.sync-scroll-type-horizontal > .e-con-inner > .e-con,
.sync-scroll-type-horizontal > .e-con-inner > .elementor-widget {
    flex-shrink: 0 !important;
    width: auto !important;
}

@media (max-width: 767px) {
    .sync-scroll-yes {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
}';
        file_put_contents($css_file, $css_content);
    }
    
    $js_file = plugin_dir_path(__FILE__) . 'assets/js/page-sync-scroll.js';
    if (!file_exists($js_file)) {
        $js_content = 'document.addEventListener(\'DOMContentLoaded\', function() {
    const scrollContainers = [];
    
    initScrollContainers();
    
    function initScrollContainers() {
        const containers = document.querySelectorAll(\'.sync-scroll-yes\');
        
        if (containers.length === 0) {
            return;
        }
        
        containers.forEach(function(container) {
            const scrollType = container.classList.contains(\'sync-scroll-type-vertical\') ? \'vertical\' : 
                               container.classList.contains(\'sync-scroll-type-parallax\') ? \'parallax\' : \'horizontal\';
            
            const directionReverse = container.classList.contains(\'sync-scroll-direction-reverse\');
            const scrollSpeed = parseFloat(container.dataset.scrollSpeed || container.getAttribute(\'data-sync-scroll-speed\') || 1);
            
            const innerContainer = container.querySelector(\'.elementor-container, .e-con-inner\');
            
            if (!innerContainer) {
                return;
            }
            
            setupContainerStyles(container, innerContainer, scrollType);
            
            scrollContainers.push({
                container: container,
                innerContainer: innerContainer,
                type: scrollType,
                reverse: directionReverse,
                speed: scrollSpeed,
                scrollWidth: 0,
                scrollHeight: 0
            });
        });
        
        if (scrollContainers.length > 0) {
            window.addEventListener(\'scroll\', handlePageScroll);
            window.addEventListener(\'resize\', updateContainerDimensions);
            updateContainerDimensions();
            handlePageScroll();
        }
    }
    
    function updateContainerDimensions() {
        scrollContainers.forEach(function(item) {
            if (item.type === \'horizontal\') {
                const containerWidth = item.innerContainer.scrollWidth;
                const viewportWidth = window.innerWidth;
                item.scrollWidth = containerWidth - viewportWidth;
            } else if (item.type === \'vertical\') {
                const containerHeight = item.innerContainer.scrollHeight;
                const viewportHeight = item.container.offsetHeight;
                item.scrollHeight = containerHeight - viewportHeight;
            }
        });
    }
    
    function setupContainerStyles(container, innerContainer, scrollType) {
        container.style.overflow = \'visible\';
        
        if (scrollType === \'horizontal\') {
            innerContainer.style.display = \'flex\';
            innerContainer.style.flexWrap = \'nowrap\';
            innerContainer.style.width = \'300%\';
            innerContainer.style.willChange = \'transform\';
            innerContainer.style.transition = \'transform 0.1s ease-out\';
            
            Array.from(innerContainer.children).forEach(function(child) {
                child.style.flexShrink = \'0\';
                child.style.width = \'auto\';
            });
        } else if (scrollType === \'vertical\') {
            innerContainer.style.height = \'200%\';
            innerContainer.style.willChange = \'transform\';
            innerContainer.style.transition = \'transform 0.1s ease-out\';
        } else if (scrollType === \'parallax\') {
            innerContainer.style.willChange = \'transform\';
            innerContainer.style.transition = \'transform 0.1s ease-out\';
        }
    }
    
    function handlePageScroll() {
        scrollContainers.forEach(function(item) {
            const rect = item.container.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            
            if (rect.top < windowHeight && rect.bottom > 0) {
                const containerHeight = item.container.offsetHeight;
                const scrollProgress = Math.min(1, Math.max(0, 
                    (windowHeight - rect.top) / (containerHeight + windowHeight)
                ));
                
                const directionFactor = item.reverse ? 1 : -1;
                
                if (item.type === \'horizontal\') {
                    const translateX = directionFactor * scrollProgress * item.scrollWidth * item.speed;
                    item.innerContainer.style.transform = `translateX(${translateX}px)`;
                } else if (item.type === \'vertical\') {
                    const translateY = directionFactor * scrollProgress * item.scrollHeight * item.speed;
                    item.innerContainer.style.transform = `translateY(${translateY}px)`;
                } else if (item.type === \'parallax\') {
                    const viewportCenter = windowHeight / 2;
                    const elementCenter = rect.top + (containerHeight / 2);
                    const distance = viewportCenter - elementCenter;
                    const maxDistance = windowHeight + containerHeight;
                    const parallaxProgress = distance / maxDistance * 2;
                    
                    const translateY = directionFactor * parallaxProgress * 100 * item.speed;
                    item.innerContainer.style.transform = `translateY(${translateY}px)`;
                }
            }
        });
    }
});';
        file_put_contents($js_file, $js_content);
    }
}
register_activation_hook(__FILE__, 'synchronized_scroll_extension_activate');