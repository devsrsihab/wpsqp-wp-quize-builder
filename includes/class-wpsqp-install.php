<?php
if (!defined('ABSPATH')) exit;

class WPSQP_Install {
    
    public static function createTables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_prefix = $wpdb->prefix . 'wpsqp_';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // =====================================================
        // 1. Categories Table (Independent)
        // =====================================================
        $table_categories = $table_prefix . 'categories';
        $sql_categories = "CREATE TABLE IF NOT EXISTS $table_categories (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text DEFAULT NULL,
            display_order int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY display_order (display_order),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        dbDelta($sql_categories);
        
        // =====================================================
        // 2. Tests Table (Depends on Categories)
        // =====================================================
        $table_tests = $table_prefix . 'tests';
        $sql_tests = "CREATE TABLE IF NOT EXISTS $table_tests (
            id int(11) NOT NULL AUTO_INCREMENT,
            category_id int(11) NOT NULL,
            title varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text DEFAULT NULL,
            total_questions int(11) DEFAULT 0,
            time_limit int(11) DEFAULT NULL,
            status enum('DRAFT','PUBLISHED','ARCHIVED') DEFAULT 'DRAFT',
            display_order int(11) DEFAULT 0,
            created_by int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            UNIQUE KEY category_title (category_id, title),
            KEY status (status),
            KEY display_order (display_order),
            CONSTRAINT fk_test_category FOREIGN KEY (category_id) REFERENCES $table_categories(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_tests);
        
        // =====================================================
        // 3. Instructions Table (Depends on Tests)
        // =====================================================
        $table_instructions = $table_prefix . 'instructions';
        $sql_instructions = "CREATE TABLE IF NOT EXISTS $table_instructions (
            id int(11) NOT NULL AUTO_INCREMENT,
            test_id int(11) NOT NULL,
            page_number int(11) NOT NULL,
            title varchar(255) DEFAULT NULL,
            content longtext NOT NULL,
            display_order int(11) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY test_page (test_id, page_number),
            KEY display_order (display_order),
            CONSTRAINT fk_instruction_test FOREIGN KEY (test_id) REFERENCES $table_tests(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_instructions);
        
