// Simple debounce function implementation
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Helper function to open edit modal for intents/actions
function mxchatOpenEditModal(intentId, phrases) {
    const modal = document.getElementById('mxchat-edit-modal');
    if (!modal) return;

    // Get form fields
    const intentIdField = document.getElementById('edit_intent_id');
    const phrasesField = document.getElementById('edit_phrases');

    // Set values
    intentIdField.value = intentId;
    phrasesField.value = phrases;

    // Show modal with animation
    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.classList.add('active');
    });

    // Set up close handlers
    const closeModal = () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); // Match the CSS transition time
    };

    // Close button handler
    const closeBtn = modal.querySelector('.mxchat-modal-close');
    if (closeBtn) {
        closeBtn.onclick = closeModal;
    }

    // Cancel button handler
    const cancelBtn = modal.querySelector('.mxchat-modal-cancel');
    if (cancelBtn) {
        cancelBtn.onclick = closeModal;
    }

    // Click outside modal to close
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeModal();
        }
    };

    // Focus the textarea
    phrasesField.focus();
}
// Live Agent Notice Dismissal Function
function dismissLiveAgentNotice() {
    if (typeof jQuery !== 'undefined' && typeof mxchatLiveAgent !== 'undefined') {
        jQuery.post(mxchatLiveAgent.ajaxurl, {
            action: 'dismiss_live_agent_notice',
            nonce: mxchatLiveAgent.nonce
        }, function(response) {
            if (response.success) {
                jQuery('#mxchat-disabled-notice').fadeOut(300);
            }
        }).fail(function() {
            // Fallback: just hide the notice if AJAX fails
            jQuery('#mxchat-disabled-notice').fadeOut(300);
        });
    } else {
        // Fallback for cases where jQuery or localized data isn't available
        var notice = document.getElementById('mxchat-disabled-notice');
        if (notice) {
            notice.style.display = 'none';
        }
    }
}

// Updated mxchatOpenActionModal function to integrate with the new selector
function mxchatOpenActionModal(isEdit = false, actionId = '', label = '', phrases = '', threshold = 85, callbackFunction = '') {
    const modal = document.getElementById('mxchat-action-modal');
    if (!modal) return;

    // Get form fields
    const actionIdField = document.getElementById('edit_action_id');
    const labelField = document.getElementById('intent_label');
    const phrasesField = document.getElementById('action_phrases');
    const formActionType = document.getElementById('form_action_type');
    const callbackGroup = document.getElementById('callback_selection_group');
    const callbackSelect = document.getElementById('callback_function');
    const saveButton = document.getElementById('mxchat-save-action-btn');
    const nonceContainer = document.getElementById('action-nonce-container');
    const thresholdSlider = document.getElementById('similarity_threshold');
    const thresholdDisplay = document.querySelector('.mxchat-threshold-value-display');

    // Set up modal for edit or create
    if (isEdit) {
        saveButton.textContent = 'Update Action';
        formActionType.value = 'mxchat_edit_intent';
        actionIdField.value = actionId;
        labelField.value = label;
        phrasesField.value = phrases;
        callbackGroup.style.display = 'none'; // Hide callback selection when editing
        thresholdSlider.value = threshold; // Set the current threshold value
        thresholdDisplay.textContent = threshold + '%'; // Update display
        
        // Remove the required attribute when editing
        callbackSelect.removeAttribute('required');
        
        // Update the nonce field for editing
        nonceContainer.innerHTML = '';  // Clear existing nonce
        if (typeof mxchatAdmin !== 'undefined' && mxchatAdmin.edit_intent_nonce) {
            nonceContainer.innerHTML = `<input type="hidden" name="_wpnonce" value="${mxchatAdmin.edit_intent_nonce}">`;
        }
    } else {
        saveButton.textContent = 'Save Action';
        formActionType.value = 'mxchat_add_intent';
        actionIdField.value = '';
        labelField.value = '';
        phrasesField.value = '';
        callbackGroup.style.display = 'block'; // Show callback selection when creating
        thresholdSlider.value = 85; // Default value for new actions
        thresholdDisplay.textContent = '85%'; // Default display
        
        // Ensure the required attribute is present when adding
        callbackSelect.setAttribute('required', 'required');
        
        // Update the nonce field for adding
        nonceContainer.innerHTML = '';  // Clear existing nonce
        if (typeof mxchatAdmin !== 'undefined' && mxchatAdmin.add_intent_nonce) {
            nonceContainer.innerHTML = `<input type="hidden" name="_wpnonce" value="${mxchatAdmin.add_intent_nonce}">`;
        }
    }

    // Show modal with animation
    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.classList.add('active');
    });

    // Set up close handlers
    const closeModal = () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); // Match the CSS transition time
    };

    // Close button handler
    const closeBtn = modal.querySelector('.mxchat-modal-close');
    if (closeBtn) {
        closeBtn.onclick = closeModal;
    }

    // Cancel button handler
    const cancelBtn = modal.querySelector('.mxchat-modal-cancel');
    if (cancelBtn) {
        cancelBtn.onclick = closeModal;
    }

    // Click outside modal to close
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeModal();
        }
    };

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    }, { once: true });

    // Focus the first field
    labelField.focus();
    
    // Dispatch an event for the action type selector to catch
    const event = new CustomEvent('mxchatModalOpened', {
        detail: {
            isEdit: isEdit,
            callbackFunction: callbackFunction || (isEdit ? callbackSelect.value : '')
        }
    });
    document.dispatchEvent(event);
    
    return closeModal; // Return close function for external use
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Set up edit button handlers for intents
    document.querySelectorAll('.mxchat-edit-button').forEach(button => {
        button.onclick = () => {
            const intentId = button.dataset.intentId;
            const phrases = button.dataset.phrases;
            mxchatOpenEditModal(intentId, phrases);
        };
    });
    
    // Set up edit button handlers for actions (new functionality)
        document.querySelectorAll('.mxchat-action-card .mxchat-edit-button').forEach(button => {
            button.onclick = () => {
                const actionId = button.dataset.actionId;
                const phrases = button.dataset.phrases;
                const label = button.dataset.label;
                const threshold = button.dataset.threshold || 85;
                const callbackFunction = button.dataset.callbackFunction; // Add this data attribute
                mxchatOpenActionModal(true, actionId, label, phrases, threshold, callbackFunction);
            };
        });
    
    // Set up add new action buttons (new functionality)
    const addActionBtn = document.getElementById('mxchat-add-action-btn');
    if (addActionBtn) {
        addActionBtn.onclick = () => mxchatOpenActionModal();
    }
    
    const createFirstAction = document.getElementById('mxchat-create-first-action');
    if (createFirstAction) {
        createFirstAction.onclick = () => mxchatOpenActionModal();
    }
    
    // Setup category-specific new action buttons (new functionality)
    document.querySelectorAll('.mxchat-new-action-button').forEach(button => {
        button.onclick = () => {
            const category = button.closest('.mxchat-new-action-card').dataset.category;
            const closeModal = mxchatOpenActionModal();
            
            // Pre-select the appropriate callback based on category
            if (category) {
                const callbackSelect = document.getElementById('callback_function');
                if (callbackSelect) {
                    setTimeout(() => {
                        // Map categories to default callbacks
                        const categoryToCallback = {
                            'data_collection': 'mxchat_handle_form_collection',
                            'integrations': 'mxchat_handle_slack_message',
                            'custom_actions': 'mxchat_handle_custom_action',
                            'recommendations': 'mxchat_handle_product_recommendations'
                            // Add more mappings as needed
                        };
                        
                        if (categoryToCallback[category]) {
                            callbackSelect.value = categoryToCallback[category];
                        }
                    }, 100);
                }
            }
        };
    });
    
    // Handle action toggle switches (new functionality)
    document.querySelectorAll('.mxchat-action-toggle').forEach(toggle => {
        toggle.onchange = function() {
            const actionId = this.dataset.actionId;
            const isEnabled = this.checked;
            
            // Show loading indicator
            const loadingEl = document.getElementById('mxchat-action-loading');
            if (loadingEl) loadingEl.style.display = 'flex';
            
            // Send AJAX request to update status
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'mxchat_toggle_action',
                    intent_id: actionId,
                    enabled: isEnabled ? 1 : 0,
                    nonce: mxchatAdmin.toggle_action_nonce // Use the correct nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to update action status: ' + (data.data?.message || 'Unknown error'));
                    this.checked = !isEnabled; // Revert the toggle
                }
            })
            .catch(error => {
                //console.error('Error:', error);
                alert('Server error. Please try again.');
                this.checked = !isEnabled; // Revert the toggle
            })
            .finally(() => {
                if (loadingEl) loadingEl.style.display = 'none';
            });
        };
    });
    
    // Handle threshold sliders in action cards (new functionality)
    document.querySelectorAll('.mxchat-threshold-slider').forEach(slider => {
        slider.oninput = function() {
            const actionId = this.id.replace('intent_threshold_', '');
            document.getElementById('threshold_output_' + actionId).textContent = this.value + '%';
        };
    });

    // Handle threshold save buttons in action cards (new functionality)
    document.querySelectorAll('.mxchat-threshold-save').forEach(button => {
        button.onclick = function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const intentId = form.querySelector('input[name="intent_id"]').value;
            const threshold = form.querySelector('input[name="intent_threshold"]').value;
            const nonce = form.querySelector('input[name="_wpnonce"]').value;
            
            // Show loading indicator
            const loadingEl = document.getElementById('mxchat-action-loading');
            if (loadingEl) loadingEl.style.display = 'flex';
            
            // Send AJAX request
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'mxchat_update_intent_threshold',
                    intent_id: intentId,
                    intent_threshold: threshold,
                    _wpnonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Visual feedback of success
                    const card = this.closest('.mxchat-action-card');
                    card.style.background = 'rgba(120, 115, 245, 0.1)';
                    setTimeout(() => {
                        card.style.background = 'white';
                    }, 300);
                } else {
                    alert('Failed to update threshold: ' + (data.data?.message || 'Unknown error'));
                }
            })
            .catch(error => {
                //console.error('Error:', error);
                alert('Server error. Please try again.');
            })
            .finally(() => {
                if (loadingEl) loadingEl.style.display = 'none';
            });
        };
    });
});

