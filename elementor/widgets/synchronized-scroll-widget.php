<?php
/**
 * Synchronized Scroll Widget per Elementor
 */

// Assicurati che non ci sia accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

class Synchronized_Scroll_Widget extends \Elementor\Widget_Base {

    /**
     * Nome del widget
     */
    public function get_name() {
        return 'synchronized_scroll';
    }

    /**
     * Titolo del widget
     */
    public function get_title() {
        return __('Synchronized Scroll', 'synchronized-scroll');
    }

    /**
     * Icona del widget
     */
    public function get_icon() {
        return 'eicon-mouse-drag';
    }

    /**
     * Categoria del widget
     */
    public function get_categories() {
        return ['synchronized-scroll'];
    }

    /**
     * Keyword per la ricerca
     */
    public function get_keywords() {
        return ['scroll', 'synchronizzato', 'verticale', 'orizzontale', 'scorrimento'];
    }

    /**
     * Script dipendenti
     */
    public function get_script_depends() {
        return ['synchronized-scroll-js'];
    }

    /**
     * Stili dipendenti
     */
    public function get_style_depends() {
        return ['synchronized-scroll-css'];
    }

    /**
     * Definisce i controlli del widget
     */
    protected function _register_controls() {
        // Sezione Impostazioni Principali
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Impostazioni Layout', 'synchronized-scroll'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'vertical_height',
            [
                'label' => __('Altezza Container Verticale', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'vh',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .sync-vertical-container' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'horizontal_height',
            [
                'label' => __('Altezza Container Orizzontale', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'vh',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .sync-horizontal-container' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'horizontal_width',
            [
                'label' => __('Larghezza Contenuto Orizzontale', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 100,
                        'max' => 500,
                        'step' => 25,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .sync-horizontal-content' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'v_direction',
            [
                'label' => __('Direzione Scorrimento Verticale', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'down',
                'options' => [
                    'down' => __('Giù', 'synchronized-scroll'),
                    'up' => __('Su', 'synchronized-scroll'),
                ],
            ]
        );

        $this->add_control(
            'h_direction',
            [
                'label' => __('Direzione Scorrimento Orizzontale', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'right',
                'options' => [
                    'right' => __('Destra', 'synchronized-scroll'),
                    'left' => __('Sinistra', 'synchronized-scroll'),
                ],
            ]
        );

        $this->add_control(
            'scroll_speed',
            [
                'label' => __('Velocità di Scorrimento', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [''],
                'range' => [
                    '' => [
                        'min' => 0.1,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => '',
                    'size' => 1,
                ],
            ]
        );

        $this->add_control(
            'transition',
            [
                'label' => __('Durata Transizione (secondi)', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [''],
                'range' => [
                    '' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.01,
                    ],
                ],
                'default' => [
                    'unit' => '',
                    'size' => 0.05,
                ],
                'selectors' => [
                    '{{WRAPPER}} .sync-vertical-content, {{WRAPPER}} .sync-horizontal-content' => 'transition: transform {{SIZE}}s ease-out;',
                ],
            ]
        );

        $this->end_controls_section();

        // Sezione Contenuto Verticale
        $this->start_controls_section(
            'section_vertical_content',
            [
                'label' => __('Contenuto Verticale', 'synchronized-scroll'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'vertical_content',
            [
                'label' => __('Contenuto Verticale', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => '<h2>Contenuto Verticale</h2><p>Questo è il contenuto che scorre verticalmente. Aggiungi testo, immagini e altri elementi qui.</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>',
            ]
        );

        $this->add_control(
            'vertical_background_color',
            [
                'label' => __('Colore Sfondo', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f5f5f5',
                'selectors' => [
                    '{{WRAPPER}} .vertical-content-inner' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'vertical_padding',
            [
                'label' => __('Padding', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .vertical-content-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Sezione Contenuto Orizzontale
        $this->start_controls_section(
            'section_horizontal_content',
            [
                'label' => __('Contenuto Orizzontale', 'synchronized-scroll'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'item_title',
            [
                'label' => __('Titolo', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Titolo Elemento', 'synchronized-scroll'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'item_content',
            [
                'label' => __('Contenuto', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('Contenuto dell\'elemento qui...', 'synchronized-scroll'),
            ]
        );

        $repeater->add_control(
            'item_background_color',
            [
                'label' => __('Colore Sfondo', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e0f7fa',
            ]
        );

        $this->add_control(
            'horizontal_items',
            [
                'label' => __('Elementi Orizzontali', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'item_title' => __('Elemento 1', 'synchronized-scroll'),
                        'item_content' => __('<p>Contenuto del primo elemento...</p>', 'synchronized-scroll'),
                        'item_background_color' => '#e0f7fa',
                    ],
                    [
                        'item_title' => __('Elemento 2', 'synchronized-scroll'),
                        'item_content' => __('<p>Contenuto del secondo elemento...</p>', 'synchronized-scroll'),
                        'item_background_color' => '#b2ebf2',
                    ],
                    [
                        'item_title' => __('Elemento 3', 'synchronized-scroll'),
                        'item_content' => __('<p>Contenuto del terzo elemento...</p>', 'synchronized-scroll'),
                        'item_background_color' => '#e0f7fa',
                    ],
                ],
                'title_field' => '{{{ item_title }}}',
            ]
        );

        $this->add_control(
            'horizontal_item_padding',
            [
                'label' => __('Padding Elementi', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .horizontal-content-inner .item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Sezione Stili
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Stile', 'synchronized-scroll'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'scroll_bar_color',
            [
                'label' => __('Colore Barra di Scorrimento', 'synchronized-scroll'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(0, 0, 0, 0.2)',
                'selectors' => [
                    '{{WRAPPER}} .sync-scroll-container::-webkit-scrollbar-thumb' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Rendering del widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Genera un ID unico per questo widget
        $widget_id = 'sync-scroll-elementor-' . $this->get_id();
        
        // Prepara le opzioni per lo script JavaScript
        $scroll_speed = isset($settings['scroll_speed']['size']) ? $settings['scroll_speed']['size'] : 1.0;
        
        // Output
        ?>
        <div class="sync-scroll-container" 
             id="<?php echo esc_attr($widget_id); ?>" 
             data-v-direction="<?php echo esc_attr($settings['v_direction']); ?>" 
             data-h-direction="<?php echo esc_attr($settings['h_direction']); ?>"
             data-scroll-speed="<?php echo esc_attr($scroll_speed); ?>">
            
            <div class="sync-vertical-container">
                <div class="sync-vertical-content">
                    <div class="vertical-content-inner">
                        <?php echo wp_kses_post($settings['vertical_content']); ?>
                    </div>
                </div>
            </div>
            
            <div class="sync-horizontal-container">
                <div class="sync-horizontal-content">
                    <div class="horizontal-content-inner">
                        <?php
                        if (!empty($settings['horizontal_items'])) {
                            foreach ($settings['horizontal_items'] as $index => $item) {
                                ?>
                                <div class="item" style="background-color: <?php echo esc_attr($item['item_background_color']); ?>">
                                    <h3><?php echo esc_html($item['item_title']); ?></h3>
                                    <?php echo wp_kses_post($item['item_content']); ?>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Inizializza lo scorrimento sincronizzato
            if (typeof window.initSynchronizedScroll === 'function') {
                window.initSynchronizedScroll($('#<?php echo esc_js($widget_id); ?>'));
            } else {
                console.error('initSynchronizedScroll function not found');
            }
        });
        </script>
        <?php
    }
    
    /**
     * Rendering del contenuto nell'editor Elementor
     */
    protected function content_template() {
		?>
		<#
		var widget_id = 'sync-scroll-elementor-template-' + view.getID();
		var scrollSpeed = settings.scroll_speed ? settings.scroll_speed.size : 1.0;
		#>
		<div class="sync-scroll-container" 
			 id="{{ widget_id }}" 
			 data-v-direction="{{ settings.v_direction }}" 
			 data-h-direction="{{ settings.h_direction }}"
			 data-scroll-speed="{{ scrollSpeed }}">
			
			<div class="sync-vertical-container">
				<div class="sync-vertical-content">
					<div class="vertical-content-inner">
						{{{ settings.vertical_content }}}
					</div>
				</div>
			</div>
			
			<div class="sync-horizontal-container">
				<div class="sync-horizontal-content">
					<div class="horizontal-content-inner">
						<# if (settings.horizontal_items) { #>
							<# _.each(settings.horizontal_items, function(item, index) { #>
								<div class="item" style="background-color: {{ item.item_background_color }}">
									<h3>{{{ item.item_title }}}</h3>
									{{{ item.item_content }}}
								</div>
							<# }); #>
						<# } #>
					</div>
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			if (typeof window.initSynchronizedScroll === 'function') {
				setTimeout(function() {
					window.initSynchronizedScroll($('#{{ widget_id }}'));
				}, 500);
			}
		});
		</script>
		<?php
	}
}