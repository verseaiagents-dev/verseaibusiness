
jQuery(document).ready(function($) {
    // Track if a form has been submitted to trigger updates
    let formSubmitted = false;
    
    // Global interval ID to manage the polling
    let updateIntervalId = null;
    
    
     $(document).on('click', '.mxchat-dismiss-button', function() {
        const $button = $(this);
        const $card = $button.closest('.mxchat-status-card');
        
        // Determine card type from data attribute or content
        let cardType = $card.data('card-type');
        if (!cardType) {
            // Fallback: determine from content
            cardType = $card.find('h4').text().includes('PDF') ? 'pdf' : 'sitemap';
        }
        
        // Fade out and remove the card
        $card.fadeOut(300, function() {
            $(this).remove();
        });
        
        // Clear the completed status on the server
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mxchat_dismiss_completed_status',
                nonce: mxchatAdmin.status_nonce,
                card_type: cardType
            },
            success: function(response) {
                //console.log('MxChat: Completed status dismissed');
            },
            error: function(xhr, status, error) {
                console.error('MxChat: Error dismissing status:', error);
            }
        });
    });
    
    // Check if we're on the right admin page with status cards or import forms
    if ($('.mxchat-status-card').length > 0 || $('.mxchat-import-options').length > 0) {
        //console.log('MxChat: Status update script initialized');
        // Initialize AJAX status updates
        initStatusUpdates();
    }
    
    // Initialize status updates
// Initialize status updates
function initStatusUpdates() {
    // Get the refresh interval (default to 2 seconds for more responsive updates)
    const refreshInterval = parseInt(mxchatAdmin.status_refresh_interval || 2000);
    
    // Check if there are active status cards
    const hasActiveStatus = $('.mxchat-status-card').length > 0;
    
    // Set up form submission listeners
    $('#mxchat-url-form, #mxchat-content-form').on('submit', function() {
        //console.log('MxChat: Form submitted, will start checking for updates');
        formSubmitted = true;
        
        // Store submission info in sessionStorage to persist through redirects
        sessionStorage.setItem('mxchat_form_submitted', 'true');
        sessionStorage.setItem('mxchat_form_submitted_time', Date.now());
        
        // Start checking for status updates right away
        startPolling(refreshInterval);
        
        // Create a temporary message
        if ($('.mxchat-processing-message').length === 0) {
            const message = $('<div class="mxchat-processing-message" style="text-align: center; padding: 15px; background: #f0f7ff; border-radius: 8px; margin-top: 15px;">Processing request... Status will update automatically.</div>');
            $('.mxchat-import-section').after(message);
            
            // Fade out after 5 seconds
            setTimeout(function() {
                message.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    });
    
    // Listen for import option clicks
    $('.mxchat-import-box').on('click', function() {
        const option = $(this).data('option');
        //console.log('MxChat: Import option clicked - ' + option);
    });
    
    // Check if we recently submitted a form (within last 60 seconds for sitemap processing)
    if (sessionStorage.getItem('mxchat_form_submitted') === 'true') {
        const submittedTime = parseInt(sessionStorage.getItem('mxchat_form_submitted_time') || '0');
        if (Date.now() - submittedTime < 60000) { // 60 seconds
            //console.log('MxChat: Detected recent form submission via sessionStorage');
            formSubmitted = true;
        } else {
            // Clear old submission data
            sessionStorage.removeItem('mxchat_form_submitted');
            sessionStorage.removeItem('mxchat_form_submitted_time');
        }
    }
    
    // Attach event listener to stop button to clear the interval
    $('.mxchat-stop-form').on('submit', function() {
        //console.log('MxChat: Stop processing requested, clearing update interval');
        stopPolling();
        sessionStorage.removeItem('mxchat_form_submitted');
        sessionStorage.removeItem('mxchat_form_submitted_time');
    });
    
    // Start the interval for automatic updates if we have status cards or a form was submitted
    if (hasActiveStatus || formSubmitted) {
        //console.log('MxChat: Starting automatic status checks');
        startPolling(refreshInterval);
    }
}
    
    // Function to start polling
    function startPolling(interval) {
        // Clear any existing interval first
        stopPolling();
        
        // Do an initial fetch immediately
        fetchStatusUpdates();
        
        // Set up new interval
        updateIntervalId = setInterval(function() {
            fetchStatusUpdates();
        }, interval);
        
        //console.log('MxChat: Polling started with interval', interval);
    }
    
    // Function to stop polling
    function stopPolling() {
        if (updateIntervalId !== null) {
            clearInterval(updateIntervalId);
            updateIntervalId = null;
            //console.log('MxChat: Polling stopped');
        }
    }
    
// Fetch status updates from the server
function fetchStatusUpdates() {
    // If user is actively viewing the failed URLs or pages, don't refresh as frequently
    const $details = $('.mxchat-failed-urls-container details, .mxchat-failed-pages-container details');
    const isUserViewing = $details.length > 0 && $details.prop('open');
    
    // If details are open, we'll refresh at a slower rate
    if (isUserViewing) {
        // Alternative: Update less frequently when details are open
        setTimeout(function() {
            performStatusUpdate(false); // Pass false for normal updates
        }, 5000); // Slow down updates to every 5 seconds when details are open
    } else {
        performStatusUpdate(false); // Pass false for normal updates
    }
}
    
// Perform the actual AJAX request
function performStatusUpdate(clearCompleted = false) {
    //console.log('MxChat: Checking for status updates...');
    
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'mxchat_get_status_updates',
            nonce: mxchatAdmin.status_nonce,
            clear_completed: clearCompleted ? 'true' : 'false'
        },
        success: function(response) {
            //console.log('MxChat: Status update received', response);
            
            // Log specific status details for debugging
            if (response.sitemap_status) {
                //console.log('Sitemap status:', response.sitemap_status.status, 'Processed:', response.sitemap_status.processed_urls, 'Total:', response.sitemap_status.total_urls);
            }
            
            // Check for completion BEFORE updating UI
            let shouldStopPolling = false;
            
            if (response.sitemap_status && response.sitemap_status.status === 'complete') {
                //console.log('MxChat: Sitemap processing complete');
                shouldStopPolling = true;
            }
            
            if (response.pdf_status && response.pdf_status.status === 'complete') {
                //console.log('MxChat: PDF processing complete');
                shouldStopPolling = true;
            }
            
            // Always update UI
            if ((response && response.is_processing) || formSubmitted || shouldStopPolling) {
                updateStatusUI(response);
            }
            
            // Show single URL status if available and no active processing
            if (response.single_url_status && !response.is_processing) {
                updateSingleUrlStatus(response.single_url_status);
            }
            
            // Handle completion - REMOVE THE AUTOMATIC PAGE RELOAD
            if (shouldStopPolling) {
                // Clear session storage
                sessionStorage.removeItem('mxchat_form_submitted');
                sessionStorage.removeItem('mxchat_form_submitted_time');
                
                // Stop polling
                stopPolling();
                
                // DON'T clear the completed status automatically
                // DON'T reload the page automatically
                
                return; // Exit early
            }
            
            // Reset form submitted flag if no active processing
            if (!response.is_processing) {
                formSubmitted = false;
                sessionStorage.removeItem('mxchat_form_submitted');
                sessionStorage.removeItem('mxchat_form_submitted_time');
                stopPolling();
            }
        },
        error: function(xhr, status, error) {
            console.error('MxChat: Status update failed:', error);
        }
    });
}

