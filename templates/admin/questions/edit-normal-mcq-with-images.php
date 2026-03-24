<?php
if (!defined('ABSPATH')) exit;

/**
 * Edit template for NORMAL_MCQ_WITH_IMAGES
 * This simply reuses the add form with existing data
 */

// Set variables for the add form
$is_edit = true;
$question = isset($question) ? $question : null;
$type_data = isset($type_data) ? $type_data : null;

// Include the add form which will handle edit mode
include WPSQP_PLUGIN_DIR . 'templates/admin/questions/normal-mcq-with-images.php';