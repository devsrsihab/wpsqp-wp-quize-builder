<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Admin {
    
    public static function addMenus() {
        add_menu_page(
            __('WPSQP Quize ', 'wpsqptxd'),
            __('WPSQP Quize ', 'wpsqptxd'),
            'manage_options',
            'wpsqp-quize',
            [self::class, 'renderDashboard'],
            'dashicons-calendar-alt',
            25
        );
        
        add_submenu_page(
            'wpsqp-quize',
            __('Dashboard', 'wpsqptxd'),
            __('Dashboard', 'wpsqptxd'),
            'manage_options',
            'wpsqp-quize',
            [self::class, 'renderDashboard']
        );

        
       
    }
    
    public static function renderDashboard() {
        global $wpdb;
       
    }
   
}