function addDismissButtonToCompletedCards() {
    // Add dismiss buttons to completed cards that don't have them
    $('.mxchat-status-card').each(function() {
        const $card = $(this);
        const $badge = $card.find('.mxchat-status-badge');
        
        // Check if this is a completed card and doesn't already have a dismiss button
        if (($badge.hasClass('mxchat-status-success') || $badge.hasClass('mxchat-status-warning')) && 
            $card.find('.mxchat-dismiss-button').length === 0) {
            
            // Look for existing action buttons container, or create one
            let $actionContainer = $card.find('.mxchat-action-buttons');
            
            if ($actionContainer.length === 0) {
                // Create the action buttons container if it doesn't exist
                $actionContainer = $('<div class="mxchat-action-buttons"></div>');
                $card.find('.mxchat-status-header').append($actionContainer);
            }
            
            // Add dismiss button WITHOUT inline styles
            const dismissButton = $('<button type="button" class="mxchat-dismiss-button">Dismiss</button>');
            
            dismissButton.on('click', function() {
                // Fade out and remove the card
                $card.fadeOut(300, function() {
                    $(this).remove();
                });
                
                // Clear the completed status on the server
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mxchat_dismiss_completed_status',
                        nonce: mxchatAdmin.status_nonce,
                        card_type: $card.find('h4').text().includes('PDF') ? 'pdf' : 'sitemap'
                    },
                    success: function(response) {
                        //console.log('MxChat: Completed status dismissed');
                    }
                });
            });
            
            $actionContainer.append(dismissButton);
        }
    });
}

    // Update the UI with status information
