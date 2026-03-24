<?php
if (!defined('ABSPATH')) exit;

/**
 * Edit template for EXTRACTS_WITH_MCQ
 * 
 * @var object $question - Main question data
 * @var object $type_data - Type-specific data (extracts_mcq row)
 */

// Decode JSON data
$extracts = [];
$options = [];
$options_content = '';
$correct_answer = '';

if ($type_data) {
    $extracts = json_decode($type_data->extracts, true) ?: [];
    $options_content = $type_data->options_content ?: '';
    $options = json_decode($type_data->options, true) ?: [];
    $correct_answer = $type_data->correct_answer ?: '';
}

// Default values if empty
if (empty($extracts)) {
    $extracts = [['id' => 'A', 'content' => '']];
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

<form id="wpsqp-question-form" method="post">
    <?php wp_nonce_field('wpsqp_save_question', 'wpsqp_question_nonce'); ?>
    <input type="hidden" name="question_id" value="<?php echo esc_attr($question->id); ?>">
    <input type="hidden" name="question_type" value="<?php echo esc_attr($question->question_type); ?>">
    
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                
                <!-- Question Content -->
                <div class="postbox">
                    <h2 class="hndle"><?php _e('Question Content', 'wpsqptxd'); ?></h2>
                    <div class="inside">
                        <?php
                        wp_editor($question->question_content, 'question_content', [
                            'textarea_name' => 'question_content',
                            'textarea_rows' => 10,
                            'media_buttons' => true,
                            'teeny' => false,
                            'quicktags' => true
                        ]);
                        ?>
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
                                <?php foreach ($extracts as $index => $extract): ?>
                                <div class="extract-tab <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo $index; ?>">
                                    <span class="tab-title">Extract <?php echo isset($extract['id']) ? $extract['id'] : chr(65 + $index); ?></span>
                                    <?php if (count($extracts) > 1): ?>
                                    <button type="button" class="remove-extract-tab" title="<?php _e('Remove Extract', 'wpsqptxd'); ?>">&times;</button>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Tab Content -->
                            <div class="extracts-tabs-content" id="extracts-tabs-content">
                                <?php foreach ($extracts as $index => $extract): ?>
                                <div class="extract-tab-content <?php echo $index === 0 ? 'active' : ''; ?>" data-tab="<?php echo $index; ?>">
                                    <div class="extract-header">
                                        <label>
                                            <strong><?php _e('Extract ID:', 'wpsqptxd'); ?></strong>
                                            <input type="text" 
                                                   name="extracts[<?php echo $index; ?>][id]" 
                                                   value="<?php echo isset($extract['id']) ? esc_attr($extract['id']) : chr(65 + $index); ?>" 
                                                   class="extract-id-input small-text"
                                                   placeholder="A, B, C, etc.">
                                        </label>
                                    </div>
                                    
                                    <div class="extract-content">
                                        <?php
                                        $editor_id = 'extract_content_' . $index;
                                        $content = isset($extract['content']) ? $extract['content'] : '';
                                        wp_editor($content, $editor_id, [
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
                
                <!-- Options Content Section -->
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
                                                   value="<?php echo isset($option['id']) ? esc_attr($option['id']) : ''; ?>"
                                                   <?php echo ($correct_answer == $option['id']) ? 'checked' : ''; ?>>
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
                                           value="<?php echo isset($option['id']) ? esc_attr($option['id']) : 'opt_' . ($index + 1); ?>">
                                    <input type="text" 
                                           name="options[<?php echo $index; ?>][text]" 
                                           value="<?php echo isset($option['text']) ? esc_attr($option['text']) : ''; ?>" 
                                           class="large-text"
                                           placeholder="<?php _e('Enter option text', 'wpsqptxd'); ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
            </div><!-- #post-body-content -->
            
            <div id="postbox-container-1" class="postbox-container">
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
                                        <?php _e('Update Question', 'wpsqptxd'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- #postbox-container-1 -->
        </div><!-- #post-body -->
    </div><!-- #poststuff -->
</form>


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

<!-- Include the same JS from extracts-with-mcq.php -->
<script>
jQuery(document).ready(function($) {
    let extractIndex = <?php echo count($extracts); ?>;
    let optionIndex = <?php echo count($options); ?>;
    
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
        let extractId = String.fromCharCode(65 + newIndex);
        
        $('#extracts-tabs-nav').append(`
            <div class="extract-tab" data-tab="${newIndex}">
                <span class="tab-title">Extract ${extractId}</span>
                <button type="button" class="remove-extract-tab">&times;</button>
            </div>
        `);
        
        $('#extracts-tabs-content').append(`
            <div class="extract-tab-content" data-tab="${newIndex}">
                <div class="extract-header">
                    <label>
                        <strong><?php _e('Extract ID:', 'wpsqptxd'); ?></strong>
                        <input type="text" name="extracts[${newIndex}][id]" value="${extractId}" class="extract-id-input small-text">
                    </label>
                </div>
                <div class="extract-content">
                    <textarea name="extracts[${newIndex}][content]" rows="8" class="large-text"></textarea>
                </div>
            </div>
        `);
        
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
        
        if (confirm('<?php _e('Are you sure?', 'wpsqptxd'); ?>')) {
            $tab.remove();
            $(`.extract-tab-content[data-tab="${tabIndex}"]`).remove();
            if ($('.extract-tab').length > 0) {
                $('.extract-tab:first').addClass('active');
                $('.extract-tab-content:first').addClass('active');
            }
        }
    });
    
    // Add new option
    $('#add-option-btn').on('click', function() {
        let newIndex = optionIndex++;
        let optionId = 'opt_' + (newIndex + 1);
        
        $('#options-container').append(`
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
                    <input type="text" name="options[${newIndex}][text]" class="large-text">
                </div>
            </div>
        `);
    });
    
    // Remove option
    $(document).on('click', '.remove-option', function() {
        if ($('.option-item').length <= 2) {
            alert('<?php _e('You need at least 2 options.', 'wpsqptxd'); ?>');
            return;
        }
        $(this).closest('.option-item').remove();
        $('.option-item').each(function(index) {
            $(this).find('.option-letter').text(String.fromCharCode(65 + index) + '.');
        });
    });
    
    // Form submit
    $('#wpsqp-question-form').on('submit', function(e) {
        e.preventDefault();
        
        if ($('input[name="correct_answer"]:checked').length === 0) {
            alert('<?php _e('Please select the correct answer.', 'wpsqptxd'); ?>');
            return;
        }
        
        $('#publish-btn').prop('disabled', true).text('Updating...');
        
        let formData = new FormData(this);
        formData.append('action', 'wpsqp_save_question');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=wpsqp-questions'); ?>';
                } else {
                    alert('Error: ' + response.data);
                    $('#publish-btn').prop('disabled', false).text('Update Question');
                }
            },
            error: function() {
                alert('<?php _e('Error updating question', 'wpsqptxd'); ?>');
                $('#publish-btn').prop('disabled', false).text('Update Question');
            }
        });
    });
});
</script>