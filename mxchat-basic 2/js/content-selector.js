jQuery(document).ready(function($) {
    // Modal elements
    const $modal = $('#mxchat-kb-content-selector-modal');
    const $openButton = $('#mxchat-open-content-selector');
    const $closeButtons = $('.mxchat-kb-modal-close');
    const $contentList = $('.mxchat-kb-content-list');
    const $loading = $('.mxchat-kb-loading');
    const $pagination = $('.mxchat-kb-pagination');
    const $processButton = $('#mxchat-kb-process-selected');
    const $selectAll = $('#mxchat-kb-select-all');
    const $selectionCount = $('.mxchat-kb-selection-count');
    
    // Filter elements
    const $searchInput = $('#mxchat-kb-content-search');1
    const $typeFilter = $('#mxchat-kb-content-type-filter');
    const $statusFilter = $('#mxchat-kb-content-status-filter');
    const $processedFilter = $('#mxchat-kb-processed-filter');
    
    // Current state - using let for variables that change
    let currentPage = 1;
    let totalPages = 1;
    let selectedItems = new Set();
    let allItems = [];
    
    // Open modal when WordPress import button is clicked
    $openButton.on('click', function() {
        $modal.show();
        // Reset to first page when opening the modal
        currentPage = 1;
        loadContent();
    });
    
    // Close modal
    $closeButtons.on('click', function() {
        $modal.hide();
    });
// Handle import option box clicks (for non-WordPress options)
$('.mxchat-import-box').on('click', function() {
    const $box = $(this);
    const option = $box.data('option');
    
    // Skip if this is the WordPress option (it has its own handler)
    if (option === 'wordpress') {
        return;
    }
    
    // Update active state
    $('.mxchat-import-box').removeClass('active');
    $box.addClass('active');
    
    // Hide all input areas
    $('#mxchat-url-input-area, #mxchat-content-input-area').hide();
    
    // Handle different import options
    switch (option) {
        case 'pdf':
        case 'sitemap':
        case 'url':
            // Show URL input area with appropriate placeholder
            $('#mxchat-url-input-area').show();
            $('#sitemap_url').attr('placeholder', $box.data('placeholder'));
            $('#import_type').val(option);
            
            // Update the description text based on the import type
            let descriptionText = '';
            if (option === 'pdf') {
                descriptionText = 'Import a PDF document by entering its URL above. PDFs are processed via cron job. If processing does not start, you can manually process batch 5 pages at a time.';
            } else if (option === 'sitemap') {
                descriptionText = 'Enter a content-specific sub-sitemap URL, not the sitemap index. Sitemaps are processed via cron job. If processing does not start, you can manually process batch 5 pages at a time.';
            } else if (option === 'url') {
                descriptionText = 'Import content from any webpage by entering its URL.';
            }
            $('#url-description-text').text(descriptionText);
            break;
            
        case 'content':
            // Show content input area
            $('#mxchat-content-input-area').show();
            break;
    }
});


// Load content via AJAX
function loadContent() {
    $loading.show();
    $contentList.find('.mxchat-kb-content-item').remove();
    
    const data = {
        action: 'mxchat_get_content_list',
        nonce: mxchatSelector.nonce,
        page: currentPage,
        per_page: 50,
        search: $searchInput.val(),
        post_type: $typeFilter.val(),
        post_status: $statusFilter.val(),
        processed_filter: $processedFilter.val()
    };
    
    //console.log('Loading content for page', currentPage, 'with filters:', data);
    
    $.ajax({
        url: mxchatSelector.ajaxurl,
        data: data,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $loading.hide();
            
            if (response.success && response.data.items && response.data.items.length > 0) {
                // Store the items directly
                let items = response.data.items;
                
                if (items.length > 0) {
                    renderContentItems(items);
                    renderPagination(parseInt(response.data.current_page), parseInt(response.data.total_pages));
                    
                    // Update state
                    allItems = items;
                    totalPages = parseInt(response.data.total_pages);
                    currentPage = parseInt(response.data.current_page);
                    
                    // Update select all checkbox based on current selection
                    updateSelectAllState();
                } else {
                    displayNoResults($processedFilter.val());
                }
            } else {
                displayNoResults($processedFilter.val());
            }
        },
        error: function(xhr, status, error) {
            $loading.hide();
            console.error('AJAX Error:', status, error);
            $contentList.html('<div class="mxchat-kb-error">Error loading content. Please try again.</div>');
            // Clear pagination on error
            $pagination.empty();
        }
    });
}

// Helper function to display appropriate "no results" message
function displayNoResults(processedStatus) {
    let message = 'No content found matching your criteria.';
    
    if (processedStatus === 'processed') {
        message = 'No content found in knowledge base.';
    } else if (processedStatus === 'unprocessed') {
        message = 'All content is already in knowledge base.';
    }
    
    $contentList.html('<div class="mxchat-kb-no-results">' + message + '</div>');
    // Clear pagination when no results
    $pagination.empty();
}
    
    // Render content items
    function renderContentItems(items) {
        let html = '';
        
        items.forEach(function(item) {
            const isSelected = selectedItems.has(item.id);
            const isProcessed = item.already_processed;
            
            // Updated badge text
            const badgeText = isProcessed ? 'In Knowledge Base' : 'Not In Knowledge Base';
            const badgeClass = isProcessed ? 'mxchat-kb-processed-badge' : 'mxchat-kb-unprocessed-badge';
            
            
            html += `
                <div class="mxchat-kb-content-item ${isProcessed ? 'processed' : ''}" data-id="${item.id}">
                    <div class="mxchat-kb-content-checkbox">
                        <input type="checkbox" id="content-${item.id}" ${isSelected ? 'checked' : ''}>
                    </div>
                    <div class="mxchat-kb-content-details">
                        <div class="mxchat-kb-content-title">
                            <a href="${item.permalink}" target="_blank">${item.title}</a>
                            <span class="${badgeClass}">${badgeText}</span>
                            ${isProcessed ? '<span class="mxchat-kb-last-updated">Last updated: ' + item.processed_date + '</span>' : ''}
                        </div>
                        <div class="mxchat-kb-content-meta">
                            <span class="mxchat-kb-content-type">${item.type}</span>
                            <span class="mxchat-kb-content-date">${item.date}</span>
                            <span class="mxchat-kb-content-words">${item.word_count} words</span>
                        </div>
                        <div class="mxchat-kb-content-excerpt">${item.excerpt}</div>
                    </div>
                </div>
            `;
        });
        
        $contentList.html(html);
        
        // Add event listeners for checkboxes using delegation for better performance
        $contentList.off('change', 'input[type="checkbox"]').on('change', 'input[type="checkbox"]', function() {
            const $checkbox = $(this);
            const itemId = parseInt($checkbox.closest('.mxchat-kb-content-item').data('id'));
            
            if ($checkbox.is(':checked')) {
                selectedItems.add(itemId);
            } else {
                selectedItems.delete(itemId);
            }
            
            updateSelection();
        });
    }
    
    // Render pagination - FIXED VERSION
    function renderPagination(currentPage, totalPages) {
        // Clear existing pagination first
        $pagination.empty();
        
        // Don't render pagination if only one page
        if (totalPages <= 1) {
            return;
        }
        
        let html = '<div class="mxchat-kb-pagination-links">';
        
        // Previous button
        if (currentPage > 1) {
            html += '<a href="#" class="mxchat-kb-page-link prev" data-page="' + (currentPage - 1) + '">&laquo; Previous</a>';
        }
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += '<span class="mxchat-kb-page-current">' + i + '</span>';
            } else {
                html += '<a href="#" class="mxchat-kb-page-link" data-page="' + i + '">' + i + '</a>';
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            html += '<a href="#" class="mxchat-kb-page-link next" data-page="' + (currentPage + 1) + '">Next &raquo;</a>';
        }
        
        html += '</div>';
        
        $pagination.html(html);
    }
    
    // Handle pagination clicks directly on the document
    $(document).on('click', '.mxchat-kb-page-link', function(e) {
        e.preventDefault();
        const newPage = parseInt($(this).data('page'));
        //console.log('Pagination clicked: changing from page', currentPage, 'to', newPage);
        
        // Only reload if the page actually changed
        if (currentPage !== newPage) {
            currentPage = newPage;
            loadContent();
        }
    });
    
    // Update selection counts and button state
    function updateSelection() {
        const selectedCount = selectedItems.size;
        $selectionCount.text(selectedCount + ' ' + (selectedCount === 1 ? 'selected' : 'selected'));
        $('.mxchat-kb-selected-count').text('(' + selectedCount + ')');
        
        // Determine if any selected items are already processed
        const hasProcessedItems = Array.from(selectedItems).some(id => {
            const item = allItems.find(item => item.id === id);
            return item && item.already_processed;
        });
        
        if (selectedCount > 0) {
            $processButton.prop('disabled', false);
            
            // Update button text based on selection
            if (hasProcessedItems && selectedCount === 1) {
                $processButton.text('Update Selected Content (1)').addClass('update-mode');
            } else if (hasProcessedItems && selectedCount > 1) {
                $processButton.text('Process/Update Selected (' + selectedCount + ')').addClass('mixed-mode');
            } else {
                $processButton.text('Process Selected Content (' + selectedCount + ')').removeClass('update-mode mixed-mode');
            }
        } else {
            $processButton.prop('disabled', true);
            $processButton.text('Process Selected Content').removeClass('update-mode mixed-mode');
            $('.mxchat-kb-selected-count').text('(0)');
        }
        
        updateSelectAllState();
    }
    
    // Update "Select All" checkbox state
    function updateSelectAllState() {
        const availableItems = allItems.length;
        const selectedAvailableItems = allItems.filter(item => selectedItems.has(item.id)).length;
        
        if (availableItems === 0) {
            $selectAll.prop('checked', false);
            $selectAll.prop('disabled', true);
        } else if (selectedAvailableItems === availableItems) {
            $selectAll.prop('checked', true);
        } else {
            $selectAll.prop('checked', false);
        }
    }
    
    // Handle Select All checkbox
    $selectAll.on('change', function() {
        const isChecked = $(this).is(':checked');
        
        $contentList.find('.mxchat-kb-content-item input[type="checkbox"]').each(function() {
            const $checkbox = $(this);
            const $item = $checkbox.closest('.mxchat-kb-content-item');
            const itemId = parseInt($item.data('id'));
            
            $checkbox.prop('checked', isChecked);
            
            if (isChecked) {
                selectedItems.add(itemId);
            } else {
                selectedItems.delete(itemId);
            }
        });
        
        updateSelection();
    });
    
    // Handle search input
    let searchTimer;
    $searchInput.on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            currentPage = 1; // Reset to first page on new search
            loadContent();
        }, 500);
    });
    
    // Handle filter changes
    $typeFilter.add($statusFilter).add($processedFilter).on('change', function() {
        currentPage = 1; // Reset to first page on filter change
        selectedItems.clear(); // Clear selection when filter changes
        loadContent();
    });
    
    // Process selected content
    $processButton.on('click', function() {
        if (selectedItems.size === 0) {
            return;
        }
        
        const $button = $(this);
        $button.prop('disabled', true);
        
        // Update button text based on mode
        if ($button.hasClass('update-mode')) {
            $button.text('Updating...');
        } else if ($button.hasClass('mixed-mode')) {
            $button.text('Processing/Updating...');
        } else {
            $button.text('Processing...');
        }
        
        // Convert selected items to array
        const selectedPostIds = Array.from(selectedItems);
        const totalToProcess = selectedPostIds.length;
        let processed = 0;
        let updated = 0;
        let failed = 0;
        const results = {
            success: [],
            updated: [],
            failed: []
        };
        
        // Create a modal to show progress
        const $progressModal = $('<div class="mxchat-kb-processing-overlay">' +
            '<div class="mxchat-kb-processing-content">' +
            '<h3>Processing Content</h3>' +
            '<p class="mxchat-kb-processing-status">Processing 1 of ' + totalToProcess + '...</p>' +
            '<div class="mxchat-kb-progress-bar"><div class="mxchat-kb-progress-fill" style="width: 0%"></div></div>' +
            '<p class="mxchat-kb-current-item"></p>' +
            '</div>' +
            '</div>');
        
        $('body').append($progressModal);
        
        // Process posts one by one
        function processNext(index) {
            if (index >= selectedPostIds.length) {
                // All done
                finishProcessing();
                return;
            }
            
            const postId = selectedPostIds[index];
            const percent = Math.round((index / totalToProcess) * 100);
            const item = allItems.find(item => item.id === postId);
            const isUpdate = item && item.already_processed;
            
            // Update progress UI
            $progressModal.find('.mxchat-kb-processing-status')
                .text((isUpdate ? 'Updating' : 'Processing') + ' ' + (index + 1) + ' of ' + totalToProcess + '...');
            $progressModal.find('.mxchat-kb-progress-fill').css('width', percent + '%');
            
            // Make AJAX request for this post
            $.ajax({
                url: mxchatSelector.ajaxurl,
                method: 'POST',
                data: {
                    action: 'mxchat_process_selected_content',
                    nonce: mxchatSelector.nonce,
                    post_ids: [postId],
                    is_update: isUpdate
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (isUpdate) {
                            updated++;
                            results.updated.push({
                                id: postId,
                                title: response.data.title || ('ID: ' + postId)
                            });
                        } else {
                            processed++;
                            results.success.push({
                                id: postId,
                                title: response.data.title || ('ID: ' + postId)
                            });
                        }
                        
                        $progressModal.find('.mxchat-kb-current-item')
                            .text('Successfully ' + (isUpdate ? 'updated' : 'processed') + ': ' + response.data.title);
                    } else {
                        failed++;
                        results.failed.push({
                            id: postId,
                            error: response.data || 'Unknown error'
                        });
                        
                        $progressModal.find('.mxchat-kb-current-item')
                            .text('Failed to ' + (isUpdate ? 'update' : 'process') + ' ID: ' + postId);
                    }
                    
                    // Process next post
                    setTimeout(function() {
                        processNext(index + 1);
                    }, 500); // Small delay between requests
                },
                error: function(xhr, status, error) {
                    failed++;
                    results.failed.push({
                        id: postId,
                        error: error || 'Server error'
                    });
                    
                    $progressModal.find('.mxchat-kb-current-item')
                        .text('Error ' + (isUpdate ? 'updating' : 'processing') + ' ID: ' + postId);
                    
                    // Process next post
                    setTimeout(function() {
                        processNext(index + 1);
                    }, 500);
                }
            });
        }
        
        // Function to finish processing and show results
        function finishProcessing() {
            // Remove progress modal
            $progressModal.remove();
            
            // Determine notification type based on results
            let notificationClass = 'success';
            if (failed > 0) {
                notificationClass = processed > 0 || updated > 0 ? 'warning' : 'error';
            }
            
            // Create summary message
            let resultHTML = '<div class="mxchat-kb-notification ' + notificationClass + '">' + 
                           '<h4>';
            
            if (processed > 0 && updated > 0) {
                resultHTML += 'Processed ' + processed + ' new items and updated ' + updated + ' existing items';
            } else if (processed > 0) {
                resultHTML += 'Processed ' + processed + ' items successfully';
            } else if (updated > 0) {
                resultHTML += 'Updated ' + updated + ' items successfully';
            } else {
                resultHTML += 'No items were processed successfully';
            }
            
            if (failed > 0) {
                resultHTML += ' with ' + failed + ' failures';
            }
            
            resultHTML += '</h4>';
            
            // Add details if there were failures
            if (failed > 0) {
                resultHTML += '<div class="mxchat-kb-results-details">';
                resultHTML += '<h5>Failed Items:</h5><ul>';
                
                results.failed.forEach(function(item) {
                    resultHTML += '<li><strong>ID: ' + item.id + '</strong>: ' + item.error + '</li>';
                });
                
                resultHTML += '</ul></div>';
            }
            
            resultHTML += '</div>';
            
            // Show results in modal
            $modal.find('.mxchat-kb-modal-content').prepend($(resultHTML));
            
            // Clear selection
            selectedItems.clear();
            updateSelection();
            
            // Enable button
            $button.prop('disabled', false)
                   .text('Process Selected Content')
                   .removeClass('update-mode mixed-mode');
            $('.mxchat-kb-selected-count').text('(0)');
            
            // Only reload if there were successful operations
            if (processed > 0 || updated > 0) {
                // Reload after delay
                setTimeout(function() {
                    // Reload the knowledge base table - if this function exists
                    if (typeof reloadKnowledgeBaseTable === 'function') {
                        reloadKnowledgeBaseTable();
                    } else {
                        // Fallback: reload content list instead of full page reload
                        loadContent();
                    }
                }, 3000);
            }
        }
        
        // Start processing the first post
        processNext(0);
    });

    // Initialize - Set WordPress as the active option by default
    $('.mxchat-import-box[data-option="wordpress"]').addClass('active');
});

