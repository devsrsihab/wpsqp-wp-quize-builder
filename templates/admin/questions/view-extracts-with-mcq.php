<?php
if (!defined('ABSPATH')) exit;

/**
 * View template for EXTRACTS_WITH_MCQ question type
 * 
 * @param object $question - Main question data
 * @param object $type_data - Type-specific data (already decoded)
 */

// Data is already decoded from AJAX, no need to json_decode again
$extracts = isset($type_data->extracts) ? $type_data->extracts : [];
$options_content = isset($type_data->options_content) ? $type_data->options_content : '';
$options = isset($type_data->options) ? $type_data->options : [];
$correct_answer = isset($type_data->correct_answer) ? $type_data->correct_answer : '';

// If extracts is still a string, then decode it
if (is_string($extracts)) {
    $extracts = json_decode($extracts, true) ?: [];
}

// If options is still a string, then decode it
if (is_string($options)) {
    $options = json_decode($options, true) ?: [];
}

// Ensure arrays
$extracts = is_array($extracts) ? $extracts : [];
$options = is_array($options) ? $options : [];

// Get question content
$question_content = isset($question->question_content) ? $question->question_content : '';
?>

<div class="wpsqp-view-extracts-mcq">
    <!-- Question Header -->
    <div class="view-section question-header">
        <div class="section-title">
            <span class="dashicons dashicons-info"></span>
            <h3><?php _e('Question', 'wpsqptxd'); ?></h3>
        </div>
        <div class="section-content question-content">
            <?php echo wp_kses_post($question_content); ?>
        </div>
    </div>

    <!-- Extracts Section -->
    <?php if (!empty($extracts)): ?>
    <div class="view-section extracts-section">
        <div class="section-title">
            <span class="dashicons dashicons-media-document"></span>
            <h3><?php _e('Extracts', 'wpsqptxd'); ?> <span class="extract-count">(<?php echo count($extracts); ?>)</span></h3>
        </div>
        
        <div class="extracts-tabs">
            <div class="extracts-tabs-nav">
                <?php foreach ($extracts as $index => $extract): 
                    $extract_id = is_array($extract) ? ($extract['id'] ?? chr(65 + $index)) : chr(65 + $index);
                ?>
                <button type="button" class="extract-tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                        data-tab="extract-<?php echo $index; ?>">
                    Extract <?php echo $extract_id; ?>
                </button>
                <?php endforeach; ?>
            </div>
            
            <div class="extracts-tabs-content">
                <?php foreach ($extracts as $index => $extract): 
                    $content = is_array($extract) ? ($extract['content'] ?? '') : '';
                ?>
                <div class="extract-tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" 
                     id="extract-<?php echo $index; ?>">
                    <div class="extract-content">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Options Content Section -->
    <?php if (!empty($options_content)): ?>
    <div class="view-section options-content-section">
        <div class="section-title">
            <span class="dashicons dashicons-editor-help"></span>
            <h3><?php _e('Instructions', 'wpsqptxd'); ?></h3>
        </div>
        <div class="section-content options-content">
            <?php echo wp_kses_post($options_content); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Options Section -->
    <?php if (!empty($options)): ?>
    <div class="view-section options-section">
        <div class="section-title">
            <span class="dashicons dashicons-yes-alt"></span>
            <h3><?php _e('Answer Options', 'wpsqptxd'); ?></h3>
        </div>
        
        <div class="options-list">
            <?php foreach ($options as $index => $option): 
                $letter = chr(65 + $index);
                $option_id = is_array($option) ? ($option['id'] ?? '') : '';
                $option_text = is_array($option) ? ($option['text'] ?? '') : '';
                $is_correct = ($option_id === $correct_answer);
            ?>
            <div class="option-item <?php echo $is_correct ? 'correct-option' : ''; ?>">
                <div class="option-marker">
                    <span class="option-letter"><?php echo $letter; ?>.</span>
                    <?php if ($is_correct): ?>
                    <span class="correct-badge" title="<?php _e('Correct Answer', 'wpsqptxd'); ?>">
                        <span class="dashicons dashicons-yes"></span>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="option-text">
                    <?php echo esc_html($option_text); ?>
                    <?php if ($is_correct): ?>
                    <span class="correct-label">(<?php _e('Correct', 'wpsqptxd'); ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Metadata Section -->
    <div class="view-section metadata-section">
        <div class="section-title">
            <span class="dashicons dashicons-admin-generic"></span>
            <h3><?php _e('Question Metadata', 'wpsqptxd'); ?></h3>
        </div>
        
        <div class="metadata-grid">
            <div class="metadata-item">
                <span class="metadata-label"><?php _e('Question ID:', 'wpsqptxd'); ?></span>
                <span class="metadata-value"><?php echo esc_html($question->id ?? ''); ?></span>
            </div>
            <div class="metadata-item">
                <span class="metadata-label"><?php _e('Question Type:', 'wpsqptxd'); ?></span>
                <span class="metadata-value type-badge">
                    <?php echo isset($question->question_type) ? str_replace('_', ' ', $question->question_type) : ''; ?>
                </span>
            </div>
            <div class="metadata-item">
                <span class="metadata-label"><?php _e('Created:', 'wpsqptxd'); ?></span>
                <span class="metadata-value">
                    <?php 
                    if (isset($question->created_at)) {
                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($question->created_at));
                    }
                    ?>
                </span>
            </div>
            <?php if (isset($question->updated_at) && isset($question->created_at) && $question->updated_at !== $question->created_at): ?>
            <div class="metadata-item">
                <span class="metadata-label"><?php _e('Last Updated:', 'wpsqptxd'); ?></span>
                <span class="metadata-value">
                    <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($question->updated_at)); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* View Modal Styles */
