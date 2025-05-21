<?php
/**
 * Plugin Name: Synchronized Scroll
 * Plugin URI: https://example.com/synchronized-scroll
 * Description: Plugin che permette di creare container con scorrimento sincronizzato verticale e orizzontale, con integrazione Elementor
 * Version: 1.1.0
 * Author: Il tuo nome
 * Author URI: https://example.com
 * Text Domain: synchronized-scroll
 */

// Impedisce l'accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

// Percorso del plugin
define('SYNC_SCROLL_PATH', plugin_dir_path(__FILE__));
define('SYNC_SCROLL_URL', plugin_dir_url(__FILE__));

class Synchronized_Scroll {
    
    /**
     * Costruttore - Inizializza il plugin
     */
    public function __construct() {
        // Registra lo shortcode [synchronized_scroll]
        add_shortcode('synchronized_scroll', array($this, 'synchronized_scroll_shortcode'));
        add_shortcode('vertical_content', array($this, 'vertical_content_shortcode'));
        add_shortcode('horizontal_content', array($this, 'horizontal_content_shortcode'));
        
        // Carica CSS e JS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Integrazione con Elementor
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_widget_category'));
    }
    
    /**
     * Registra e carica CSS e JavaScript
     */
    public function enqueue_scripts() {
        // Registra e carica lo stile CSS
        wp_enqueue_style(
            'synchronized-scroll-css',
            SYNC_SCROLL_URL . 'assets/css/synchronized-scroll.css',
            array(),
            '1.1.0'
        );
        
        // Registra e carica lo script JavaScript
        wp_enqueue_script(
            'synchronized-scroll-js',
            SYNC_SCROLL_URL . 'assets/js/synchronized-scroll.js',
            array('jquery'),
            '1.1.0',
            true
        );
    }
    
    /**
     * Shortcode per il contenuto verticale
     */
    public function vertical_content_shortcode($atts, $content = null) {
        return '<div class="vertical-content-inner">' . do_shortcode($content) . '</div>';
    }
    
    /**
     * Shortcode per il contenuto orizzontale
     */
    public function horizontal_content_shortcode($atts, $content = null) {
        return '<div class="horizontal-content-inner">' . do_shortcode($content) . '</div>';
    }
    
