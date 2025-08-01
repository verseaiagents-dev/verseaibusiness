jQuery(document).ready(function($) {
    // Current page state
    let currentPage = 1;
    const perPage = 50; // Display 50 sessions per page
    let totalPages = 1;

    // Select/Deselect All functionality
    const selectButton = $('#mxchat-select-all-transcripts');
    let isSelected = false;

    selectButton.click(function() {
        isSelected = !isSelected;
        $(this).toggleClass('selected');
        
        // Update button text
        const buttonText = $(this).find('.button-text');
        buttonText.text(isSelected ? 'Deselect All' : 'Select All');
        
        // Update checkboxes (only for current page)
        $('#mxchat-transcripts').find('input[type=checkbox]').prop('checked', isSelected);
    });

    // Search functionality
    $('#mxchat-search-transcripts').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm.length > 0) {
            // Reset to first page when searching
            currentPage = 1;
            
            // Load with search filter
            loadTranscripts(currentPage, searchTerm);
        } else {
            // Reset to first page with no search term
            currentPage = 1;
            loadTranscripts(currentPage, '');
        }
    });

    // Initial load of transcripts
    loadTranscripts(currentPage, '');

    // Function to load transcripts with pagination
    function loadTranscripts(page, searchTerm = '') {
        $('#mxchat-transcripts').html('<div class="mxchat-loading">Loading transcripts...</div>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mxchat_fetch_chat_history',
                page: page,
                per_page: perPage,
                search: searchTerm
            },
            success: function(response) {
                $('#mxchat-transcripts').html(response.html);
                currentPage = response.page;
                totalPages = response.total_pages;
                
                // Reset selection state when page changes
                isSelected = false;
                selectButton.removeClass('selected');
                selectButton.find('.button-text').text('Select All');
                
                // Add click handlers to pagination buttons
                $('.mxchat-pagination-button').on('click', function() {
                    var pageNum = $(this).data('page');
                    loadTranscripts(pageNum, searchTerm);
                    
                    // Scroll to top of transcripts
                    $('html, body').animate({
                        scrollTop: $('#mxchat-transcripts').offset().top - 50
                    }, 300);
                });
                
                // Re-attach event handlers for newly loaded content if needed
                // This is important because the content is dynamically loaded
                attachDynamicEventHandlers();
            },
            error: function(xhr, status, error) {
                $('#mxchat-transcripts').html('<div class="mxchat-error">Error loading chat transcripts. Please try again.</div>');
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    }
    
    // Attach event handlers to dynamically loaded content
    function attachDynamicEventHandlers() {
        // Add any event handlers for dynamically loaded content here if needed
    }

    // Delete form submission
    $('#mxchat-delete-form').submit(function(e) {
        e.preventDefault();
        
        // Get all checked session IDs
        var checkedSessionIds = $('input[name="delete_session_ids[]"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (checkedSessionIds.length === 0) {
            alert("Please select at least one chat session to delete.");
            return;
        }
        
        // Confirm deletion
        if (!confirm("Are you sure you want to delete the selected chat sessions? This action cannot be undone.")) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mxchat_delete_chat_history',
                delete_session_ids: checkedSessionIds,
                security: $('#mxchat_delete_chat_nonce').val()
            },
            success: function(response) {
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.success) {
                    alert("Success: " + jsonResponse.success);
                } else if (jsonResponse.error) {
                    alert("Error: " + jsonResponse.error);
                } else {
                    //console.log("Unexpected response format.");
                }
                
                // Reload the current page of transcripts
                loadTranscripts(currentPage);
            },
            error: function(xhr, status, error) {
                //console.error("AJAX Error: " + status + " - " + error);
                //console.log(xhr.responseText);
                alert("An error occurred while deleting chat sessions. Please try again.");
            }
        });
    });
    
    // Export functionality - this remains unchanged as it should export all transcripts
    $('#mxchat-export-transcripts').on('click', function() {
        var $button = $(this);
        $button.prop('disabled', true).addClass('loading');

        // Create a form and submit it
        var $form = $('<form>', {
            'method': 'post',
            'action': ajaxurl
        });

        $form.append($('<input>', {
            'type': 'hidden',
            'name': 'action',
            'value': 'mxchat_export_transcripts'
        }));

        $form.append($('<input>', {
            'type': 'hidden',
            'name': 'security',
            'value': mxchatAdmin.export_nonce
        }));

        $form.appendTo('body').submit();

        // Re-enable the button after a short delay
        setTimeout(function() {
            $button.prop('disabled', false).removeClass('loading');
        }, 2000);
    });
    
    // Chat Email Notification Modal functionality
    
    // Open modal
    $('#mxchat-chat-email-notification-btn').on('click', function(e) {
        e.preventDefault();
        $('#mxchat-chat-email-notification-modal').fadeIn(300);
    });
    
    // Close modal
    $('.mxchat-chat-notification-modal-close, .mxchat-chat-notification-modal-cancel').on('click', function() {
        $('#mxchat-chat-email-notification-modal').fadeOut(300);
    });
    
    // Close modal on outside click
    $('#mxchat-chat-email-notification-modal').on('click', function(e) {
        if ($(e.target).is('#mxchat-chat-email-notification-modal')) {
            $(this).fadeOut(300);
        }
    });
    
    // Handle form submission - Let WordPress handle it normally for settings
    $('#mxchat-chat-email-notification-form').on('submit', function(e) {
        // Don't prevent default - let the form submit normally to WordPress options.php
        var $submitButton = $(this).find('button[type="submit"]');
        var originalText = $submitButton.text();
        
        // Just show a loading state
        $submitButton.text('Saving...').prop('disabled', true);
        
        // The form will submit normally and reload the page
    });
});