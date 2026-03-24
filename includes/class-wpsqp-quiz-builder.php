<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Quiz_Builder {
    
    public static function render() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        // Get categories
        $categories = $wpdb->get_results("SELECT * FROM {$table_prefix}categories WHERE is_active = 1 ORDER BY display_order");
        
        // Get tests if editing
        $test_id = isset($_GET['test_id']) ? intval($_GET['test_id']) : 0;
        $test = null;
        $test_questions = [];
        
        if ($test_id) {
            $test = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_prefix}tests WHERE id = %d",
                $test_id
            ));
            
            if ($test) {
                $test_questions = $wpdb->get_results($wpdb->prepare(
                    "SELECT tq.*, q.question_type, q.question_content 
                    FROM {$table_prefix}test_questions tq
                    JOIN {$table_prefix}questions q ON tq.question_id = q.id
                    WHERE tq.test_id = %d
                    ORDER BY tq.question_number",
                    $test_id
                ));
            }
        }
        
        // Get all questions for selection
        $all_questions = $wpdb->get_results(
            "SELECT q.id, q.question_type, q.question_content, 
                    COUNT(tq.id) as used_count
            FROM {$table_prefix}questions q
            LEFT JOIN {$table_prefix}test_questions tq ON q.id = tq.question_id
            GROUP BY q.id
            ORDER BY q.created_at DESC
            LIMIT 50"
        );
        
        // Enqueue assets
        wp_enqueue_media();
        wp_enqueue_style('wpsqp-quiz-builder', WPSQP_PLUGIN_URL . 'admin/css/quiz-builder.css', [], WPSQP_VERSION);
        wp_enqueue_script('wpsqp-quiz-builder', WPSQP_PLUGIN_URL . 'admin/js/quiz-builder.js', ['jquery'], WPSQP_VERSION, true);
        

        
        include WPSQP_PLUGIN_DIR . 'templates/admin/quiz-builder.php';
    }
    
    public static function saveTest() {
        check_ajax_referer('wpsqp_quiz_nonce', 'nonce');
        
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $test_id = isset($_POST['test_id']) ? intval($_POST['test_id']) : 0;
        $category_id = intval($_POST['category_id']);
        $title = sanitize_text_field($_POST['title']);
        $slug = sanitize_title($title);
        $description = wp_kses_post($_POST['description']);
        $time_limit = intval($_POST['time_limit']);
        $status = sanitize_text_field($_POST['status']);
        
        $data = [
            'category_id' => $category_id,
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'time_limit' => $time_limit,
            'status' => $status,
            'total_questions' => isset($_POST['questions']) ? count($_POST['questions']) : 0
        ];
        
        if ($test_id) {
            $wpdb->update($table_prefix . 'tests', $data, ['id' => $test_id]);
            
            // Delete existing questions
            $wpdb->delete($table_prefix . 'test_questions', ['test_id' => $test_id]);
        } else {
            $data['created_by'] = get_current_user_id();
            $wpdb->insert($table_prefix . 'tests', $data);
            $test_id = $wpdb->insert_id;
        }
        
        // Save questions
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            foreach ($_POST['questions'] as $index => $question_id) {
                $wpdb->insert($table_prefix . 'test_questions', [
                    'test_id' => $test_id,
                    'question_id' => sanitize_text_field($question_id),
                    'question_number' => $index + 1,
                    'display_order' => $index + 1
                ]);
            }
        }
        
        // Save instructions
        if (isset($_POST['instructions']) && is_array($_POST['instructions'])) {
            $wpdb->delete($table_prefix . 'instructions', ['test_id' => $test_id]);
            
            foreach ($_POST['instructions'] as $index => $instruction) {
                if (!empty($instruction['content'])) {
                    $wpdb->insert($table_prefix . 'instructions', [
                        'test_id' => $test_id,
                        'page_number' => $index + 1,
                        'title' => sanitize_text_field($instruction['title']),
                        'content' => wp_kses_post($instruction['content']),
                        'display_order' => $index + 1
                    ]);
                }
            }
        }
        
        wp_send_json_success([
            'test_id' => $test_id,
            'message' => __('Test saved successfully!', 'wpsqptxd')
        ]);
    }
}