        // =====================================================
        // 4. Questions Table (Independent)
        // =====================================================
        $table_questions = $table_prefix . 'questions';
        $sql_questions = "CREATE TABLE IF NOT EXISTS $table_questions (
            id varchar(50) NOT NULL,
            question_type enum('NORMAL_MCQ','NORMAL_MCQ_WITH_IMAGES','EXTRACTS_WITH_MCQ','EXTRACTS_WITH_MATCHING','WRITING_TASK','SENTENCE_MATCHING','GAP_FILL_DROPDOWN') NOT NULL,
            question_content longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY question_type (question_type)
        ) $charset_collate;";
        
        dbDelta($sql_questions);
        
        // =====================================================
        // 5. Test Questions (Depends on Tests and Questions)
        // =====================================================
        $table_test_questions = $table_prefix . 'test_questions';
        $sql_test_questions = "CREATE TABLE IF NOT EXISTS $table_test_questions (
            id int(11) NOT NULL AUTO_INCREMENT,
            test_id int(11) NOT NULL,
            question_id varchar(50) NOT NULL,
            question_number int(11) NOT NULL,
            is_required tinyint(1) DEFAULT 1,
            display_order int(11) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY test_question (test_id, question_id),
            UNIQUE KEY test_question_number (test_id, question_number),
            KEY display_order (display_order),
            CONSTRAINT fk_tq_test FOREIGN KEY (test_id) REFERENCES $table_tests(id) ON DELETE CASCADE,
            CONSTRAINT fk_tq_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_test_questions);
        
        // =====================================================
        // 6. Normal MCQ Table (Depends on Questions)
        // =====================================================
        $table_normal_mcq = $table_prefix . 'normal_mcq';
        $sql_normal_mcq = "CREATE TABLE IF NOT EXISTS $table_normal_mcq (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            options longtext NOT NULL,
            correct_answer varchar(50) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_normal_mcq_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_normal_mcq);
        
        // =====================================================
        // 7. Normal MCQ With Images Table (Depends on Questions)
        // =====================================================
        $table_normal_mcq_images = $table_prefix . 'normal_mcq_images';
        $sql_normal_mcq_images = "CREATE TABLE IF NOT EXISTS $table_normal_mcq_images (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            options longtext NOT NULL,
            correct_answer varchar(50) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_normal_mcq_images_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_normal_mcq_images);
        
        // =====================================================
        // 8. Extracts With MCQ Table (Depends on Questions)
        // =====================================================
        $table_extracts_mcq = $table_prefix . 'extracts_mcq';
        $sql_extracts_mcq = "CREATE TABLE IF NOT EXISTS $table_extracts_mcq (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            extracts longtext NOT NULL,
            options_content longtext DEFAULT NULL,
            options longtext NOT NULL,
            correct_answer varchar(50) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_extracts_mcq_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_extracts_mcq);
        
        // =====================================================
        // 9. Extracts With Matching Table (Depends on Questions)
        // =====================================================
        $table_extracts_matching = $table_prefix . 'extracts_matching';
        $sql_extracts_matching = "CREATE TABLE IF NOT EXISTS $table_extracts_matching (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            extracts longtext NOT NULL,
            statements longtext NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_extracts_matching_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_extracts_matching);
        
        // =====================================================
        // 10. Writing Task Table (Depends on Questions)
        // =====================================================
        $table_writing_tasks = $table_prefix . 'writing_tasks';
        $sql_writing_tasks = "CREATE TABLE IF NOT EXISTS $table_writing_tasks (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            writing_content longtext NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_writing_task_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_writing_tasks);
        
        // =====================================================
        // 11. Sentence Matching Table (Depends on Questions)
        // =====================================================
        $table_sentence_matching = $table_prefix . 'sentence_matching';
        $sql_sentence_matching = "CREATE TABLE IF NOT EXISTS $table_sentence_matching (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            question_content longtext NOT NULL,
            gaps longtext NOT NULL,
            sentences longtext NOT NULL,
            correct_answers longtext NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_sentence_matching_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_sentence_matching);
        
        // =====================================================
        // 12. Gap Fill Dropdown Table (Depends on Questions)
        // =====================================================
        $table_gap_fill = $table_prefix . 'gap_fill_dropdown';
        $sql_gap_fill = "CREATE TABLE IF NOT EXISTS $table_gap_fill (
            id varchar(50) NOT NULL,
            question_id varchar(50) NOT NULL,
            questions_content longtext NOT NULL,
            gaps longtext NOT NULL,
            options longtext NOT NULL,
            correct_answers longtext NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY question_id (question_id),
            CONSTRAINT fk_gap_fill_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_gap_fill);
        
        // =====================================================
        // 13. User Attempts Table (Depends on Tests)
        // =====================================================
        $table_user_attempts = $table_prefix . 'user_attempts';
        $sql_user_attempts = "CREATE TABLE IF NOT EXISTS $table_user_attempts (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            test_id int(11) NOT NULL,
            attempt_number int(11) DEFAULT 1,
            student_name varchar(255) NOT NULL,
            student_email varchar(255) DEFAULT NULL,
            status enum('WELCOME','INSTRUCTIONS_VIEWED','IN_PROGRESS','PAUSED','COMPLETED','TIMED_OUT','ABANDONED') DEFAULT 'WELCOME',
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            last_activity_at datetime DEFAULT CURRENT_TIMESTAMP,
            time_remaining int(11) DEFAULT NULL,
            total_questions int(11) NOT NULL,
            questions_answered int(11) DEFAULT 0,
            questions_flagged longtext DEFAULT NULL,
            total_score int(11) DEFAULT 0,
            max_possible_score int(11) DEFAULT 0,
            percentage_score float DEFAULT 0,
            is_passed tinyint(1) DEFAULT 0,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_test_attempt (user_id, test_id, attempt_number),
            KEY user_id (user_id),
            KEY test_id (test_id),
            KEY status (status),
            CONSTRAINT fk_attempt_test FOREIGN KEY (test_id) REFERENCES $table_tests(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_user_attempts);
        
        // =====================================================
        // 14. User Answers Table (Depends on UserAttempts and Questions)
        // =====================================================
        $table_user_answers = $table_prefix . 'user_answers';
        $sql_user_answers = "CREATE TABLE IF NOT EXISTS $table_user_answers (
            id int(11) NOT NULL AUTO_INCREMENT,
            attempt_id int(11) NOT NULL,
            question_id varchar(50) NOT NULL,
            question_number int(11) NOT NULL,
            answer_data longtext NOT NULL,
            is_flagged tinyint(1) DEFAULT 0,
            flagged_at datetime DEFAULT NULL,
            is_answered tinyint(1) DEFAULT 0,
            is_correct tinyint(1) DEFAULT NULL,
            time_spent int(11) DEFAULT 0,
            last_updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY attempt_question (attempt_id, question_id),
            KEY attempt_id (attempt_id),
            KEY question_id (question_id),
            KEY is_flagged (is_flagged),
            KEY is_answered (is_answered),
            CONSTRAINT fk_answer_attempt FOREIGN KEY (attempt_id) REFERENCES $table_user_attempts(id) ON DELETE CASCADE,
            CONSTRAINT fk_answer_question FOREIGN KEY (question_id) REFERENCES $table_questions(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_user_answers);
        
        // =====================================================
        // Insert Default Categories (Only if table is empty)
        // =====================================================
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_categories");
        
        if ($count == 0) {
            $default_categories = [
                ['name' => 'Reading', 'slug' => 'reading', 'description' => 'Reading comprehension and analysis tests', 'display_order' => 1],
                ['name' => 'Mathematical Reasoning', 'slug' => 'mathematical-reasoning', 'description' => 'Math problem solving and reasoning', 'display_order' => 2],
                ['name' => 'Thinking Skills', 'slug' => 'thinking-skills', 'description' => 'Critical thinking and logic', 'display_order' => 3],
                ['name' => 'Writing', 'slug' => 'writing', 'description' => 'Essay and creative writing', 'display_order' => 4]
            ];
            
            foreach ($default_categories as $category) {
                $wpdb->insert($table_categories, $category);
            }
        }
    }
}