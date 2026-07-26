/**
 * TwinTack Custom Grips — customer intake wizard (GF form 9 parity).
 *
 * @package TwinTack_Custom_Grips
 * @since   1.4.0
 */
(function ($) {
    'use strict';

    var $form = $('#ttcg-customer-intake-form');
    if (!$form.length || typeof ttcgIntake === 'undefined') {
        return;
    }

    var totalSteps = ttcgIntake.total_steps || 4;
    var stepTitles = ttcgIntake.step_titles || [];
    var currentStep = 1;

    var $steps = $form.find('.ttcg-intake-step');
    var $prev = $('#ttcg-intake-prev');
    var $next = $('#ttcg-intake-next');
    var $submit = $('#ttcg-intake-submit');
    var $feedback = $('#ttcg-intake-feedback');
    var $stepLabel = $('#ttcg-intake-step-label');
    var $progressBar = $('#ttcg-intake-progress-bar');
    var $quantity = $('#ttcg-intake-quantity');
    var $unitPrice = $('#ttcg-intake-unit-price');

    function layoutsNeedingSecondary(layout) {
        return layout === '2-Color Fade' || layout === '3-Color Fade' || layout === 'Splatter';
    }

    function layoutsNeedingTertiary(layout) {
        return layout === '3-Color Fade';
    }

    function getSelectedLayout() {
        return $form.find('input[name="design_layout"]:checked').val() || '';
    }

    function updateColorGroups() {
        var layout = getSelectedLayout();
        var showSecondary = layoutsNeedingSecondary(layout);
        var showTertiary = layoutsNeedingTertiary(layout);

        var $secondary = $form.find('[data-color-group="secondary"]');
        var $tertiary = $form.find('[data-color-group="tertiary"]');

        $secondary.prop('hidden', !showSecondary);
        $tertiary.prop('hidden', !showTertiary);

        $secondary.find('input').prop('required', showSecondary);
        $tertiary.find('input').prop('required', showTertiary);

        if (!showSecondary) {
            $secondary.find('input:checked').prop('checked', false);
        }
        if (!showTertiary) {
            $tertiary.find('input:checked').prop('checked', false);
        }
    }

    function formatPrice(amount) {
        if (ttcgIntake.currency_symbol) {
            return ttcgIntake.currency_symbol + parseFloat(amount).toFixed(2);
        }
        return '$' + parseFloat(amount).toFixed(2);
    }

    function updateUnitPrice() {
        var qty = parseInt($quantity.val(), 10) || ttcgIntake.min_qty;
        var amount = qty >= 75 ? ttcgIntake.price_tier_high : ttcgIntake.price_tier_low;
        $unitPrice.text(formatPrice(amount));
    }

    function updateProgressUI() {
        var pct = Math.round((currentStep / totalSteps) * 100);
        $progressBar.css('width', pct + '%');
        $progressBar.closest('[role="progressbar"]').attr('aria-valuenow', pct);

        var title = stepTitles[currentStep - 1] || '';
        $stepLabel.text(
            'Step ' + currentStep + ' of ' + totalSteps + (title ? ' - ' + title : '')
        );

        var isLastStep = currentStep >= totalSteps;

        if (currentStep === 1) {
            $prev.attr('hidden', 'hidden').hide();
        } else {
            $prev.removeAttr('hidden').show();
        }

        if (isLastStep) {
            $next.attr('hidden', 'hidden').hide();
            $submit.removeAttr('hidden').show();
        } else {
            $next.removeAttr('hidden').show();
            $submit.attr('hidden', 'hidden').hide();
        }
    }

    function showStep(step) {
        currentStep = step;
        $steps.each(function () {
            var $el = $(this);
            $el.prop('hidden', parseInt($el.data('step'), 10) !== step);
        });
        updateProgressUI();
        $feedback.removeClass('ttcg-customer-intake__feedback--error').html('');
        $form.find('.ttcg-intake-step__field-error').remove();

        var $active = $steps.filter('[data-step="' + step + '"]');
        if ($active.length) {
            $('html, body').animate({ scrollTop: $active.offset().top - 80 }, 200);
        }
    }

    function fieldError($field, message) {
        var $row = $field.closest('.ttcg-form-row, .ttcg-image-choice');
        if (!$row.find('.ttcg-intake-step__field-error').length) {
            $row.append('<p class="ttcg-intake-step__field-error">' + message + '</p>');
        }
    }

    function validateStep(step) {
        $form.find('.ttcg-intake-step__field-error').remove();
        var valid = true;
        var $panel = $steps.filter('[data-step="' + step + '"]');

        if (step === 1) {
            var layout = getSelectedLayout();
            if (!layout) {
                fieldError($panel.find('[data-field="design_layout"]'), ttcgIntake.strings.select_layout);
                valid = false;
            }
            if (!$form.find('input[name="primary_color"]:checked').length) {
                fieldError($panel.find('[data-color-group="primary"]'), ttcgIntake.strings.select_primary);
                valid = false;
            }
            if (layoutsNeedingSecondary(layout) && !$form.find('input[name="secondary_color"]:checked').length) {
                fieldError($panel.find('[data-color-group="secondary"]'), ttcgIntake.strings.select_secondary);
                valid = false;
            }
            if (layoutsNeedingTertiary(layout) && !$form.find('input[name="tertiary_color"]:checked').length) {
                fieldError($panel.find('[data-color-group="tertiary"]'), ttcgIntake.strings.select_tertiary);
                valid = false;
            }
        }

        if (step === 2) {
            var teamName = $('#ttcg-intake-team_name').val().trim();
            if (!teamName) {
                fieldError($('#ttcg-intake-team_name'), ttcgIntake.strings.team_required);
                valid = false;
            }
        }

        if (step === 3) {
            var qty = parseInt($quantity.val(), 10);
            if (!qty || qty < ttcgIntake.min_qty || qty > ttcgIntake.max_qty) {
                fieldError($quantity, ttcgIntake.strings.quantity_invalid);
                valid = false;
            }
        }

        if (step === 4) {
            if (!$('#ttcg-intake-first_name').val().trim()) {
                fieldError($('#ttcg-intake-first_name'), ttcgIntake.strings.name_required);
                valid = false;
            }
            if (!$('#ttcg-intake-last_name').val().trim()) {
                fieldError($('#ttcg-intake-last_name'), ttcgIntake.strings.name_required);
                valid = false;
            }
            if (!$('#ttcg-intake-phone').val().trim()) {
                fieldError($('#ttcg-intake-phone'), ttcgIntake.strings.phone_required);
                valid = false;
            }
        }

        if (step === 2 && !validateArtworkFileSize()) {
            valid = false;
        }

        return valid;
    }

    function validateArtworkFileSize() {
        var fileInput = document.getElementById('ttcg-intake-artwork_file');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            return true;
        }

        var maxBytes = ttcgIntake.max_upload_size || 0;
        if (maxBytes > 0 && fileInput.files[0].size > maxBytes) {
            var label = ttcgIntake.max_upload_label || '';
            var message = ttcgIntake.strings.file_too_large.replace('%s', label);
            fieldError($(fileInput), message);
            return false;
        }

        return true;
    }

    $form.on('change', 'input[name="design_layout"]', updateColorGroups);
    $quantity.on('change input', updateUnitPrice);

    $next.on('click', function () {
        if (!validateStep(currentStep)) {
            return;
        }
        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
            return;
        }
        $form.trigger('submit');
    });

    $prev.on('click', function () {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        if (!validateStep(currentStep)) {
            return;
        }

        if (!validateArtworkFileSize()) {
            return;
        }

        var formData = new FormData(this);
        // action + nonce live only in the AJAX URL — POST copies can be blank and override $_REQUEST (admin-ajax 400).
        formData.delete('action');
        formData.delete('nonce');

        $submit.prop('disabled', true).text(ttcgIntake.strings.submitting);
        $feedback.removeClass('ttcg-customer-intake__feedback--error ttcg-customer-intake__feedback--success').html('');

        $.ajax({
            url: ttcgIntake.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: { withCredentials: true },
            dataType: 'json'
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
            .fail(function (xhr) {
                var message = ttcgIntake.strings.error;
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                } else if (xhr.status === 400) {
                    message = ttcgIntake.strings.post_too_large || 'Request failed (400). Try refreshing the page and submitting again.';
                } else if (xhr.status === 403) {
                    message = 'Session expired or security check failed. Please refresh and log in again.';
                } else if (xhr.responseText === '0') {
                    message = ttcgIntake.strings.post_too_large || message;
                }
                $feedback.addClass('ttcg-customer-intake__feedback--error').text(message);
                $submit.prop('disabled', false).text(ttcgIntake.strings.submit);
            });
    });

    updateColorGroups();
    updateUnitPrice();
    updateProgressUI();
    showStep(1);
}(jQuery));
