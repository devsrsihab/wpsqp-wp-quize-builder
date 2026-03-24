<?php if (!defined('ABSPATH')) exit; ?>

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

<style>
.question-types-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}
.question-type-card {
    background: #f8f9fa;
    border: 1px solid #e2e4e7;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.question-type-card:hover {
    border-color: #2271b1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.question-type-card .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: #2271b1;
    margin-bottom: 10px;
}
.question-type-card h4 {
    margin: 10px 0 5px;
    font-size: 14px;
    font-weight: 600;
}
.question-type-card p {
    margin: 0;
    font-size: 12px;
    color: #666;
}
@media (max-width: 600px) {
    .question-types-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.question-type-card').on('click', function() {
        let type = $(this).data('type');
        window.location.href = '<?php echo admin_url('admin.php?page=wpsqp-add-question&type='); ?>' + type;
    });
});
</script>