// Tab switching functionality - Keep this separate
jQuery(document).ready(function($) {
    
// Tab switching functionality
// Modified tab switching to check for Pinecone changes
$('.mxchat-kb-tab-button').on('click', function() {
    var tabId = $(this).data('tab');
    var $button = $(this);
    
    // Switch tabs immediately for all tabs
    $('.mxchat-kb-tab-button').removeClass('active');
    $('.mxchat-kb-tab-content').removeClass('active');
    $button.addClass('active');
    $('#mxchat-kb-tab-' + tabId).addClass('active');
    
    // Check if Pinecone was changed and we're going to import tab
    if (tabId === 'import' && sessionStorage.getItem('mxchat_pinecone_changed') === 'true') {
        sessionStorage.removeItem('mxchat_pinecone_changed');
        
        // Show refresh notice
        var $knowledgeCard = $('#mxchat-kb-tab-import .mxchat-card').eq(1);
        if ($knowledgeCard.length > 0 && $knowledgeCard.find('.notice-warning').length === 0) {
            var refreshNotice = $('<div class="notice notice-warning" style="margin: 15px 0; padding: 10px 15px;">' +
                '<p style="margin: 0;">' +
                '<span class="dashicons dashicons-info" style="color: #f0ad4e; margin-right: 5px;"></span>' +
                'Database settings have changed. ' +
                '<a href="#" onclick="location.reload(); return false;" style="font-weight: bold;">Click here to refresh</a> to see the updated knowledge base.' +
                '</p></div>');
            
            $knowledgeCard.prepend(refreshNotice);
        }
    }
    
    // Initialize Pinecone functionality when Pinecone tab is activated
    if (tabId === 'pinecone') {
        setTimeout(function() {
            initPineconeFeatures();
        }, 100);
    }
});


    // Pinecone functionality
    function initPineconeFeatures() {
        //console.log('Initializing Pinecone features...');
        
        if ($('#mxchat-kb-tab-pinecone').length === 0) {
            return;
        }
        
        initPineconeToggle();
        initPineconeConnectionTest();
        checkPineconeCompatibility();
    }
    
    function initPineconeToggle() {
        // Remove any existing handlers to prevent duplicates
        $('input[name="mxchat_pinecone_addon_options[mxchat_use_pinecone]"]').off('change.pineconeToggle');
        
        // Add the toggle handler for UI only (auto-save will handle the actual saving)
        $('input[name="mxchat_pinecone_addon_options[mxchat_use_pinecone]"]').on('change.pineconeToggle', function() {
            var $checkbox = $(this);
            var isChecked = $checkbox.is(':checked');
            var settingsDiv = $('.mxchat-pinecone-settings');
            
            //console.log('Pinecone toggle changed to:', isChecked);
            
            // Update the UI immediately
            if (isChecked) {
                settingsDiv.slideDown(300);
            } else {
                settingsDiv.slideUp(300);
            }
        });
        
        // Set initial state based on current checkbox value
        var currentToggle = $('input[name="mxchat_pinecone_addon_options[mxchat_use_pinecone]"]');
        if (currentToggle.length > 0) {
            var settingsDiv = $('.mxchat-pinecone-settings');
            if (currentToggle.is(':checked')) {
                settingsDiv.show();
            } else {
                settingsDiv.hide();
            }
        }
    }
    
    function initPineconeConnectionTest() {
        $('#test-pinecone-connection').off('click.pinecone');
        
        $('#test-pinecone-connection').on('click.pinecone', function() {
            var button = $(this);
            var resultDiv = $('#connection-test-result');
            
            var apiKey = $('#mxchat_pinecone_api_key').val();
            var host = $('#mxchat_pinecone_host').val();
            var index = $('#mxchat_pinecone_index').val();
            
            if (!apiKey || !host || !index) {
                resultDiv.html('<div class="notice notice-error"><p>Please fill in all required fields first.</p></div>').show();
                return;
            }
            
            button.prop('disabled', true).text('Testing...');
            resultDiv.hide();
            
            var ajaxUrl = (typeof mxchatPromptsAdmin !== 'undefined') ? mxchatPromptsAdmin.ajax_url : ajaxurl;
            var nonce = (typeof mxchatPromptsAdmin !== 'undefined') ? mxchatPromptsAdmin.prompts_setting_nonce : 
                       (typeof mxchatAdmin !== 'undefined') ? mxchatAdmin.setting_nonce : '';
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mxchat_test_pinecone_connection',
                    _ajax_nonce: nonce,
                    api_key: apiKey,
                    host: host,
                    index_name: index
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<div class="notice notice-success"><p><span class="dashicons dashicons-yes-alt"></span> ' + response.data.message + '</p></div>');
                    } else {
                        resultDiv.html('<div class="notice notice-error"><p><span class="dashicons dashicons-warning"></span> ' + response.data.message + '</p></div>');
                    }
                    resultDiv.show();
                },
                error: function() {
                    resultDiv.html('<div class="notice notice-error"><p>Connection test failed. Please check your settings.</p></div>').show();
                },
                complete: function() {
                    button.prop('disabled', false).text('Test Connection');
                }
            });
        });
    }
    
    function checkPineconeCompatibility() {
        if ($('.mxchat-pinecone-compatibility-notice').length > 0) {
            return;
        }
        
        var hasOldAddon = $('body').hasClass('mxchat-pinecone-addon-active') || 
                         $('.pcm-card').length > 0;
        
        if (hasOldAddon) {
            var compatibilityNotice = $(`
                <div class="notice notice-info mxchat-pinecone-compatibility-notice">
                    <p><strong>Pinecone Integration Notice:</strong> We've detected you have the Pinecone add-on installed. 
                    Pinecone functionality is now built into the core plugin. You can safely deactivate the separate 
                    Pinecone add-on after confirming your settings are migrated below.</p>
                </div>
            `);
            
            $('#mxchat-kb-tab-pinecone .mxchat-card').prepend(compatibilityNotice);
            
            migratePineconeSettings();
        }
    }
    
    function migratePineconeSettings() {
        if (typeof ajaxurl !== 'undefined') {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mxchat_migrate_pinecone_settings',
                    _ajax_nonce: (typeof mxchatAdmin !== 'undefined') ? mxchatAdmin.setting_nonce : ''
                },
                success: function(response) {
                    if (response.success && response.data.migrated) {
                        location.reload();
                    }
                },
                error: function() {
                    //console.log('Pinecone settings migration not available');
                }
            });
        }
    }
    
});