function updateStatusUI(data) {
    // Update PDF status if available
    if (data.pdf_status) {
        updatePdfStatus(data.pdf_status);
    }
    
    // Update sitemap status if available
    if (data.sitemap_status) {
        updateSitemapStatus(data.sitemap_status);
    }
    
    // Handle single URL status if available and no active processing
    if (data.single_url_status && !data.is_processing) {
        updateSingleUrlStatus(data.single_url_status);
    } else if (data.is_processing) {
        // Hide single URL status while processing
        $('#mxchat-single-url-status-container').hide();
    }
    
    // Add dismiss buttons to any completed cards
    addDismissButtonToCompletedCards();
}
    
    // Update PDF status card
function updatePdfStatus(status) {
    // Check if PDF card exists
    let $pdfCard = $('.mxchat-status-card:contains("PDF Processing")');
    
    // If no card exists but we have status, create it
    if ($pdfCard.length === 0 && status) {
        //console.log('MxChat: Creating new PDF status card');
        createPdfStatusCard(status);
        $pdfCard = $('.mxchat-status-card:contains("PDF Processing")');
    }
    
    // If card exists, update it
    if ($pdfCard.length > 0) {
        // Update progress bar
        $pdfCard.find('.mxchat-progress-fill').css('width', status.percentage + '%');
        
        // Update progress text
        let progressText = 'Progress: ' + status.processed_pages + ' of ' + 
                          status.total_pages + ' pages (' + status.percentage + '%)';
        
        $pdfCard.find('.mxchat-status-details p:first').text(progressText);
        
        // Update failed pages count if exists
        const $failedText = $pdfCard.find('.mxchat-status-details p:contains("Failed pages")');
        if (status.failed_pages && status.failed_pages > 0) {
            if ($failedText.length === 0) {
                // Add failed pages text after progress
                $pdfCard.find('.mxchat-status-details p:first').after(
                    '<p><strong>Failed pages:</strong> ' + status.failed_pages + '</p>'
                );
            } else {
                $failedText.html('<strong>Failed pages:</strong> ' + status.failed_pages);
            }
        } else if ($failedText.length > 0) {
            $failedText.remove();
        }
        
        // Update status text
        const $statusText = $pdfCard.find('.mxchat-status-details p:contains("Status:")');
        if ($statusText.length > 0) {
            $statusText.text('Status: ' + status.status.charAt(0).toUpperCase() + status.status.slice(1));
        }
        
        // Update last update text
        const $lastUpdateText = $pdfCard.find('.mxchat-status-details p:contains("Last update:")');
        if ($lastUpdateText.length > 0) {
            $lastUpdateText.text('Last update: ' + status.last_update);
        }
        
        // Update status badges
        $pdfCard.find('.mxchat-status-badge').remove();
        if (status.status === 'error') {
            $pdfCard.find('.mxchat-status-header').append('<span class="mxchat-status-badge mxchat-status-failed">Error</span>');
        } else if (status.status === 'complete') {
            if (status.failed_pages && status.failed_pages > 0) {
                $pdfCard.find('.mxchat-status-header').append('<span class="mxchat-status-badge mxchat-status-warning">Completed with ' + status.failed_pages + ' failures</span>');
            } else {
                $pdfCard.find('.mxchat-status-header').append('<span class="mxchat-status-badge mxchat-status-success">Complete</span>');
            }
        }
        
        // Update or add completion summary
        if (status.completion_summary) {
            let $summaryContainer = $pdfCard.find('.mxchat-completion-summary');
            if ($summaryContainer.length === 0) {
                const summaryHtml = '<div class="mxchat-completion-summary">' +
                    '<h5>Processing Summary</h5>' +
                    '<p><strong>Total Pages:</strong> ' + status.completion_summary.total_pages + '</p>' +
                    '<p><strong>Successful:</strong> ' + status.completion_summary.successful_pages + '</p>' +
                    '<p><strong>Failed:</strong> ' + status.completion_summary.failed_pages + '</p>' +
                    '<p><strong>Completed:</strong> ' + status.completion_summary.completion_time + '</p>' +
                    '</div>';
                $pdfCard.find('.mxchat-status-details').append(summaryHtml);
            }
        }
        
        // Update failed pages list if exists
        if (status.failed_pages_list && status.failed_pages_list.length > 0) {
            updateFailedPagesList($pdfCard, status.failed_pages_list);
        }
        
        // If we have an error, show it
        if (status.status === 'error' && status.error) {
            let $errorNotice = $pdfCard.find('.mxchat-error-notice');
            
            if ($errorNotice.length === 0) {
                $errorNotice = $('<div class="mxchat-error-notice"><p class="error"></p></div>');
                $pdfCard.find('.mxchat-status-details').append($errorNotice);
            }
            
            $errorNotice.find('p.error').text(status.error);
        }
    }
}
    
    // Create a new PDF status card
