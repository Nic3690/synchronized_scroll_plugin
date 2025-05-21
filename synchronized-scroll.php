<?php
/**
 * Plugin Name: Synchronized Scroll for Elementor
 * Plugin URI: https://example.com/synchronized-scroll
 * Description: Add synchronized scroll behaviors to any Elementor container
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: sync-scroll-elementor
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

        add_action('wp_footer', [$this, 'add_custom_script']);
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
                'selectors' => [
                    '{{WRAPPER}}' => '--sync-scroll-speed: {{SIZE}};',
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
            'sync_scroll_overflow',
            [
                'label' => __('Overflow', 'sync-scroll-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'hidden',
                'options' => [
                    'visible' => __('Visible', 'sync-scroll-elementor'),
                    'hidden' => __('Hidden', 'sync-scroll-elementor'),
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'overflow-x: {{VALUE}} !important;',
                ],
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
                    'size' => 200,
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
                    'size' => 0.2,
                ],
                'condition' => [
                    'enable_sync_scroll' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-container, {{WRAPPER}} .elementor-widget-wrap' => 'transition: transform {{SIZE}}s ease-out;',
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

            $element->add_render_attribute('_wrapper', 'class', 'sync-scroll-container-parent');
            $element->add_render_attribute('_wrapper', 'id', 'sync-scroll-parent-' . $element_id);
            
            if ($element_type === 'section') {
                $element->add_render_attribute('_wrapper', 'data-sync-scroll-id', $element_id);
                $element->add_render_attribute('_wrapper', 'data-sync-scroll-type', $settings['sync_scroll_type']);
                $element->add_render_attribute('_wrapper', 'data-sync-scroll-direction', $settings['sync_scroll_direction']);
                $element->add_render_attribute('_wrapper', 'data-sync-scroll-speed', $settings['sync_scroll_speed']['size']);
                
                if ('yes' === $settings['sync_scroll_sticky']) {
                    $element->add_render_attribute('_wrapper', 'class', 'sync-scroll-sticky-section');
                }

                if ('horizontal' === $settings['sync_scroll_type']) {
                    $width = isset($settings['sync_scroll_container_width']['size']) ? $settings['sync_scroll_container_width']['size'] : 200;
                    $unit = isset($settings['sync_scroll_container_width']['unit']) ? $settings['sync_scroll_container_width']['unit'] : '%';
                    $element->add_render_attribute('_wrapper', 'data-sync-scroll-width', $width . $unit);
                }
            }

            if ('horizontal' === $settings['sync_scroll_type']) {
                echo '<div class="sync-scroll-overflow-wrapper">';
            }
        }
    }

    public function after_render($element) {
        $settings = $element->get_settings_for_display();
        
        if ('yes' === $settings['enable_sync_scroll']) {
            if ('horizontal' === $settings['sync_scroll_type']) {
                echo '</div>';
            }

            $element_id = $element->get_id();
            ?>
            <script>
                (function($) {
                    $(document).ready(function() {
                        if (typeof initSyncScroll === 'function') {
                            initSyncScroll('#sync-scroll-parent-<?php echo esc_js($element_id); ?>');
                        }
                    });
                })(jQuery);
            </script>
            <?php
        }
    }

    public function add_custom_script() {
        ?>
        <script>
        </script>
        <?php
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
        $css_content = <<<CSS

.sync-scroll-container-parent {
    position: relative;
    overflow: hidden;
}

.sync-scroll-overflow-wrapper {
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.sync-scroll-type-horizontal .elementor-container,
.sync-scroll-type-horizontal .elementor-widget-wrap {
    will-change: transform;
    transition: transform 0.2s ease-out;
}

.sync-scroll-type-vertical .elementor-container,
.sync-scroll-type-vertical .elementor-widget-wrap {
    will-change: transform;
    transition: transform 0.2s ease-out;
}

.sync-scroll-type-parallax .elementor-container,
.sync-scroll-type-parallax .elementor-widget-wrap {
    will-change: transform;
    transition: transform 0.2s ease-out;
}

.sync-scroll-sticky-yes {
    position: sticky;
    top: 0;
}

@media (max-width: 767px) {
    .sync-scroll-container-parent {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}
CSS;
        file_put_contents($css_file, $css_content);
    }

    $js_file = plugin_dir_path(__FILE__) . 'assets/js/sync-scroll.js';
    if (!file_exists($js_file)) {
        $js_content = <<<JS
(function($) {
    'use strict';

    $(document).ready(function() {
        $('.sync-scroll-container-parent').each(function() {
            initSyncScroll($(this));
        });
    });

    window.initSyncScroll = function(selector) {
        const \$parent = $(selector);
        if (\$parent.length === 0) return;
        
        const elementId = \$parent.data('sync-scroll-id');
        const scrollType = \$parent.data('sync-scroll-type') || 'horizontal';
        const scrollDirection = \$parent.data('sync-scroll-direction') || 'normal';
        const scrollSpeed = parseFloat(\$parent.data('sync-scroll-speed')) || 1.0;
        const contentWidth = \$parent.data('sync-scroll-width');
        
        console.log('Initializing sync scroll for', elementId, 'with type', scrollType);

        const \$container = \$parent.find('.elementor-container, .elementor-widget-wrap').first();
        if (\$container.length === 0) {
            console.error('Container element not found in', elementId);
            return;
        }

        if (scrollType === 'horizontal' && contentWidth) {
            \$container.css('width', contentWidth);
            \$container.css('display', 'flex');
            \$container.css('flex-wrap', 'nowrap');

            \$container.find('.elementor-column, .elementor-widget').css({
                'flex-shrink': '0',
                'width': 'auto'
            });
        }

        const originalTransform = \$container.css('transform');

        const parentHeight = \$parent.outerHeight();
        const containerWidth = \$container.outerWidth();
        const viewportWidth = window.innerWidth;
        const maxScroll = containerWidth - viewportWidth;

        const directionFactor = scrollDirection === 'reverse' ? -1 : 1;

        function handleScroll() {
            const rect = \$parent[0].getBoundingClientRect();
            const viewportHeight = window.innerHeight;

            if (rect.top < viewportHeight && rect.bottom > 0) {
                const totalScrollableDistance = parentHeight + viewportHeight;
                const scrollProgress = (viewportHeight - rect.top) / totalScrollableDistance;
                const clampedProgress = Math.max(0, Math.min(scrollProgress, 1));

                if (scrollType === 'horizontal') {
                    const translateX = -maxScroll * clampedProgress * scrollSpeed * directionFactor;
                    \$container.css('transform', 'translateX(' + translateX + 'px)');
                } else if (scrollType === 'vertical') {
                    const translateY = -maxScroll * clampedProgress * scrollSpeed * directionFactor;
                    \$container.css('transform', 'translateY(' + translateY + 'px)');
                } else if (scrollType === 'parallax') {
                    const translateY = -100 * clampedProgress * scrollSpeed * directionFactor;
                    \$container.css('transform', 'translateY(' + translateY + 'px)');
                }
            } else if (rect.bottom < 0) {
                if (scrollType === 'horizontal') {
                    \$container.css('transform', 'translateX(' + (-maxScroll) + 'px)');
                } else if (scrollType === 'vertical' || scrollType === 'parallax') {
                    \$container.css('transform', 'translateY(' + (-maxScroll) + 'px)');
                }
            } else {
                \$container.css('transform', originalTransform);
            }
        }

        $(window).on('scroll', handleScroll);
        $(window).on('resize', function() {
            setTimeout(function() {
                handleScroll();
            }, 100);
        });

        setTimeout(handleScroll, 100);
    };
    
})(jQuery);
JS;
        file_put_contents($js_file, $js_content);
    }
}
register_activation_hook(__FILE__, 'synchronized_scroll_extension_activate');