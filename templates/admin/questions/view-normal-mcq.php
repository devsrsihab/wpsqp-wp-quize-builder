<?php
if (!defined('ABSPATH')) exit;

// Debug: Log that template is loading
error_log('=== view-normal-mcq.php is LOADING ===');
error_log('Question ID: ' . ($question->id ?? 'not set'));
error_log('Question Type: ' . ($question->question_type ?? 'not set'));

// Decode options if they are JSON string
$options = [];
$correct_answer = '';

if ($type_data) {
    $options_raw = isset($type_data->options) ? $type_data->options : [];
    if (is_string($options_raw)) {
        $options = json_decode($options_raw, true) ?: [];
    } else {
        $options = is_array($options_raw) ? $options_raw : [];
    }
    $correct_answer = isset($type_data->correct_answer) ? $type_data->correct_answer : '';
}

error_log('Options count: ' . count($options));
error_log('Correct answer (stored as ID): ' . $correct_answer);

// Ensure options array
$options = is_array($options) ? $options : [];

// Get question content - fix escape characters
$question_content = isset($question->question_content) ? stripslashes($question->question_content) : '';
?>

<div class="wpsqp-view-normal-mcq">
    
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

    <!-- Options Section -->
    <div class="view-section options-section">
        <div class="section-title">
            <span class="dashicons dashicons-yes-alt"></span>
            <h3><?php _e('Answer Options', 'wpsqptxd'); ?></h3>
        </div>
        
        <?php if (!empty($options)): ?>
        <div class="options-list">
            <?php 
            foreach ($options as $index => $option):
                // Get option data
                $option_id = is_array($option) ? ($option['id'] ?? '') : '';
                $option_text = is_array($option) ? ($option['text'] ?? '') : '';
                
                // 🔥 FIX: Check if this option is correct by comparing IDs
                $is_correct = ($option_id === $correct_answer);
                
                $letter = chr(65 + $index);
            ?>
            <div class="option-item <?php echo $is_correct ? 'correct-option' : ''; ?>">
                <div class="option-marker">
                    <span class="option-letter"><?php echo $letter; ?>.</span>
                    <?php if ($is_correct): ?>
                    <span class="correct-badge" title="<?php _e('Correct Answer', 'wpsqptxd'); ?>">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="option-text">
                    <?php echo esc_html($option_text); ?>
                    <?php if ($is_correct): ?>
                    <span class="correct-label">(<?php _e('Correct', 'wpsqptxd'); ?>)</span>
                    <?php endif; ?>
                </div>
                <?php if ($is_correct): ?>
                <div class="option-corner-badge">
                    <span class="dashicons dashicons-awards"></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="section-content">
            <p class="no-options-message"><?php _e('No options found for this question.', 'wpsqptxd'); ?></p>
        </div>
        <?php endif; ?>
    </div>

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
                <span class="metadata-label"><?php _e('Total Options:', 'wpsqptxd'); ?></span>
                <span class="metadata-value"><?php echo count($options); ?></span>
            </div>
            <div class="metadata-item">
                <span class="metadata-label"><?php _e('Correct Answer:', 'wpsqptxd'); ?></span>
                <span class="metadata-value correct-answer-badge">
                    <?php
                    // Find correct answer letter
                    $correct_letter = '';
                    foreach ($options as $index => $option) {
                        $option_id = is_array($option) ? ($option['id'] ?? '') : '';
                        if ($option_id === $correct_answer) {
                            $correct_letter = chr(65 + $index);
                            break;
                        }
                    }
                    echo $correct_letter ? $correct_letter . '. ' : 'Not set';
                    ?>
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
/* =====================================================
   NORMAL MCQ View Template Styles
   ===================================================== */
.wpsqp-view-normal-mcq {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    max-width: 100%;
    line-height: 1.5;
}

