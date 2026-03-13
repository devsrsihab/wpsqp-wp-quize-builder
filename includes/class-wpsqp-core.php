<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Core {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->loadDependencies();
        $this->initHooks();
    }
    
    private function loadDependencies() {
        // Load all required classes
        $files = [
        'includes/class-wpsqp-install.php',
        'includes/class-wpsqp-admin.php',
        'includes/class-wpsqp-metaboxes.php',
        'includes/class-wpsqp-ajax.php',
        'includes/class-wpsqp-shortcodes.php',


        ];
        
    
        foreach ($files as $file) {
            $path = WPSQP_PLUGIN_DIR . $file;

            
            if (file_exists($path)) {
                require_once $path;
            } 
        }
    }
    
    private function initHooks() {
        // Load text domain
        add_action('init', [$this, 'loadPluginTextDomain']);
        
        // Register shortcodes
        add_action('init', [$this, 'registerShortcodes']);
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Database creation
        add_action('init', [WPSQP_Install::class, 'createTables']);
        
        // Admin menus
        add_action('admin_menu', [WPSQP_Admin::class, 'addMenus']);
        
        // Meta boxes

        // AJAX handlers
        WPSQP_Ajax::init();
    
        
        // WooCommerce order meta box
        add_action('add_meta_boxes', [$this, 'addOrderMetaBox']);
    }
    
    public function loadPluginTextDomain() {
        load_plugin_textdomain(
            'wpsqpcngres',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    public function registerShortcodes() {
        WPSQP_Shortcodes::register();
    }
    
    public function enqueueAssets() {
        wp_enqueue_style('wpsqp-frontend', WPSQP_PLUGIN_URL . 'assets/css/frontend.css', [], WPSQP_VERSION);
        wp_enqueue_script('wpsqp-frontend', WPSQP_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], WPSQP_VERSION, true);
        

        wp_localize_script('wpsqp-frontend', 'wpsqp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpsqp_nonce'),
            'user_logged_in' => is_user_logged_in(),
        ]);
    }
    

    
    public function enqueueAdminAssets($hook) {
        if (strpos($hook, 'wpsqp-') !== false || $hook == 'post.php' || $hook == 'post-new.php') {
            wp_enqueue_style('wpsqp-admin', WPSQP_PLUGIN_URL . 'assets/css/admin-style.css', [], WPSQP_VERSION);
            wp_enqueue_script('wpsqp-admin', WPSQP_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WPSQP_VERSION, true);
        }
    }
    
    public function addOrderMetaBox() {
        
    }
}