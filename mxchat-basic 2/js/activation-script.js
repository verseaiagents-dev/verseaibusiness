/**
 * MxChat License Activation JS
 * Handles the license activation form submission with timeout handling
 */
jQuery(document).ready(function($) {
    // Activation handling
    const form = $('#mxchat-activation-form');
    const spinner = $('#mxchat-activation-spinner');
    const submitButton = $('#activate_license_button');
    const licenseStatus = $('#mxchat-license-status');
    
    if (form.length && licenseStatus.length && submitButton.length) {
        function handleActivationResponse(response) {
            spinner.hide();
            if (response.success) {
                licenseStatus.text('Active');
                licenseStatus.removeClass('inactive').addClass('active');
                form.hide();
            } else {
                licenseStatus.text('Inactive');
                alert(response.data || 'Activation failed. Please check your input.');
                submitButton.prop('disabled', false);
            }
        }

        function checkActivationStatus() {
            const email = $('#mxchat_pro_email').val();
            const key = $('#mxchat_activation_key').val();
            
            $.ajax({
                type: 'POST',
                url: mxchatAdmin.ajax_url,
                data: {
                    action: 'mxchat_check_license_status',
                    email: email,
                    key: key,
                    security: mxchatAdmin.license_nonce
                },
                success: function(response) {
                    if (response.is_active) {
                        // License is actually active, update UI
                        licenseStatus.text('Active');
                        licenseStatus.removeClass('inactive').addClass('active');
                        form.hide();
                        alert('Your license has been activated successfully!');
                    } else {
                        // Truly not activated
                        alert('License activation timed out. Please try again or contact support if the problem persists.');
                        submitButton.prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Unable to verify license status. Please refresh the page to check if activation was successful.');
                    submitButton.prop('disabled', false);
                }
            });
        }
    
        form.on('submit', function(event) {
            event.preventDefault();
            spinner.show();
            submitButton.prop('disabled', true);
        
            var formData = {
                action: 'mxchat_activate_license',
                mxchat_pro_email: $('#mxchat_pro_email').val(),
                mxchat_activation_key: $('#mxchat_activation_key').val(),
                security: mxchatAdmin.license_nonce
            };
        
            // Use $.ajax instead of $.post to have more control
            $.ajax({
                type: 'POST',
                url: mxchatAdmin.ajax_url,
                data: formData,
                timeout: 70000, // 70 seconds (slightly longer than server timeout)
                success: function(response) {
                    handleActivationResponse(response);
                },
                error: function(jqXHR, textStatus) {
                    if (textStatus === 'timeout') {
                        spinner.hide();
                        alert('Activation is taking longer than expected. Checking status...');
                        checkActivationStatus();
                    } else {
                        //console.log('AJAX Error:', textStatus, jqXHR.responseText);
                        alert('Activation failed due to a server error: ' + (jqXHR.responseText || textStatus));
                        spinner.hide();
                        submitButton.prop('disabled', false);
                    }
                }
            });
        });
    }
});