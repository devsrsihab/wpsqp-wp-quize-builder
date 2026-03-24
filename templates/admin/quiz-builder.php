<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap wpsqp-quiz-builder">
    <h1 class="wp-heading-inline">
        <?php echo $test_id ? __('Edit Quiz', 'wpsqptxd') : __('Create New Quiz', 'wpsqptxd'); ?>
    </h1>
    <a href="?page=wpsqp-make-quiz" class="page-title-action"><?php _e('Add New', 'wpsqptxd'); ?></a>
    <hr class="wp-header-end">

    <div class="wpsqp-builder-container">
        <!-- Left Sidebar - Question Library -->
        <div class="wpsqp-question-library">
            <div class="library-header">
                <h3><?php _e('Question Library', 'wpsqptxd'); ?></h3>
                <button type="button" class="button button-primary add-question-btn" data-type="new">
                    <span class="dashicons dashicons-plus-alt"></span> <?php _e('New Question', 'wpsqptxd'); ?>
                </button>
            </div>
            
            <div class="library-search">
                <input type="text" id="question-search" placeholder="<?php _e('Search questions...', 'wpsqptxd'); ?>">
                <select id="question-type-filter">
                    <option value=""><?php _e('All Types', 'wpsqptxd'); ?></option>
                    <option value="NORMAL_MCQ"><?php _e('Multiple Choice', 'wpsqptxd'); ?></option>
                    <option value="NORMAL_MCQ_WITH_IMAGES"><?php _e('MCQ with Images', 'wpsqptxd'); ?></option>
                    <option value="EXTRACTS_WITH_MCQ"><?php _e('Extracts with MCQ', 'wpsqptxd'); ?></option>
                    <option value="EXTRACTS_WITH_MATCHING"><?php _e('Extracts Matching', 'wpsqptxd'); ?></option>
                    <option value="WRITING_TASK"><?php _e('Writing Task', 'wpsqptxd'); ?></option>
                    <option value="SENTENCE_MATCHING"><?php _e('Sentence Matching', 'wpsqptxd'); ?></option>
                    <option value="GAP_FILL_DROPDOWN"><?php _e('Gap Fill', 'wpsqptxd'); ?></option>
                </select>
            </div>

            <div class="library-questions-list">
                <?php foreach ($all_questions as $question): ?>
                <div class="library-question-item" data-id="<?php echo esc_attr($question->id); ?>" data-type="<?php echo esc_attr($question->question_type); ?>">
                    <div class="question-preview">
                        <span class="question-type-badge type-<?php echo strtolower($question->question_type); ?>">
                            <?php echo str_replace('_', ' ', $question->question_type); ?>
                        </span>
                        <div class="question-text">
                            <?php echo wp_trim_words(strip_tags($question->question_content), 10); ?>
                        </div>
                    </div>
                    <div class="question-actions">
                        <button type="button" class="button button-small add-to-test" data-id="<?php echo esc_attr($question->id); ?>">
                            <span class="dashicons dashicons-plus"></span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main Builder Area -->
        <div class="wpsqp-builder-main">
            <form id="wpsqp-quiz-form" method="post">
                <?php wp_nonce_field('wpsqp_save_quiz', 'wpsqp_quiz_nonce'); ?>
                
                <div class="quiz-basic-info">
                    <div class="info-row">
                        <div class="info-field">
                            <label><?php _e('Category', 'wpsqptxd'); ?></label>
                            <select name="category_id" required>
                                <option value=""><?php _e('Select Category', 'wpsqptxd'); ?></option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat->id; ?>" <?php selected($test->category_id ?? 0, $cat->id); ?>>
                                    <?php echo $cat->name; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="info-field">
                            <label><?php _e('Quiz Title', 'wpsqptxd'); ?></label>
                            <input type="text" name="title" value="<?php echo esc_attr($test->title ?? ''); ?>" required>
                        </div>
                        
                        <div class="info-field">
                            <label><?php _e('Time Limit (minutes)', 'wpsqptxd'); ?></label>
                            <input type="number" name="time_limit" value="<?php echo esc_attr($test->time_limit ?? 60); ?>" min="0">
                        </div>
                        
                        <div class="info-field">
                            <label><?php _e('Status', 'wpsqptxd'); ?></label>
                            <select name="status">
                                <option value="DRAFT" <?php selected($test->status ?? 'DRAFT', 'DRAFT'); ?>><?php _e('Draft', 'wpsqptxd'); ?></option>
                                <option value="PUBLISHED" <?php selected($test->status ?? '', 'PUBLISHED'); ?>><?php _e('Published', 'wpsqptxd'); ?></option>
                                <option value="ARCHIVED" <?php selected($test->status ?? '', 'ARCHIVED'); ?>><?php _e('Archived', 'wpsqptxd'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="info-field full-width">
                        <label><?php _e('Description', 'wpsqptxd'); ?></label>
                        <textarea name="description" rows="3"><?php echo esc_textarea($test->description ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="quiz-tabs">
                    <div class="tab-buttons">
                        <button type="button" class="tab-button active" data-tab="questions"><?php _e('Questions', 'wpsqptxd'); ?></button>
                        <button type="button" class="tab-button" data-tab="instructions"><?php _e('Instructions', 'wpsqptxd'); ?></button>
                        <button type="button" class="tab-button" data-tab="settings"><?php _e('Settings', 'wpsqptxd'); ?></button>
                    </div>

                    <!-- Questions Tab -->
                    <div class="tab-panel active" id="tab-questions">
                        <div class="questions-header">
                            <h3><?php _e('Selected Questions', 'wpsqptxd'); ?></h3>
                            <div class="questions-actions">
                                <button type="button" class="button" id="reorder-questions">
                                    <span class="dashicons dashicons-sort"></span> <?php _e('Reorder', 'wpsqptxd'); ?>
                                </button>
                                <button type="button" class="button" id="remove-all-questions">
                                    <span class="dashicons dashicons-trash"></span> <?php _e('Remove All', 'wpsqptxd'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="selected-questions-list" id="sortable-questions">
                            <?php if (!empty($test_questions)): ?>
                                <?php foreach ($test_questions as $q): ?>
                                <div class="selected-question-item" data-id="<?php echo esc_attr($q->question_id); ?>">
                                    <span class="drag-handle dashicons dashicons-menu"></span>
                                    <span class="question-number"><?php echo $q->question_number; ?>.</span>
                                    <div class="question-content">
                                        <span class="question-type"><?php echo str_replace('_', ' ', $q->question_type); ?></span>
                                        <span class="question-text"><?php echo wp_trim_words(strip_tags($q->question_content), 5); ?></span>
                                    </div>
                                    <input type="hidden" name="questions[]" value="<?php echo esc_attr($q->question_id); ?>">
                                    <button type="button" class="button button-small remove-question">
                                        <span class="dashicons dashicons-no-alt"></span>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-questions-message">
                                    <p><?php _e('No questions selected. Drag questions from the library or click "Add New" to create one.', 'wpsqptxd'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Instructions Tab -->
                    <div class="tab-panel" id="tab-instructions">
                        <div class="instructions-header">
                            <h3><?php _e('Test Instructions', 'wpsqptxd'); ?></h3>
                            <button type="button" class="button" id="add-instruction">
                                <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Instruction Page', 'wpsqptxd'); ?>
                            </button>
                        </div>
                        
                        <div class="instructions-list" id="instructions-container">
                            <?php 
                            $instructions = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM {$table_prefix}instructions WHERE test_id = %d ORDER BY page_number",
                                $test_id
                            ));
                            
                            if (!empty($instructions)):
                                foreach ($instructions as $index => $inst):
                            ?>
                            <div class="instruction-item" data-index="<?php echo $index; ?>">
                                <div class="instruction-header">
                                    <span class="instruction-title"><?php echo esc_html($inst->title ?: 'Page ' . ($index + 1)); ?></span>
                                    <button type="button" class="button button-small remove-instruction">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                                <div class="instruction-content">
                                    <input type="text" name="instructions[<?php echo $index; ?>][title]" 
                                           value="<?php echo esc_attr($inst->title); ?>" 
                                           placeholder="<?php _e('Page Title', 'wpsqptxd'); ?>">
                                    <textarea name="instructions[<?php echo $index; ?>][content]" 
                                              rows="4" 
                                              placeholder="<?php _e('Instruction content...', 'wpsqptxd'); ?>"><?php echo esc_textarea($inst->content); ?></textarea>
                                </div>
                            </div>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <div class="no-instructions-message">
                                <p><?php _e('No instructions added yet. Click "Add Instruction Page" to create one.', 'wpsqptxd'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-panel" id="tab-settings">
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label>
                                    <input type="checkbox" name="randomize_questions" value="1" 
                                           <?php checked($test->randomize_questions ?? 0, 1); ?>>
                                    <?php _e('Randomize question order', 'wpsqptxd'); ?>
                                </label>
                                <p class="description"><?php _e('Questions will appear in random order for each student', 'wpsqptxd'); ?></p>
                            </div>
                            
                            <div class="setting-item">
                                <label>
                                    <input type="checkbox" name="show_results_immediately" value="1"
                                           <?php checked($test->show_results_immediately ?? 1, 1); ?>>
                                    <?php _e('Show results immediately after completion', 'wpsqptxd'); ?>
                                </label>
                            </div>
                            
                            <div class="setting-item">
                                <label>
                                    <input type="checkbox" name="allow_question_flagging" value="1"
                                           <?php checked($test->allow_question_flagging ?? 1, 1); ?>>
                                    <?php _e('Allow students to flag questions for review', 'wpsqptxd'); ?>
                                </label>
                            </div>
                            
                            <div class="setting-item">
                                <label>
                                    <input type="number" name="passing_score" value="<?php echo esc_attr($test->passing_score ?? 70); ?>" min="0" max="100">
                                    <?php _e('Passing Score (%)', 'wpsqptxd'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="quiz-form-actions">
                    <button type="button" class="button button-primary" id="save-quiz">
                        <span class="dashicons dashicons-saved"></span>
                        <?php echo $test_id ? __('Update Quiz', 'wpsqptxd') : __('Save Quiz', 'wpsqptxd'); ?>
                    </button>
                    
                    <a href="?page=wpsqp-quiz" class="button">
                        <?php _e('Cancel', 'wpsqptxd'); ?>
                    </a>
                    
                    <?php if ($test_id): ?>
                    <button type="button" class="button button-link-delete" id="delete-quiz">
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Delete Quiz', 'wpsqptxd'); ?>
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Question Type Selection Modal -->
<div id="question-type-modal" class="wpsqp-modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Select Question Type', 'wpsqptxd'); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="question-types-grid">
                <div class="question-type-card" data-type="NORMAL_MCQ">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <h4><?php _e('Multiple Choice', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Single answer multiple choice question', 'wpsqptxd'); ?></p>
                </div>
                
                <div class="question-type-card" data-type="NORMAL_MCQ_WITH_IMAGES">
                    <span class="dashicons dashicons-format-image"></span>
                    <h4><?php _e('MCQ with Images', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Multiple choice with image options', 'wpsqptxd'); ?></p>
                </div>
                
                <div class="question-type-card" data-type="EXTRACTS_WITH_MCQ">
                    <span class="dashicons dashicons-media-document"></span>
                    <h4><?php _e('Extracts with MCQ', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Multiple extracts followed by MCQ', 'wpsqptxd'); ?></p>
                </div>
                
                <div class="question-type-card" data-type="EXTRACTS_WITH_MATCHING">
                    <span class="dashicons dashicons-networking"></span>
                    <h4><?php _e('Extracts Matching', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Match statements to extracts', 'wpsqptxd'); ?></p>
                </div>
                
                <div class="question-type-card" data-type="WRITING_TASK">
                    <span class="dashicons dashicons-edit"></span>
                    <h4><?php _e('Writing Task', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Essay or creative writing prompt', 'wpsqptxd'); ?></p>
                </div>
                
                <div class="question-type-card" data-type="SENTENCE_MATCHING">
                    <span class="dashicons dashicons-editor-paragraph"></span>
                    <h4><?php _e('Sentence Matching', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Match sentences to gaps in passage', 'wpsqptxd'); ?></p>
                </div>
                
                <div class="question-type-card" data-type="GAP_FILL_DROPDOWN">
                    <span class="dashicons dashicons-editor-table"></span>
                    <h4><?php _e('Gap Fill', 'wpsqptxd'); ?></h4>
                    <p><?php _e('Fill in the blanks with dropdown', 'wpsqptxd'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>