.wpsqp-view-extracts-mcq {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    max-width: 100%;
}

.view-section {
    background: #fff;
    border: 1px solid #e2e4e7;
    border-radius: 6px;
    margin-bottom: 20px;
    overflow: hidden;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e4e7;
}

.section-title .dashicons {
    color: #2271b1;
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.section-title h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1e1e1e;
    flex: 1;
}

.section-content {
    padding: 16px;
    line-height: 1.6;
}

/* Question Content */
.question-content {
    background: #f8f9fa;
    font-size: 14px;
}

/* Extracts Tabs */
.extracts-tabs {
    border: none;
}

.extracts-tabs-nav {
    display: flex;
    border-bottom: 1px solid #e2e4e7;
    background: #f1f1f1;
    padding: 0;
    overflow-x: auto;
}

.extract-tab-btn {
    padding: 10px 20px;
    border: none;
    background: none;
    border-right: 1px solid #e2e4e7;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #555;
    white-space: nowrap;
}

.extract-tab-btn:hover {
    background: #e5e5e5;
}

.extract-tab-btn.active {
    background: #fff;
    color: #2271b1;
    border-bottom: 2px solid #2271b1;
}

.extracts-tabs-content {
    padding: 20px;
}

.extract-tab-pane {
    display: none;
}

.extract-tab-pane.active {
    display: block;
}

.extract-content {
    line-height: 1.6;
    font-size: 14px;
}

/* Options Content */
.options-content {
    background: #fff3e0;
    border-left: 4px solid #ffb900;
    font-style: italic;
}

/* Options List */
.options-list {
    padding: 8px;
}

.option-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    margin: 8px 0;
    background: #f8f9fa;
    border: 1px solid #e2e4e7;
    border-radius: 4px;
}

.option-item.correct-option {
    background: #ecf7ed;
    border-color: #46b450;
    box-shadow: 0 2px 8px rgba(70, 180, 80, 0.1);
}

.option-marker {
    display: flex;
    align-items: center;
    gap: 4px;
    min-width: 40px;
}

.option-letter {
    font-weight: 600;
    color: #2271b1;
    font-size: 14px;
}

.correct-badge {
    color: #46b450;
}

.correct-badge .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.option-text {
    flex: 1;
    font-size: 14px;
    line-height: 1.5;
}

.correct-label {
    color: #46b450;
    font-size: 12px;
    font-weight: 500;
    margin-left: 8px;
}

/* Metadata Section */
.metadata-section {
    background: #f8f9fa;
}

.metadata-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    padding: 16px;
}

.metadata-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.metadata-label {
    font-size: 11px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metadata-value {
    font-size: 13px;
    color: #1e1e1e;
    word-break: break-all;
}

.type-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #e3f2fd;
    color: #1565c0;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.extract-count {
    font-size: 12px;
    font-weight: normal;
    color: #666;
    margin-left: 5px;
}

/* Responsive */
@media (max-width: 600px) {
    .metadata-grid {
        grid-template-columns: 1fr;
    }
    
    .extract-tab-btn {
        padding: 8px 12px;
        font-size: 12px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Extract tabs functionality
    $('.extract-tab-btn').on('click', function() {
        let tabId = $(this).data('tab');
        
        $('.extract-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.extract-tab-pane').removeClass('active');
        $('#' + tabId).addClass('active');
    });
});
</script>