function createPdfStatusCard(status) {
    let html = '<div class="mxchat-status-card">';
    html += '<div class="mxchat-status-header">';
    html += '<h4>PDF Processing Status</h4>';
    
    // Add stop processing form if processing
    if (status.status === 'processing') {
        html += '<form method="post" class="mxchat-stop-form" action="' + 
               mxchatAdmin.admin_url + 'admin-post.php?action=mxchat_stop_processing">';
        html += '<input type="hidden" name="mxchat_stop_processing_nonce" value="' + 
               mxchatAdmin.stop_nonce + '">';
        html += '<button type="submit" name="stop_processing" class="mxchat-button-secondary">';
        html += 'Stop Processing</button></form>';
    }
    
    // Add status badges
    if (status.status === 'error') {
        html += '<span class="mxchat-status-badge mxchat-status-failed">Error</span>';
    } else if (status.status === 'complete') {
        if (status.failed_pages && status.failed_pages > 0) {
            html += '<span class="mxchat-status-badge mxchat-status-warning">Completed with ' + status.failed_pages + ' failures</span>';
        } else {
            html += '<span class="mxchat-status-badge mxchat-status-success">Complete</span>';
        }
    }
    
    html += '</div>'; // End header
    
    // Progress bar
    html += '<div class="mxchat-progress-bar">';
    html += '<div class="mxchat-progress-fill" style="width: ' + status.percentage + '%"></div>';
    html += '</div>';
    
    // Status details
    html += '<div class="mxchat-status-details">';
    html += '<p>Progress: ' + status.processed_pages + ' of ' + 
            status.total_pages + ' pages (' + status.percentage + '%)</p>';
    
    // Show failed pages count if any
    if (status.failed_pages && status.failed_pages > 0) {
        html += '<p><strong>Failed pages:</strong> ' + status.failed_pages + '</p>';
    }
    
    html += '<p>Status: ' + status.status.charAt(0).toUpperCase() + status.status.slice(1) + '</p>';
    html += '<p>Last update: ' + status.last_update + '</p>';
    
    // Add completion summary if available
    if (status.completion_summary) {
        html += '<div class="mxchat-completion-summary">';
        html += '<h5>Processing Summary</h5>';
        html += '<p><strong>Total Pages:</strong> ' + status.completion_summary.total_pages + '</p>';
        html += '<p><strong>Successful:</strong> ' + status.completion_summary.successful_pages + '</p>';
        html += '<p><strong>Failed:</strong> ' + status.completion_summary.failed_pages + '</p>';
        html += '<p><strong>Completed:</strong> ' + status.completion_summary.completion_time + '</p>';
        html += '</div>';
    }
    
    // Add failed pages list if any
    if (status.failed_pages_list && status.failed_pages_list.length > 0) {
        html += createFailedPagesHtml(status.failed_pages_list);
    }
    
    // Add error message if any
    if (status.status === 'error' && status.error) {
        html += '<div class="mxchat-error-notice">';
        html += '<p class="error">' + status.error + '</p>';
        html += '</div>';
    }
    
    html += '</div>'; // End details
    html += '</div>'; // End card
    
    // Insert the card into the page
    let $importTabContent = $('#mxchat-kb-tab-import');
    if ($importTabContent.length > 0) {
        let $sitemapCard = $importTabContent.find('.mxchat-status-card:contains("Sitemap Processing")');
        if ($sitemapCard.length > 0) {
            $sitemapCard.before($(html));
        } else {
            $importTabContent.find('.mxchat-import-section').after($(html));
        }
    } else {
        let $sitemapCard = $('.mxchat-status-card:contains("Sitemap Processing")');
        if ($sitemapCard.length > 0) {
            $sitemapCard.before($(html));
        } else {
            $('.mxchat-import-section').after($(html));
        }
    }
}
    
    // Update sitemap status card
