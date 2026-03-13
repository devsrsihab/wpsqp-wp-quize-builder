<?php
/**
 * Plugin Name: WP Selective Quize Pro
 * Description: A practice test and quiz system for selective school preparation with categories, instructions, progress tracking, and result review.
 * Version: 1.0.0
 * Author: Md. Sohanur Rahman Sihab
 * Author URI: https://example.com
 * License: GPL2+
 * Text Domain: wpsqptxd
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'WPSQP_VERSION', '1.0.0' );
define( 'WPSQP_PLUGIN_FILE', __FILE__ );
define('WPSQP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSQP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for classes
spl_autoload_register(function ($class) {
    // Only autoload WPSQP_ classes
    if (strpos($class, 'WPSQP_') !== 0) {
        return;
    }
    
    // Convert class name to file name
    $class_name = str_replace('wpsqp_', '', $class);
    $file_name = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
    
    // Possible paths
    $paths = [
        WPSQP_PLUGIN_DIR . 'includes/' . $file_name,
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Initialize plugin
function wpsqp_init() {
    require_once WPSQP_PLUGIN_DIR . 'includes/class-wpsqp-core.php';
    WPSQP_Core::getInstance();
}
add_action('plugins_loaded', 'wpsqp_init');