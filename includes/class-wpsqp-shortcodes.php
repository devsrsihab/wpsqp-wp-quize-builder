<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Shortcodes {
    
    public static function register() {
        // add_shortcode('congress_registration', [self::class, 'renderRegistrationForm']);
    }

    
    
    
    // public static function renderRegistrationForm($atts) {
    //     $congress_id = isset($_GET['congress_id']) ? intval($_GET['congress_id']) : 0;
    //     if (!$congress_id) {
    //         return '<div class="crs-error">Please select a congress to register.</div>';
    //     }
        
    //     ob_start();
    //     include CRS_PLUGIN_DIR . 'templates/registration-form.php';
    //     return ob_get_clean();
    // }
    

}