jQuery(document).ready(function($) {
    // Ensure we have a debounce function (use lodash if available, otherwise use our implementation)
    const useDebounce = (window._ && window._.debounce) ? window._.debounce : debounce;

    // --- AJAX Auto-Save ---
    let $autosaveSections = $('.mxchat-autosave-section');
    
    // *** ADD THIS: Extend auto-save sections to include Pinecone settings ***
    const $pineconeAutosaveSection = $('#mxchat-kb-tab-pinecone');
    if ($pineconeAutosaveSection.length) {
        $autosaveSections = $autosaveSections.add($pineconeAutosaveSection);
        //console.log('Added Pinecone section to auto-save monitoring');
    }
    
    // Track whether fields have been modified by user
    const userModifiedFields = new Set();

    if ($autosaveSections.length) {
        // Track user interactions with input fields to determine if changes are user-initiated
        $autosaveSections.find('input, textarea, select').on('focus keydown paste', function() {
            const fieldName = $(this).attr('name');
            if (fieldName) {
                userModifiedFields.add(fieldName);
            }
        });
    
        // Handle real-time range slider value updates
        $autosaveSections.find('input[type="range"]').on('input', function() {
            const value = $(this).val();
            $('#threshold_value').text(value);
        });

        // Handle all input changes (including range slider)
         $autosaveSections.find('input, textarea, select').on('change', function() {
                    const $field = $(this);
                    const name = $field.attr('name');
                    
                    // Skip saving for API key fields that haven't been interacted with and are empty
                    const isApiKeyField = name && (
                        name === 'loops_api_key' || 
                        name === 'api_key' || 
                        name === 'xai_api_key' || 
                        name === 'claude_api_key' ||
                        name === 'voyage_api_key' ||
                        name === 'gemini_api_key' ||
                        name === 'deepseek_api_key' ||
                        name.indexOf('_api_key') !== -1
                    );
                    
                    // Skip processing if:
                    // 1. It's an API key field
                    // 2. The user hasn't interacted with it
                    // 3. The field is empty
                    if (isApiKeyField && !userModifiedFields.has(name) && (!$field.val() || $field.val().trim() === '')) {
                        //console.log('Skipping auto-save for untouched API key field:', name);
                        return;
                    }
                    
                    let value;
        
                    // Handle different input types
                    if ($field.attr('type') === 'checkbox') {
                        // *** UPDATED: Handle Pinecone checkboxes differently ***
                        if (name && name.indexOf('mxchat_pinecone_addon_options') !== -1) {
                            value = $field.is(':checked') ? '1' : '0';
                        } else {
                            value = $field.is(':checked') ? 'on' : 'off';
                        }
                    } else {
                        value = $field.val();
                    }
        
                    // Create feedback container
                    const feedbackContainer = $('<div class="feedback-container"></div>');
                    const spinner = $('<div class="saving-spinner"></div>');
                    const successIcon = $('<div class="success-icon">✔</div>');
        
                    // Position feedback container based on input type
                    if ($field.closest('.toggle-switch').length) {
                        $field.closest('td').append(feedbackContainer);
                    } else if ($field.closest('.mxchat-toggle-switch').length) {
                        $field.closest('.mxchat-toggle-container').append(feedbackContainer);
                    } else if ($field.closest('.slider-container').length) {
                        $field.closest('.slider-container').after(feedbackContainer);
                    } else {
                        $field.after(feedbackContainer);
                    }
                    feedbackContainer.append(spinner);
        
                    // Determine which AJAX action and nonce to use:
                    var ajaxAction, nonce;
                    // *** UPDATED: Add Pinecone fields to prompts action ***
                    if (name.indexOf('mxchat_prompts_options') !== -1 ||
                        name === 'mxchat_auto_sync_posts' || 
                        name === 'mxchat_auto_sync_pages' ||
                        name.indexOf('mxchat_auto_sync_') === 0 ||
                        name.indexOf('mxchat_pinecone_addon_options') !== -1) { // *** ADD THIS LINE ***
                        ajaxAction = 'mxchat_save_prompts_setting';
                        nonce = mxchatPromptsAdmin.prompts_setting_nonce;
                    } else {
                        // Otherwise, use the existing AJAX action.
                        ajaxAction = 'mxchat_save_setting';
                        nonce = mxchatAdmin.setting_nonce;
                    }
        
                    // *** ADD THIS: Debug logging for Pinecone fields ***
                    if (name && name.indexOf('mxchat_pinecone_addon_options') !== -1) {
                        //console.log('Saving Pinecone field:', name, '=', value);
                    }
        
                    // AJAX save request
                    $.ajax({
                        url: (ajaxAction === 'mxchat_save_prompts_setting') ? mxchatPromptsAdmin.ajax_url : mxchatAdmin.ajax_url,
                        type: 'POST',
                        data: {
                            action: ajaxAction,
                            name: name,
                            value: value,
                            _ajax_nonce: nonce
                        },
                        success: function(response) {
            if (response.success) {
                spinner.fadeOut(200, function() {
                    feedbackContainer.append(successIcon);
                    successIcon.fadeIn(200).delay(1000).fadeOut(200, function() {
                        feedbackContainer.remove();
                    });
                });
                
                // *** ADD THIS: Update Pinecone checkbox state after successful save ***
                if (name && name.indexOf('mxchat_pinecone_addon_options[mxchat_use_pinecone]') !== -1) {
                    //console.log('Pinecone toggle saved successfully, value:', value);
                    
                    // The checkbox state is already updated by the user interaction
                    // But let's make sure the UI state matches the saved value
                    var $checkbox = $('input[name="mxchat_pinecone_addon_options[mxchat_use_pinecone]"]');
                    var settingsDiv = $('.mxchat-pinecone-settings');
                    
                    // Double-check the UI state matches what was saved
                    if (value === '1' && !$checkbox.is(':checked')) {
                        $checkbox.prop('checked', true);
                        settingsDiv.slideDown(300);
                    } else if (value === '0' && $checkbox.is(':checked')) {
                        $checkbox.prop('checked', false);
                        settingsDiv.slideUp(300);
                    }
                    
                    //console.log('Pinecone UI state synchronized');
                    
                    // Check if Knowledge Import tab is currently active
                    if ($('.mxchat-kb-tab-button[data-tab="import"]').hasClass('active')) {
                        // Show a notice that we need to refresh
                        var $knowledgeCard = $('#mxchat-kb-tab-import .mxchat-card').eq(1);
                        if ($knowledgeCard.length > 0) {
                            // Add a refresh notice at the top of the knowledge base card
                            var refreshNotice = $('<div class="notice notice-warning" style="margin: 15px 0; padding: 10px 15px;">' +
                                '<p style="margin: 0;">' +
                                '<span class="dashicons dashicons-info" style="color: #f0ad4e; margin-right: 5px;"></span>' +
                                'Database settings have changed. ' +
                                '<a href="#" onclick="location.reload(); return false;" style="font-weight: bold;">Click here to refresh</a> to see the updated knowledge base.' +
                                '</p></div>');
                            
                            $knowledgeCard.prepend(refreshNotice);
                        }
                    } else {
                        // If not on import tab, set a flag to refresh when they go there
                        sessionStorage.setItem('mxchat_pinecone_changed', 'true');
                    }
                }
                
                // *** ADD THIS: Debug logging for successful saves ***
                if (name && name.indexOf('mxchat_pinecone_addon_options') !== -1) {
                    //console.log('Pinecone field saved successfully:', name, '=', value);
                }
                
                // Check if the response contains a "no changes" message and log it
                if (response.data && response.data.message === 'No changes detected') {
                    //console.log('No changes detected for field:', name);
                }
            } else {
                // Only show alert for actual errors, not for "no changes"
                let errorMessage = response.data?.message || 'Unknown error';
                
                // Don't display an alert for "no changes" message
                if (errorMessage !== 'No changes detected' && errorMessage !== 'Update failed or no changes') {
                    alert('Error saving: ' + errorMessage);
                } else {
                    // Still provide visual feedback that no changes were needed
                    spinner.fadeOut(200, function() {
                        feedbackContainer.append(successIcon);
                        successIcon.fadeIn(200).delay(1000).fadeOut(200, function() {
                            feedbackContainer.remove();
                        });
                    });
                    //console.log('No changes detected for field:', name);
                    return;
                }
                
                // Only revert checkbox state if it was an actual error
                if (errorMessage !== 'No changes detected' && errorMessage !== 'Update failed or no changes') {
                    if ($field.attr('type') === 'checkbox') {
                        $field.prop('checked', !$field.is(':checked'));
                    }
                }
                
                // Always clean up the feedback container
                feedbackContainer.remove();
            }
        },
                        error: function(xhr, textStatus, error) {
                            //console.error('AJAX Error:', textStatus, error);
                            alert('An error occurred while saving. Please try again.');
                            
                            // Revert checkbox state on error
                            if ($field.attr('type') === 'checkbox') {
                                $field.prop('checked', !$field.is(':checked'));
                            }
                            
                            feedbackContainer.remove();
                        }
                    });
                });

        // Initialize color pickers with debouncing
        $autosaveSections.find('.my-color-field').each(function() {
            const $colorField = $(this);
            
            $(this).wpColorPicker({
                change: useDebounce(function(event, ui) {
                    // Safety check - ensure we have a valid field and value
                    if (!$colorField || !$colorField.val()) {
                        //console.warn('Color picker not ready');
                        return;
                    }

                    const name = $colorField.attr('name');
                    const value = $colorField.val();

                    if (!name || !value) {
                        //console.warn('Missing required color picker values');
                        return;
                    }

                    // Create feedback container
                    const feedbackContainer = $('<div class="feedback-container"></div>');
                    const spinner = $('<div class="saving-spinner"></div>');
                    const successIcon = $('<div class="success-icon">✔</div>');

                    // Position feedback container
                    $colorField.closest('.wp-picker-container').after(feedbackContainer);
                    feedbackContainer.append(spinner);

                    // Determine which AJAX action and nonce to use:
                    var ajaxAction, nonce;
                    // Use the new AJAX action for submenu fields:
                    if (name.indexOf('mxchat_prompts_options') !== -1 ||
                        name === 'mxchat_auto_sync_posts' || 
                        name === 'mxchat_auto_sync_pages' ||
                        name.indexOf('mxchat_auto_sync_') === 0) { // Modified to catch all auto-sync fields
                        ajaxAction = 'mxchat_save_prompts_setting';
                        nonce = mxchatPromptsAdmin.prompts_setting_nonce;
                    } else {
                        // Otherwise, use the existing AJAX action.
                        ajaxAction = 'mxchat_save_setting';
                        nonce = mxchatAdmin.setting_nonce;
                    }
                    // AJAX save request
                    $.ajax({
                        url: (ajaxAction === 'mxchat_save_prompts_setting') ? mxchatPromptsAdmin.ajax_url : mxchatAdmin.ajax_url,
                        type: 'POST',
                        data: {
                            action: ajaxAction,
                            name: name,
                            value: value,
                            _ajax_nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                spinner.fadeOut(200, function() {
                                    feedbackContainer.append(successIcon);
                                    successIcon.fadeIn(200).delay(1000).fadeOut(200, function() {
                                        feedbackContainer.remove();
                                    });
                                });
                            } else {
                                alert('Error saving: ' + (response.data?.message || 'Unknown error'));
                                feedbackContainer.remove();
                            }
                        },
                        error: function() {
                            alert('An error occurred while saving.');
                            feedbackContainer.remove();
                        }
                    });
                }, 500)
            });
        });

        // Reinitialize color pickers when switching tabs
        $('.mxchat-tab-button').on('click.mxchat', function() {
            setTimeout(function() {
                $('.my-color-field:visible').wpColorPicker('close');
            }, 100);
        });
    }

// Initialize tabs system
function initTabs() {
    // Remove any existing handlers first
    $('.mxchat-tab-button').off('click.mxchat');
    
    // Add new click handlers
    $('.mxchat-tab-button').on('click.mxchat', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $this = $(this);
        
        // Get tab ID from data-tab attribute
        var tabId = $this.data('tab') || 'chatbot';
        
        // Safety check for empty tabId
        if (!tabId) {
            //console.warn('No tab identifier found');
            return;
        }
        
        // Update tab buttons
        $('.mxchat-tab-button').removeClass('active');
        $this.addClass('active');
        
        // Update content areas - with safety check
        $('.mxchat-tab-content').removeClass('active');
        var $targetTab = $('#' + tabId);
        if ($targetTab.length) {
            $targetTab.addClass('active');
            // Removed localStorage saving functionality
        } else {
            //console.warn('Tab content #' + tabId + ' not found');
        }
    });
}

