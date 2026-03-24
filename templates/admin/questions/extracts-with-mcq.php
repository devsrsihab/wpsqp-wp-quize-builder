<?php
if (!defined('ABSPATH')) exit;

/**
 * Template for EXTRACTS_WITH_MCQ question type
 * 
 * This template provides:
 * - Multiple extracts (with tabs)
 * - Options content area
 * - Multiple choice options
 * - Correct answer selection
 */

// Generate unique ID for this question
$question_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : uniqid('q_');
$edit_mode = isset($_GET['id']) ? true : false;

// Load existing data if editing
$extracts_data = [];
$options_content = '';
$options = [];
$correct_answer = '';

if ($edit_mode) {
    global $wpdb;
    $table_prefix = $wpdb->prefix . 'wpsqp_';
    
    // Get question data
    $question = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_prefix}questions WHERE id = %s",
        $question_id
    ));
    
    if ($question) {
        $question_content = $question->question_content;
        
        // Get type-specific data
        $type_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_prefix}extracts_mcq WHERE question_id = %s",
            $question_id
        ));
        
        if ($type_data) {
            $extracts_data = json_decode($type_data->extracts, true) ?: [];
            $options_content = $type_data->options_content ?: '';
            $options = json_decode($type_data->options, true) ?: [];
            $correct_answer = $type_data->correct_answer ?: '';
        }
    }
}

// Default values
if (empty($extracts_data)) {
    $extracts_data = [
        ['id' => 'A', 'content' => '']
    ];
}

if (empty($options)) {
    $options = [
        ['id' => 'opt_1', 'text' => ''],
        ['id' => 'opt_2', 'text' => ''],
        ['id' => 'opt_3', 'text' => ''],
        ['id' => 'opt_4', 'text' => '']
    ];
}
?>

