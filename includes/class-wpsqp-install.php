<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Install {
    
    public static function createTables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Bookings table
        $table_name = $wpdb->prefix . 'wpsqp_test';

 
    }
}