function updateSitemapStatus(status) {
    // Check if sitemap card exists
    let $sitemapCard = $('.mxchat-status-card:contains("Sitemap Processing")');
    
    // If no card exists but we have status, create it
    if ($sitemapCard.length === 0 && status) {
        //console.log('MxChat: Creating new sitemap status card');
        createSitemapStatusCard(status);
        $sitemapCard = $('.mxchat-status-card:contains("Sitemap Processing")');
    }
    
    // If card exists, update it
    if ($sitemapCard.length > 0) {
        // Update progress bar
        $sitemapCard.find('.mxchat-progress-fill').css('width', status.percentage + '%');
        
        // Update progress text
        let progressText = 'Progress: ' + status.processed_urls + ' of ' + 
                          status.total_urls + ' URLs (' + status.percentage + '%)';
        
        $sitemapCard.find('.mxchat-status-details p:first').text(progressText);
        
        // Update failed URLs count if exists
        const $failedText = $sitemapCard.find('.mxchat-status-details p:contains("Failed URLs")');
        if (status.failed_urls && status.failed_urls > 0) {
            if ($failedText.length === 0) {
                // Add failed URLs text after progress
                $sitemapCard.find('.mxchat-status-details p:first').after(
                    '<p><strong>Failed URLs:</strong> ' + status.failed_urls + '</p>'
                );
            } else {
                $failedText.html('<strong>Failed URLs:</strong> ' + status.failed_urls);
            }
        } else if ($failedText.length > 0) {
            $failedText.remove();
        }
        
        // Update status badges
        $sitemapCard.find('.mxchat-status-badge').remove();
        if (status.status === 'error') {
            $sitemapCard.find('.mxchat-status-header').append('<span class="mxchat-status-badge mxchat-status-failed">Error</span>');
        } else if (status.status === 'complete') {
            if (status.failed_urls && status.failed_urls > 0) {
                $sitemapCard.find('.mxchat-status-header').append('<span class="mxchat-status-badge mxchat-status-warning">Completed with ' + status.failed_urls + ' failures</span>');
            } else {
                $sitemapCard.find('.mxchat-status-header').append('<span class="mxchat-status-badge mxchat-status-success">Complete</span>');
            }
        }
        
        // Update or add completion summary
        if (status.completion_summary) {
            let $summaryContainer = $sitemapCard.find('.mxchat-completion-summary');
            if ($summaryContainer.length === 0) {
                const summaryHtml = '<div class="mxchat-completion-summary">' +
                    '<h5>Processing Summary</h5>' +
                    '<p><strong>Total URLs:</strong> ' + status.completion_summary.total_urls + '</p>' +
                    '<p><strong>Successful:</strong> ' + status.completion_summary.successful_urls + '</p>' +
                    '<p><strong>Failed:</strong> ' + status.completion_summary.failed_urls + '</p>' +
                    '<p><strong>Completed:</strong> ' + status.completion_summary.completion_time + '</p>' +
                    '</div>';
                $sitemapCard.find('.mxchat-status-details').append(summaryHtml);
            }
        }
        
        // Check if details is already open before updating
        const isDetailsOpen = $sitemapCard.find('.mxchat-failed-urls-container details').prop('open');
        
        // Update errors display
        let $errorContainer = $sitemapCard.find('.mxchat-error-notice');
        
        if ($errorContainer.length === 0 && 
            (status.error || status.last_error || (status.failed_urls_list && status.failed_urls_list.length > 0))) {
            // Create error container if it doesn't exist
            $errorContainer = $('<div class="mxchat-error-notice"></div>');
            $sitemapCard.find('.mxchat-status-details').append($errorContainer);
        }
        
        // Update or create error notices
        if ($errorContainer.length > 0) {
            let errorHTML = '';
            
            if (status.error) {
                errorHTML += '<p class="error">' + status.error + '</p>';
            }
            
            if (status.last_error) {
                errorHTML += '<p class="last-error">Last error: ' + status.last_error + '</p>';
            }
            
            // Add failed URLs list
            if (status.failed_urls_list && status.failed_urls_list.length > 0) {
                errorHTML += '<div class="mxchat-failed-urls-container">';
                errorHTML += '<h5>Failed URLs (' + status.failed_urls_list.length + ')</h5>';
                
                // Set the 'open' attribute based on previous state
                errorHTML += '<details' + (isDetailsOpen ? ' open' : '') + '>';
                errorHTML += '<summary>Show Failed URLs</summary>';
                errorHTML += '<div class="mxchat-failed-urls-list">';
                
                // Create table for failed URLs
                errorHTML += '<table class="widefat striped">';
                errorHTML += '<thead><tr><th>URL</th><th>Error</th><th>Retries</th><th>Time</th></tr></thead>';
                errorHTML += '<tbody>';
                
                // Sort failed URLs by most recent
                const sortedFailedUrls = [...status.failed_urls_list].sort((a, b) => b.time - a.time);
                
                // Show up to 50 failed URLs
                const displayUrls = sortedFailedUrls.slice(0, 50);
                
                displayUrls.forEach(item => {
                    const timeAgo = formatTimeAgo(item.time);
                    const retries = item.retries || 'N/A';
                    errorHTML += '<tr>';
                    errorHTML += '<td style="word-break: break-all;">';
                    errorHTML += '<a href="' + item.url + '" target="_blank" rel="noopener noreferrer">';
                    errorHTML += truncateUrl(item.url) + '</a></td>';
                    errorHTML += '<td style="word-break: break-word;">' + item.error + '</td>';
                    errorHTML += '<td>' + retries + '</td>';
                    errorHTML += '<td>' + timeAgo + '</td>';
                    errorHTML += '</tr>';
                });
                
                errorHTML += '</tbody></table>';
                
                if (status.failed_urls_list.length > 50) {
                    errorHTML += '<div class="mxchat-failed-urls-more">+ ' + 
                                 (status.failed_urls_list.length - 50) + 
                                 ' more failed URLs not shown</div>';
                }
                
                errorHTML += '</div>'; // End of failed-urls-list
                errorHTML += '</details>';
                errorHTML += '</div>'; // End of failed-urls-container
            }
            
            $errorContainer.html(errorHTML);
            
            // Additionally, add a click handler to pause refreshes when viewing details
            $sitemapCard.find('.mxchat-failed-urls-container details').on('toggle', function() {
                if (this.open) {
                    // User opened the details - set a flag
                    $(this).data('user-opened', true);
                } else {
                    // User closed the details - remove the flag
                    $(this).data('user-opened', false);
                }
            });
        }
    }
}

