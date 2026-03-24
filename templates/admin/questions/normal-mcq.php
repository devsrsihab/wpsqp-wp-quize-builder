<?php
/**
 * Template: Add/Edit NORMAL_MCQ question
 * Follows same pattern as extracts-with-mcq.php
 */
if (!defined('ABSPATH')) exit;

// Edit mode হলে existing data load করো
$is_edit     = isset($question) && $question;
$question_id = $is_edit ? esc_attr($question->id) : '';
$q_content   = $is_edit ? $question->question_content : '';

// Edit mode তে options ও correct_answer load
$options        = [];
$correct_answer = '';
if ($is_edit && isset($type_data) && $type_data) {
    $options        = json_decode($type_data->options, true) ?: [];
    $correct_answer = $type_data->correct_answer;
}

// Default: 4টা empty option
if (empty($options)) {
    $options = [
        ['id' => 'opt_1', 'text' => ''],
        ['id' => 'opt_2', 'text' => ''],
        ['id' => 'opt_3', 'text' => ''],
        ['id' => 'opt_4', 'text' => ''],
    ];
}
?>
<div class="wrap wpsqp-question-wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? __('Edit Question', 'wpsqptxd') : __('Add Question', 'wpsqptxd'); ?>
        <span class="wpsqp-type-badge">NORMAL MCQ</span>
    </h1>
    <a href="<?php echo admin_url('admin.php?page=wpsqp-questions'); ?>" class="page-title-action">
        <?php _e('Back to Questions', 'wpsqptxd'); ?>
    </a>
    <hr class="wp-header-end">

    <div id="wpsqp-feedback" class="notice" style="display:none;"></div>

    <form id="wpsqp-normal-mcq-form" method="post">
        <?php wp_nonce_field('wpsqp_save_question', 'wpsqp_question_nonce'); ?>
        <input type="hidden" name="question_type" value="NORMAL_MCQ">
        <input type="hidden" name="question_id"   value="<?php echo $question_id; ?>">

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
            </div>
        </div>

        <!-- ═══════════════════════════════════════
             SECTION 2: Answer Options
        ════════════════════════════════════════ -->
        <div class="postbox">
            <div class="postbox-header">
                <h2><?php _e('Answer Options', 'wpsqptxd'); ?></h2>
            </div>
            <div class="inside">
                <p class="description">
                    <?php _e('Add answer options below. Select the correct answer using the radio button.', 'wpsqptxd'); ?>
                </p>

                <table class="widefat wpsqp-options-table" id="wpsqp-options-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><?php _e('Correct', 'wpsqptxd'); ?></th>
                            <th style="width:80px;"><?php _e('Option', 'wpsqptxd'); ?></th>
                            <th><?php _e('Text', 'wpsqptxd'); ?></th>
                            <th style="width:80px;"><?php _e('Remove', 'wpsqptxd'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wpsqp-options-body">
                        <?php foreach ($options as $index => $option) :
                            $opt_id    = esc_attr($option['id']);
                            $opt_text  = esc_attr($option['text']);
                            $opt_label = chr(65 + $index); // A, B, C, D...
                            $checked   = ($correct_answer === (string)$index || $correct_answer === $opt_id) ? 'checked' : '';
                        ?>
                        <tr class="wpsqp-option-row" data-index="<?php echo $index; ?>">
                            <td class="text-center">
                                <input type="radio"
                                       name="correct_answer"
                                       value="<?php echo $index; ?>"
                                       <?php echo $checked; ?>
                                       required>
                            </td>
                            <td>
                                <span class="wpsqp-option-label"><?php echo $opt_label; ?></span>
                                <input type="hidden" name="options[<?php echo $index; ?>][id]" value="<?php echo $opt_id; ?>">
                            </td>
                            <td>
                                <input type="text"
                                       name="options[<?php echo $index; ?>][text]"
                                       value="<?php echo $opt_text; ?>"
                                       class="large-text wpsqp-option-text"
                                       placeholder="<?php echo sprintf(__('Option %s text...', 'wpsqptxd'), $opt_label); ?>"
                                       required>
                            </td>
                            <td class="text-center">
                                <?php if ($index >= 2) : // প্রথম দুটো remove করা যাবে না ?>
                                <button type="button" class="button wpsqp-remove-option">
                                    <?php _e('Remove', 'wpsqptxd'); ?>
                                </button>
                                <?php else : ?>
                                <span class="description"><?php _e('Required', 'wpsqptxd'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p>
                    <button type="button" id="wpsqp-add-option" class="button button-secondary">
                        + <?php _e('Add Option', 'wpsqptxd'); ?>
                    </button>
                    <span class="description" style="margin-left:10px;">
                        <?php _e('Minimum 2 options, maximum 6 options.', 'wpsqptxd'); ?>
                    </span>
                </p>
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

<!-- ═══════════════════════════════════════
     JavaScript — inline, form-specific
════════════════════════════════════════ -->
<script>
(function($) {
    'use strict';

    var optionCount = <?php echo count($options); ?>;
    var maxOptions  = 6;

    // ── Option labels আপডেট করো (A, B, C...)
    function reindexOptions() {
        $('#wpsqp-options-body .wpsqp-option-row').each(function(i) {
            var label = String.fromCharCode(65 + i);
            $(this).attr('data-index', i);
            $(this).find('.wpsqp-option-label').text(label);
            $(this).find('input[type="radio"]').val(i);
            $(this).find('input[name^="options["]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/options\[\d+\]/, 'options[' + i + ']'));
            });
            $(this).find('input[type="text"]').attr(
                'placeholder',
                'Option ' + label + ' text...'
            );
            // প্রথম দুটোতে Remove button দেখাবে না
            if (i < 2) {
                $(this).find('.wpsqp-remove-option').hide();
                $(this).find('.description').show();
            } else {
                $(this).find('.wpsqp-remove-option').show();
                $(this).find('.description').hide();
            }
        });
        optionCount = $('#wpsqp-options-body .wpsqp-option-row').length;
        $('#wpsqp-add-option').prop('disabled', optionCount >= maxOptions);
    }

    // ── নতুন option row যোগ করো
    $('#wpsqp-add-option').on('click', function() {
        if (optionCount >= maxOptions) return;

        var newIndex = optionCount;
        var label    = String.fromCharCode(65 + newIndex);
        var optId    = 'opt_' + (newIndex + 1) + '_' + Date.now();

        var row = '<tr class="wpsqp-option-row" data-index="' + newIndex + '">' +
            '<td class="text-center">' +
                '<input type="radio" name="correct_answer" value="' + newIndex + '" required>' +
            '</td>' +
            '<td>' +
                '<span class="wpsqp-option-label">' + label + '</span>' +
                '<input type="hidden" name="options[' + newIndex + '][id]" value="' + optId + '">' +
            '</td>' +
            '<td>' +
                '<input type="text" name="options[' + newIndex + '][text]" ' +
                       'value="" class="large-text wpsqp-option-text" ' +
                       'placeholder="Option ' + label + ' text..." required>' +
            '</td>' +
            '<td class="text-center">' +
                '<button type="button" class="button wpsqp-remove-option">Remove</button>' +
                '<span class="description" style="display:none;">Required</span>' +
            '</td>' +
        '</tr>';

        $('#wpsqp-options-body').append(row);
        reindexOptions();
    });

    // ── Option remove করো
    $(document).on('click', '.wpsqp-remove-option', function() {
        $(this).closest('.wpsqp-option-row').remove();
        reindexOptions();
    });

    // ── Form submit — AJAX


    $('#wpsqp-normal-mcq-form').on('submit', function(e) {
        e.preventDefault();

        var $btn    = $('#wpsqp-save-btn');
        var $status = $('#wpsqp-save-status');

        if ($('#wpsqp-options-body .wpsqp-option-row').length < 2) {
            alert('<?php _e('Please add at least 2 options.', 'wpsqptxd'); ?>');
            return;
        }
        if (!$('input[name="correct_answer"]:checked').length) {
            alert('<?php _e('Please select the correct answer.', 'wpsqptxd'); ?>');
            return;
        }

        $btn.prop('disabled', true).text('Saving...');

        // ✅ tinyMCE content আগে sync করো তারপর serialize করো
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('question_content')) {
            tinyMCE.get('question_content').save(); // textarea-তে content sync করে
        }

        var formData = new FormData(document.getElementById('wpsqp-normal-mcq-form'));
        formData.append('action', 'wpsqp_save_question');

        $.ajax({
            url:         wpsqp_admin_ajax.ajax_url,
            method:      'POST',
            data:        formData,
            processData: false,  // ✅ FormData-র জন্য জরুরি
            contentType: false,  // ✅ FormData-র জন্য জরুরি
            success: function(response) {
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
            error: function() {
                $status.css('color', 'red').text('Server error. Please try again.');
                $btn.prop('disabled', false).text('<?php echo $is_edit ? __('Update Question', 'wpsqptxd') : __('Save Question', 'wpsqptxd'); ?>');
            }
        });
    });

})(jQuery);
</script>