<?php
if (!defined('ABSPATH')) exit;

/**
 * Template: Add/Edit NORMAL_MCQ_WITH_IMAGES question
 */

$is_edit     = isset($question) && $question;
$question_id = $is_edit ? esc_attr($question->id) : '';
$q_content   = $is_edit ? $question->question_content : '';

// Load existing data for edit mode
$options        = [];
$correct_answer = '';
$option_content = '';

if ($is_edit && isset($type_data) && $type_data) {
    $options_raw = is_string($type_data->options) ? json_decode($type_data->options, true) : $type_data->options;
    $options = is_array($options_raw) ? $options_raw : [];
    $correct_answer = $type_data->correct_answer;
    $option_content = isset($type_data->option_content) ? $type_data->option_content : '';
}

// Default: 4 empty options
if (empty($options)) {
    $options = [
        ['id' => 'opt_1', 'text' => '', 'image_id' => ''],
        ['id' => 'opt_2', 'text' => '', 'image_id' => ''],
        ['id' => 'opt_3', 'text' => '', 'image_id' => ''],
        ['id' => 'opt_4', 'text' => '', 'image_id' => ''],
    ];
}

$max_options = 6;

// Function to get image URL from ID (for edit mode display)
function wpsqp_get_image_url($image_id) {
    if (empty($image_id)) {
        return '';
    }
    $url = wp_get_attachment_url($image_id);
    if (!$url) {
        $url = wp_get_attachment_image_url($image_id, 'medium');
    }
    return $url;
}
?>

