<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Admin {
    
    public static function addMenus() {
        // Main menu
        add_menu_page(
            __('WPSQP Quiz', 'wpsqptxd'),
            __('WPSQP Quiz', 'wpsqptxd'),
            'manage_options',
            'wpsqp-quiz',
            [self::class, 'renderDashboard'],
            'dashicons-welcome-learn',
            25
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wpsqp-quiz',
            __('Dashboard', 'wpsqptxd'),
            __('Dashboard', 'wpsqptxd'),
            'manage_options',
            'wpsqp-quiz',
            [self::class, 'renderDashboard']
        );
        
        // Make Quiz submenu
        add_submenu_page(
            'wpsqp-quiz',
            __('Make Quiz', 'wpsqptxd'),
            __('Make Quiz', 'wpsqptxd'),
            'manage_options',
            'wpsqp-make-quiz',
            [self::class, 'renderQuizBuilder']
        );
        
        // Questions submenu
        add_submenu_page(
            'wpsqp-quiz',
            __('Questions', 'wpsqptxd'),
            __('Questions', 'wpsqptxd'),
            'manage_options',
            'wpsqp-questions',
            [self::class, 'renderQuestions']
        );
        
        // Categories submenu
        add_submenu_page(
            'wpsqp-quiz',
            __('Categories', 'wpsqptxd'),
            __('Categories', 'wpsqptxd'),
            'manage_options',
            'wpsqp-categories',
            [self::class, 'renderCategories']
        );
        
        // Hidden pages for add/edit
        add_submenu_page(
            null, // Hidden
            __('Add Category', 'wpsqptxd'),
            __('Add Category', 'wpsqptxd'),
            'manage_options',
            'wpsqp-add-category',
            [self::class, 'renderAddCategory']
        );
        
        add_submenu_page(
            null, // Hidden
            __('Edit Category', 'wpsqptxd'),
            __('Edit Category', 'wpsqptxd'),
            'manage_options',
            'wpsqp-edit-category',
            [self::class, 'renderEditCategory']
        );
        
        add_submenu_page(
            null, // Hidden
            __('Add Question', 'wpsqptxd'),
            __('Add Question', 'wpsqptxd'),
            'manage_options',
            'wpsqp-add-question',
            [self::class, 'renderAddQuestion']
        );
        
        add_submenu_page(
            null, // Hidden
            __('Edit Question', 'wpsqptxd'),
            __('Edit Question', 'wpsqptxd'),
            'manage_options',
            'wpsqp-edit-question',
            [self::class, 'renderEditQuestion']
        );
    }
    
    public static function renderDashboard() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $total_tests = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}tests");
        $total_questions = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}questions");
        $total_categories = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}categories");
        $total_attempts = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}user_attempts");
        
        include WPSQP_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    public static function renderQuizBuilder() {
        if (file_exists(WPSQP_PLUGIN_DIR . 'includes/class-wpsqp-quiz-builder.php')) {
            require_once WPSQP_PLUGIN_DIR . 'includes/class-wpsqp-quiz-builder.php';
            WPSQP_Quiz_Builder::render();
        }
    }
    
    public static function renderQuestions() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        // Get all questions
        $questions = $wpdb->get_results("
            SELECT q.*, 
                   COUNT(tq.id) as used_in_tests 
            FROM {$table_prefix}questions q
            LEFT JOIN {$table_prefix}test_questions tq ON q.id = tq.question_id
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ");
        
        include WPSQP_PLUGIN_DIR . 'templates/admin/questions.php';
    }
    
    public static function renderCategories() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        // Get all categories
        $categories = $wpdb->get_results("
            SELECT c.*, 
                   COUNT(t.id) as test_count 
            FROM {$table_prefix}categories c
            LEFT JOIN {$table_prefix}tests t ON c.id = t.category_id
            GROUP BY c.id
            ORDER BY c.display_order ASC
        ");
        
        include WPSQP_PLUGIN_DIR . 'templates/admin/categories.php';
    }
    
    public static function renderAddCategory() {
        wp_enqueue_media();
        include WPSQP_PLUGIN_DIR . 'templates/admin/add-category.php';
    }
    
    public static function renderEditCategory() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_prefix}categories WHERE id = %d",
            $id
        ));
        
        if (!$category) {
            wp_die(__('Category not found', 'wpsqptxd'));
        }
        
        wp_enqueue_media();
        include WPSQP_PLUGIN_DIR . 'templates/admin/edit-category.php';
    }
    
    public static function renderAddQuestion() {
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        
        if (!$type) {
            wp_die(__('Question type is required', 'wpsqptxd'));
        }
        
        wp_enqueue_media();
        wp_enqueue_editor();
        
        // Convert type to filename format
        $type_lower = strtolower($type);
        $type_lower = str_replace('_', '-', $type_lower);
        
        // First try to load type-specific template
        $template = WPSQP_PLUGIN_DIR . 'templates/admin/questions/' . $type_lower . '.php';
        
        error_log('Looking for template: ' . $template);
        
        if (file_exists($template)) {
            error_log('Template found: ' . $template);
            include $template;
        } else {
            error_log('Template not found, using generic form: ' . $template);
            // If no type-specific template, use generic form
            include WPSQP_PLUGIN_DIR . 'templates/admin/add-question.php';
        }
    }
    
    public static function renderEditQuestion() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
        
        if (!$id) {
            wp_die(__('Question ID is required', 'wpsqptxd'));
        }
        
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_prefix}questions WHERE id = %s",
            $id
        ));
        
        if (!$question) {
            wp_die(__('Question not found', 'wpsqptxd'));
        }
        
        // Load question type specific data
        $type_data = null;
        switch ($question->question_type) {
            case 'NORMAL_MCQ':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}normal_mcq WHERE question_id = %s",
                    $id
                ));
                break;
            case 'NORMAL_MCQ_WITH_IMAGES':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}normal_mcq_images WHERE question_id = %s",
                    $id
                ));
                break;
            case 'EXTRACTS_WITH_MCQ':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}extracts_mcq WHERE question_id = %s",
                    $id
                ));
                break;
            case 'EXTRACTS_WITH_MATCHING':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}extracts_matching WHERE question_id = %s",
                    $id
                ));
                break;
            case 'WRITING_TASK':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}writing_tasks WHERE question_id = %s",
                    $id
                ));
                break;
            case 'SENTENCE_MATCHING':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}sentence_matching WHERE question_id = %s",
                    $id
                ));
                break;
            case 'GAP_FILL_DROPDOWN':
                $type_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_prefix}gap_fill_dropdown WHERE question_id = %s",
                    $id
                ));
                break;
        }
        
        wp_enqueue_media();
        wp_enqueue_editor();
        
        include WPSQP_PLUGIN_DIR . 'templates/admin/edit-question.php';
    }
}