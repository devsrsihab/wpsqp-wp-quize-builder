<?php
if (!defined('ABSPATH')) exit;

$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
$type_label = str_replace('_', ' ', $type);
$type_lower = strtolower(str_replace('_', '-', $type));

// Check if type-specific template exists
$specific_template = WPSQP_PLUGIN_DIR . 'templates/admin/questions/' . $type_lower . '.php';

if (file_exists($specific_template)) {
    // If type-specific template exists, include it directly
    include $specific_template;
    return;
}
?>

<div class="wrap wpsqp-add-question-page">
    <h1><?php printf(__('Add %s Question', 'wpsqptxd'), $type_label); ?></h1>
    
    <form id="wpsqp-question-form" method="post">
        <?php wp_nonce_field('wpsqp_save_question', 'wpsqp_question_nonce'); ?>
        <input type="hidden" name="question_type" value="<?php echo esc_attr($type); ?>">
        
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <!-- Question Content -->
                    <div class="postbox">
                        <h2 class="hndle"><?php _e('Question Content', 'wpsqptxd'); ?></h2>
                        <div class="inside">
                            <?php
                            wp_editor('', 'question_content', [
                                'textarea_name' => 'question_content',
                                'textarea_rows' => 10,
                                'media_buttons' => true,
                                'teeny' => false,
                                'quicktags' => true
                            ]);
                            ?>
                        </div>
                    </div>
                </div>
                
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
                                        <button type="submit" class="button button-primary button-large">
                                            <?php _e('Save Question', 'wpsqptxd'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="postbox-container-2" class="postbox-container">
                    <!-- Question Type Specific Fields -->
                    <div class="postbox">
                        <h2 class="hndle"><?php printf(__('%s Options', 'wpsqptxd'), $type_label); ?></h2>
                        <div class="inside">
                            <p class="description">
                                <?php _e('This question type requires specific configuration.', 'wpsqptxd'); ?>
                            </p>
                            <div class="question-type-fields">
                                <!-- Type-specific fields will be loaded here -->
                                <p class="description">
                                    <?php _e('No specific template found for this question type.', 'wpsqptxd'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Generic add question form loaded for type: <?php echo $type; ?>');
    
    // Form submit
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
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('<?php _e('Error saving question', 'wpsqptxd'); ?>');
            }
        });
    });
});
</script>