<div class="wrap wpsqp-question-wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? __('Edit Question', 'wpsqptxd') : __('Add Question', 'wpsqptxd'); ?>
        <span class="wpsqp-type-badge">NORMAL MCQ WITH IMAGES</span>
    </h1>
    <a href="<?php echo admin_url('admin.php?page=wpsqp-questions'); ?>" class="page-title-action">
        <?php _e('Back to Questions', 'wpsqptxd'); ?>
    </a>
    <hr class="wp-header-end">

    <div id="wpsqp-feedback" class="notice" style="display:none;"></div>

    <form id="wpsqp-normal-mcq-images-form" method="post">
        <?php wp_nonce_field('wpsqp_save_question', 'wpsqp_question_nonce'); ?>
        <input type="hidden" name="question_type" value="NORMAL_MCQ_WITH_IMAGES">
        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">

        <!-- ═══════════════════════════════════════
             SECTION 1: Question Content
        ════════════════════════════════════════ -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><?php _e('Question Content', 'wpsqptxd'); ?></h2>
            </div>
            <div class="inside">
                <?php
                wp_editor($q_content, 'question_content', [
                    'textarea_name' => 'question_content',
                    'textarea_rows' => 8,
                    'media_buttons' => true,
                    'teeny'         => false,
                ]);
                ?>
                <p class="description">
                    <?php _e('Enter the question text or instructions here.', 'wpsqptxd'); ?>
                </p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             SECTION: Option Content (Rich Text)
        ════════════════════════════════════════ -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><?php _e('Option Content / Question Statement', 'wpsqptxd'); ?></h2>
            </div>
            <div class="inside">
                <p class="description">
                    <?php _e('Add the question statement or instruction that appears above the options. You can use bold, italic, images, etc.', 'wpsqptxd'); ?>
                </p>
                <?php
                wp_editor($option_content, 'option_content', [
                    'textarea_name' => 'option_content',
                    'textarea_rows' => 5,
                    'media_buttons' => true,
                    'teeny'         => false,
                ]);
                ?>
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             SECTION 2: Answer Options with Images
        ════════════════════════════════════════ -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><?php _e('Answer Options with Images', 'wpsqptxd'); ?></h2>
            </div>
            <div class="inside">
                <p class="description">
                    <?php _e('Add answer options below. Each option can have an image. Select the correct answer using the radio button.', 'wpsqptxd'); ?>
                </p>

                <div class="wpsqp-options-grid" id="wpsqp-options-grid">
                    <?php foreach ($options as $index => $option):
                        $opt_id = isset($option['id']) ? esc_attr($option['id']) : 'opt_' . ($index + 1);
                        $opt_text = isset($option['text']) ? esc_attr($option['text']) : '';
                        $opt_image_id = isset($option['image_id']) ? intval($option['image_id']) : 0;
                        $opt_image_url = $opt_image_id ? wp_get_attachment_url($opt_image_id) : '';
                        $opt_label = chr(65 + $index);
                        $checked = ($correct_answer === $opt_id || (string)$index === (string)$correct_answer) ? 'checked' : '';
                    ?>
                    <div class="wpsqp-option-card" data-original-index="<?php echo $index; ?>">
                        <input type="hidden" name="options[<?php echo $index; ?>][image_id]" class="option-image-id" value="<?php echo $opt_image_id; ?>">    
                    
                    <div class="option-card-header">
                            <div class="option-letter"><?php echo $opt_label; ?></div>
                            <div class="option-actions">
                                <label class="correct-label">
                                    <input type="radio" 
                                           name="correct_answer" 
                                           value="<?php echo $opt_id; ?>"
                                           <?php echo $checked; ?>>
                                    <?php _e('Correct', 'wpsqptxd'); ?>
                                </label>
                                <?php if ($index >= 2): ?>
                                <button type="button" class="button button-small remove-option">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="option-image-preview">
                            <?php if ($opt_image_url): ?>
                            <img src="<?php echo esc_url($opt_image_url); ?>" alt="<?php echo esc_attr($opt_text); ?>" class="preview-img">
                            <?php else: ?>
                            <div class="image-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                                <span class="placeholder-text"><?php _e('No image', 'wpsqptxd'); ?></span>
                            </div>
                            <?php endif; ?>
                            <button type="button" class="button button-small upload-image-btn">
                                <span class="dashicons dashicons-upload"></span> <?php _e('Upload Image', 'wpsqptxd'); ?>
                            </button>
                        </div>
                        
                        <div class="option-text-field">
                            <input type="hidden" name="options[<?php echo $index; ?>][id]" value="<?php echo $opt_id; ?>">
                            <input type="hidden" name="options[<?php echo $index; ?>][image_id]" class="option-image-id" value="<?php echo $opt_image_id; ?>">
                            <input type="text" 
                                   name="options[<?php echo $index; ?>][text]" 
                                   value="<?php echo $opt_text; ?>" 
                                   class="large-text"
                                   placeholder="<?php echo sprintf(__('Option %s text...', 'wpsqptxd'), $opt_label); ?>"
                                   >
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="add-option-wrap">
                    <button type="button" id="wpsqp-add-option" class="button button-secondary">
                        <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Option', 'wpsqptxd'); ?>
                    </button>
                    <span class="description">
                        <?php _e('Minimum 2 options, maximum ' . $max_options . ' options.', 'wpsqptxd'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             SECTION 3: Submit
        ════════════════════════════════════════ -->
        <div class="postbox">
            <div class="inside" style="padding: 12px 16px;">
                <button type="submit" id="wpsqp-save-btn" class="button button-primary button-large">
                    <?php echo $is_edit ? __('Update Question', 'wpsqptxd') : __('Save Question', 'wpsqptxd'); ?>
                </button>
                <span id="wpsqp-save-status" style="margin-left:12px;"></span>
            </div>
        </div>
    </form>
</div>

<style>
/* Keep your existing styles */
.wpsqp-question-wrap {
    max-width: 1200px;
}

.wpsqp-type-badge {
    display: inline-block;
    background: #f3e5f5;
    color: #7b1fa2;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 10px;
    vertical-align: middle;
}

.postbox-header {
    border-bottom: 1px solid #ccd0d4;
    padding: 10px 12px;
    background: #f6f7f7;
}

.postbox-header h2 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #23282d;
}

.wpsqp-options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.wpsqp-option-card {
    background: #fff;
    border: 1px solid #e2e4e7;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
    position: relative;
}

.wpsqp-option-card:hover {
    border-color: #2271b1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.option-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.option-letter {
    font-size: 18px;
    font-weight: 700;
    color: #2271b1;
    background: #e3f2fd;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.option-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.correct-label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #46b450;
    cursor: pointer;
}

