<?php
if (!defined('ABSPATH')) exit;

/**
 * Edit template for NORMAL_MCQ_WITH_IMAGES
 * Reuses the add form with existing data
 */

$is_edit = true;
$question = isset($question) ? $question : null;
$type_data = isset($type_data) ? $type_data : null;

// Load the main form template
include WPSQP_PLUGIN_DIR . 'templates/admin/questions/normal-mcq-with-images.php';