// Initialize tabs and handle events
initTabs();
$(document).on('widget-added widget-updated postbox-toggled', initTabs);

// Always activate the first tab (Chatbot)
$('.mxchat-tab-button').first().trigger('click.mxchat');
    
    // Attach edit modal event handler
    $(document).on('click', '.mxchat-edit-button', function() {
        const intentId = $(this).data('intent-id');
        const phrases = $(this).data('phrases');
        mxchatOpenEditModal(intentId, phrases);
    });
    
// Toggle visibility handlers
function toggleVisibility(selector) {
    $(selector).on('click', function() {
        var inputField = $(this).prev('input');
        if (inputField.attr('type') === 'password') {
            inputField.attr('type', 'text');
            $(this).text('Hide');
        } else {
            inputField.attr('type', 'password');
            $(this).text('Show');
        }
    });
}

// Initialize all toggle visibility buttons
[
    '#toggleApiKeyVisibility',
    '#toggleWooCommerceSecretVisibility',
    '#toggleVoyageAPIKeyVisibility',
    '#toggleLoopsApiKeyVisibility',
    '#toggleXaiApiKeyVisibility',
    '#toggleClaudeApiKeyVisibility',
    '#toggleBraveApiKeyVisibility',
    '#toggleWebhookUrlVisibility',
    '#toggleSecretKeyVisibility',
    '#toggleBotTokenVisibility',
    '#toggleDeepSeekApiKeyVisibility',
    '#toggleGeminiApiKeyVisibility' // Added Gemini toggle
].forEach(toggleVisibility);

// Handle API key visibility based on model selection
function setupAPIKeyVisibility() {
    // Cache the selectors
    const $chatModelSelect = $('#model');
    const $embeddingModelSelect = $('#embedding_model');
    
    // First, locate and mark the API key rows
    setupAPIKeyRows();
    
    // Initial setup based on current selections
    updateApiKeyVisibility();
    
    // Listen for changes to the model selectors
    $chatModelSelect.on('change', updateApiKeyVisibility);
    $embeddingModelSelect.on('change', updateApiKeyVisibility);
    
    /**
     * Locate and mark rows that contain API key fields
     */
    function setupAPIKeyRows() {
        // Find key rows by their field IDs
        const providerMap = {
            'api_key': 'openai',
            'xai_api_key': 'xai',
            'claude_api_key': 'claude',
            'deepseek_api_key': 'deepseek',
            'voyage_api_key': 'voyage',
            'gemini_api_key': 'gemini' // Added Gemini API key mapping
        };
        
        $.each(providerMap, function(fieldId, provider) {
            const $field = $('#' + fieldId);
            if ($field.length) {
                const $row = $field.closest('tr');
                $row.addClass('mxchat-setting-row');
                $row.attr('data-provider', provider);
            }
        });
    }
    
/**
 * Updates the visibility of API key fields based on current model selections
 */
function updateApiKeyVisibility() {
    const chatModel = $chatModelSelect.val();
    const embeddingModel = $embeddingModelSelect.val();
    
    // Determine which providers are needed
    const isOpenAIChat = chatModel && chatModel.startsWith('gpt-');
    const isXAI = chatModel && chatModel.startsWith('grok-');
    const isClaude = chatModel && chatModel.startsWith('claude-');
    const isDeepSeek = chatModel && chatModel.startsWith('deepseek-');
    const isGemini = chatModel && chatModel.startsWith('gemini-'); // Added Gemini detection
    
    const isOpenAIEmbedding = embeddingModel && embeddingModel.startsWith('text-embedding-');
    const isVoyage = embeddingModel && embeddingModel.startsWith('voyage-');
    const isGeminiEmbedding = embeddingModel && embeddingModel.startsWith('gemini-embedding-');
    
    // Update API key visibility for each provider
    updateWrapperVisibility('openai', isOpenAIChat || isOpenAIEmbedding);
    updateWrapperVisibility('xai', isXAI);
    updateWrapperVisibility('claude', isClaude);
    updateWrapperVisibility('deepseek', isDeepSeek);
    updateWrapperVisibility('voyage', isVoyage);
    updateWrapperVisibility('gemini', isGemini || isGeminiEmbedding); // Updated Gemini visibility for both chat and embedding
    
    // Update provider-specific notices for OpenAI
    if (isOpenAIChat && isOpenAIEmbedding) {
        $('div[data-provider="openai"] .api-key-notice').text(
            'Required for your selected chat model and embedding model. Important: You must add credits before use.'
        );
    } else if (isOpenAIChat) {
        $('div[data-provider="openai"] .api-key-notice').text(
            'Required for your selected chat model. Important: You must add credits before use.'
        );
    } else if (isOpenAIEmbedding) {
        $('div[data-provider="openai"] .api-key-notice').text(
            'Required for your selected embedding model. Important: You must add credits before use.'
        );
    }
    
    // Update provider-specific notices for Gemini
    if (isGemini && isGeminiEmbedding) {
        $('div[data-provider="gemini"] .api-key-notice').text(
            'Required for your selected chat model and embedding model.'
        );
    } else if (isGemini) {
        $('div[data-provider="gemini"] .api-key-notice').text(
            'Required for your selected chat model.'
        );
    } else if (isGeminiEmbedding) {
        $('div[data-provider="gemini"] .api-key-notice').text(
            'Required for your selected embedding model.'
        );
    }
}
    
    /**
     * Updates visibility of a specific provider's API key wrapper
     */
    function updateWrapperVisibility(provider, isVisible) {
        const $row = $('tr.mxchat-setting-row[data-provider="' + provider + '"]');
        
        if (!$row.length) {
            //console.warn('API key row not found for provider: ' + provider);
            return;
        }
        
        if (isVisible) {
            $row.show();
            if (!$row.hasClass('highlighted')) {
                $row.addClass('highlighted');
                setTimeout(() => {
                    $row.removeClass('highlighted');
                }, 1500);
            }
        } else {
            $row.hide();
        }
    }
}