.remove-option {
    padding: 4px 8px !important;
    min-height: auto !important;
}

.remove-option .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.option-image-preview {
    text-align: center;
    margin-bottom: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    min-height: 140px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.option-image-preview img {
    max-width: 100%;
    max-height: 120px;
    border-radius: 6px;
    margin-bottom: 8px;
    object-fit: contain;
}

.image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    color: #999;
}

.image-placeholder .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 8px;
}

.image-placeholder .placeholder-text {
    font-size: 12px;
}

.upload-image-btn {
    min-height: 26px;
    line-height: 2.18181818;
    padding: 0 8px;
    font-size: 11px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 5px;
}

.upload-image-btn .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.option-text-field {
    margin-top: 12px;
}

.option-text-field input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.add-option-wrap {
    margin-top: 20px;
    text-align: center;
    display: flex;
    justify-content: center;
    gap: 20px;
    align-items: center;
}

#wpsqp-add-option {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

@media (max-width: 768px) {
    .wpsqp-options-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let optionCount = <?php echo count($options); ?>;
    let maxOptions = <?php echo $max_options; ?>;
    
    // =====================================================
    // Media Upload Handler - Store Image ID
    // =====================================================
    function openMediaUploader(button) {
        var mediaUploader = wp.media({
            title: '<?php _e('Select Option Image', 'wpsqptxd'); ?>',
            button: {
                text: '<?php _e('Use this image', 'wpsqptxd'); ?>'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            var imageId = attachment.id;
            var imageUrl = attachment.url;
            
            var $button = $(button);
            var $optionCard = $button.closest('.wpsqp-option-card');
            var $previewDiv = $optionCard.find('.option-image-preview');
            var $imageIdField = $optionCard.find('.option-image-id');
            
            // Store Image ID
            $imageIdField.val(imageId);
            
            // Update preview
            $previewDiv.find('img').remove();
            $previewDiv.find('.image-placeholder').remove();
            $previewDiv.prepend('<img src="' + imageUrl + '" alt="Option image" class="preview-img">');
            
            console.log('Image ID saved for option:', imageId);
        });
        
        mediaUploader.open();
    }
    
    // =====================================================
    // Add New Option
    // =====================================================
    $('#wpsqp-add-option').on('click', function() {
        if (optionCount >= maxOptions) {
            alert('<?php _e('Maximum ' . $max_options . ' options allowed.', 'wpsqptxd'); ?>');
            return;
        }
        
        let newIndex = optionCount;
        let optId = 'opt_' + (newIndex + 1);
        let label = String.fromCharCode(65 + newIndex);
        
        var cardHtml = `
            <div class="wpsqp-option-card">
                <div class="option-card-header">
                    <div class="option-letter">${label}</div>
                    <div class="option-actions">
                        <label class="correct-label">
                            <input type="radio" name="correct_answer" value="${optId}">
                            <?php _e('Correct', 'wpsqptxd'); ?>
                        </label>
                        <button type="button" class="button button-small remove-option">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="option-image-preview">
                    <div class="image-placeholder">
                        <span class="dashicons dashicons-format-image"></span>
                        <span class="placeholder-text"><?php _e('No image', 'wpsqptxd'); ?></span>
                    </div>
                    <button type="button" class="button button-small upload-image-btn">
                        <span class="dashicons dashicons-upload"></span> <?php _e('Upload Image', 'wpsqptxd'); ?>
                    </button>
                </div>
                <div class="option-text-field">
                    <input type="hidden" name="options[${newIndex}][id]" value="${optId}">
                    <input type="hidden" name="options[${newIndex}][image_id]" class="option-image-id" value="">
                    <input type="text" 
                           name="options[${newIndex}][text]" 
                           class="large-text"
                           placeholder="<?php _e('Option text...', 'wpsqptxd'); ?>"
                           >
                </div>
            </div>
        `;
        
        $('#wpsqp-options-grid').append(cardHtml);
        optionCount++;
        $('#wpsqp-add-option').prop('disabled', optionCount >= maxOptions);
    });
    
    // =====================================================
    // Remove Option
    // =====================================================
    $(document).on('click', '.remove-option', function() {
        if (optionCount <= 2) {
            alert('<?php _e('Minimum 2 options required.', 'wpsqptxd'); ?>');
            return;
        }
        
        if (confirm('<?php _e('Remove this option?', 'wpsqptxd'); ?>')) {
            $(this).closest('.wpsqp-option-card').remove();
            optionCount--;
            $('#wpsqp-add-option').prop('disabled', optionCount >= maxOptions);
            updateOptionLetters();
        }
    });
    
    // =====================================================
    // Update Option Letters (A, B, C...)
    // =====================================================
    function updateOptionLetters() {
        $('.wpsqp-option-card').each(function(index) {
            let label = String.fromCharCode(65 + index);
            $(this).find('.option-letter').text(label);
            
            // Update the radio button value to match new ID
            let optId = 'opt_' + (index + 1);
            $(this).find('input[name="correct_answer"]').val(optId);
            
            // Update hidden id field
            $(this).find('input[name^="options["][type="hidden"]').each(function() {
                let name = $(this).attr('name');
                if (name.indexOf('[id]') !== -1) {
                    $(this).val(optId);
                }
            });
        });
        
        // Also update the name attributes of all input fields
        $('.wpsqp-option-card').each(function(newIndex) {
            $(this).find('input[name^="options["]').each(function() {
                let oldName = $(this).attr('name');
                let newName = oldName.replace(/options\[\d+\]/, 'options[' + newIndex + ']');
                $(this).attr('name', newName);
            });
        });
    }
    
    // =====================================================
    // Upload Button Click Handler
    // =====================================================
    $(document).on('click', '.upload-image-btn', function(e) {
        e.preventDefault();
        openMediaUploader(this);
    });
    
    // =====================================================
    // Form Submit - Sync TinyMCE and Save
    // =====================================================
    $('#wpsqp-normal-mcq-images-form').on('submit', function(e) {
        e.preventDefault();
        
        var $btn = $('#wpsqp-save-btn');
        var $status = $('#wpsqp-save-status');
        
        // Validate options count
        if ($('.wpsqp-option-card').length < 2) {
            alert('<?php _e('Please add at least 2 options.', 'wpsqptxd'); ?>');
            return;
        }
        
        // Validate correct answer selected
        if ($('input[name="correct_answer"]:checked').length === 0) {
            alert('<?php _e('Please select the correct answer.', 'wpsqptxd'); ?>');
            return;
        }
        
        $btn.prop('disabled', true).text('Saving...');
        
        // Sync ALL TinyMCE editors
        if (typeof tinyMCE !== 'undefined') {
            if (tinyMCE.get('question_content')) {
                tinyMCE.get('question_content').save();
            }
            if (tinyMCE.get('option_content')) {
                tinyMCE.get('option_content').save();
            }
            tinyMCE.triggerSave();
        }
        
        var formData = new FormData(this);
        formData.append('action', 'wpsqp_save_question');
        
        // Debug: Log all image IDs before submit
        console.log('=== Form Data Before Submit ===');
        $('.option-image-id').each(function(index) {
            console.log('Option ' + index + ' image ID:', $(this).val());
        });
        
        $.ajax({
            url: wpsqp_admin_ajax.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    $status.css('color', 'green').text('✓ ' + response.data.message);
                    setTimeout(function() {
                        window.location.href = '<?php echo admin_url('admin.php?page=wpsqp-questions'); ?>';
                    }, 1200);
                } else {
                    $status.css('color', 'red').text('✗ ' + response.data);
                    $btn.prop('disabled', false).text('<?php echo $is_edit ? __('Update Question', 'wpsqptxd') : __('Save Question', 'wpsqptxd'); ?>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                $status.css('color', 'red').text('Server error. Please try again.');
                $btn.prop('disabled', false).text('<?php echo $is_edit ? __('Update Question', 'wpsqptxd') : __('Save Question', 'wpsqptxd'); ?>');
            }
        });
    });
});
</script>