<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wpsqp-questions-page">
    <h1 class="wp-heading-inline"><?php _e('Questions', 'wpsqptxd'); ?></h1>
    <button type="button" class="page-title-action add-new-question-btn">
        <?php _e('Add New Question', 'wpsqptxd'); ?>
    </button>
    <hr class="wp-header-end">

    <!-- Questions List Table -->
    <?php if (!empty($questions)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="60"><?php _e('ID', 'wpsqptxd'); ?></th>
                    <th width="180"><?php _e('Type', 'wpsqptxd'); ?></th>
                    <th width="100"><?php _e('Used in Tests', 'wpsqptxd'); ?></th>
                    <th width="120"><?php _e('Created', 'wpsqptxd'); ?></th>
                    <th width="200"><?php _e('Actions', 'wpsqptxd'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo substr($question->id, 0, 8); ?></td>
                        <td>
                            <span class="question-type-badge type-<?php echo strtolower($question->question_type); ?>">
                                <?php echo str_replace('_', ' ', $question->question_type); ?>
                            </span>
                        </td>
                        <td><?php echo intval($question->used_in_tests); ?></td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($question->created_at)); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wpsqp-edit-question&id=' . $question->id); ?>" 
                               class="button button-small">
                                <span class="dashicons dashicons-edit"></span> <?php _e('Edit', 'wpsqptxd'); ?>
                            </a>
                            <button type="button" 
                                    class="button button-small view-question" 
                                    data-id="<?php echo esc_attr($question->id); ?>"
                                    data-type="<?php echo esc_attr($question->question_type); ?>">
                                <span class="dashicons dashicons-visibility"></span> <?php _e('View', 'wpsqptxd'); ?>
                            </button>
                            <button type="button" 
                                    class="button button-small delete-question" 
                                    data-id="<?php echo esc_attr($question->id); ?>">
                                <span class="dashicons dashicons-trash"></span> <?php _e('Delete', 'wpsqptxd'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No questions found.', 'wpsqptxd'); ?> 
            <button type="button" class="button-link add-new-question-btn">
                <?php _e('Add your first question', 'wpsqptxd'); ?>
            </button></p>
        </div>
    <?php endif; ?>
</div>

<!-- Question Type Selection Modal -->
<?php include WPSQP_PLUGIN_DIR . 'templates/modals/question-type-modal.php'; ?>

<!-- View Question Modal Container (will be filled dynamically) -->
<div id="view-question-modal" class="wpsqp-modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3 id="view-modal-title"><?php _e('Question Preview', 'wpsqptxd'); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="view-modal-body">
            <div class="preview-loading"><?php _e('Loading question...', 'wpsqptxd'); ?></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary modal-close">
                <?php _e('Close', 'wpsqptxd'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-question-modal" class="wpsqp-modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h3><?php _e('Delete Question', 'wpsqptxd'); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p><?php _e('Are you sure you want to delete this question?', 'wpsqptxd'); ?></p>
            <p class="description"><?php _e('This action cannot be undone.', 'wpsqptxd'); ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary" id="confirm-delete-question">
                <?php _e('Delete', 'wpsqptxd'); ?>
            </button>
            <button type="button" class="button modal-close">
                <?php _e('Cancel', 'wpsqptxd'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.wpsqp-questions-page .question-type-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.type-normal_mcq { background: #e3f2fd; color: #1565c0; }
.type-normal_mcq_with_images { background: #f3e5f5; color: #7b1fa2; }
.type-extracts_with_mcq { background: #e8f5e9; color: #2e7d32; }
.type-extracts_with_matching { background: #fff3e0; color: #e65100; }
.type-writing_task { background: #ffebee; color: #c62828; }
.type-sentence_matching { background: #e0f2f1; color: #00695c; }
.type-gap_fill_dropdown { background: #fce4ec; color: #c2185b; }
.wpsqp-questions-page .button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    vertical-align: middle;
}
</style>

<!-- <script>
jQuery(document).ready(function($) {
    let currentQuestionId = null;
    
    // Add new question button
    $('.add-new-question-btn').on('click', function() {
        $('#question-type-modal').fadeIn(200);
    });
    
    // View question
    $('.view-question').on('click', function() {
        let id = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsqp_get_question_data',
                id: id,
                nonce: '<?php echo wp_create_nonce('wpsqp_question_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    let html = '<h4>Question Content:</h4>';
                    html += '<div class="question-content">' + response.data.question.question_content + '</div>';
                    
                    if (response.data.type_data) {
                        html += '<h4>Question Data:</h4>';
                        html += '<pre>' + JSON.stringify(response.data.type_data, null, 2) + '</pre>';
                    }
                    
                    $('#question-preview-content').html(html);
                    $('#view-question-modal').fadeIn(200);
                } else {
                    alert(response.data);
                }
            }
        });
    });
    
    // Delete question
    $('.delete-question').on('click', function() {
        currentQuestionId = $(this).data('id');
        $('#delete-question-modal').fadeIn(200);
    });
    
    $('#confirm-delete-question').on('click', function() {
        if (!currentQuestionId) return;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsqp_delete_question',
                id: currentQuestionId,
                nonce: '<?php echo wp_create_nonce('wpsqp_question_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('<?php _e('Error deleting question', 'wpsqptxd'); ?>');
            }
        });
    });
    
    // Modal close
    $('.modal-close, .modal-overlay').on('click', function() {
        $('.wpsqp-modal').fadeOut(200);
    });
});
</script> -->


<!-- Update the JavaScript for view button -->
<script>
jQuery(document).ready(function($) {
    let currentQuestionId = null;

    // Add new question button
    $('.add-new-question-btn').on('click', function() {
        $('#question-type-modal').fadeIn(200);
    });
    
    // View question button handler
    $('.view-question').on('click', function() {
        let questionId = $(this).data('id');
        let questionType = $(this).data('type');
        
        console.log('Viewing question:', questionId, 'Type:', questionType);
        
        // Show modal with loading
        $('#view-modal-body').html('<div class="preview-loading">Loading question...</div>');
        $('#view-question-modal').fadeIn(200);
        
        // Load question data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsqp_get_question_data',
                id: questionId,
                nonce: '<?php echo wp_create_nonce('wpsqp_question_nonce'); ?>'
            },
            success: function(response) {
                console.log('Get question data response:', response);
                
                if (response.success) {
                    let question = response.data.question;
                    let typeData = response.data.type_data;
                    
                    console.log('Question type:', question.question_type);
                    console.log('Question data:', response.data);

                    
                    // 🔥 FIX: Load view template for ALL question types
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wpsqp_load_view_template',
                            question_type: question.question_type,
                            question: question,
                            type_data: typeData,
                            nonce: '<?php echo wp_create_nonce('wpsqp_question_nonce'); ?>'
                        },
                        success: function(templateResponse) {
                            console.log('Template response:', templateResponse);
                            
                            if (templateResponse.success) {
                                $('#view-modal-body').html(templateResponse.data.html);
                            } else {
                                console.error('Template error:', templateResponse.data);
                                showFallbackView(question, typeData);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Template load error:', error);
                            showFallbackView(question, typeData);
                        }
                    });
                } else {
                    $('#view-modal-body').html('<div class="preview-error">Error: ' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Get question error:', error);
                $('#view-modal-body').html('<div class="preview-error">Error loading question</div>');
            }
        });
    });
    
    // Fallback view function
    function showFallbackView(question, typeData) {
        let html = '<div class="fallback-view">';
        html += '<h4>Question Content</h4>';
        html += '<div class="question-content">' + (question.question_content || '') + '</div>';
        html += '<h4>Question Data</h4>';
        html += '<pre>' + JSON.stringify(typeData, null, 2) + '</pre>';
        html += '</div>';
        $('#view-modal-body').html(html);
    }

    // Delete question
    $('.delete-question').on('click', function() {
        currentQuestionId = $(this).data('id');
        $('#delete-question-modal').fadeIn(200);
    });
    
    $('#confirm-delete-question').on('click', function() {
        if (!currentQuestionId) return;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsqp_delete_question',
                id: currentQuestionId,
                nonce: '<?php echo wp_create_nonce('wpsqp_question_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('<?php _e('Error deleting question', 'wpsqptxd'); ?>');
            }
        });
    });

    // Modal close handlers
    $('.modal-close, .modal-overlay').on('click', function() {
        $('#view-question-modal').fadeOut(200);
        $('#delete-question-modal').fadeOut(200);
        $('#question-type-modal').fadeOut(200);
    });
});
</script>