// Add this to your JavaScript file
function setupMxChatModelSelector() {
    const $modelSelect = $('#model');
    const $modelSelectorButton = $('<button>', {
        type: 'button',
        id: 'mxchat_model_selector_btn',
        class: 'button-primary mxchat-model-selector-btn',
        text: 'Select AI Model'
    });
    
    // Replace the select dropdown with a button
    $modelSelect.hide().after($modelSelectorButton);
    
    // Update button text to show currently selected model
    function updateButtonText() {
        const selectedModel = $modelSelect.val();
        const selectedModelText = $modelSelect.find('option:selected').text();
        $modelSelectorButton.text(selectedModelText);
    }
    
    // Initialize button text
    updateButtonText();
    
    // Create and append modal HTML
    const modelSelectorModal = `
        <div id="mxchat_model_selector_modal" class="mxchat-model-selector-modal">
            <div class="mxchat-model-selector-modal-content">
                <div class="mxchat-model-selector-modal-header">
                    <h3>Select AI Model</h3>
                    <span class="mxchat-model-selector-modal-close">&times;</span>
                </div>
                <div class="mxchat-model-selector-modal-body">
                    <div class="mxchat-model-selector-search-container">
                        <input type="text" id="mxchat_model_search_input" class="mxchat-model-search-input" placeholder="Search models...">
                    </div>
                    <div class="mxchat-model-selector-categories">
                        <button class="mxchat-model-category-btn active" data-category="all">All</button>
                        <button class="mxchat-model-category-btn" data-category="gemini">Google Gemini</button>
                        <button class="mxchat-model-category-btn" data-category="openai">OpenAI</button>
                        <button class="mxchat-model-category-btn" data-category="claude">Claude</button>
                        <button class="mxchat-model-category-btn" data-category="xai">X.AI</button>
                        <button class="mxchat-model-category-btn" data-category="deepseek">DeepSeek</button>
                    </div>
                    <div class="mxchat-model-selector-grid" id="mxchat_models_grid"></div>
                </div>
                <div class="mxchat-model-selector-modal-footer">
                    <button id="mxchat_cancel_model_selection" class="button mxchat-model-cancel-btn">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modelSelectorModal);
    
    // Populate models grid
    function populateModelsGrid(filter = '', category = 'all') {
        const $grid = $('#mxchat_models_grid');
        $grid.empty();
        
        const models = {
            gemini: [
                { value: 'gemini-2.0-flash', label: 'Gemini 2.0 Flash', description: 'Next-Gen features, speed & multimodal generation' },
                { value: 'gemini-2.0-flash-lite', label: 'Gemini 2.0 Flash-Lite', description: 'Cost-efficient with low latency' },
                { value: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro', description: 'Complex reasoning tasks requiring more intelligence' },
                { value: 'gemini-1.5-flash', label: 'Gemini 1.5 Flash', description: 'Fast and versatile performance' },
            ],
            openai: [
                { value: 'gpt-4.1-2025-04-14', label: 'GPT-4.1', description: 'Flagship model for complex tasks' },
                { value: 'gpt-4o', label: 'GPT-4o', description: 'Recommended for most use cases' },
                { value: 'gpt-4o-mini', label: 'GPT-4o Mini', description: 'Fast and lightweight' },
                { value: 'gpt-4-turbo', label: 'GPT-4 Turbo', description: 'High-performance model' },
                { value: 'gpt-4', label: 'GPT-4', description: 'High intelligence model' },
                { value: 'gpt-3.5-turbo', label: 'GPT-3.5 Turbo', description: 'Affordable and fast' },
            ],
            claude: [
                { value: 'claude-opus-4-20250514', label: 'Claude 4 Opus', description: 'Most capable Claude model' },
                { value: 'claude-sonnet-4-20250514', label: 'Claude 4 Sonnet', description: 'High performance' },
                { value: 'claude-3-7-sonnet-20250219', label: 'Claude 3.7 Sonnet', description: 'High intelligence' },
                { value: 'claude-3-5-sonnet-20241022', label: 'Claude 3.5 Sonnet', description: 'Intelligent and balanced' },
                { value: 'claude-3-opus-20240229', label: 'Claude 3 Opus', description: 'Highly complex tasks' },
                { value: 'claude-3-sonnet-20240229', label: 'Claude 3 Sonnet', description: 'Balanced performance' },
                { value: 'claude-3-haiku-20240307', label: 'Claude 3 Haiku', description: 'Fastest Claude model' },
            ],
            xai: [
                { value: 'grok-3-beta', label: 'Grok-3', description: 'Powerful model with 131K context' },
                { value: 'grok-3-fast-beta', label: 'Grok-3 Fast', description: 'High performance with faster responses' },
                { value: 'grok-3-mini-beta', label: 'Grok-3 Mini', description: 'Affordable model with good performance' },
                { value: 'grok-3-mini-fast-beta', label: 'Grok-3 Mini Fast', description: 'Quick and cost-effective' },
                { value: 'grok-2', label: 'Grok 2', description: 'Latest X.AI model' },
            ],
            deepseek: [
                { value: 'deepseek-chat', label: 'DeepSeek-V3', description: 'Advanced AI assistant' },
            ],
        };
        
        let allModels = [];
        Object.keys(models).forEach(key => {
            if (category === 'all' || category === key) {
                allModels = allModels.concat(models[key]);
            }
        });
        
        // Filter by search term if present
        if (filter) {
            const lowerFilter = filter.toLowerCase();
            allModels = allModels.filter(model => 
                model.label.toLowerCase().includes(lowerFilter) || 
                model.description.toLowerCase().includes(lowerFilter)
            );
        }
        
        // Create model cards
        allModels.forEach(model => {
            const isSelected = $modelSelect.val() === model.value;
            const $modelCard = $(`
                <div class="mxchat-model-selector-card ${isSelected ? 'mxchat-model-selected' : ''}" data-value="${model.value}">
                    <div class="mxchat-model-selector-icon">${getModelIcon(model.value)}</div>
                    <div class="mxchat-model-selector-info">
                        <h4 class="mxchat-model-selector-title">${model.label}</h4>
                        <p class="mxchat-model-selector-description">${model.description}</p>
                    </div>
                    ${isSelected ? '<div class="mxchat-model-selector-checkmark">✓</div>' : ''}
                </div>
            `);
            $grid.append($modelCard);
        });
    }
    
// Helper function to get icon for each model
function getModelIcon(modelValue) {
    if (modelValue.startsWith('gemini-')) return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 48 48" class="mxchat-model-icon-gemini"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"></path></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"></use></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"></path><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"></path><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.9 1L48 0v48H0z"></path><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"></path></svg>';
    if (modelValue.startsWith('gpt-')) return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320" class="mxchat-model-icon-openai"><path fill="currentColor" d="M297 131a80.6 80.6 0 0 0-93.7-104.2 80.6 80.6 0 0 0-137 29A80.6 80.6 0 0 0 23 189a80.6 80.6 0 0 0 93.7 104.2 80.6 80.6 0 0 0 137-29A80.7 80.7 0 0 0 297.1 131zM176.9 299c-14 .1-27.6-4.8-38.4-13.8l1.9-1 63.7-36.9c3.3-1.8 5.3-5.3 5.2-9v-89.9l27 15.6c.3.1.4.4.5.7v74.4a60 60 0 0 1-60 60zM47.9 244a59.7 59.7 0 0 1-7.1-40.1l1.9 1.1 63.7 36.8c3.2 1.9 7.2 1.9 10.5 0l77.8-45V228c0 .3-.2.6-.4.8L129.9 266a60 60 0 0 1-82-22zM31.2 105c7-12.2 18-21.5 31.2-26.3v75.8c0 3.7 2 7.2 5.2 9l77.8 45-27 15.5a1 1 0 0 1-.9 0L53.1 187a60 60 0 0 1-22-82zm221.2 51.5-77.8-45 27-15.5a1 1 0 0 1 .9 0l64.4 37.1a60 60 0 0 1-9.3 108.2v-75.8c0-3.7-2-7.2-5.2-9zm26.8-40.4-1.9-1.1-63.7-36.8a10.4 10.4 0 0 0-10.5 0L125.4 123V92c0-.3 0-.6.3-.8L190.1 54a60 60 0 0 1 89.1 62.1zm-168.5 55.4-27-15.5a1 1 0 0 1-.4-.7V80.9a60 60 0 0 1 98.3-46.1l-1.9 1L116 72.8a10.3 10.3 0 0 0-5.2 9v89.8zm14.6-31.5 34.7-20 34.6 20v40L160 200l-34.7-20z"></path></svg>';
    if (modelValue.startsWith('claude-')) return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 176" fill="none" class="mxchat-model-icon-claude"><path fill="currentColor" d="m147.487 0l70.081 175.78H256L185.919 0zM66.183 106.221l23.98-61.774l23.98 61.774zM70.07 0L0 175.78h39.18l14.33-36.914h73.308l14.328 36.914h39.179L110.255 0z"></path></svg>';
    if (modelValue.startsWith('grok-')) return '<svg fill="currentColor" fill-rule="evenodd" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="mxchat-model-icon-xai"><path d="M6.469 8.776L16.512 23h-4.464L2.005 8.776H6.47zm-.004 7.9l2.233 3.164L6.467 23H2l4.465-6.324zM22 2.582V23h-3.659V7.764L22 2.582zM22 1l-9.952 14.095-2.233-3.163L17.533 1H22z"></path></svg>';
    if (modelValue.startsWith('deepseek-')) return '<svg height="1em" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg" class="mxchat-model-icon-deepseek"><path d="M23.748 4.482c-.254-.124-.364.113-.512.234-.051.039-.094.09-.137.136-.372.397-.806.657-1.373.626-.829-.046-1.537.214-2.163.848-.133-.782-.575-1.248-1.247-1.548-.352-.156-.708-.311-.955-.65-.172-.241-.219-.51-.305-.774-.055-.16-.11-.323-.293-.35-.2-.031-.278.136-.356.276-.313.572-.434 1.202-.422 1.84.027 1.436.633 2.58 1.838 3.393.137.093.172.187.129.323-.082.28-.18.552-.266.833-.055.179-.137.217-.329.14a5.526 5.526 0 01-1.736-1.18c-.857-.828-1.631-1.742-2.597-2.458a11.365 11.365 0 00-.689-.471c-.985-.957.13-1.743.388-1.836.27-.098.093-.432-.779-.428-.872.004-1.67.295-2.687.684a3.055 3.055 0 01-.465.137 9.597 9.597 0 00-2.883-.102c-1.885.21-3.39 1.102-4.497 2.623C.082 8.606-.231 10.684.152 12.85c.403 2.284 1.569 4.175 3.36 5.653 1.858 1.533 3.997 2.284 6.438 2.14 1.482-.085 3.133-.284 4.994-1.86.47.234.962.327 1.78.397.63.059 1.236-.03 1.705-.128.735-.156.684-.837.419-.961-2.155-1.004-1.682-.595-2.113-.926 1.096-1.296 2.746-2.642 3.392-7.003.05-.347.007-.565 0-.845-.004-.17.035-.237.23-.256a4.173 4.173 0 001.545-.475c1.396-.763 1.96-2.015 2.093-3.517.02-.23-.004-.467-.247-.588zM11.581 18c-2.089-1.642-3.102-2.183-3.52-2.16-.392.024-.321.471-.235.763.09.288.207.486.371.739.114.167.192.416-.113.603-.673.416-1.842-.14-1.897-.167-1.361-.802-2.5-1.86-3.301-3.307-.774-1.393-1.224-2.887-1.298-4.482-.02-.386.093-.522.477-.592a4.696 4.696 0 011.529-.039c2.132.312 3.946 1.265 5.468 2.774.868.86 1.525 1.887 2.202 2.891.72 1.066 1.494 2.082 2.48 2.914.348.292.625.514.891.677-.802.09-2.14.11-3.054-.614zm1-6.44a.306.306 0 01.415-.287.302.302 0 01.2.288.306.306 0 01-.31.307.303.303 0 01-.304-.308zm3.11 1.596c-.2.081-.399.151-.59.16a1.245 1.245 0 01-.798-.254c-.274-.23-.47-.358-.552-.758a1.73 1.73 0 01.016-.588c.07-.327-.008-.537-.239-.727-.187-.156-.426-.199-.688-.199a.559.559 0 01-.254-.078c-.11-.054-.2-.19-.114-.358.028-.054.16-.186.192-.21.356-.202.767-.136 1.146.016.352.144.618.408 1.001.782.391.451.462.576.685.914.176.265.336.537.445.848.067.195-.019.354-.25.452z" fill="currentColor"></path></svg>';
    return '<span class="dashicons dashicons-admin-generic mxchat-model-icon-generic"></span>';
}
    
    // Event handlers
    $modelSelectorButton.on('click', function() {
        $('#mxchat_model_selector_modal').show();
        populateModelsGrid('', 'all');
    });
    
    $('.mxchat-model-selector-modal-close, #mxchat_cancel_model_selection').on('click', function() {
        $('#mxchat_model_selector_modal').hide();
    });
    
    $('.mxchat-model-category-btn').on('click', function() {
        $('.mxchat-model-category-btn').removeClass('active');
        $(this).addClass('active');
        const category = $(this).data('category');
        const searchTerm = $('#mxchat_model_search_input').val();
        populateModelsGrid(searchTerm, category);
    });
    
    $('#mxchat_model_search_input').on('input', function() {
        const searchTerm = $(this).val();
        const activeCategory = $('.mxchat-model-category-btn.active').data('category');
        populateModelsGrid(searchTerm, activeCategory);
    });
    
    $(document).on('click', '.mxchat-model-selector-card', function() {
        const modelValue = $(this).data('value');
        $modelSelect.val(modelValue).trigger('change');
        updateButtonText();
        $('#mxchat_model_selector_modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#mxchat_model_selector_modal')) {
            $('#mxchat_model_selector_modal').hide();
        }
    });
}

// Embedding model selector - completely separate from chat model selector
function setupMxChatEmbeddingModelSelector() {
    const $embeddingModelSelect = $('#embedding_model');
    
    // Skip if the element doesn't exist on the page
    if ($embeddingModelSelect.length === 0) {
        return;
    }
    
    const $embeddingModelSelectorButton = $('<button>', {
        type: 'button',
        id: 'mxchat_embedding_model_selector_btn',
        class: 'button-primary mxchat-embedding-model-selector-btn', // Changed class name to be more specific
        text: 'Select Embedding Model'
    });
    
    // Replace the select dropdown with a button
    $embeddingModelSelect.hide().after($embeddingModelSelectorButton);
    
    // Update button text to show currently selected model
    function updateButtonText() {
        const selectedModel = $embeddingModelSelect.val();
        const selectedModelText = $embeddingModelSelect.find('option:selected').text();
        $embeddingModelSelectorButton.text(selectedModelText);
    }
    
    // Initialize button text
    updateButtonText();
    
    // Create a unique ID for the modal to avoid conflicts
    const embeddingModalId = 'mxchat_embedding_model_selector_modal';
    
    // Create and append modal HTML with unique IDs
    const embeddingModelSelectorModal = `
        <div id="${embeddingModalId}" class="mxchat-embedding-model-selector-modal">
            <div class="mxchat-embedding-model-selector-modal-content">
                <div class="mxchat-embedding-model-selector-modal-header">
                    <h3>Select Embedding Model</h3>
                    <span class="mxchat-embedding-model-selector-modal-close">&times;</span>
                </div>
                <div class="mxchat-embedding-model-selector-modal-body">
                    <div class="mxchat-embedding-model-selector-search-container">
                        <input type="text" id="mxchat_embedding_model_search_input" class="mxchat-embedding-model-search-input" placeholder="Search models...">
                    </div>
                    <div class="mxchat-embedding-model-selector-categories">
                        <button class="mxchat-embedding-model-category-btn active" data-category="all">All</button>
                        <button class="mxchat-embedding-model-category-btn" data-category="openai">OpenAI</button>
                        <button class="mxchat-embedding-model-category-btn" data-category="voyage">Voyage AI</button>
                        <button class="mxchat-embedding-model-category-btn" data-category="gemini">Google Gemini</button>
                    </div>
                    <div class="mxchat-embedding-model-selector-grid" id="mxchat_embedding_models_grid"></div>
                </div>
                <div class="mxchat-embedding-model-selector-modal-footer">
                    <button id="mxchat_cancel_embedding_model_selection" class="button mxchat-embedding-model-cancel-btn">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    // Use jQuery's append to ensure it doesn't clash with existing modals
    $('body').append(embeddingModelSelectorModal);
    
    // Populate models grid
    function populateEmbeddingModelsGrid(filter = '', category = 'all') {
        const $grid = $('#mxchat_embedding_models_grid');
        $grid.empty();
        
        // Define embedding models with descriptions and context lengths
        const models = {
            openai: [
                { 
                    value: 'text-embedding-3-small', 
                    label: 'TE3 Small', 
                    description: 'Fast and cost-effective embeddings (1536 dimensions, 8K context)'
                },
                { 
                    value: 'text-embedding-ada-002', 
                    label: 'Ada 2', 
                    description: 'Balanced performance embeddings (1536 dimensions, 8K context)'
                },
                { 
                    value: 'text-embedding-3-large', 
                    label: 'TE3 Large', 
                    description: 'High-performance embeddings (3072 dimensions, 8K context)'
                }
            ],
            voyage: [
                { 
                    value: 'voyage-3-large', 
                    label: 'Voyage-3 Large', 
                    description: 'Advanced semantic search embeddings (2048 dimensions, 32K context)'
                }
            ],
            gemini: [
                { 
                    value: 'gemini-embedding-exp-03-07', 
                    label: 'Gemini Embedding', 
                    description: 'Experimental SOTA embeddings (1536 dimensions, 8K context)'
                }
            ]
        };
        
        let allModels = [];
        Object.keys(models).forEach(key => {
            if (category === 'all' || category === key) {
                allModels = allModels.concat(models[key]);
            }
        });
        
        // Filter by search term if present
        if (filter) {
            const lowerFilter = filter.toLowerCase();
            allModels = allModels.filter(model => 
                model.label.toLowerCase().includes(lowerFilter) || 
                model.description.toLowerCase().includes(lowerFilter)
            );
        }
        
        // Create model cards
        allModels.forEach(model => {
            const isSelected = $embeddingModelSelect.val() === model.value;
            let providerClass = 'mxchat-embedding-model-provider-openai';
            
            if (model.value.startsWith('voyage-')) {
                providerClass = 'mxchat-embedding-model-provider-voyage';
            } else if (model.value.startsWith('gemini-embedding-')) {
                providerClass = 'mxchat-embedding-model-provider-gemini';
            }
            
            let iconHTML = '';
            if (model.value.startsWith('voyage-')) {
                iconHTML = '<span class="dashicons dashicons-chart-line mxchat-embedding-model-icon-voyage"></span>';
            } else if (model.value.startsWith('gemini-embedding-')) {
                iconHTML = '<span class="dashicons dashicons-google mxchat-embedding-model-icon-gemini"></span>';
            } else {
                iconHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320" class="mxchat-embedding-model-icon-openai"><path fill="currentColor" d="M297 131a80.6 80.6 0 0 0-93.7-104.2 80.6 80.6 0 0 0-137 29A80.6 80.6 0 0 0 23 189a80.6 80.6 0 0 0 93.7 104.2 80.6 80.6 0 0 0 137-29A80.7 80.7 0 0 0 297.1 131zM176.9 299c-14 .1-27.6-4.8-38.4-13.8l1.9-1 63.7-36.9c3.3-1.8 5.3-5.3 5.2-9v-89.9l27 15.6c.3.1.4.4.5.7v74.4a60 60 0 0 1-60 60zM47.9 244a59.7 59.7 0 0 1-7.1-40.1l1.9 1.1 63.7 36.8c3.2 1.9 7.2 1.9 10.5 0l77.8-45V228c0 .3-.2.6-.4.8L129.9 266a60 60 0 0 1-82-22zM31.2 105c7-12.2 18-21.5 31.2-26.3v75.8c0 3.7 2 7.2 5.2 9l77.8 45-27 15.5a1 1 0 0 1-.9 0L53.1 187a60 60 0 0 1-22-82zm221.2 51.5-77.8-45 27-15.5a1 1 0 0 1 .9 0l64.4 37.1a60 60 0 0 1-9.3 108.2v-75.8c0-3.7-2-7.2-5.2-9zm26.8-40.4-1.9-1.1-63.7-36.8a10.4 10.4 0 0 0-10.5 0L125.4 123V92c0-.3 0-.6.3-.8L190.1 54a60 60 0 0 1 89.1 62.1zm-168.5 55.4-27-15.5a1 1 0 0 1-.4-.7V80.9a60 60 0 0 1 98.3-46.1l-1.9 1L116 72.8a10.3 10.3 0 0 0-5.2 9v89.8zm14.6-31.5 34.7-20 34.6 20v40L160 200l-34.7-20z"></path></svg>';
            }
            
            const $modelCard = $(`
                <div class="mxchat-embedding-model-selector-card ${isSelected ? 'mxchat-embedding-model-selected' : ''} ${providerClass}" data-value="${model.value}">
                    <div class="mxchat-embedding-model-selector-icon">
                        ${iconHTML}
                    </div>
                    <div class="mxchat-embedding-model-selector-info">
                        <h4 class="mxchat-embedding-model-selector-title">${model.label}</h4>
                        <p class="mxchat-embedding-model-selector-description">${model.description}</p>
                    </div>
                    ${isSelected ? '<div class="mxchat-embedding-model-selector-checkmark">✓</div>' : ''}
                </div>
            `);
            
            $grid.append($modelCard);
        });
    }
    
    // Event handlers - use namespaced events to avoid conflicts
    $embeddingModelSelectorButton.on('click.embeddingModelSelector', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        $('#' + embeddingModalId).show();
        populateEmbeddingModelsGrid('', 'all');
    });
    
    $('.mxchat-embedding-model-selector-modal-close, #mxchat_cancel_embedding_model_selection').on('click.embeddingModelSelector', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        $('#' + embeddingModalId).hide();
    });
    
    $('.mxchat-embedding-model-selector-categories .mxchat-embedding-model-category-btn').on('click.embeddingModelSelector', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        $('.mxchat-embedding-model-selector-categories .mxchat-embedding-model-category-btn').removeClass('active');
        $(this).addClass('active');
        const category = $(this).data('category');
        const searchTerm = $('#mxchat_embedding_model_search_input').val();
        populateEmbeddingModelsGrid(searchTerm, category);
    });
    
    $('#mxchat_embedding_model_search_input').on('input.embeddingModelSelector', function() {
        const searchTerm = $(this).val();
        const activeCategory = $('.mxchat-embedding-model-selector-categories .mxchat-embedding-model-category-btn.active').data('category');
        populateEmbeddingModelsGrid(searchTerm, activeCategory);
    });
    
    // Use a direct selector to avoid conflicts with other card elements
    $(document).on('click.embeddingModelSelector', '.mxchat-embedding-model-selector-grid .mxchat-embedding-model-selector-card', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        const modelValue = $(this).data('value');
        
        // Important: Only update this specific select element
        $embeddingModelSelect.val(modelValue);
        
        // Manually trigger change only on this element
        const changeEvent = new Event('change', { bubbles: true });
        $embeddingModelSelect[0].dispatchEvent(changeEvent);
        
        // Update button text
        updateButtonText();
        
        // Hide modal
        $('#' + embeddingModalId).hide();
    });
    
    // Close modal when clicking outside - use namespaced events
    $(window).on('click.embeddingModelSelector', function(event) {
        if ($(event.target).is('#' + embeddingModalId)) {
            $('#' + embeddingModalId).hide();
        }
    });
}

