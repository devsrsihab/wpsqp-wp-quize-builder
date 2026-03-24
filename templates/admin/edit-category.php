<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wpsqp-edit-category-page">
    <h1><?php _e('Edit Category', 'wpsqptxd'); ?></h1>
    
    <form id="wpsqp-category-form" method="post">
        <?php wp_nonce_field('wpsqp_save_category', 'wpsqp_category_nonce'); ?>
        <input type="hidden" name="id" value="<?php echo $category->id; ?>">
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cat-name"><?php _e('Name', 'wpsqptxd'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="cat-name" 
                           name="name" 
                           class="regular-text" 
                           value="<?php echo esc_attr($category->name); ?>"
                           required>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cat-slug"><?php _e('Slug', 'wpsqptxd'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="cat-slug" 
                           name="slug" 
                           class="regular-text" 
                           value="<?php echo esc_attr($category->slug); ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cat-description"><?php _e('Description', 'wpsqptxd'); ?></label>
                </th>
                <td>
                    <textarea id="cat-description" 
                              name="description" 
                              rows="5" 
                              class="large-text"><?php echo esc_textarea($category->description); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cat-order"><?php _e('Display Order', 'wpsqptxd'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="cat-order" 
                           name="display_order" 
                           value="<?php echo $category->display_order; ?>" 
                           min="0" 
                           class="small-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Status', 'wpsqptxd'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               <?php checked($category->is_active, 1); ?>>
                        <?php _e('Active', 'wpsqptxd'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php _e('Update Category', 'wpsqptxd'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=wpsqp-categories'); ?>" class="button">
                <?php _e('Cancel', 'wpsqptxd'); ?>
            </a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#wpsqp-category-form').on('submit', function(e) {
        e.preventDefault();
        
        // Get the nonce value from the hidden field
        let nonce = $('input[name="wpsqp_category_nonce"]').val();
        
        // Serialize form data
        let formData = $(this).serializeArray();
        
        // Add action to the data
        formData.push({name: 'action', value: 'wpsqp_save_category'});
        
        console.log('Sending data:', formData); // Debug log
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Response:', response); // Debug log
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=wpsqp-categories'); ?>';
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', status, error);
                console.log('Response Text:', xhr.responseText);
                alert('<?php _e('Error updating category', 'wpsqptxd'); ?>');
            }
        });
    });
});
</script>