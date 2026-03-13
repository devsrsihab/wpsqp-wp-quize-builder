<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Ajax {
    
    public static function init() {
        $ajax_handlers = [
            // 'wpsqp_load_step' => ['logged_in' => true, 'nopriv' => true],
            // 'wpsqp_send_invoice' => ['logged_in' => true, 'capability' => 'manage_options'],
            // 'wpsqp_send_test_email' => ['logged_in' => true, 'capability' => 'manage_options'],
            // 'wpsqp_stripe_webhook' => ['logged_in' => false, 'nopriv' => true]
        ];
        
        foreach ($ajax_handlers as $handler => $args) {
            if ($args['logged_in']) {
                add_action('wp_ajax_' . $handler, [self::class, $handler]);
            }
            if (!empty($args['nopriv'])) {
                add_action('wp_ajax_nopriv_' . $handler, [self::class, $handler]);
            }
        }
    }
    

}