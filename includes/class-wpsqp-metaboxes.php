<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Metaboxes {
    
    // public static function addMetaBoxes() {
    //     add_meta_box(
    //         'crs_congress_details',
    //         __('Congress Details', 'crscngres'),
    //         [self::class, 'renderCongressDetails'],
    //         'congress',
    //         'normal',
    //         'high'
    //     );
        
    //     add_meta_box(
    //         'crs_congress_meals',
    //         __('Congress Meals', 'crscngres'),
    //         [self::class, 'renderCongressMeals'],
    //         'congress',
    //         'normal',
    //         'default'
    //     );

    // }
    
    // public static function renderCongressDetails($post) {
    //     wp_nonce_field('crs_save_congress', 'crs_congress_nonce');
    //     $start_date = get_post_meta($post->ID, 'start_date', true);
    //     $end_date = get_post_meta($post->ID, 'end_date', true);
    //     $location = get_post_meta($post->ID, 'location', true);
    //     $registration_deadline = get_post_meta($post->ID, 'registration_deadline', true);
    //     include CRS_PLUGIN_DIR . 'templates/metaboxes/congress-details.php';
    // }
    
    // public static function renderCongressMeals($post) {
    //     $meals = get_post_meta($post->ID, 'congress_meals', true);
    //     if (!is_array($meals)) $meals = [];
    //     if (empty($meals)) {
    //         $meals[] = ['meal_title' => '', 'meal_type' => 'Meal', 'meal_date' => '', 'meal_price' => '', 'meal_status' => 'Enable'];
    //     }
    //     include CRS_PLUGIN_DIR . 'templates/metaboxes/congress-meals.php';
    // }
    
   
}