// Call this function after the DOM is fully loaded
$(document).ready(function() {
    setupMxChatModelSelector();
    setupMxChatEmbeddingModelSelector();
});
    
    // Initialize API key visibility
    setupAPIKeyVisibility();
    
    // Add Intent Form Submission
    $('#mxchat-add-intent-form').on('submit', function(event) {
        $('#mxchat-intent-loading').show();
        $('#mxchat-intent-loading-text').show();
        $(this).find('button[type="submit"]').hide();
    });
    
    // Inline Edit Functionality
    $('.edit-button').on('click', function() {
        var row = $(this).closest('tr');
        row.find('.content-view, .url-view').hide();
        row.find('.content-edit, .url-edit').show();
        row.find('.edit-button').hide();
        row.find('.save-button').show();
    });
    
    // Save button handler
// Save button handler
$('.save-button').on('click', function() {
    var button = $(this);
    var row = button.closest('tr');
    var id = button.data('id');
    var nonce = button.data('nonce'); // Get nonce from button data attribute
    var newContent = row.find('.content-edit').val();
    var newUrl = row.find('.url-edit').val();

    //console.log('Nonce from button:', nonce); // Debug

    button.prop('disabled', true);
    button.text('Saving...');

    $.ajax({
        url: mxchatAdmin.ajax_url,
        type: 'POST',
        data: {
            action: 'mxchat_save_inline_prompt',
            id: id,
            article_content: newContent,
            article_url: newUrl,
            _ajax_nonce: nonce  // Use nonce from button
        },
        success: function(response) {
            button.prop('disabled', false);
            button.text('Save');
            
            if (response.success) {
                row.find('.content-view').html(newContent.replace(/\n/g, "<br>"));
                if (newUrl) {
                    row.find('.url-view').html('<a href="' + newUrl + '" target="_blank">' + newUrl + '</a>');
                } else {
                    row.find('.url-view').html('N/A');
                }
                
                row.find('.content-edit, .url-edit').hide();
                row.find('.content-view, .url-view').show();
                row.find('.save-button').hide();
                row.find('.edit-button').show();
            } else {
                alert('Error saving content: ' + (response.data?.message || 'Unknown error'));
            }
        },
        error: function() {
            button.prop('disabled', false);
            button.text('Save');
            alert('An error occurred while saving.');
        }
    });
});
    
    
    // Questions handling
    $('.mxchat-add-question').on('click', function () {
        const container = $('#mxchat-additional-questions-container');
        const questionCount = container.find('.mxchat-question-row').length + 4;
        const questionIndex = container.find('.mxchat-question-row').length;
    
        const newQuestion = `
            <div class="mxchat-question-row">
                <input type="text"
                       name="additional_popular_questions[]"
                       placeholder="Enter Additional Popular Question ${questionCount}"
                       class="regular-text mxchat-question-input"
                       data-question-index="${questionIndex}" />
                <button type="button" class="button mxchat-remove-question"
                        aria-label="Remove question">Remove</button>
            </div>
        `;
        container.append(newQuestion);
    });
    
    $(document).on('click', '.mxchat-remove-question', function () {
        $(this).closest('.mxchat-question-row').remove();
        saveQuestions();
    });
    
    $(document).on('change', '.mxchat-question-input', function() {
        saveQuestions();
    });
    
    function saveQuestions() {
        const questions = [];
        $('.mxchat-question-input').each(function() {
            const value = $(this).val().trim();
            if (value) {
                questions.push(value);
            }
        });
    
        const feedbackContainer = $('<div class="feedback-container"></div>');
        const spinner = $('<div class="saving-spinner"></div>');
        const successIcon = $('<div class="success-icon">✔</div>');
    
        // Append feedback after the add button
        $('.mxchat-add-question').after(feedbackContainer);
        feedbackContainer.append(spinner);
    
        // Save via AJAX
        $.ajax({
            url: mxchatAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'mxchat_save_setting',
                name: 'additional_popular_questions',
                value: JSON.stringify(questions),
                _ajax_nonce: mxchatAdmin.setting_nonce
            },
            success: function(response) {
                if (response.success) {
                    spinner.fadeOut(200, function() {
                        feedbackContainer.append(successIcon);
                        successIcon.fadeIn(200).delay(1000).fadeOut(200, function() {
                            feedbackContainer.remove();
                        });
                    });
                } else {
                    alert('Error saving questions: ' + (response.data?.message || 'Unknown error'));
                    feedbackContainer.remove();
                }
            },
            error: function() {
                alert('An error occurred while saving questions.');
                feedbackContainer.remove();
            }
        });
    }
    
    // Live agent status handler
    const statusToggle = document.getElementById('live_agent_status');
    const statusText = statusToggle?.parentElement.nextElementSibling?.querySelector('.status-text');
    if (statusToggle && statusText) {
        statusToggle.addEventListener('change', function() {
            // Update display text
            statusText.textContent = this.checked ? 'Online' : 'Offline';
            
            // Send the correct on/off value to the server
            if (window.mxchatSaveSetting) {
                window.mxchatSaveSetting('live_agent_status', this.checked ? 'on' : 'off');
            }
        });
    }
    
    // Function to adjust the textarea height to content
    function adjustTextareaHeight() {
        this.style.height = 'auto'; // Reset to auto to calculate scrollHeight
        this.style.height = this.scrollHeight + 'px'; // Expand to content height
    }

    // Function to reset the textarea height to initial
    function resetTextareaHeight() {
        this.style.height = ''; // Remove inline height, reverting to CSS default
    }

    // Target the specific textarea by ID
    var $textarea = $('#system_prompt_instructions');

    // Bind events
    $textarea.on('focus input', adjustTextareaHeight) // Expand on focus and input
             .on('blur', resetTextareaHeight); // Reset on blur
});