    /**
     * Shortcode per inserire i container di scorrimento sincronizzato
     */
    public function synchronized_scroll_shortcode($atts, $content = null) {
        // Attributi dello shortcode con valori predefiniti
        $attributes = shortcode_atts(
            array(
                'vertical_height' => '50vh',      // Altezza del container verticale
                'horizontal_height' => '50vh',    // Altezza del container orizzontale
                'mobile_height' => '400px',       // Altezza su mobile
                'breakpoint' => '769px',          // Breakpoint per il cambio a mobile
                'horizontal_width' => '300%',     // Larghezza del contenuto orizzontale
                'v_direction' => 'down',          // Direzione scorrimento verticale (up, down)
                'h_direction' => 'right',         // Direzione scorrimento orizzontale (left, right)
                'scroll_speed' => '1.0',          // Velocità di scorrimento (moltiplicatore)
                'transition' => '0.05s'           // Durata della transizione per l'effetto smooth
            ),
            $atts
        );
        
        // Carica CSS e JS necessari (assicuriamo che siano caricati)
        if (!wp_script_is('synchronized-scroll-js', 'enqueued')) {
            wp_enqueue_style('synchronized-scroll-css');
            wp_enqueue_script('synchronized-scroll-js');
        }
        
        // Genera un ID unico per questa istanza
        $unique_id = 'sync-scroll-' . uniqid();
        
        // Utilizziamo preg_match_all per estrarre contenuti dagli shortcode nidificati
        $vertical_content = '';
        if (preg_match_all('/\[vertical_content\](.*?)\[\/vertical_content\]/is', $content, $vert_matches)) {
            $vertical_content = '<div class="vertical-content-inner">' . do_shortcode($vert_matches[1][0]) . '</div>';
        } else {
            $vertical_content = $this->get_default_vertical_content();
        }
        
        $horizontal_content = '';
        if (preg_match_all('/\[horizontal_content\](.*?)\[\/horizontal_content\]/is', $content, $horiz_matches)) {
            $horizontal_content = '<div class="horizontal-content-inner">' . do_shortcode($horiz_matches[1][0]) . '</div>';
        } else {
            $horizontal_content = $this->get_default_horizontal_content();
        }
        
        // CSS inline per applicare le altezze personalizzate
        $custom_css = '<style>
            #' . $unique_id . ' {
                height: 100vh;
                width: 100%;
                overflow-y: scroll;
                position: relative;
            }
            #' . $unique_id . ' .sync-vertical-container {
                height: ' . esc_attr($attributes['vertical_height']) . ';
            }
            #' . $unique_id . ' .sync-horizontal-container {
                height: ' . esc_attr($attributes['horizontal_height']) . ';
            }
            #' . $unique_id . ' .sync-vertical-content,
            #' . $unique_id . ' .sync-horizontal-content {
                transition: transform ' . esc_attr($attributes['transition']) . ' ease-out;
            }
            #' . $unique_id . ' .sync-horizontal-content {
                width: ' . esc_attr($attributes['horizontal_width']) . ';
            }
            @media (max-width: ' . esc_attr($attributes['breakpoint']) . ') {
                #' . $unique_id . ' .sync-vertical-container {
                    height: ' . esc_attr($attributes['mobile_height']) . ';
                }
                #' . $unique_id . ' .sync-horizontal-container {
                    height: calc(100vh - ' . esc_attr($attributes['mobile_height']) . ');
                }
            }
        </style>';
        
        // Aggiungi attributi data per JavaScript
        $data_attributes = 'data-v-direction="' . esc_attr($attributes['v_direction']) . '" 
                           data-h-direction="' . esc_attr($attributes['h_direction']) . '"
                           data-scroll-speed="' . esc_attr($attributes['scroll_speed']) . '"';
        
        // Output HTML
        $output = $custom_css . '
        <div class="sync-scroll-container" id="' . esc_attr($unique_id) . '" ' . $data_attributes . '>
            <div class="sync-vertical-container">
                <div class="sync-vertical-content">
                    ' . $vertical_content . '
                </div>
            </div>
            
            <div class="sync-horizontal-container">
                <div class="sync-horizontal-content">
                    ' . $horizontal_content . '
                </div>
            </div>
        </div>';
        
        return $output;
    }
    
    /**
     * Contenuto verticale predefinito
     */
    private function get_default_vertical_content() {
        return '
        <div class="vertical-content-inner">
            <h2>Contenuto Verticale</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        </div>';
    }
    
    /**
     * Contenuto orizzontale predefinito
     */
    private function get_default_horizontal_content() {
        return '
        <div class="horizontal-content-inner">
            <div class="item">
                <h3>Elemento 1</h3>
                <p>Descrizione elemento 1</p>
            </div>
            <div class="item">
                <h3>Elemento 2</h3>
                <p>Descrizione elemento 2</p>
            </div>
            <div class="item">
                <h3>Elemento 3</h3>
                <p>Descrizione elemento 3</p>
            </div>
            <div class="item">
                <h3>Elemento 4</h3>
                <p>Descrizione elemento 4</p>
            </div>
            <div class="item">
                <h3>Elemento 5</h3>
                <p>Descrizione elemento 5</p>
            </div>
        </div>';
    }
    
    /**
     * Aggiunge categoria widget per Elementor
     */
    public function add_elementor_widget_category($elements_manager) {
        $elements_manager->add_category(
            'synchronized-scroll',
            [
                'title' => __('Synchronized Scroll', 'synchronized-scroll'),
                'icon' => 'fa fa-arrows-alt',
            ]
        );
    }
    
    /**
     * Registra widget Elementor
     */
    public function register_elementor_widgets() {
        // Richiede il file del widget solo se Elementor è attivo
        if (did_action('elementor/loaded')) {
            require_once(SYNC_SCROLL_PATH . 'elementor/widgets/synchronized-scroll-widget.php');
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Synchronized_Scroll_Widget());
        }
    }
}

// Inizializza il plugin
$synchronized_scroll = new Synchronized_Scroll();
?>