<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap wpsqp-categories-page">
    <h1 class="wp-heading-inline"><?php _e('Categories', 'wpsqptxd'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=wpsqp-add-category'); ?>" class="page-title-action">
        <?php _e('Add New Category', 'wpsqptxd'); ?>
    </a>
    <hr class="wp-header-end">

    <!-- Categories List Table -->
    <?php if (!empty($categories)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="50"><?php _e('ID', 'wpsqptxd'); ?></th>
                    <th><?php _e('Name', 'wpsqptxd'); ?></th>
                    <th><?php _e('Slug', 'wpsqptxd'); ?></th>
                    <th><?php _e('Description', 'wpsqptxd'); ?></th>
                    <th width="80"><?php _e('Tests', 'wpsqptxd'); ?></th>
                    <th width="80"><?php _e('Order', 'wpsqptxd'); ?></th>
                    <th width="100"><?php _e('Status', 'wpsqptxd'); ?></th>
                    <th width="200"><?php _e('Actions', 'wpsqptxd'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category->id; ?></td>
                        <td><strong><?php echo esc_html($category->name); ?></strong></td>
                        <td><?php echo esc_html($category->slug); ?></td>
                        <td><?php echo wp_trim_words(esc_html($category->description), 10); ?></td>
                        <td><?php echo intval($category->test_count); ?></td>
                        <td><?php echo $category->display_order; ?></td>
                        <td>
                            <?php if ($category->is_active): ?>
                                <span class="status-active"><?php _e('Active', 'wpsqptxd'); ?></span>
                            <?php else: ?>
                                <span class="status-inactive"><?php _e('Inactive', 'wpsqptxd'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wpsqp-edit-category&id=' . $category->id); ?>" 
                               class="button button-small">
                                <span class="dashicons dashicons-edit"></span> <?php _e('Edit', 'wpsqptxd'); ?>
                            </a>
                            <button type="button" 
                                    class="button button-small delete-category" 
                                    data-id="<?php echo $category->id; ?>"
                                    data-name="<?php echo esc_attr($category->name); ?>">
                                <span class="dashicons dashicons-trash"></span> <?php _e('Delete', 'wpsqptxd'); ?>
                            </button>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No categories found.', 'wpsqptxd'); ?> 
            <a href="<?php echo admin_url('admin.php?page=wpsqp-add-category'); ?>">
                <?php _e('Add your first category', 'wpsqptxd'); ?>
            </a></p>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-category-modal" class="wpsqp-modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h3><?php _e('Delete Category', 'wpsqptxd'); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p><?php _e('Are you sure you want to delete', 'wpsqptxd'); ?> 
               <strong id="delete-category-name"></strong>?</p>
            <p class="description"><?php _e('This action cannot be undone.', 'wpsqptxd'); ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="button button-primary" id="confirm-delete-category">
                <?php _e('Delete', 'wpsqptxd'); ?>
            </button>
            <button type="button" class="button modal-close">
                <?php _e('Cancel', 'wpsqptxd'); ?>
            </button>
        </div>
    </div>
</div>


<script>
jQuery(document).ready(function($) {
    let currentCategoryId = null;
    
    // Delete button click
    $('.delete-category').on('click', function() {
        currentCategoryId = $(this).data('id');
        $('#delete-category-name').text($(this).data('name'));
        $('#delete-category-modal').fadeIn(200);
    });
    
    // Confirm delete
    $('#confirm-delete-category').on('click', function() {
        if (!currentCategoryId) return;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsqp_delete_category',
                id: currentCategoryId,
                nonce: '<?php echo wp_create_nonce('wpsqp_category_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('<?php _e('Error deleting category', 'wpsqptxd'); ?>');
            }
        });
    });
    
    // Activate/Deactivate
    $('.activate-category, .deactivate-category').on('click', function() {
        let $btn = $(this);
        let id = $btn.data('id');
        let isActive = $btn.hasClass('activate-category') ? 1 : 0;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsqp_save_category',
                id: id,
                is_active: isActive,
                nonce: '<?php echo wp_create_nonce('wpsqp_category_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
    
    // Modal close
    $('.modal-close, .modal-overlay').on('click', function() {
        $('#delete-category-modal').fadeOut(200);
    });
});
</script>