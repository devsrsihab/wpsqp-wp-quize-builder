<?php
if (!defined('ABSPATH')) exit;

/**
 * Edit Question Template
 * 
 * @param object $question - Question data from database
 * @param object $type_data - Question type specific data
 */

// Get question type for template loading
$type = $question->question_type;
$type_lower = strtolower(str_replace('_', '-', $type));
$type_label = str_replace('_', ' ', $type);

// Check if type-specific edit template exists
$type_template = WPSQP_PLUGIN_DIR . 'templates/admin/questions/edit-' . $type_lower . '.php';

// If no specific edit template, use the add template with edit mode
$add_template = WPSQP_PLUGIN_DIR . 'templates/admin/questions/' . $type_lower . '.php';
?>

<div class="wrap wpsqp-edit-question-page">
    
    <?php if (file_exists($type_template)): ?>
        <!-- Use type-specific edit template -->
        <?php include $type_template; ?>
        
    <?php elseif (file_exists($add_template)): ?>
        <!-- Use add template with edit mode (will need modifications) -->
        <div class="notice notice-warning">
            <p><?php _e('Using generic edit form. Some fields may not load correctly.', 'wpsqptxd'); ?></p>
        </div>
        <?php 
        // Set edit mode flag
        $edit_mode = true;
        include $add_template; 
        ?>
        
    <?php else: ?>
        <!-- Fallback generic edit form -->
        <div class="notice notice-error">
            <p><?php _e('Question type template not found.', 'wpsqptxd'); ?></p>
        </div>
        
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
                        
                        <!-- Type-specific data display -->
                        <div class="postbox">
                            <h2 class="hndle"><?php printf(__('%s Data', 'wpsqptxd'), $type_label); ?></h2>
                            <div class="inside">
                                <pre><?php print_r($type_data); ?></pre>
                            </div>
                        </div>
                    </div>
                    
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
                                            <button type="submit" class="button button-primary button-large">
                                                <?php _e('Update Question', 'wpsqptxd'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wpsqp-question-form').on('submit', function(e) {
                e.preventDefault();
                
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
                        }
                    },
                    error: function() {
                        alert('<?php _e('Error updating question', 'wpsqptxd'); ?>');
                    }
                });
            });
        });
        </script>
    <?php endif; ?>
</div>