function createFailedPagesHtml(failedPagesList) {
    let html = '<div class="mxchat-error-notice">';
    html += '<div class="mxchat-failed-pages-container">';
    html += '<h5>Failed Pages (' + failedPagesList.length + ')</h5>';
    html += '<details>';
    html += '<summary>Show Failed Pages</summary>';
    html += '<div class="mxchat-failed-pages-list">';
    
    // Create table for failed pages
    html += '<table class="widefat striped">';
    html += '<thead><tr><th>Page</th><th>Error</th><th>Retries</th><th>Time</th></tr></thead>';
    html += '<tbody>';
    
    // Sort failed pages by most recent
    const sortedFailedPages = [...failedPagesList].sort((a, b) => b.time - a.time);
    
    sortedFailedPages.forEach(item => {
        const timeAgo = formatTimeAgo(item.time);
        html += '<tr>';
        html += '<td>Page ' + item.page + '</td>';
        html += '<td style="word-break: break-word;">' + item.error + '</td>';
        html += '<td>' + item.retries + '</td>';
        html += '<td>' + timeAgo + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    html += '</div>'; // End of failed-pages-list
    html += '</details>';
    html += '</div>'; // End of failed-pages-container
    html += '</div>'; // End of error-notice
    
    return html;
}

// NEW: Update failed pages list in existing card
function updateFailedPagesList($pdfCard, failedPagesList) {
    // Check if details is already open before updating
    const isDetailsOpen = $pdfCard.find('.mxchat-failed-pages-container details').prop('open');
    
    let $errorContainer = $pdfCard.find('.mxchat-failed-pages-container').parent();
    
    if ($errorContainer.length === 0) {
        // Create new failed pages container
        $pdfCard.find('.mxchat-status-details').append(createFailedPagesHtml(failedPagesList));
    } else {
        // Update existing container
        $errorContainer.html(createFailedPagesHtml(failedPagesList));
        
        // Restore open state if it was open before
        if (isDetailsOpen) {
            $pdfCard.find('.mxchat-failed-pages-container details').prop('open', true);
        }
    }
}

    
    // Create a new sitemap status card
    function createSitemapStatusCard(status) {
        let html = '<div class="mxchat-status-card">';
        html += '<div class="mxchat-status-header">';
        html += '<h4>Sitemap Processing Status</h4>';
        
        // Add stop processing form if processing
        if (status.status === 'processing') {
            html += '<form method="post" class="mxchat-stop-form" action="' + 
                   mxchatAdmin.admin_url + 'admin-post.php?action=mxchat_stop_processing">';
            html += '<input type="hidden" name="mxchat_stop_processing_nonce" value="' + 
                   mxchatAdmin.stop_nonce + '">';
            html += '<button type="submit" name="stop_processing" class="mxchat-button-secondary">';
            html += 'Stop Processing</button></form>';
        }
        
        // Add error badge if error
        if (status.status === 'error') {
            html += '<span class="mxchat-status-badge mxchat-status-failed">Error</span>';
        }
        
        html += '</div>'; // End header
        
        // Progress bar
        html += '<div class="mxchat-progress-bar">';
        html += '<div class="mxchat-progress-fill" style="width: ' + status.percentage + '%"></div>';
        html += '</div>';
        
        // Status details
        html += '<div class="mxchat-status-details">';
        html += '<p>Progress: ' + status.processed_urls + ' of ' + 
                status.total_urls + ' URLs (' + status.percentage + '%)</p>';
        
        // Add error message if any
        if ((status.error || status.last_error) && status.status === 'error') {
            html += '<div class="mxchat-error-notice">';
            
            if (status.error) {
                html += '<p class="error">' + status.error + '</p>';
            }
            
            if (status.last_error) {
                html += '<p class="last-error">Last error: ' + status.last_error + '</p>';
            }
            
            html += '</div>';
        }
        
        html += '</div>'; // End details
        html += '</div>'; // End card
        
        // Try to find the import tab content to insert the status card into
        let $importTabContent = $('#mxchat-kb-tab-import');
        if ($importTabContent.length > 0) {
            // For the tabbed interface, add to the import tab
            let $pdfCard = $importTabContent.find('.mxchat-status-card:contains("PDF Processing")');
            if ($pdfCard.length > 0) {
                $pdfCard.after($(html));
            } else {
                $importTabContent.find('.mxchat-import-section').after($(html));
            }
        } else {
            // Fallback to the old method
            let $pdfCard = $('.mxchat-status-card:contains("PDF Processing")');
            if ($pdfCard.length > 0) {
                $pdfCard.after($(html));
            } else {
                $('.mxchat-import-section').after($(html));
            }
        }
    }
    
    // Update single URL status
    function updateSingleUrlStatus(status) {
        // Check if container exists
        let $container = $('#mxchat-single-url-status-container');
        
        if ($container.length === 0) {
            // Create container
            $container = $('<div id="mxchat-single-url-status-container"></div>');
            
            // Try to find the import tab content to insert the status card into
            let $importTabContent = $('#mxchat-kb-tab-import');
            if ($importTabContent.length > 0) {
                // For the tabbed interface, add to the import tab
                let $lastStatusCard = $importTabContent.find('.mxchat-status-card').last();
                if ($lastStatusCard.length > 0) {
                    $lastStatusCard.after($container);
                } else {
                    $importTabContent.find('.mxchat-import-section').after($container);
                }
            } else {
                // Fallback to the old method
                let $lastStatusCard = $('.mxchat-status-card').last();
                if ($lastStatusCard.length > 0) {
                    $lastStatusCard.after($container);
                } else {
                    $('.mxchat-import-section').after($container);
                }
            }
        }
        
        // Update container content
        let html = '<div class="mxchat-status-card">';
        html += '<div class="mxchat-status-header">';
        html += '<h4>Last URL Submission</h4>';
        
        if (status.status === 'failed') {
            html += '<span class="mxchat-status-badge mxchat-status-failed">Failed</span>';
        } else {
            html += '<span class="mxchat-status-badge mxchat-status-success">Success</span>';
        }
        
        html += '</div>'; // End header
        
        html += '<div class="mxchat-status-details">';
        html += '<p><strong>URL:</strong> ';
        html += '<a href="' + status.url + '" target="_blank">';
        
        // Truncate URL if needed
        const displayUrl = status.url.length > 60 ? status.url.substring(0, 57) + '...' : status.url;
        html += displayUrl;
        
        html += '</a></p>';
        html += '<p><strong>Submitted:</strong> ' + status.human_time + '</p>';
        
        if (status.status === 'failed' && status.error) {
            html += '<div class="mxchat-error-notice">';
            html += '<p class="error">' + status.error + '</p>';
            html += '</div>';
        }
        
        if (status.status === 'complete') {
            html += '<p><strong>Content Length:</strong> ' + status.content_length + ' characters</p>';
            html += '<p><strong>Embedding Dimensions:</strong> ' + status.embedding_dimensions + '</p>';
        }
        
        html += '</div>'; // End details
        html += '</div>'; // End card
        
        $container.html(html).show();
    }
    
    // Helper function to format time ago
    function formatTimeAgo(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const seconds = now - timestamp;
        
        if (seconds < 60) {
            return seconds + ' seconds ago';
        } else if (seconds < 3600) {
            return Math.floor(seconds / 60) + ' minutes ago';
        } else if (seconds < 86400) {
            return Math.floor(seconds / 3600) + ' hours ago';
        } else {
            return Math.floor(seconds / 86400) + ' days ago';
        }
    }
    
    // Helper function to truncate long URLs
    function truncateUrl(url) {
        const maxLength = 50;
        if (url.length <= maxLength) return url;
        
        // Remove protocol
        let displayUrl = url.replace(/^https?:\/\//, '');
        
        if (displayUrl.length <= maxLength) return displayUrl;
        
        // Keep the domain and truncate the path
        const domainMatch = displayUrl.match(/^([^\/]+)\//);
        if (domainMatch) {
            const domain = domainMatch[1];
            const path = displayUrl.substring(domain.length);
            
            if (path.length > 10) {
                return domain + path.substring(0, maxLength - domain.length - 3) + '...';
            }
        }
        
        // Final fallback for very long strings
        return displayUrl.substring(0, maxLength - 3) + '...';
    }
    
    // Manual Batch Processing Button Handler
$(document).on('click', '.mxchat-manual-batch-btn', function() {
    const $btn = $(this);
    const processType = $btn.data('process-type');
    const url = $btn.data('url');
    
    //console.log('Manual batch processing requested:', processType, url);
    
    // Disable button and show loading
    $btn.prop('disabled', true).text('Processing...');
    
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'mxchat_manual_batch_process',
            nonce: mxchatAdmin.status_nonce,
            process_type: processType,
            url: url
        },
        success: function(response) {
            //console.log('Manual batch response:', response);
            
            if (response.success) {
                // Show success message briefly
                $btn.text('✓ Processed ' + response.data.processed);
                
                // Re-enable button after 2 seconds
                setTimeout(function() {
                    $btn.prop('disabled', false).text('Process Batch');
                }, 2000);
                
                // Trigger status update to show progress
                setTimeout(function() {
                    fetchStatusUpdates();
                }, 1000); // Wait 1 second then refresh status
                
            } else {
                console.error('Manual batch failed:', response.data);
                alert('Error: ' + response.data);
                $btn.prop('disabled', false).text('Process Batch');
            }
        },
        error: function(xhr, status, error) {
            console.error('Manual batch AJAX error:', error);
            alert('Processing failed. Please try again.');
            $btn.prop('disabled', false).text('Process Batch');
        }
    });
});
});