document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the correct page before initializing
    const modal = document.getElementById('mxchat-action-modal');
    
    // Only initialize if the modal exists on this page
    if (modal) {
        //console.log('MXChat Action Modal JS Loaded');
        
        // Initialize the action modal functionality
        initStepBasedActionModal();
    }
    
    // Function to initialize the step-based action modal
    function initStepBasedActionModal() {
        // We already checked for modal existence above, so no need to check again
        
        const actionStep1 = document.getElementById('mxchat-action-step-1');
        const actionStep2 = document.getElementById('mxchat-action-step-2');
        const backToStep1Btn = document.getElementById('mxchat-back-to-step-1');
        const searchInput = document.getElementById('action-type-search');
        const categoryButtons = modal.querySelectorAll('.mxchat-category-button');
        const actionCards = modal.querySelectorAll('.mxchat-action-type-card');
        const actionForm = document.getElementById('mxchat-action-form');
        const callbackInput = document.getElementById('callback_function');
        const actionIdField = document.getElementById('edit_action_id');
        const labelField = document.getElementById('intent_label');
        const phrasesField = document.getElementById('action_phrases');
        const formActionType = document.getElementById('form_action_type');
        const nonceContainer = document.getElementById('action-nonce-container');
        const thresholdSlider = document.getElementById('similarity_threshold');
        const thresholdDisplay = document.querySelector('.mxchat-threshold-value-display');
        
        // Rest of your initialization code remains the same...
        
        // Log the structure of one action card for debugging
        if (actionCards.length > 0) {
            //console.log('First action card data attributes:', actionCards[0].dataset);
            //console.log('First action card HTML:', actionCards[0].outerHTML);
        }
        
        // Add click event listeners to category buttons
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                //console.log('Category button clicked:', this.dataset.category);
                
                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get selected category
                const category = this.dataset.category;
                
                // Filter action cards
                filterActionCards(category, searchInput.value);
            });
        });
        
        // Add search functionality
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // Get active category
                const activeCategory = modal.querySelector('.mxchat-category-button.active')?.dataset.category || 'all';
                //console.log('Search input changed, active category:', activeCategory);
                
                // Filter action cards
                filterActionCards(activeCategory, this.value);
            });
        }
        
        // Add click event listeners to action cards
        actionCards.forEach(card => {
            card.addEventListener('click', function() {
                // Get the action data
                const isPro = this.dataset.pro === 'true';
                const isInstalled = this.dataset.installed === 'true';
                const addonName = this.dataset.addon || '';
                const actionValue = this.dataset.value;
                const actionLabel = this.dataset.label;
                const actionIcon = this.querySelector('.dashicons').getAttribute('class').replace('dashicons dashicons-', '');
                const actionDescription = this.querySelector('p').textContent;
                
                // Pro check using the proper detection method
                const proIsActivated = typeof mxchatAdmin !== 'undefined' && 
                                     (mxchatAdmin.is_activated === '1' || 
                                      mxchatAdmin.is_activated === 'true' || 
                                      mxchatAdmin.is_activated === true);
                
                // Handle different states
                if (isPro && !proIsActivated) {
                    // Pro feature but no Pro license
                    showProFeatureNotice();
                    return;
                }
                
                if (addonName && !isInstalled) {
                    // Add-on required but not installed
                    const addonDisplayName = this.querySelector('.mxchat-addon-info')?.textContent?.replace('Requires ', '') || addonName + ' Add-on';
                    showAddonRequiredNotice(addonDisplayName);
                    return;
                }
                
                // If we get here, the action is available - proceed as normal
                callbackInput.value = actionValue;
                
                // Update the selected action display in step 2
                document.getElementById('selected-action-title').textContent = actionLabel;
                document.getElementById('selected-action-description').textContent = actionDescription;
                document.getElementById('selected-action-icon').innerHTML = 
                    `<span class="dashicons dashicons-${actionIcon}"></span>`;
                
                // Set a default label based on the action type (user can change it)
                if (!labelField.value) {
                    labelField.value = actionLabel;
                }
                
                // Move to step 2
                actionStep1.classList.remove('active');
                actionStep2.classList.add('active');
                
                // Update modal title
            });
        });
        
        // Back button functionality
        if (backToStep1Btn) {
            backToStep1Btn.addEventListener('click', function() {
                //console.log('Back button clicked');
                actionStep2.classList.remove('active');
                actionStep1.classList.add('active');
            });
        }
        
        // Function to filter action cards by category and search term
        function filterActionCards(category, searchTerm) {
            searchTerm = searchTerm.toLowerCase().trim();
            //console.log(`Filtering cards by category: "${category}", search: "${searchTerm}"`);
            
            let visibleCount = 0;
            
            // Show all cards initially with animation
            actionCards.forEach((card, index) => {
                // Reset animation
                card.style.animation = 'none';
                // Trigger reflow
                void card.offsetWidth;
                
                // Determine if card should be visible based on category and search term
                const cardCategory = card.dataset.category || '';
                const matchesCategory = category === 'all' || cardCategory === category;
                
                const cardTitle = card.querySelector('h4')?.textContent?.toLowerCase() || '';
                const cardDesc = card.querySelector('p')?.textContent?.toLowerCase() || '';
                const matchesSearch = searchTerm === '' || 
                                    cardTitle.includes(searchTerm) || 
                                    cardDesc.includes(searchTerm);
                
                // Show/hide card with animation
                if (matchesCategory && matchesSearch) {
                    card.style.display = 'flex';
                    // Staggered animation for cards
                    card.style.animation = `fadeIn 0.2s ease forwards ${index * 0.03}s`;
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            //console.log(`Filter results: ${visibleCount} cards visible out of ${actionCards.length}`);
        }
        
        // Function to show notice for Pro features
        function showProFeatureNotice() {
            //console.log('Showing Pro feature notice');
            // Check if we already have a notification container
            let noticeContainer = document.querySelector('.mxchat-pro-notice');
            
            if (!noticeContainer) {
                // Create the notice container
                noticeContainer = document.createElement('div');
                noticeContainer.className = 'mxchat-pro-notice';
                
                // Create content
                noticeContainer.innerHTML = `
                    <div class="mxchat-pro-notice-content">
                        <h3>MxChat Pro Feature</h3>
                        <p>This action is available in the Pro version only.</p>
                        <div class="mxchat-pro-notice-buttons">
                            <button class="mxchat-button-secondary mxchat-pro-notice-close">Close</button>
                            <a href="https://mxchat.ai/" class="mxchat-button-primary">Upgrade to Pro</a>
                        </div>
                    </div>
                `;
                
                // Append to body
                document.body.appendChild(noticeContainer);
                
                // Add close functionality
                const closeButton = noticeContainer.querySelector('.mxchat-pro-notice-close');
                closeButton.addEventListener('click', function() {
                    noticeContainer.classList.remove('active');
                    setTimeout(() => {
                        noticeContainer.remove();
                    }, 300);
                });
                
                // Click outside to close
                noticeContainer.addEventListener('click', function(e) {
                    if (e.target === noticeContainer) {
                        closeButton.click();
                    }
                });
                
                // Show with animation
                setTimeout(() => {
                    noticeContainer.classList.add('active');
                }, 10);
            } else {
                // If it already exists, just make it visible again
                noticeContainer.classList.add('active');
            }
        }

        // Function to show notice for add-on requirements
        function showAddonRequiredNotice(addonName) {
            //console.log(`Showing add-on notice for: ${addonName}`);
            // Check if we already have a notification container
            let noticeContainer = document.querySelector('.mxchat-addon-notice');
            
            if (!noticeContainer) {
                // Create the notice container
                noticeContainer = document.createElement('div');
                noticeContainer.className = 'mxchat-addon-notice';
                
                // Create content
                noticeContainer.innerHTML = `
                    <div class="mxchat-addon-notice-content">
                        <span class="mxchat-addon-notice-icon">🧩</span>
                        <h3>Add-on Required</h3>
                        <p>This action requires the <strong>${addonName}</strong> add-on to be installed.</p>
                        <div class="mxchat-addon-notice-buttons">
                            <button class="mxchat-button-secondary mxchat-addon-notice-close">Close</button>
                            <a href="admin.php?page=mxchat-addons" class="mxchat-button-primary">Get Add-ons</a>
                        </div>
                    </div>
                `;
                
                // Append to body
                document.body.appendChild(noticeContainer);
                
                // Add close functionality
                const closeButton = noticeContainer.querySelector('.mxchat-addon-notice-close');
                closeButton.addEventListener('click', function() {
                    noticeContainer.classList.remove('active');
                    setTimeout(() => {
                        noticeContainer.remove();
                    }, 300);
                });
                
                // Click outside to close
                noticeContainer.addEventListener('click', function(e) {
                    if (e.target === noticeContainer) {
                        closeButton.click();
                    }
                });
                
                // Show with animation
                setTimeout(() => {
                    noticeContainer.classList.add('active');
                }, 10);
            } else {
                // If it already exists, update the content
                const addonNameElement = noticeContainer.querySelector('p strong');
                if (addonNameElement) {
                    addonNameElement.textContent = addonName;
                }
                
                // Make it visible again
                noticeContainer.classList.add('active');
            }
        }
        
        // Form submission handling
        if (actionForm) {
            actionForm.addEventListener('submit', function() {
                //console.log('Form submitted');
                document.getElementById('mxchat-action-loading').style.display = 'flex';
                this.querySelector('button[type="submit"]').disabled = true;
            });
        }
    }
    
    // Setup add action buttons (only if we're on the correct page)
    if (modal) {
        // Update the modal open function to support the step-based flow
        window.mxchatOpenActionModal = function(isEdit = false, actionId = '', label = '', phrases = '', threshold = 85, callbackFunction = '') {
            //console.log('Modal opening, edit mode:', isEdit);
            
            // No need to check again, we already verified modal exists
            
            // Get form fields
            const actionIdField = document.getElementById('edit_action_id');
            const labelField = document.getElementById('intent_label');
            const phrasesField = document.getElementById('action_phrases');
            const formActionType = document.getElementById('form_action_type');
            const callbackInput = document.getElementById('callback_function'); 
            const saveButton = document.getElementById('mxchat-save-action-btn');
            const nonceContainer = document.getElementById('action-nonce-container');
            const thresholdSlider = document.getElementById('similarity_threshold');
            const thresholdDisplay = document.querySelector('.mxchat-threshold-value-display');
            const actionStep1 = document.getElementById('mxchat-action-step-1');
            const actionStep2 = document.getElementById('mxchat-action-step-2');
            const searchInput = document.getElementById('action-type-search');
            
            // Set up modal for edit or create
            if (isEdit) {
                saveButton.textContent = 'Update Action';
                formActionType.value = 'mxchat_edit_intent';
                actionIdField.value = actionId;
                labelField.value = label;
                phrasesField.value = phrases;
                callbackInput.value = callbackFunction;
                thresholdSlider.value = threshold; // Set the current threshold value
                thresholdDisplay.textContent = threshold + '%'; // Update display
                
                // Update the nonce field for editing
                nonceContainer.innerHTML = '';  // Clear existing nonce
                if (typeof mxchatAdmin !== 'undefined' && mxchatAdmin.edit_intent_nonce) {
                    nonceContainer.innerHTML = `<input type="hidden" name="_wpnonce" value="${mxchatAdmin.edit_intent_nonce}">`;
                }
                
                // For editing, go directly to step 2 and update the selected action display
                actionStep1.classList.remove('active');
                actionStep2.classList.add('active');
                
                // Find the matching action card to get its details
                const actionCards = document.querySelectorAll('.mxchat-action-type-card');
                let foundCard = null;
                
                actionCards.forEach(card => {
                    if (card.dataset.value === callbackFunction) {
                        foundCard = card;
                    }
                });
                
                if (foundCard) {
                    //console.log('Found matching action card for:', callbackFunction);
                    const actionLabel = foundCard.dataset.label || foundCard.querySelector('h4')?.textContent || '';
                    const actionIconElement = foundCard.querySelector('.dashicons');
                    const actionIcon = actionIconElement 
                        ? actionIconElement.getAttribute('class').replace('dashicons dashicons-', '') 
                        : 'admin-generic';
                    const actionDescription = foundCard.querySelector('p')?.textContent || '';
                    
                    document.getElementById('selected-action-title').textContent = actionLabel;
                    document.getElementById('selected-action-description').textContent = actionDescription;
                    document.getElementById('selected-action-icon').innerHTML = 
                        `<span class="dashicons dashicons-${actionIcon}"></span>`;
                } else {
                    //console.log('No matching action card found for:', callbackFunction);
                    // Fallback if we can't find the card
                    document.getElementById('selected-action-title').textContent = label;
                    document.getElementById('selected-action-description').textContent = 'Configure this action for your chatbot';
                    document.getElementById('selected-action-icon').innerHTML = 
                        `<span class="dashicons dashicons-admin-generic"></span>`;
                }
            } else {
                //console.log('Setting up create mode');
                saveButton.textContent = 'Save Action';
                formActionType.value = 'mxchat_add_intent';
                actionIdField.value = '';
                labelField.value = '';
                phrasesField.value = '';
                callbackInput.value = '';
                thresholdSlider.value = 85; // Default value for new actions
                thresholdDisplay.textContent = '85%'; // Default display
                
                // Update the nonce field for adding
                nonceContainer.innerHTML = '';  // Clear existing nonce
                if (typeof mxchatAdmin !== 'undefined' && mxchatAdmin.add_intent_nonce) {
                    nonceContainer.innerHTML = `<input type="hidden" name="_wpnonce" value="${mxchatAdmin.add_intent_nonce}">`;
                }
                
                // For creating new, start at step 1
                actionStep1.classList.add('active');
                actionStep2.classList.remove('active');
            }
            
            // Show modal with animation
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                modal.classList.add('active');
            });
            
            // Set up close handlers
            const closeModal = () => {
                //console.log('Closing modal');
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300); // Match the CSS transition time
            };
            
            // Close button handler
            const closeBtn = modal.querySelector('.mxchat-modal-close');
            if (closeBtn) {
                closeBtn.onclick = closeModal;
            }
            
            // Cancel button handler
            const cancelBtns = modal.querySelectorAll('.mxchat-modal-cancel');
            if (cancelBtns) {
                cancelBtns.forEach(btn => {
                    btn.onclick = closeModal;
                });
            }
            
            // Click outside modal to close
            modal.onclick = (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            };
            
            // Escape key to close modal
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            }, { once: true });
            
            // Focus appropriate field based on current step
            if (isEdit || actionStep2.classList.contains('active')) {
                if (labelField) labelField.focus();
            } else {
                if (searchInput) searchInput.focus();
            }
            
            return closeModal; // Return close function for external use
        };
        
        // Setup add action buttons
        const addActionBtn = document.getElementById('mxchat-add-action-btn');
        if (addActionBtn) {
            //console.log('Add action button found');
            addActionBtn.onclick = () => window.mxchatOpenActionModal();
        }
        
        const createFirstAction = document.getElementById('mxchat-create-first-action');
        if (createFirstAction) {
            //console.log('Create first action button found');
            createFirstAction.onclick = () => window.mxchatOpenActionModal();
        }
        
        // Setup edit buttons
        const editButtons = document.querySelectorAll('.mxchat-action-card .mxchat-edit-button');
        //console.log('Edit buttons found:', editButtons.length);
        editButtons.forEach(button => {
            button.onclick = () => {
                const actionId = button.dataset.actionId;
                const phrases = button.dataset.phrases;
                const label = button.dataset.label;
                const threshold = button.dataset.threshold || 85;
                const callbackFunction = button.dataset.callbackFunction; 

                window.mxchatOpenActionModal(true, actionId, label, phrases, threshold, callbackFunction);
            };
        });
    }
});

