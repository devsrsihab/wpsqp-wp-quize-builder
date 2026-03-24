<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Ajax {
    
    public static function init() {
        $ajax_handlers = [
            'wpsqp_save_test'          => ['logged_in' => true, 'capability' => 'manage_options'],
            'wpsqp_save_category'      => ['logged_in' => true, 'capability' => 'manage_options'],
            'wpsqp_delete_category'    => ['logged_in' => true, 'capability' => 'manage_options'],
            'wpsqp_save_question'      => ['logged_in' => true, 'capability' => 'manage_options'],
            'wpsqp_delete_question'    => ['logged_in' => true, 'capability' => 'manage_options'],
            'wpsqp_get_question_data'  => ['logged_in' => true, 'capability' => 'manage_options'],
            'wpsqp_load_view_template' => ['logged_in' => true, 'capability' => 'manage_options'],
        ];
        
        foreach ($ajax_handlers as $handler => $args) {
            if ($args['logged_in']) {
                add_action('wp_ajax_' . $handler, [self::class, $handler]);
            }
        }
    }
    
    /**
     * Save question handler
     */
    public static function wpsqp_save_question() {
        error_log('=== wpsqp_save_question called ===');
        error_log('POST data: ' . print_r($_POST, true));
        
        // Verify nonce
        if (!isset($_POST['wpsqp_question_nonce']) || !wp_verify_nonce($_POST['wpsqp_question_nonce'], 'wpsqp_save_question')) {
            error_log('Nonce verification failed');
            wp_send_json_error('Security check failed. Please refresh the page.');
        }
        
        // Check capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        // ✅ FIX: empty check করো — empty string হলে নতুন id generate করো
        $question_id   = !empty($_POST['question_id'])
            ? sanitize_text_field($_POST['question_id'])
            : uniqid('q_');
        $question_type    = isset($_POST['question_type'])    ? sanitize_text_field($_POST['question_type'])    : '';
        $question_content = isset($_POST['question_content']) ? wp_kses_post($_POST['question_content'])        : '';
        
        if (empty($question_type)) {
            wp_send_json_error('Question type is required');
        }
        
        error_log('Saving question: ' . $question_id . ' of type: ' . $question_type);
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Save main question
            $question_data = [
                'id'               => $question_id,
                'question_type'    => $question_type,
                'question_content' => $question_content,
                'created_at'       => current_time('mysql'),
                'updated_at'       => current_time('mysql'),
            ];
            
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_prefix}questions WHERE id = %s",
                $question_id
            ));
            
            if ($existing) {
                error_log('Updating existing question: ' . $question_id);
                unset($question_data['created_at']);
                $wpdb->update($table_prefix . 'questions', $question_data, ['id' => $question_id]);
            } else {
                error_log('Inserting new question: ' . $question_id);
                $wpdb->insert($table_prefix . 'questions', $question_data);
            }
            
            // Save type-specific data
            self::saveQuestionTypeData($table_prefix, $question_id, $question_type, $_POST);
            
            $wpdb->query('COMMIT');
            error_log('Question saved successfully: ' . $question_id);
            wp_send_json_success([
                'id'      => $question_id,
                'message' => __('Question saved successfully', 'wpsqptxd'),
            ]);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Error saving question: ' . $e->getMessage());
            wp_send_json_error('Error saving question: ' . $e->getMessage());
        }
    }

    /**
     * Load view template for question type
     */
    public static function wpsqp_load_view_template() {
        error_log('=== wpsqp_load_view_template called ===');
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpsqp_question_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error('Security check failed');
        }
        
        // Check capability
        if (!current_user_can('manage_options')) {
            error_log('Insufficient permissions');
            wp_send_json_error('Insufficient permissions');
        }
        
        // Get parameters
        $question_type = isset($_POST['question_type']) ? sanitize_text_field($_POST['question_type']) : '';
        $question      = isset($_POST['question'])      ? (object)$_POST['question']                   : null;
        $type_data     = isset($_POST['type_data'])     ? (object)$_POST['type_data']                  : null;
        
        error_log('Question Type: ' . $question_type);
        error_log('Question ID: ' . ($question->id ?? 'not set'));
        
        // Validate
        if (!$question_type || !$question) {
            error_log('Invalid parameters - question_type: ' . $question_type);
            wp_send_json_error('Invalid parameters');
        }
        
        // Convert type to filename (e.g., EXTRACTS_WITH_MCQ -> extracts-with-mcq)
        $type_lower = strtolower(str_replace('_', '-', $question_type));
        $view_template = WPSQP_PLUGIN_DIR . 'templates/admin/questions/view-' . $type_lower . '.php';
        
        error_log('Looking for view template: ' . $view_template);
        error_log('File exists: ' . (file_exists($view_template) ? 'YES' : 'NO'));
        
        // Check if template exists
        if (file_exists($view_template)) {
            error_log('Template found, loading...');
            ob_start();
            include $view_template;
            $html = ob_get_clean();
            error_log('Template loaded, HTML length: ' . strlen($html));
            wp_send_json_success(['html' => $html]);
        } else {
            error_log('Template NOT FOUND: ' . $view_template);
            
            // Try to find what files are in the directory
            $dir = dirname($view_template);
            error_log('Checking directory: ' . $dir);
            if (is_dir($dir)) {
                $files = scandir($dir);
                error_log('Files in directory: ' . implode(', ', $files));
            } else {
                error_log('Directory does not exist: ' . $dir);
            }
            
            // Optional: Fallback to generic view
            $generic_template = WPSQP_PLUGIN_DIR . 'templates/admin/questions/view-generic.php';
            if (file_exists($generic_template)) {
                error_log('Using generic fallback template');
                ob_start();
                include $generic_template;
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html]);
            } else {
                wp_send_json_error('View template not found for: ' . $question_type . ' at path: ' . $view_template);
            }
        }
    }
        
    /**
     * Save question type specific data
     */
    private static function saveQuestionTypeData($table_prefix, $question_id, $type, $data) {
        error_log('Saving type-specific data for: ' . $type);
        
        switch ($type) {
            case 'NORMAL_MCQ':
                self::saveNormalMCQ($table_prefix, $question_id, $data);
                break;
            case 'NORMAL_MCQ_WITH_IMAGES':
                self::saveNormalMCQWithImages($table_prefix, $question_id, $data);
                break;
            case 'EXTRACTS_WITH_MCQ':
                self::saveExtractsWithMCQ($table_prefix, $question_id, $data);
                break;
            case 'EXTRACTS_WITH_MATCHING':
                self::saveExtractsWithMatching($table_prefix, $question_id, $data);
                break;
            case 'WRITING_TASK':
                self::saveWritingTask($table_prefix, $question_id, $data);
                break;
            case 'SENTENCE_MATCHING':
                self::saveSentenceMatching($table_prefix, $question_id, $data);
                break;
            case 'GAP_FILL_DROPDOWN':
                self::saveGapFillDropdown($table_prefix, $question_id, $data);
                break;
            default:
                error_log('Unknown question type: ' . $type);
                throw new Exception('Unknown question type: ' . $type);
        }
    }

    /**
     * Save NORMAL_MCQ specific data
     */
    private static function saveNormalMCQ($table_prefix, $question_id, $data) {
        global $wpdb;
        
        error_log('=== saveNormalMCQ called ===');
        error_log('Raw data: ' . print_r($data, true));
        
        $options = [];
        
        // Get correct answer from radio button value (should be option index)
        $correct_answer_index = isset($data['correct_answer']) ? $data['correct_answer'] : '';
        error_log('Correct answer index from form: ' . $correct_answer_index);
        
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $index => $option) {
                // Generate option ID based on index (opt_1, opt_2, etc.)
                $option_id = 'opt_' . ($index + 1);
                
                $options[] = [
                    'id'   => $option_id,
                    'text' => sanitize_text_field($option['text']),
                    // No isCorrect field here - we'll use separate correct_answer field
                ];
                
                error_log('Option ' . $index . ': ID=' . $option_id . ', Text=' . $option['text']);
            }
        }
        
        // Determine which option is correct based on the index
        $correct_answer_id = '';
        if ($correct_answer_index !== '' && isset($options[$correct_answer_index])) {
            $correct_answer_id = $options[$correct_answer_index]['id'];
            error_log('Setting correct_answer to: ' . $correct_answer_id . ' (from index ' . $correct_answer_index . ')');
        }
        
        $type_data = [
            'id'             => uniqid('nmcq_'),
            'question_id'    => $question_id,
            'options'        => json_encode($options, JSON_UNESCAPED_UNICODE),
            'correct_answer' => $correct_answer_id,
        ];
        
        error_log('Final type_data: ' . print_r($type_data, true));
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}normal_mcq WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            error_log('Updating existing record');
            $wpdb->update($table_prefix . 'normal_mcq', $type_data, ['question_id' => $question_id]);
        } else {
            error_log('Inserting new record');
            $wpdb->insert($table_prefix . 'normal_mcq', $type_data);
        }
        
        error_log('saveNormalMCQ completed');
    }
    
    /**
     * Save NORMAL_MCQ_WITH_IMAGES specific data
     */
    private static function saveNormalMCQWithImages($table_prefix, $question_id, $data) {
        global $wpdb;

        $options = [];
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $index => $option) {
                $options[] = [
                    'id'        => isset($option['id']) ? sanitize_text_field($option['id']) : 'opt_' . ($index + 1),
                    'imageUrl'  => isset($option['imageUrl']) ? esc_url_raw($option['imageUrl']) : '',
                    'text'      => sanitize_text_field($option['text']),
                    'isCorrect' => isset($data['correct_answer']) && (string)$data['correct_answer'] === (string)$index,
                ];
            }
        }
        
        $correct_answer = isset($data['correct_answer']) ? sanitize_text_field($data['correct_answer']) : '';
        
        $type_data = [
            'id'             => uniqid('nmcqi_'),
            'question_id'    => $question_id,
            'options'        => json_encode($options),
            'correct_answer' => $correct_answer,
        ];
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}normal_mcq_images WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            $wpdb->update($table_prefix . 'normal_mcq_images', $type_data, ['question_id' => $question_id]);
        } else {
            $wpdb->insert($table_prefix . 'normal_mcq_images', $type_data);
        }
    }

    /**
     * Save EXTRACTS_WITH_MCQ specific data
     */
    private static function saveExtractsWithMCQ($table_prefix, $question_id, $data) {
        global $wpdb;

        $extracts = [];
        if (isset($data['extracts']) && is_array($data['extracts'])) {
            foreach ($data['extracts'] as $extract) {
                $extracts[] = [
                    'id'      => sanitize_text_field($extract['id']),
                    'content' => wp_kses_post($extract['content']),
                ];
            }
        }
        
        $options_content = isset($data['options_content']) ? wp_kses_post($data['options_content']) : '';
        
        $options = [];
        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $option) {
                $options[] = [
                    'id'   => sanitize_text_field($option['id']),
                    'text' => sanitize_text_field($option['text']),
                ];
            }
        }
        
        $correct_answer = isset($data['correct_answer']) ? sanitize_text_field($data['correct_answer']) : '';
        
        $type_data = [
            'id'              => uniqid('emcq_'),
            'question_id'     => $question_id,
            'extracts'        => json_encode($extracts),
            'options_content' => $options_content,
            'options'         => json_encode($options),
            'correct_answer'  => $correct_answer,
        ];
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}extracts_mcq WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            $wpdb->update($table_prefix . 'extracts_mcq', $type_data, ['question_id' => $question_id]);
        } else {
            $wpdb->insert($table_prefix . 'extracts_mcq', $type_data);
        }
    }
    
    /**
     * Save EXTRACTS_WITH_MATCHING specific data
     */
    private static function saveExtractsWithMatching($table_prefix, $question_id, $data) {
        global $wpdb;

        $extracts = [];
        if (isset($data['extracts']) && is_array($data['extracts'])) {
            foreach ($data['extracts'] as $extract) {
                $extracts[] = [
                    'id'      => sanitize_text_field($extract['id']),
                    'content' => wp_kses_post($extract['content']),
                ];
            }
        }
        
        $statements = [];
        if (isset($data['statements']) && is_array($data['statements'])) {
            foreach ($data['statements'] as $stmt) {
                $statements[] = [
                    'id'               => sanitize_text_field($stmt['id']),
                    'text'             => sanitize_text_field($stmt['text']),
                    'correctExtractId' => sanitize_text_field($stmt['correct_extract']),
                ];
            }
        }
        
        $type_data = [
            'id'          => uniqid('ematch_'),
            'question_id' => $question_id,
            'extracts'    => json_encode($extracts),
            'statements'  => json_encode($statements),
        ];
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}extracts_matching WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            $wpdb->update($table_prefix . 'extracts_matching', $type_data, ['question_id' => $question_id]);
        } else {
            $wpdb->insert($table_prefix . 'extracts_matching', $type_data);
        }
    }
    
    /**
     * Save WRITING_TASK specific data
     */
    private static function saveWritingTask($table_prefix, $question_id, $data) {
        global $wpdb;

        $writing_content = isset($data['writing_content']) ? wp_kses_post($data['writing_content']) : '';
        
        $type_data = [
            'id'              => uniqid('wt_'),
            'question_id'     => $question_id,
            'writing_content' => $writing_content,
        ];
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}writing_tasks WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            $wpdb->update($table_prefix . 'writing_tasks', $type_data, ['question_id' => $question_id]);
        } else {
            $wpdb->insert($table_prefix . 'writing_tasks', $type_data);
        }
    }
    
    /**
     * Save SENTENCE_MATCHING specific data
     */
    private static function saveSentenceMatching($table_prefix, $question_id, $data) {
        global $wpdb;

        $gaps = [];
        if (isset($data['gaps']) && is_array($data['gaps'])) {
            foreach ($data['gaps'] as $gap) {
                $gaps[] = [
                    'id'    => sanitize_text_field($gap['id']),
                    'order' => sanitize_text_field($gap['order']),
                    'text'  => sanitize_text_field($gap['text']),
                ];
            }
        }
        
        $sentences = [];
        if (isset($data['sentences']) && is_array($data['sentences'])) {
            foreach ($data['sentences'] as $sentence) {
                $sentences[] = [
                    'id'   => sanitize_text_field($sentence['id']),
                    'text' => sanitize_text_field($sentence['text']),
                ];
            }
        }
        
        $correct_answers = [];
        if (isset($data['correct_answers']) && is_array($data['correct_answers'])) {
            foreach ($data['correct_answers'] as $ca) {
                $correct_answers[] = [
                    'gapId'             => sanitize_text_field($ca['gap_id']),
                    'correctSentenceId' => sanitize_text_field($ca['sentence_id']),
                ];
            }
        }
        
        $passage_content = isset($data['passage_content']) ? wp_kses_post($data['passage_content']) : '';
        
        $type_data = [
            'id'               => uniqid('sm_'),
            'question_id'      => $question_id,
            'question_content' => $passage_content,
            'gaps'             => json_encode($gaps),
            'sentences'        => json_encode($sentences),
            'correct_answers'  => json_encode($correct_answers),
        ];
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}sentence_matching WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            $wpdb->update($table_prefix . 'sentence_matching', $type_data, ['question_id' => $question_id]);
        } else {
            $wpdb->insert($table_prefix . 'sentence_matching', $type_data);
        }
    }
    
    /**
     * Save GAP_FILL_DROPDOWN specific data
     */
    private static function saveGapFillDropdown($table_prefix, $question_id, $data) {
        global $wpdb;

        $gaps = [];
        if (isset($data['gaps']) && is_array($data['gaps'])) {
            foreach ($data['gaps'] as $gap) {
                $gaps[] = [
                    'id'          => sanitize_text_field($gap['id']),
                    'placeholder' => sanitize_text_field($gap['placeholder']),
                ];
            }
        }
        
        $options = [];
        if (isset($data['gap_options']) && is_array($data['gap_options'])) {
            foreach ($data['gap_options'] as $gap_option) {
                $choices = [];
                if (isset($gap_option['choices']) && is_array($gap_option['choices'])) {
                    foreach ($gap_option['choices'] as $choice) {
                        $choices[] = [
                            'value' => sanitize_text_field($choice['value']),
                            'label' => sanitize_text_field($choice['label']),
                        ];
                    }
                }
                $options[] = [
                    'gapId'   => sanitize_text_field($gap_option['gap_id']),
                    'choices' => $choices,
                ];
            }
        }
        
        $correct_answers = [];
        if (isset($data['correct_answers']) && is_array($data['correct_answers'])) {
            foreach ($data['correct_answers'] as $ca) {
                $correct_answers[] = [
                    'gapId'        => sanitize_text_field($ca['gap_id']),
                    'correctValue' => sanitize_text_field($ca['value']),
                ];
            }
        }
        
        $passage_content = isset($data['passage_content']) ? wp_kses_post($data['passage_content']) : '';
        
        $type_data = [
            'id'               => uniqid('gf_'),
            'question_id'      => $question_id,
            'questions_content' => $passage_content,
            'gaps'             => json_encode($gaps),
            'options'          => json_encode($options),
            'correct_answers'  => json_encode($correct_answers),
        ];
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_prefix}gap_fill_dropdown WHERE question_id = %s",
            $question_id
        ));
        
        if ($existing) {
            unset($type_data['id']);
            $wpdb->update($table_prefix . 'gap_fill_dropdown', $type_data, ['question_id' => $question_id]);
        } else {
            $wpdb->insert($table_prefix . 'gap_fill_dropdown', $type_data);
        }
    }
    
    /**
     * Save test
     */
    public static function wpsqp_save_test() {
        error_log('wpsqp_save_test called');
        wp_send_json_error('Not implemented yet');
    }
    
    /**
     * Save category
     */
    public static function wpsqp_save_category() {
        error_log('wpsqp_save_category called');
        
        if (!isset($_POST['wpsqp_category_nonce']) || !wp_verify_nonce($_POST['wpsqp_category_nonce'], 'wpsqp_save_category')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $id           = isset($_POST['id'])            ? intval($_POST['id'])                          : 0;
        $name         = isset($_POST['name'])          ? sanitize_text_field($_POST['name'])           : '';
        $slug         = isset($_POST['slug'])          ? sanitize_title($_POST['slug'])                : sanitize_title($name);
        $description  = isset($_POST['description'])   ? sanitize_textarea_field($_POST['description']) : '';
        $display_order = isset($_POST['display_order']) ? intval($_POST['display_order'])              : 0;
        $is_active    = isset($_POST['is_active'])     ? 1                                             : 0;
        
        $data = [
            'name'          => $name,
            'slug'          => $slug,
            'description'   => $description,
            'display_order' => $display_order,
            'is_active'     => $is_active,
        ];
        
        if ($id) {
            $result = $wpdb->update($table_prefix . 'categories', $data, ['id' => $id]);
            if ($result !== false) {
                wp_send_json_success(['id' => $id, 'message' => 'Category updated']);
            }
        } else {
            $result = $wpdb->insert($table_prefix . 'categories', $data);
            if ($result) {
                wp_send_json_success(['id' => $wpdb->insert_id, 'message' => 'Category created']);
            }
        }
        
        wp_send_json_error('Failed to save category');
    }
    
    /**
     * Delete category
     */
    public static function wpsqp_delete_category() {
        error_log('wpsqp_delete_category called');
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpsqp_category_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error('Invalid category ID');
        }
        
        $result = $wpdb->delete($table_prefix . 'categories', ['id' => $id]);
        
        if ($result) {
            wp_send_json_success(['message' => 'Category deleted']);
        }
        
        wp_send_json_error('Failed to delete category');
    }
    
    /**
     * Delete question
     */
    public static function wpsqp_delete_question() {
        error_log('wpsqp_delete_question called');
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpsqp_question_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        if (!$id) {
            wp_send_json_error('Invalid question ID');
        }
        
        $result = $wpdb->delete($table_prefix . 'questions', ['id' => $id]);
        
        if ($result) {
            wp_send_json_success(['message' => 'Question deleted']);
        }
        
        wp_send_json_error('Failed to delete question');
    }
    
    /**
     * Get question data for editing or preview
     */
    public static function wpsqp_get_question_data() {
        error_log('=== wpsqp_get_question_data called ===');
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpsqp_question_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error('Security check failed');
        }
        
        // Check capability
        if (!current_user_can('manage_options')) {
            error_log('Insufficient permissions');
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        error_log('Fetching question ID: ' . $id);
        
        if (empty($id)) {
            wp_send_json_error('Invalid question ID');
        }
        
        // Get main question data
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_prefix}questions WHERE id = %s",
            $id
        ));
        
        if (!$question) {
            error_log('Question not found: ' . $id);
            wp_send_json_error('Question not found');
        }
        
        error_log('Question found - Type: ' . $question->question_type);
        
        // Load type-specific data based on question type
        $type_data = null;
        
        switch ($question->question_type) {
            case 'NORMAL_MCQ':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}normal_mcq WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    // Decode options
                    if (is_string($type_data->options)) {
                        $type_data->options = json_decode($type_data->options, true);
                    }
                    // Ensure correct_answer is stored as ID (opt_1, opt_2, etc.)
                    error_log('NORMAL_MCQ - correct_answer: ' . $type_data->correct_answer);
                    error_log('NORMAL_MCQ - options: ' . print_r($type_data->options, true));
                }
                break;
                
            case 'NORMAL_MCQ_WITH_IMAGES':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}normal_mcq_images WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    if (is_string($type_data->options)) {
                        $type_data->options = json_decode($type_data->options, true);
                    }
                    error_log('NORMAL_MCQ_WITH_IMAGES - Options count: ' . (is_array($type_data->options) ? count($type_data->options) : 0));
                }
                break;
                
            case 'EXTRACTS_WITH_MCQ':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}extracts_mcq WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    // Decode JSON fields
                    if (is_string($type_data->extracts)) {
                        $type_data->extracts = json_decode($type_data->extracts, true);
                    }
                    if (is_string($type_data->options)) {
                        $type_data->options = json_decode($type_data->options, true);
                    }
                    error_log('EXTRACTS_WITH_MCQ - Extracts count: ' . (is_array($type_data->extracts) ? count($type_data->extracts) : 0));
                    error_log('EXTRACTS_WITH_MCQ - Options count: ' . (is_array($type_data->options) ? count($type_data->options) : 0));
                }
                break;
                
            case 'EXTRACTS_WITH_MATCHING':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}extracts_matching WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    if (is_string($type_data->extracts)) {
                        $type_data->extracts = json_decode($type_data->extracts, true);
                    }
                    if (is_string($type_data->statements)) {
                        $type_data->statements = json_decode($type_data->statements, true);
                    }
                    error_log('EXTRACTS_WITH_MATCHING - Extracts count: ' . (is_array($type_data->extracts) ? count($type_data->extracts) : 0));
                    error_log('EXTRACTS_WITH_MATCHING - Statements count: ' . (is_array($type_data->statements) ? count($type_data->statements) : 0));
                }
                break;
                
            case 'WRITING_TASK':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}writing_tasks WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    error_log('WRITING_TASK - Writing content length: ' . strlen($type_data->writing_content));
                }
                break;
                
            case 'SENTENCE_MATCHING':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}sentence_matching WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    if (is_string($type_data->gaps)) {
                        $type_data->gaps = json_decode($type_data->gaps, true);
                    }
                    if (is_string($type_data->sentences)) {
                        $type_data->sentences = json_decode($type_data->sentences, true);
                    }
                    if (is_string($type_data->correct_answers)) {
                        $type_data->correct_answers = json_decode($type_data->correct_answers, true);
                    }
                    error_log('SENTENCE_MATCHING - Gaps count: ' . (is_array($type_data->gaps) ? count($type_data->gaps) : 0));
                    error_log('SENTENCE_MATCHING - Sentences count: ' . (is_array($type_data->sentences) ? count($type_data->sentences) : 0));
                }
                break;
                
            case 'GAP_FILL_DROPDOWN':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}gap_fill_dropdown WHERE question_id = %s",
                    $id
                ));
                if ($type_data) {
                    if (is_string($type_data->gaps)) {
                        $type_data->gaps = json_decode($type_data->gaps, true);
                    }
                    if (is_string($type_data->options)) {
                        $type_data->options = json_decode($type_data->options, true);
                    }
                    if (is_string($type_data->correct_answers)) {
                        $type_data->correct_answers = json_decode($type_data->correct_answers, true);
                    }
                    error_log('GAP_FILL_DROPDOWN - Gaps count: ' . (is_array($type_data->gaps) ? count($type_data->gaps) : 0));
                }
                break;
                
            default:
                error_log('Unknown question type: ' . $question->question_type);
                break;
        }
        
        // Prepare response data
        $response_data = [
            'question' => $question,
            'type_data' => $type_data,
        ];
        
        error_log('Sending success response for question: ' . $id);
        wp_send_json_success($response_data);
    }













}