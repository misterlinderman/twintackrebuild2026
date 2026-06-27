/**
 * TwinTack Custom Grips — customer native intake form.
 *
 * @package TwinTack_Custom_Grips
 * @since   1.3.0
 */
(function ($) {
    'use strict';

    var $form = $('#ttcg-customer-intake-form');
    if (!$form.length || typeof ttcgIntake === 'undefined') {
        return;
    }

    var $layout = $('#ttcg-intake-design_layout');
    var $submit = $('#ttcg-intake-submit');
    var $feedback = $('#ttcg-intake-feedback');

    function layoutsNeedingSecondary(layout) {
        return layout && layout !== 'Solid Color';
    }

    function layoutsNeedingTertiary(layout) {
        return layout === 'Three Tone' || layout === '3-Color Fade';
    }

    function updateColorFields() {
        var layout = $layout.val();
        var showSecondary = layoutsNeedingSecondary(layout);
        var showTertiary = layoutsNeedingTertiary(layout);

        $form.find('[data-color-field="secondary"]').toggle(showSecondary);
        $form.find('[data-color-field="tertiary"]').toggle(showTertiary);

        if (!showSecondary) {
            $('#ttcg-intake-secondary_color').val('');
        }
        if (!showTertiary) {
            $('#ttcg-intake-tertiary_color').val('');
        }
    }

    $layout.on('change', updateColorFields);
    updateColorFields();

    $form.on('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'ttcg_submit_grip_intake');
        formData.append('nonce', ttcgIntake.nonce);

        $submit.prop('disabled', true).text(ttcgIntake.strings.submitting);
        $feedback.removeClass('ttcg-customer-intake__feedback--error ttcg-customer-intake__feedback--success').html('');

        $.ajax({
            url: ttcgIntake.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
            .done(function (res) {
                if (res.success && res.data && res.data.cart_url) {
                    $feedback.addClass('ttcg-customer-intake__feedback--success').text(res.data.message);
                    window.location.href = res.data.cart_url;
                    return;
                }

                var message = (res.data && res.data.message) ? res.data.message : ttcgIntake.strings.error;
                $feedback.addClass('ttcg-customer-intake__feedback--error').text(message);
                $submit.prop('disabled', false).text(ttcgIntake.strings.submit);
            })
            .fail(function () {
                $feedback.addClass('ttcg-customer-intake__feedback--error').text(ttcgIntake.strings.error);
                $submit.prop('disabled', false).text(ttcgIntake.strings.submit);
            });
    });
}(jQuery));