jQuery(document).ready(function($) {
    // Toggle custom post types container
    $('#mxchat-custom-post-types-toggle').on('click', function(e) {
        e.preventDefault();
        
        $('#mxchat-custom-post-types-container').slideToggle(300);
        
        // Rotate the toggle icon
        const $icon = $(this).find('.mxchat-accordion-icon');
        if ($('#mxchat-custom-post-types-container').is(':visible')) {
            $icon.css('transform', 'rotate(180deg)');
            $(this).closest('.mxchat-settings-accordion').addClass('active');
        } else {
            $icon.css('transform', 'rotate(0deg)');
            $(this).closest('.mxchat-settings-accordion').removeClass('active');
        }
    });
    
    // If there are any selections made, auto-expand the container
    function autoExpandIfNeeded() {
        // Check if any checkbox in the container is checked
        const hasCheckedItems = $('#mxchat-custom-post-types-container input[type="checkbox"]:checked').length > 0;
        
        if (hasCheckedItems) {
            $('#mxchat-custom-post-types-container').show();
            $('#mxchat-custom-post-types-toggle .mxchat-accordion-icon').css('transform', 'rotate(180deg)');
            $('.mxchat-settings-accordion').addClass('active');
        }
    }
    
    // Run on page load
    autoExpandIfNeeded();
});