<div class="wrap wpsqp-question-page">
    <h1><?php echo $edit_mode ? __('Edit Extracts with MCQ Question', 'wpsqptxd') : __('Add Extracts with MCQ Question', 'wpsqptxd'); ?></h1>
    
    <form id="wpsqp-question-form" method="post">
        <?php wp_nonce_field('wpsqp_save_question', 'wpsqp_question_nonce'); ?>
        <input type="hidden" name="question_id" value="<?php echo esc_attr($question_id); ?>">
        <input type="hidden" name="question_type" value="EXTRACTS_WITH_MCQ">
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Main Content Area -->
                <div id="post-body-content">
                    
                    <!-- Question Title/Instruction -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Question Instruction', 'wpsqptxd'); ?></h2>
                        <div class="inside">
                            <p class="description">
                                <?php _e('This question type presents multiple extracts (text passages) followed by a multiple choice question.', 'wpsqptxd'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Extracts Tabs Section -->
                    <div class="postbox">
                        <h2 class="hndle">
                            <span><?php _e('Extracts', 'wpsqptxd'); ?></span>
                            <button type="button" class="button button-small" id="add-extract-btn" style="margin-left: 10px;">
                                <span class="dashicons dashicons-plus"></span> <?php _e('Add Extract', 'wpsqptxd'); ?>
                            </button>
                        </h2>
                        <div class="inside">
                            <div class="extracts-tabs-container">
                                <!-- Tab Navigation -->
                                <div class="extracts-tabs-nav" id="extracts-tabs-nav">
                                    <?php foreach ($extracts_data as $index => $extract): ?>
                                    <div class="extract-tab <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo $index; ?>">
                                        <span class="tab-title">Extract <?php echo $extract['id']; ?></span>
                                        <?php if (count($extracts_data) > 1): ?>
                                        <button type="button" class="remove-extract-tab" title="<?php _e('Remove Extract', 'wpsqptxd'); ?>">&times;</button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Tab Content -->
                                <div class="extracts-tabs-content" id="extracts-tabs-content">
                                    <?php foreach ($extracts_data as $index => $extract): ?>
                                    <div class="extract-tab-content <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo $index; ?>">
                                        <div class="extract-header">
                                            <label>
                                                <strong><?php _e('Extract ID:', 'wpsqptxd'); ?></strong>
                                                <input type="text" 
                                                       name="extracts[<?php echo $index; ?>][id]" 
                                                       value="<?php echo esc_attr($extract['id']); ?>" 
                                                       class="extract-id-input small-text"
                                                       placeholder="A, B, C, etc.">
                                            </label>
                                        </div>
                                        
                                        <div class="extract-content">
                                            <?php
                                            wp_editor($extract['content'], 'extract_content_' . $index, [
                                                'textarea_name' => 'extracts[' . $index . '][content]',
                                                'textarea_rows' => 8,
                                                'media_buttons' => true,
                                                'teeny' => false,
                                                'quicktags' => true,
                                                'editor_class' => 'extract-editor'
                                            ]);
                                            ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options Content Section (for instructions like "Which extract...") -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Options Content / Instructions', 'wpsqptxd'); ?></h2>
                        <div class="inside">
                            <p class="description">
                                <?php _e('This text appears above the options (e.g., "Which extract mentions...")', 'wpsqptxd'); ?>
                            </p>
                            <?php
                            wp_editor($options_content, 'options_content', [
                                'textarea_name' => 'options_content',
                                'textarea_rows' => 5,
                                'media_buttons' => true,
                                'teeny' => false,
                                'quicktags' => true,
                                'editor_class' => 'options-editor'
                            ]);
                            ?>
                        </div>
                    </div>
                    
                    <!-- Options Section -->
                    <div class="postbox">
                        <h2 class="hndle">
                            <span><?php _e('Answer Options', 'wpsqptxd'); ?></span>
                            <button type="button" class="button button-small" id="add-option-btn" style="margin-left: 10px;">
                                <span class="dashicons dashicons-plus"></span> <?php _e('Add Option', 'wpsqptxd'); ?>
                            </button>
                        </h2>
                        <div class="inside">
                            <p class="description">
                                <?php _e('Add the multiple choice options. Select the correct answer.', 'wpsqptxd'); ?>
                            </p>
                            
                            <div class="options-container" id="options-container">
                                <?php foreach ($options as $index => $option): ?>
                                <div class="option-item" data-index="<?php echo $index; ?>">
                                    <div class="option-header">
                                        <span class="option-letter"><?php echo chr(65 + $index); ?>.</span>
                                        <div class="option-actions">
                                            <label>
                                                <input type="radio" 
                                                       name="correct_answer" 
                                                       value="<?php echo esc_attr($option['id']); ?>"
                                                       <?php checked($correct_answer, $option['id']); ?>>
                                                <?php _e('Correct Answer', 'wpsqptxd'); ?>
                                            </label>
                                            <?php if (count($options) > 2): ?>
                                            <button type="button" class="button button-small remove-option">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="option-content">
                                        <input type="hidden" 
                                               name="options[<?php echo $index; ?>][id]" 
                                               value="<?php echo esc_attr($option['id']); ?>">
                                        <input type="text" 
                                               name="options[<?php echo $index; ?>][text]" 
                                               value="<?php echo esc_attr($option['text']); ?>" 
                                               class="large-text"
                                               placeholder="<?php _e('Enter option text', 'wpsqptxd'); ?>">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                </div><!-- #post-body-content -->
                
                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    
                    <!-- Publish Box -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Publish', 'wpsqptxd'); ?></h2>
                        <div class="inside">
                            <div class="submitbox">
                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                        <a href="<?php echo admin_url('admin.php?page=wpsqp-questions'); ?>" class="submitdelete deletion">
                                            <?php _e('Cancel', 'wpsqptxd'); ?>
                                        </a>
                                    </div>
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <button type="submit" class="button button-primary button-large" id="publish-btn">
                                            <?php echo $edit_mode ? __('Update Question', 'wpsqptxd') : __('Save Question', 'wpsqptxd'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Box -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Preview', 'wpsqptxd'); ?></h2>
                        <div class="inside">
                            <p><?php _e('Click to see how this question will look:', 'wpsqptxd'); ?></p>
                            <button type="button" class="button" id="preview-question-btn">
                                <span class="dashicons dashicons-visibility"></span> <?php _e('Preview Question', 'wpsqptxd'); ?>
                            </button>
                        </div>
                    </div>
                    
                </div><!-- #postbox-container-1 -->
                
            </div><!-- #post-body -->
        </div><!-- #poststuff -->
    </form>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="wpsqp-modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3><?php _e('Question Preview', 'wpsqptxd'); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="preview-content">
            <!-- Preview will be loaded here -->
            <div class="preview-loading"><?php _e('Loading preview...', 'wpsqptxd'); ?></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button modal-close"><?php _e('Close', 'wpsqptxd'); ?></button>
        </div>
    </div>
</div>

<style>
/* Extracts Tabs Styling */
.extracts-tabs-container {
    border: 1px solid #ccd0d4;
    background: #fff;
    margin-top: 10px;
}

.extracts-tabs-nav {
    display: flex;
    border-bottom: 1px solid #ccd0d4;
    background: #f8f9fa;
    padding: 0;
    overflow-x: auto;
}

.extract-tab {
    padding: 8px 15px;
    border-right: 1px solid #ccd0d4;
    cursor: pointer;
    background: #f1f1f1;
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.extract-tab.active {
    background: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
}

.extract-tab .tab-title {
    font-weight: 500;
}

.remove-extract-tab {
    background: none;
    border: none;
    color: #999;
    font-size: 16px;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
}

.remove-extract-tab:hover {
    color: #dc3232;
}

.extracts-tabs-content {
    padding: 20px;
}

.extract-tab-content {
    display: none;
}

.extract-tab-content.active {
    display: block;
}

.extract-header {
    margin-bottom: 15px;
}

.extract-id-input {
    width: 60px !important;
    text-align: center;
    font-weight: bold;
}

/* Options Styling */
.options-container {
    margin-top: 15px;
}

.option-item {
    background: #f8f9fa;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-bottom: 15px;
    padding: 15px;
}

.option-item:last-child {
    margin-bottom: 0;
}

.option-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.option-letter {
    font-size: 16px;
    font-weight: bold;
    color: #2271b1;
}

.option-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.option-actions label {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #46b450;
}

.remove-option .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Preview Modal */
#preview-content {
    min-height: 400px;
    max-height: 60vh;
    overflow-y: auto;
}

.preview-loading {
    text-align: center;
    padding: 50px;
    color: #999;
}

/* Form Elements */
.wpsqp-question-page .postbox .hndle {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wpsqp-question-page .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    vertical-align: middle;
}

/* Button spacing */
#add-extract-btn,
#add-option-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
    let extractIndex = <?php echo count($extracts_data); ?>;
    let optionIndex = <?php echo count($options); ?>;
    
    // =====================================================
    // EXTRACT TABS MANAGEMENT
    // =====================================================
    
    // Tab switching
    $(document).on('click', '.extract-tab', function() {
        let tabIndex = $(this).data('tab');
        
        $('.extract-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.extract-tab-content').removeClass('active');
        $(`.extract-tab-content[data-tab="${tabIndex}"]`).addClass('active');
    });
    
    // Add new extract
    $('#add-extract-btn').on('click', function() {
        let newIndex = extractIndex++;
        let tabId = 'tab_' + newIndex;
        let extractId = String.fromCharCode(65 + newIndex); // A, B, C, etc.
        
        // Add tab
        let tabHtml = `
            <div class="extract-tab" data-tab="${newIndex}">
                <span class="tab-title">Extract ${extractId}</span>
                <button type="button" class="remove-extract-tab">&times;</button>
            </div>
        `;
        $('#extracts-tabs-nav').append(tabHtml);
        
        // Add content
        let contentHtml = `
            <div class="extract-tab-content" data-tab="${newIndex}">
                <div class="extract-header">
                    <label>
                        <strong><?php _e('Extract ID:', 'wpsqptxd'); ?></strong>
                        <input type="text" 
                               name="extracts[${newIndex}][id]" 
                               value="${extractId}" 
                               class="extract-id-input small-text">
                    </label>
                </div>
                <div class="extract-content">
                    <textarea 
                        name="extracts[${newIndex}][content]" 
                        rows="8" 
                        class="large-text"
                        placeholder="<?php _e('Enter extract content here...', 'wpsqptxd'); ?>"></textarea>
                </div>
            </div>
        `;
        $('#extracts-tabs-content').append(contentHtml);
        
        // Switch to new tab
        $('.extract-tab').removeClass('active');
        $(`.extract-tab[data-tab="${newIndex}"]`).addClass('active');
        $('.extract-tab-content').removeClass('active');
        $(`.extract-tab-content[data-tab="${newIndex}"]`).addClass('active');
    });
    
    // Remove extract
    $(document).on('click', '.remove-extract-tab', function(e) {
        e.stopPropagation();
        
        let $tab = $(this).closest('.extract-tab');
        let tabIndex = $tab.data('tab');
        
        if (confirm('<?php _e('Are you sure you want to remove this extract?', 'wpsqptxd'); ?>')) {
            // Remove tab and content
            $tab.remove();
            $(`.extract-tab-content[data-tab="${tabIndex}"]`).remove();
            
            // Activate first tab if available
            if ($('.extract-tab').length > 0) {
                $('.extract-tab:first').addClass('active');
                $('.extract-tab-content:first').addClass('active');
            }
        }
    });
    
    // =====================================================
    // OPTIONS MANAGEMENT
    // =====================================================
    
    // Add new option
    $('#add-option-btn').on('click', function() {
        let newIndex = optionIndex++;
        let optionId = 'opt_' + (newIndex + 1);
        
        let html = `
            <div class="option-item" data-index="${newIndex}">
                <div class="option-header">
                    <span class="option-letter">${String.fromCharCode(65 + newIndex)}.</span>
                    <div class="option-actions">
                        <label>
                            <input type="radio" name="correct_answer" value="${optionId}">
                            <?php _e('Correct Answer', 'wpsqptxd'); ?>
                        </label>
                        <button type="button" class="button button-small remove-option">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="option-content">
                    <input type="hidden" name="options[${newIndex}][id]" value="${optionId}">
                    <input type="text" 
                           name="options[${newIndex}][text]" 
                           class="large-text"
                           placeholder="<?php _e('Enter option text', 'wpsqptxd'); ?>">
                </div>
            </div>
        `;
        
        $('#options-container').append(html);
    });
    
    // Remove option
    $(document).on('click', '.remove-option', function() {
        let $option = $(this).closest('.option-item');
        
        if ($('.option-item').length <= 2) {
            alert('<?php _e('You need at least 2 options.', 'wpsqptxd'); ?>');
            return;
        }
        
        if (confirm('<?php _e('Remove this option?', 'wpsqptxd'); ?>')) {
            $option.remove();
            updateOptionLetters();
        }
    });
    
    // Update option letters after removal
    function updateOptionLetters() {
        $('.option-item').each(function(index) {
            $(this).find('.option-letter').text(String.fromCharCode(65 + index) + '.');
        });
    }
    
    // =====================================================
    // FORM SUBMISSION
    // =====================================================
    
    $('#wpsqp-question-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate
        let hasCorrectAnswer = $('input[name="correct_answer"]:checked').length > 0;
        if (!hasCorrectAnswer) {
            alert('<?php _e('Please select the correct answer.', 'wpsqptxd'); ?>');
            return;
        }
        
        // Disable button
        $('#publish-btn').prop('disabled', true).text('Saving...');
        
        // Get form data
        let formData = $(this).serialize();
        formData += '&action=wpsqp_save_question';
        
        console.log('Submitting form data:', formData);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=wpsqp-questions'); ?>';
                } else {
                    alert('Error: ' + response.data);
                    $('#publish-btn').prop('disabled', false).text('Save Question');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                alert('<?php _e('Error saving question. Please check console.', 'wpsqptxd'); ?>');
                $('#publish-btn').prop('disabled', false).text('Save Question');
            }
        });
    });
    
    // =====================================================
    // PREVIEW FUNCTIONALITY
    // =====================================================
    
    $('#preview-question-btn').on('click', function() {
        let extracts = [];
        let options = [];
        
        // Collect extracts
        $('.extract-tab-content').each(function() {
            let $content = $(this);
            let id = $content.find('.extract-id-input').val();
            let textarea = $content.find('textarea').val() || $content.find('.wp-editor-area').val();
            
            if (id && textarea) {
                extracts.push({id: id, content: textarea});
            }
        });
        
        // Collect options
        $('.option-item').each(function() {
            let $item = $(this);
            let id = $item.find('input[name^="options"][type="hidden"]').val();
            let text = $item.find('input[type="text"]').val();
            
            if (id && text) {
                options.push({id: id, text: text});
            }
        });
        
        let optionsContent = $('#options_content').val() || tinymce.get('options_content')?.getContent() || '';
        let correctAnswer = $('input[name="correct_answer"]:checked').val();
        
        // Build preview HTML
        let previewHtml = '<div class="question-preview">';
        
        // Extracts
        previewHtml += '<div class="preview-extracts">';
        extracts.forEach(function(extract, index) {
            previewHtml += `
                <div class="preview-extract">
                    <h4>Extract ${extract.id}</h4>
                    <div class="preview-extract-content">${extract.content}</div>
                </div>
            `;
        });
        previewHtml += '</div>';
        
        // Options content
        if (optionsContent) {
            previewHtml += `<div class="preview-options-content">${optionsContent}</div>`;
        }
        
        // Options
        previewHtml += '<div class="preview-options">';
        options.forEach(function(option, index) {
            let letter = String.fromCharCode(65 + index);
            previewHtml += `
                <div class="preview-option ${option.id === correctAnswer ? 'correct-answer' : ''}">
                    <span class="option-letter">${letter}.</span>
                    <span class="option-text">${option.text}</span>
                    ${option.id === correctAnswer ? '<span class="correct-badge">(Correct)</span>' : ''}
                </div>
            `;
        });
        previewHtml += '</div>';
        previewHtml += '</div>';
        
        $('#preview-content').html(previewHtml);
        $('#preview-modal').fadeIn(200);
    });
    
    // Modal close
    $('.modal-close, .modal-overlay').on('click', function() {
        $('.wpsqp-modal').fadeOut(200);
    });
});
</script>