// Handle AJAX delete for Pinecone
jQuery(document).on('click', '.delete-button-ajax', function(e) {
    e.preventDefault();
    
    if (!confirm('Are you sure you want to delete this entry?')) {
        return;
    }
    
    var $button = jQuery(this);
    var $row = $button.closest('tr');
    var vectorId = $button.data('vector-id');
    var nonce = $button.data('nonce');
    
    // Disable button and show loading
    $button.prop('disabled', true);
    $button.find('.dashicons').removeClass('dashicons-trash').addClass('dashicons-update-alt');
    $row.addClass('mxchat-row-deleting');
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'mxchat_delete_pinecone_prompt',
            nonce: nonce,
            vector_id: vectorId
        },
        success: function(response) {
            if (response.success) {
                // Immediately remove the row with animation
                $row.fadeOut(500, function() {
                    jQuery(this).remove();
                    
                    // Update record count
                    var $countSpan = jQuery('.mxchat-record-count');
                    if ($countSpan.length) {
                        var currentText = $countSpan.text();
                        var matches = currentText.match(/\((\d+)/);
                        if (matches) {
                            var currentCount = parseInt(matches[1]);
                            var newCount = Math.max(0, currentCount - 1);
                            $countSpan.text($countSpan.text().replace(/\(\d+/, '(' + newCount));
                        }
                    }
                });
                
                // Show success message
                jQuery('<div class="notice notice-success is-dismissible"><p>Entry deleted successfully from Pinecone.</p></div>')
                    .insertAfter('.mxchat-hero')
                    .delay(3000)
                    .fadeOut();
                    
            } else {
                // Re-enable button and show error
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('dashicons-update-alt').addClass('dashicons-trash');
                $row.removeClass('mxchat-row-deleting');
                
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            // Re-enable button
            $button.prop('disabled', false);
            $button.find('.dashicons').removeClass('dashicons-update-alt').addClass('dashicons-trash');
            $row.removeClass('mxchat-row-deleting');
            
            alert('Network error occurred');
        }
    });
});