document.addEventListener('DOMContentLoaded', function() {
    var viewSampleBtn = document.getElementById('mxchatViewSampleBtn');
    var modal = document.getElementById('mxchatSampleModal');
    var modalClose = document.getElementById('mxchatModalClose');
    var closeBtn = document.getElementById('mxchatCloseBtn');
    var copyBtn = document.getElementById('mxchatCopyBtn');
    var instructionsContent = document.querySelector('.mxchat-instructions-content');
    var modalContent = document.querySelector('.mxchat-instructions-modal-content');

    if (!viewSampleBtn || !modal) {
        return;
    }

    // Open modal
    viewSampleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        modal.classList.add('mxchat-instructions-show');
    });

    // Close modal function
    function closeModal(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        modal.classList.remove('mxchat-instructions-show');
    }

    // Close modal events
    if (modalClose) {
        modalClose.addEventListener('click', function(e) {
            closeModal(e);
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            closeModal(e);
        });
    }

    // Close on backdrop click ONLY (not on hover)
    modal.addEventListener('click', function(e) {
        // Only close if clicking directly on the overlay, not on child elements
        if (e.target === modal) {
            closeModal(e);
        }
    });

    // Prevent modal content clicks from closing the modal
    if (modalContent) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('mxchat-instructions-show')) {
            closeModal();
        }
    });

    // Copy functionality
    if (copyBtn && instructionsContent) {
        copyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var text = instructionsContent.textContent;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess();
                }).catch(function() {
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        });
    }

    function fallbackCopy(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            console.error('Copy failed');
        }
        document.body.removeChild(textArea);
    }

    function showCopySuccess() {
        var originalText = copyBtn.innerHTML;
        copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>Copied!';
        
        setTimeout(function() {
            copyBtn.innerHTML = originalText;
        }, 2000);
    }
});