/* Section Styles */
.view-section {
    background: #fff;
    border: 1px solid #e2e4e7;
    border-radius: 12px;
    margin-bottom: 24px;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.view-section:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Section Title */
.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
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
    font-size: 15px;
    font-weight: 600;
    color: #1e1e1e;
    flex: 1;
    letter-spacing: -0.2px;
}

/* Section Content */
.section-content {
    padding: 20px;
    line-height: 1.6;
}

/* Question Content */
.question-content {
    background: #f8f9fa;
    font-size: 15px;
    color: #2c3338;
    max-height: 400px;
    overflow-y: auto;
}

.question-content p:first-child {
    margin-top: 0;
}

.question-content p:last-child {
    margin-bottom: 0;
}

/* Options Section */
.options-section {
    border-color: #e2e4e7;
}

.options-list {
    padding: 12px 16px;
}

/* Option Item */
.option-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    margin: 8px 0;
    background: #fafbfc;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.2s ease;
    position: relative;
}

.option-item:hover {
    background: #f8f9fa;
    border-color: #cbd5e0;
    transform: translateX(2px);
}

.option-item.correct-option {
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border-color: #81c784;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.15);
}

/* Option Marker */
.option-marker {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 48px;
}

.option-letter {
    font-weight: 700;
    font-size: 16px;
    color: #2271b1;
    background: #fff;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid #cbd5e0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.correct-option .option-letter {
    background: #4caf50;
    border-color: #4caf50;
    color: #fff;
}

.correct-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.correct-badge .dashicons {
    color: #4caf50;
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Option Text */
.option-text {
    flex: 1;
    font-size: 14px;
    color: #2c3338;
    line-height: 1.5;
}

.correct-option .option-text {
    font-weight: 500;
    color: #1e4620;
}

.correct-label {
    color: #4caf50;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
    background: rgba(76, 175, 80, 0.1);
    padding: 2px 8px;
    border-radius: 20px;
}

/* Option Corner Badge */
.option-corner-badge {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.6;
}

.option-corner-badge .dashicons {
    color: #4caf50;
    font-size: 24px;
    width: 24px;
    height: 24px;
}

/* Metadata Section */
.metadata-section {
    background: #f8f9fa;
    border-color: #e2e4e7;
}

.metadata-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    padding: 20px;
}

.metadata-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.metadata-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metadata-value {
    font-size: 13px;
    color: #1e1e1e;
    word-break: break-all;
    font-weight: 500;
}

.correct-answer-badge {
    color: #4caf50;
    font-weight: 600;
}

.type-badge {
    display: inline-block;
    padding: 4px 10px;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdef5 100%);
    color: #0b5e7e;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

/* No Options Message */
.no-options-message {
    color: #6c757d;
    text-align: center;
    padding: 20px;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .metadata-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        padding: 16px;
    }
    
    .option-item {
        padding: 12px 14px;
        gap: 10px;
    }
    
    .option-letter {
        width: 28px;
        height: 28px;
        font-size: 14px;
    }
    
    .option-text {
        font-size: 13px;
    }
    
    .section-title {
        padding: 12px 16px;
    }
    
    .section-content {
        padding: 16px;
    }
    
    .options-list {
        padding: 8px 12px;
    }
    
    .question-content {
        max-height: 300px;
    }
}

@media (max-width: 480px) {
    .metadata-grid {
        grid-template-columns: 1fr;
    }
    
    .option-item {
        flex-wrap: wrap;
        padding: 12px;
    }
    
    .option-marker {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .option-corner-badge {
        display: none;
    }
    
    .correct-label {
        display: inline-block;
        margin-left: 0;
        margin-top: 4px;
    }
    
    .section-title h3 {
        font-size: 13px;
    }
    
    .question-content {
        max-height: 250px;
        font-size: 14px;
    }
}

/* Animation for correct option */
@keyframes pulse-green {
    0% {
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(76, 175, 80, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
    }
}

.option-item.correct-option {
    animation: pulse-green 0.6s ease;
}

/* Scrollbar for question content */
.question-content::-webkit-scrollbar {
    width: 8px;
}

.question-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.question-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.question-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>