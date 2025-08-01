jQuery(document).ready(function($) {
    
    // ====================================
    // GLOBAL VARIABLES & CONFIGURATION
    // ====================================
    const toolbarIconColor = mxchatChat.toolbar_icon_color || '#212121';
    
    // Initialize color settings
    var userMessageBgColor = mxchatChat.user_message_bg_color;
    var userMessageFontColor = mxchatChat.user_message_font_color;
    var botMessageBgColor = mxchatChat.bot_message_bg_color;
    var botMessageFontColor = mxchatChat.bot_message_font_color;
    var liveAgentMessageBgColor = mxchatChat.live_agent_message_bg_color;
    var liveAgentMessageFontColor = mxchatChat.live_agent_message_font_color;
    
    var linkTarget = mxchatChat.link_target_toggle === 'on' ? '_blank' : '_self';
    let lastSeenMessageId = '';
    let notificationCheckInterval;
    let notificationBadge;
    var sessionId = getChatSession();
    let pollingInterval;
    let processedMessageIds = new Set();
    let activePdfFile = null;
    let activeWordFile = null;


    // ====================================
    // SESSION MANAGEMENT
    // ====================================
    
    function getChatSession() {
        var sessionId = getCookie('mxchat_session_id');
        //console.log("Session ID retrieved from cookie: ", sessionId);
    
        if (!sessionId) {
            sessionId = generateSessionId();
            //console.log("Generated new session ID: ", sessionId);
            setChatSession(sessionId);
        }
    
        //console.log("Final session ID: ", sessionId);
        return sessionId;
    }
    
    function setChatSession(sessionId) {
        // Set the cookie with a 24-hour expiration (86400 seconds)
        document.cookie = "mxchat_session_id=" + sessionId + "; path=/; max-age=86400; SameSite=Lax";
    }
    
    function getCookie(name) {
        let value = "; " + document.cookie;
        let parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }
    
    function generateSessionId() {
        return 'mxchat_chat_' + Math.random().toString(36).substr(2, 9);
    }

// ====================================
// CONTEXTUAL AWARENESS FUNCTIONALITY
// ====================================

function getPageContext() {
    // Check if contextual awareness is enabled
    if (mxchatChat.contextual_awareness_toggle !== 'on') {
        return null;
    }
    
    // Get page URL
    const pageUrl = window.location.href;
    
    // Get page title
    const pageTitle = document.title || '';
    
    // Get main content from the page
    let pageContent = '';
    
    // Try to get content from common content areas
    const contentSelectors = [
        'main',
        '[role="main"]',
        '.content',
        '.main-content',
        '.post-content',
        '.entry-content',
        '.page-content',
        'article',
        '#content',
        '#main'
    ];
    
    let contentElement = null;
    for (const selector of contentSelectors) {
        contentElement = document.querySelector(selector);
        if (contentElement) {
            break;
        }
    }
    
    // If no specific content area found, use body but exclude header, footer, nav, sidebar
    if (!contentElement) {
        contentElement = document.body;
    }
    
    if (contentElement) {
        // Clone the element to avoid modifying the original
        const clone = contentElement.cloneNode(true);
        
        // Remove unwanted elements
        const unwantedSelectors = [
            'header',
            'footer',
            'nav',
            '.navigation',
            '.sidebar',
            '.widget',
            '.menu',
            'script',
            'style',
            '.comments',
            '#comments',
            '.breadcrumb',
            '.breadcrumbs',
            '#floating-chatbot',
            '#floating-chatbot-button',
            '.mxchat',
            '[class*="chat"]',
            '[id*="chat"]'
        ];
        
        unwantedSelectors.forEach(selector => {
            const elements = clone.querySelectorAll(selector);
            elements.forEach(el => el.remove());
        });
        
        // Get text content and clean it up
        pageContent = clone.textContent || clone.innerText || '';
        
        // Clean up whitespace and limit length
        pageContent = pageContent
            .replace(/\s+/g, ' ')
            .trim()
            .substring(0, 3000); // Limit to 3000 characters to avoid token limits
    }
    
    // Only return context if we have meaningful content
    if (!pageContent || pageContent.length < 50) {
        return null;
    }
    
    return {
        url: pageUrl,
        title: pageTitle,
        content: pageContent
    };
}

// ====================================
// CORE CHAT FUNCTIONALITY
// ====================================
// Update your existing sendMessage function
function sendMessage() {
    var message = $('#chat-input').val();
    if (message) {
        appendMessage("user", message);
        $('#chat-input').val('');
        $('#chat-input').css('height', 'auto');

        $('#mxchat-popular-questions').hide();
        appendThinkingMessage();
        scrollToBottom();

        const currentModel = mxchatChat.model || 'gpt-4o';

        // Check if streaming is enabled AND supported for this model
        if (shouldUseStreaming(currentModel)) {
            callMxChatStream(message, function(response) {
                $('.bot-message.temporary-message').removeClass('temporary-message');
            });
        } else {
            callMxChat(message, function(response) {
                replaceLastMessage("bot", response);
            });
        }
    }
}

// Update your existing sendMessageToChatbot function
function sendMessageToChatbot(message) {
    var sessionId = getChatSession();

    $('#mxchat-popular-questions').hide();
    appendThinkingMessage();
    scrollToBottom();

    const currentModel = mxchatChat.model || 'gpt-4o';

    // Check if streaming is enabled AND supported for this model
    if (shouldUseStreaming(currentModel)) {
        callMxChatStream(message, function(response) {
            $('.bot-message.temporary-message').removeClass('temporary-message');
        });
    } else {
        callMxChat(message, function(response) {
            $('.temporary-message').remove();
            replaceLastMessage("bot", response);
        });
    }
}


// Updated shouldUseStreaming function with debugging
function shouldUseStreaming(model) {
    // Check if streaming is enabled in settings (using your toggle naming pattern)
    const streamingEnabled = mxchatChat.enable_streaming_toggle === 'on';
    
    // Check if model supports streaming
    const streamingSupported = isStreamingSupported(model);
    
    
    // Only use streaming if both enabled and supported
    return streamingEnabled && streamingSupported;
}

function callMxChat(message, callback) {
    // Get page context if contextual awareness is enabled
    const pageContext = getPageContext();
    
    // Prepare AJAX data
    const ajaxData = {
        action: 'mxchat_handle_chat_request',
        message: message,
        session_id: getChatSession(),
        nonce: mxchatChat.nonce
    };
    
    // Add page context if available
    if (pageContext) {
        ajaxData.page_context = JSON.stringify(pageContext);
    }
    
    $.ajax({
        url: mxchatChat.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: ajaxData,
        success: function(response) {
            // Log the full response for debugging
            //console.log("API Response:", response);

            // First check if this is a successful response by looking for text, html, or message fields
            // This preserves compatibility with your server response format
            if (response.text !== undefined || response.html !== undefined || response.message !== undefined || 
                (response.success === true && response.data && response.data.status === 'waiting_for_agent')) {

                // Handle successful response - this is your original success handling code

                // Existing chat mode check
                if (response.chat_mode) {
                    updateChatModeIndicator(response.chat_mode);
                }
                else if (response.fallbackResponse && response.fallbackResponse.chat_mode) {
                    updateChatModeIndicator(response.fallbackResponse.chat_mode);
                }

                // Add PDF filename handling
                if (response.data && response.data.filename) {
                    showActivePdf(response.data.filename);
                    activePdfFile = response.data.filename;
                }

                // Add redirect check here
                if (response.redirect_url) {
                    let responseText = response.text || '';
                    if (responseText) {
                        replaceLastMessage("bot", responseText);
                    }
                    setTimeout(() => {
                        window.location.href = response.redirect_url;
                    }, 1500);
                    return;
                }

                // Check for live agent response
                if (response.success && response.data && response.data.status === 'waiting_for_agent') {
                    updateChatModeIndicator('agent');
                    return;
                }

                // Handle other responses
                let responseText = response.text || '';
                let responseHtml = response.html || '';
                let responseMessage = response.message || '';

                if (responseText === 'You are now chatting with the AI chatbot.') {
                    updateChatModeIndicator('ai');
                }

                // Handle the message and show notification if chat is hidden
                if (responseText || responseHtml || responseMessage) {
                    // Update the messages as before
                    if (responseText && responseHtml) {
                        replaceLastMessage("bot", responseText, responseHtml);
                    } else if (responseText) {
                        replaceLastMessage("bot", responseText);
                    } else if (responseHtml) {
                        replaceLastMessage("bot", "", responseHtml);
                    } else if (responseMessage) {
                        replaceLastMessage("bot", responseMessage);
                    }

                    // Check if chat is hidden and show notification
                    if ($('#floating-chatbot').hasClass('hidden')) {
                        const badge = $('#chat-notification-badge');
                        if (badge.length) {
                            badge.show();
                        }
                    }
                } else {
                    ////console.error("Unexpected response format:", response);
                    replaceLastMessage("bot", "I received an empty response. Please try again or contact support if this persists.");
                }

                if (response.message_id) {
                    lastSeenMessageId = response.message_id;
                }

                return;
            }

            // If we got here, it's likely an error response
            // Now we can check for error conditions with our robust error handling

            let errorMessage = "";
            let errorCode = "";

            // Check various possible error locations in the response
            if (response.data && response.data.error_message) {
                errorMessage = response.data.error_message;
                errorCode = response.data.error_code || "";
            } else if (response.error_message) {
                errorMessage = response.error_message;
                errorCode = response.error_code || "";
            } else if (response.message) {
                errorMessage = response.message;
            } else if (typeof response.data === 'string') {
                errorMessage = response.data;
            } else if (!response.success) {
                // Explicit check for success: false without other error info
                errorMessage = "An error occurred. Please try again or contact support.";
            } else {
                // Fallback for any other unexpected response format
                errorMessage = "Unexpected response received. Please try again or contact support.";
            }

            // Log the error with code for debugging
            //console.log("Response data:", response.data);
            ////console.error("API Error:", errorMessage, "Code:", errorCode);

            // Format user-friendly error message
            let displayMessage = errorMessage;

            // Customize message for admin users
            if (mxchatChat.is_admin) {
                // For admin users, show more technical details including error code
                displayMessage = errorMessage + (errorCode ? " (Error code: " + errorCode + ")" : "");
            }

            replaceLastMessage("bot", displayMessage);
        },
        error: function(xhr, status, error) {
            //console.error("AJAX Error:", status, error);
            //console.log("Response Text:", xhr.responseText);

            let errorMessage = "An unexpected error occurred.";

            // Try to parse the response if it's JSON
            try {
                const responseJson = JSON.parse(xhr.responseText);
                //console.log("Parsed error response:", responseJson);

                if (responseJson.data && responseJson.data.error_message) {
                    errorMessage = responseJson.data.error_message;
                } else if (responseJson.message) {
                    errorMessage = responseJson.message;
                }
            } catch (e) {
                // Not JSON or parsing failed, use HTTP status based messages
                if (xhr.status === 0) {
                    errorMessage = "Network error: Please check your internet connection.";
                } else if (xhr.status === 403) {
                    errorMessage = "Access denied: Your session may have expired. Please refresh the page.";
                } else if (xhr.status === 404) {
                    errorMessage = "API endpoint not found. Please contact support.";
                } else if (xhr.status === 429) {
                    errorMessage = "Too many requests. Please try again in a moment.";
                } else if (xhr.status >= 500) {
                    errorMessage = "Server error: The server encountered an issue. Please try again later.";
                }
            }

            replaceLastMessage("bot", errorMessage);
        }
    });
}

function callMxChatStream(message, callback) {
    //console.log("Using streaming for message:", message);
    
    const currentModel = mxchatChat.model || 'gpt-4o';
    if (!isStreamingSupported(currentModel)) {
        //console.log("Streaming not supported, falling back to regular call");
        callMxChat(message, callback);
        return;
    }

    // Get page context if contextual awareness is enabled
    const pageContext = getPageContext();

    const formData = new FormData();
    formData.append('action', 'mxchat_stream_chat');
    formData.append('message', message);
    formData.append('session_id', getChatSession());
    formData.append('nonce', mxchatChat.nonce);
    
    // Add page context if available
    if (pageContext) {
        formData.append('page_context', JSON.stringify(pageContext));
    }

    let accumulatedContent = '';
    let testingDataReceived = false;

    fetch(mxchatChat.ajax_url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        //console.log("Streaming response received:", response);
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        // Check if response is JSON instead of streaming
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            //console.log("Received JSON response instead of stream, handling as regular response");
            return response.json().then(data => {

                // FIXED: Always check for testing panel, not just in testing mode
                if (window.mxchatTestPanelInstance && data.testing_data) {
                    //console.log('Testing data found in streaming JSON response:', data.testing_data);
                    window.mxchatTestPanelInstance.handleTestingData(data.testing_data);
                }
                
                // Handle as regular JSON response
                $('.bot-message.temporary-message').remove();
                
                // Handle different response formats (including intent responses)
                if (data.text || data.html || data.message) {
                    if (data.text && data.html) {
                        replaceLastMessage("bot", data.text, data.html);
                    } else if (data.text) {
                        replaceLastMessage("bot", data.text);
                    } else if (data.html) {
                        replaceLastMessage("bot", "", data.html);
                    } else if (data.message) {
                        replaceLastMessage("bot", data.message);
                    }
                }
                
                // Handle other response properties
                if (data.chat_mode) {
                    updateChatModeIndicator(data.chat_mode);
                }
                
                if (data.data && data.data.filename) {
                    showActivePdf(data.data.filename);
                    activePdfFile = data.data.filename;
                }
                
                if (callback) {
                    callback(data.text || data.message || '');
                }
            });
        }

        // Continue with streaming processing
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        function processStream() {
            reader.read().then(({ done, value }) => {
                if (done) {
                    //console.log("Streaming completed, final content:", accumulatedContent);
                    if (callback) {
                        callback(accumulatedContent);
                    }
                    return;
                }

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const data = line.substring(6);

                        if (data === '[DONE]') {
                            //console.log("Received [DONE] signal");
                            if (callback) {
                                callback(accumulatedContent);
                            }
                            return;
                        }

                        try {
                            const json = JSON.parse(data);
                            
                            // FIXED: Always check for testing panel and handle testing data properly
                            if (json.testing_data && !testingDataReceived) {
                                //console.log('Testing data received in stream:', json.testing_data);
                                if (window.mxchatTestPanelInstance) {
                                    window.mxchatTestPanelInstance.handleTestingData(json.testing_data);
                                    testingDataReceived = true;
                                }
                            }
                            // Handle content streaming
                            else if (json.content) {
                                accumulatedContent += json.content;
                                updateStreamingMessage(accumulatedContent);
                            } 
                            // Handle errors
                            else if (json.error) {
                                //console.error("Streaming error:", json.error);
                                replaceLastMessage("bot", "Error: " + json.error);
                                return;
                            }
                        } catch (e) {
                            //console.error('Error parsing SSE data:', e, 'Data:', data);
                        }
                    }
                }

                processStream();
            });
        }

        processStream();
    })
    .catch(error => {
        //console.error('Streaming error:', error);
        callMxChat(message, callback);
    });
}


// Function to update message during streaming
function updateStreamingMessage(content) {
    const formattedContent = linkify(formatBoldText(convertNewlinesToBreaks(formatCodeBlocks(content))));

    // Find the temporary message
    const tempMessage = $('.bot-message.temporary-message').last();

    if (tempMessage.length) {
        // Update existing message
        tempMessage.html(formattedContent);
    } else {
        // Create new temporary message if it doesn't exist
        appendMessage("bot", content, '', [], true);
    }

}

// Function to check if streaming is supported for the current model
function isStreamingSupported(model) {
    if (!model) return false;

    //console.log("Checking streaming support for model:", model); // Debug log

    // Get the model prefix
    const modelPrefix = model.split('-')[0].toLowerCase();
    
    //console.log("Model prefix:", modelPrefix); // Debug log

    // Support streaming for OpenAI and Claude models
    const isSupported = modelPrefix === 'gpt' || modelPrefix === 'o1' || modelPrefix === 'claude';
    
    //console.log("Streaming supported:", isSupported); // Debug log
    
    return isSupported;
}

// Update the event handlers to use the correct function names
$('#send-button').off('click').on('click', function() {
    sendMessage(); // Use the updated sendMessage function
});

// Override enter key handler
$('#chat-input').off('keypress').on('keypress', function(e) {
    if (e.which == 13 && !e.shiftKey) {
        e.preventDefault();
        sendMessage(); // Use the updated sendMessage function
    }
});

    
    function appendMessage(sender, messageText = '', messageHtml = '', images = [], isTemporary = false) {
        try {
            // Determine styles based on sender type
            let messageClass, bgColor, fontColor;
    
            if (sender === "user") {
                messageClass = "user-message";
                bgColor = userMessageBgColor;
                fontColor = userMessageFontColor;
                // Only sanitize user input
                messageText = sanitizeUserInput(messageText);
            } else if (sender === "agent") {
                messageClass = "agent-message";
                bgColor = liveAgentMessageBgColor;
                fontColor = liveAgentMessageFontColor;
            } else {
                messageClass = "bot-message";
                bgColor = botMessageBgColor;
                fontColor = botMessageFontColor;
            }
    
            const messageDiv = $('<div>')
                .addClass(messageClass)
                .attr('dir', 'auto') // Add dir="auto" for automatic text direction
                .css({
                    'background': bgColor,
                    'color': fontColor,
                    'margin-bottom': '1em'
                });
    
            // Process the message content based on sender
            let fullMessage;
            if (sender === "user") {
                // For user messages, apply linkify after sanitization
                fullMessage = linkify(formatBoldText(convertNewlinesToBreaks(formatCodeBlocks(messageText))));
            } else {
                // For bot/agent messages, preserve HTML
                fullMessage = messageText;
            }
    
            // Add images if provided
            if (images && images.length > 0) {
                fullMessage += '<div class="image-gallery" dir="auto">'; // Add dir="auto" to image gallery
                images.forEach(img => {
                    // Ensure image URLs and titles are properly escaped
                    const safeTitle = sanitizeUserInput(img.title);
                    const safeUrl = encodeURI(img.image_url);
                    const safeThumbnail = encodeURI(img.thumbnail_url);
                    
                    fullMessage += `
                        <div style="margin-bottom: 10px;">
                            <strong>${safeTitle}</strong><br>
                            <a href="${safeUrl}" target="_blank">
                                <img src="${safeThumbnail}" alt="${safeTitle}" style="max-width: 100px; height: auto; margin: 5px;" />
                            </a>
                        </div>`;
                });
                fullMessage += '</div>';
            }
    
            // Append HTML content if provided
            if (messageHtml && sender !== "user") {
                fullMessage += '<br><br>' + messageHtml;
            }
    
            messageDiv.html(fullMessage);
    
            if (isTemporary) {
                messageDiv.addClass('temporary-message');
            }
    
            messageDiv.hide().appendTo('#chat-box').fadeIn(300, function() {
                if (sender === "bot") {
                    const lastUserMessage = $('#chat-box').find('.user-message').last();
                    if (lastUserMessage.length) {
                        scrollElementToTop(lastUserMessage);
                    }
                }
            });
    
            if (messageText.id) {
                lastSeenMessageId = messageText.id;
                hideNotification();
            }
        } catch (error) {
            //console.error("Error rendering message:", error);
        }
    }
    
    function replaceLastMessage(sender, responseText, responseHtml = '', images = []) {
        var messageClass = sender === "user" ? "user-message" : sender === "agent" ? "agent-message" : "bot-message";
        var lastMessageDiv = $('#chat-box').find('.bot-message.temporary-message, .agent-message.temporary-message').last();
    
        // Determine styles
        let bgColor, fontColor;
        if (sender === "user") {
            bgColor = userMessageBgColor;
            fontColor = userMessageFontColor;
        } else if (sender === "agent") {
            bgColor = liveAgentMessageBgColor;
            fontColor = liveAgentMessageFontColor;
        } else {
            bgColor = botMessageBgColor;
            fontColor = botMessageFontColor;
        }
    
        var fullMessage = linkify(formatBoldText(convertNewlinesToBreaks(formatCodeBlocks(responseText))));
        if (responseHtml) {
            fullMessage += '<br><br>' + responseHtml;
        }
    
        if (images.length > 0) {
            fullMessage += '<div class="image-gallery" dir="auto">'; // Add dir="auto" to image gallery
            images.forEach(img => {
                fullMessage += `
                    <div style="margin-bottom: 10px;">
                        <strong>${img.title}</strong><br>
                        <a href="${img.image_url}" target="_blank">
                            <img src="${img.thumbnail_url}" alt="${img.title}" style="max-width: 100px; height: auto; margin: 5px;" />
                        </a>
                    </div>`;
            });
            fullMessage += '</div>';
        }
    
        if (lastMessageDiv.length) {
            lastMessageDiv.fadeOut(200, function() {
                $(this)
                    .html(fullMessage)
                    .removeClass('bot-message user-message')
                    .addClass(messageClass)
                    .attr('dir', 'auto') // Add dir="auto" for automatic text direction
                    .css({
                        'background-color': bgColor,
                        'color': fontColor,
                    })
                    .removeClass('temporary-message')
                    .fadeIn(200, function() {
                        if (sender === "bot" || sender === "agent") {
                            const lastUserMessage = $('#chat-box').find('.user-message').last();
                            if (lastUserMessage.length) {
                                scrollElementToTop(lastUserMessage);
                            }
                            // Show notification if chat is hidden
                            if ($('#floating-chatbot').hasClass('hidden')) {
                                showNotification();
                            }
                        }
                    });
            });
        } else {
            appendMessage(sender, responseText, responseHtml, images);
        }
    }
    
    function appendThinkingMessage() {
        // Remove any existing thinking dots first
        $('.thinking-dots').remove();

        // Retrieve the bot message font color and background color
        var botMessageFontColor = mxchatChat.bot_message_font_color;
        var botMessageBgColor = mxchatChat.bot_message_bg_color;


        var thinkingHtml = '<div class="thinking-dots-container">' +
                           '<div class="thinking-dots">' +
                           '<span class="dot" style="background-color: ' + botMessageFontColor + ';"></span>' +
                           '<span class="dot" style="background-color: ' + botMessageFontColor + ';"></span>' +
                           '<span class="dot" style="background-color: ' + botMessageFontColor + ';"></span>' +
                           '</div>' +
                           '</div>';

        // Append the thinking dots to the chat container (or within the temporary message div)
        $("#chat-box").append('<div class="bot-message temporary-message" style="background-color: ' + botMessageBgColor + ';">' + thinkingHtml + '</div>');
        scrollToBottom();
    }
    
    function removeThinkingDots() {
        $('.thinking-dots').closest('.temporary-message').remove();
    }


    // ====================================
    // TEXT FORMATTING & PROCESSING
    // ====================================
    
    function linkify(inputText) {
        if (!inputText) return '';
        
        // Process markdown headers
        let processedText = formatMarkdownHeaders(inputText);
        
        // Process markdown links
        const markdownLinkPattern = /\[([^\]]+)\]\((https?:\/\/[^\s]+)\)/g;
        processedText = processedText.replace(markdownLinkPattern, (match, text, url) => {
            const safeUrl = encodeURI(url);
            const safeText = sanitizeUserInput(text);
            return `<a href="${safeUrl}" target="${linkTarget}">${safeText}</a>`;
        });
    
        // Process phone numbers (tel:)
        const phonePattern = /\[([^\]]+)\]\((tel:[\d+]+)\)/g;
        processedText = processedText.replace(phonePattern, (match, text, phone) => {
            const safePhone = encodeURI(phone);
            const safeText = sanitizeUserInput(text);
            return `<a href="${safePhone}">${safeText}</a>`;
        });
    
        // Process standalone URLs
        const urlPattern = /(^|[^">])(https?:\/\/[^\s<]+)/gim;
        processedText = processedText.replace(urlPattern, (match, prefix, url) => {
            const safeUrl = encodeURI(url);
            return `${prefix}<a href="${safeUrl}" target="${linkTarget}">${url}</a>`;
        });
    
        // Process www. URLs
        const wwwPattern = /(^|[^">])(www\.[\S]+(\b|$))(?![^<]*<\/a>)/gim;
        processedText = processedText.replace(wwwPattern, (match, prefix, url) => {
            const safeUrl = encodeURI(`http://${url}`);
            return `${prefix}<a href="${safeUrl}" target="${linkTarget}">${url}</a>`;
        });
    
        return processedText;
    }
    
    function formatMarkdownHeaders(text) {
        // Handle h1 to h6 headers
        return text.replace(/^(#{1,6})\s(.+)$/gm, function(match, hashes, content) {
            const level = hashes.length;
            return `<h${level} class="chat-heading">${content}</h${level}>`;
        });
    }
    
    function formatBoldText(text) {
        return text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    }
    
    function convertNewlinesToBreaks(text) {
        // Split the text into paragraphs (marked by double newlines or multiple <br> tags)
        const paragraphs = text.split(/(?:\n\n|\<br\>\s*\<br\>)/g);
        
        // Wrap each paragraph in <p> tags
        return paragraphs
            .map(para => `<p>${para.trim()}</p>`)
            .join('');
    }
    
    function formatCodeBlocks(text) {
        // First handle raw PHP tags
        text = text.replace(/(<\?php[\s\S]*?\?>)/g, (match) => {
            return `<pre><code class="language-php">${escapeHtml(match)}</code></pre>`;
        });
    
        // Then handle code blocks with backticks
        text = text.replace(/```php5?\n([\s\S]+?)```/gi, (match, code) => {
            return `<pre><code class="language-php">${escapeHtml(code)}</code></pre>`;
        });
    
        return text;
    }
    
    function sanitizeUserInput(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    
    function escapeHtml(unsafe) {
        // First check if it's already a code block
        if (unsafe.includes('<pre><code') || unsafe.includes('</code></pre>')) {
            return unsafe;
        }
        
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    function decodeHTMLEntities(text) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = text;
        return textArea.value;
    }


    // ====================================
    // UI & SCROLLING CONTROLS
    // ====================================
    
    function scrollToBottom(instant = false) {
        var chatBox = $('#chat-box');
        if (instant) {
            // Instantly set the scroll position to the bottom
            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        } else {
            // Use requestAnimationFrame for smoother scrolling if needed
            let start = null;
            const scrollHeight = chatBox.prop("scrollHeight");
            const initialScroll = chatBox.scrollTop();
            const distance = scrollHeight - initialScroll;
            const duration = 500; // Duration in ms
    
            function smoothScroll(timestamp) {
                if (!start) start = timestamp;
                const progress = timestamp - start;
                const currentScroll = initialScroll + (distance * (progress / duration));
                chatBox.scrollTop(currentScroll);
    
                if (progress < duration) {
                    requestAnimationFrame(smoothScroll);
                } else {
                    chatBox.scrollTop(scrollHeight); // Ensure it's exactly at the bottom
                }
            }
    
            requestAnimationFrame(smoothScroll);
        }
    }
    
    function scrollElementToTop(element) {
        var chatBox = $('#chat-box');
        var elementTop = element.position().top + chatBox.scrollTop();
        chatBox.animate({ scrollTop: elementTop }, 500);
    }
    
    function showChatWidget() {
        // First ensure display is set
        $('#floating-chatbot-button').css('display', 'flex');
        // Then handle the fade
        $('#floating-chatbot-button').fadeTo(500, 1);
        // Force visibility
        $('#floating-chatbot-button').removeClass('hidden');
        //console.log('Showing widget');
    }
    
    function hideChatWidget() {
        $('#floating-chatbot-button').css('display', 'none');
        $('#floating-chatbot-button').addClass('hidden');
        //console.log('Hiding widget');
    }
    
    function disableScroll() {
        if (isMobile()) {
            $('body').css('overflow', 'hidden');
        }
    }
    
    function enableScroll() {
        if (isMobile()) {
            $('body').css('overflow', '');
        }
    }
    
    function isMobile() {
        // This can be a simple check, or more sophisticated detection of mobile devices
        return window.innerWidth <= 768; // Example threshold for mobile devices
    }
    
    function setFullHeight() {
        var vh = $(window).innerHeight() * 0.01;
        $(':root').css('--vh', vh + 'px');
    }


    // ====================================
    // NOTIFICATION SYSTEM
    // ====================================
    
    function createNotificationBadge() {
        //console.log("Creating notification badge...");
        const chatButton = document.getElementById('floating-chatbot-button');
        //console.log("Chat button found:", !!chatButton);
        
        if (!chatButton) return;
    
        // Remove any existing badge first
        const existingBadge = chatButton.querySelector('.chat-notification-badge');
        if (existingBadge) {
            //console.log("Removing existing badge");
            existingBadge.remove();
        }
    
        notificationBadge = document.createElement('div');
        notificationBadge.className = 'chat-notification-badge';
        notificationBadge.style.cssText = `
            display: none;
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10001;
        `;
        chatButton.style.position = 'relative';
        chatButton.appendChild(notificationBadge);
        
    }
    
    function showNotification() {
        const badge = document.getElementById('chat-notification-badge');
        if (badge && $('#floating-chatbot').hasClass('hidden')) {
            badge.style.display = 'block';
            badge.textContent = '1';
        }
    }
    
    function hideNotification() {
        const badge = document.getElementById('chat-notification-badge');
        if (badge) {
            badge.style.display = 'none';
        }
    }
    
    function startNotificationChecking() {
        const chatPersistenceEnabled = mxchatChat.chat_persistence_toggle === 'on';
        if (!chatPersistenceEnabled) return;
    
        createNotificationBadge();
        notificationCheckInterval = setInterval(checkForNewMessages, 30000); // Check every 30 seconds
    }
    
    function stopNotificationChecking() {
        if (notificationCheckInterval) {
            clearInterval(notificationCheckInterval);
        }
    }
    
    function checkForNewMessages() {
        const sessionId = getChatSession();
        const chatPersistenceEnabled = mxchatChat.chat_persistence_toggle === 'on';
        
        if (!chatPersistenceEnabled) return;
    
        $.ajax({
            url: mxchatChat.ajax_url,
            type: 'POST',
            data: {
                action: 'mxchat_check_new_messages',
                session_id: sessionId,
                last_seen_id: lastSeenMessageId,
                nonce: mxchatChat.nonce
            },
            success: function(response) {
                if (response.success && response.data.hasNewMessages) {
                    showNotification();
                }
            }
        });
    }


    // ====================================
    // LIVE AGENT FUNCTIONALITY
    // ====================================
    
    function updateChatModeIndicator(mode) {
        const indicator = document.getElementById('chat-mode-indicator');
        if (indicator) {
            // For Live Agent, keep as is; for AI mode, use the customized text
            if (mode === 'agent') {
                indicator.textContent = 'Live Agent';
            } else {
                // Get the custom AI agent text from a data attribute we'll add to the element
                const customAiText = indicator.getAttribute('data-ai-text') || 'AI Agent';
                indicator.textContent = customAiText;
            }
        }
        // Start or stop polling based on mode
        if (mode === 'agent') {
            startPolling();
        } else {
            stopPolling();
        }
    }
    
    function startPolling() {
        // Clear any existing interval first
        stopPolling();
        // Start new polling interval
        pollingInterval = setInterval(checkForAgentMessages, 5000);
        //console.log("Started agent message polling");
    }
    
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
            //console.log("Stopped agent message polling");
        }
    }
    
function checkForAgentMessages() {
    const sessionId = getChatSession();
    $.ajax({
        url: mxchatChat.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'mxchat_fetch_new_messages',
            session_id: sessionId,
            last_seen_id: lastSeenMessageId,
            persistence_enabled: 'true', // Add this too
            nonce: mxchatChat.nonce
        },
        success: function (response) {
            if (response.success && response.data?.new_messages) {
                let hasNewMessage = false;
                
                response.data.new_messages.forEach(function (message) {
                    if (message.role === "agent" && !processedMessageIds.has(message.id)) {
                        hasNewMessage = true;
                        // CHANGE THIS LINE:
                        appendMessage("agent", message.content); // Instead of replaceLastMessage
                        lastSeenMessageId = message.id;
                        processedMessageIds.add(message.id);
                    }
                });

                if (hasNewMessage && $('#floating-chatbot').hasClass('hidden')) {
                    showNotification();
                }
                
                scrollToBottom(true);
            }
        },
        error: function (xhr, status, error) {
            //console.error("Polling error:", xhr, status, error);
        }
    });
}

    // ====================================
    // CHAT HISTORY & PERSISTENCE
    // ====================================
    
    function loadChatHistory() {
        var sessionId = getChatSession();
        var chatPersistenceEnabled = mxchatChat.chat_persistence_toggle === 'on';
    
        if (chatPersistenceEnabled && sessionId) {
            $.ajax({
                url: mxchatChat.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mxchat_fetch_conversation_history',
                    session_id: sessionId
                },
                success: function(response) {
                    if (response.success && response.data && Array.isArray(response.data.conversation)) {
    
    
                        var $chatBox = $('#chat-box');
                        var $fragment = $(document.createDocumentFragment());
                        let highestMessageId = lastSeenMessageId;
    
                        if (response.data.chat_mode) {
                            updateChatModeIndicator(response.data.chat_mode);
                        }
    
                        $.each(response.data.conversation, function(index, message) {
                            // Skip agent messages if persistence is off
                            if (!chatPersistenceEnabled && message.role === 'agent') {
                                return;
                            }
    
                            var messageClass, messageBgColor, messageFontColor;
    
                            switch (message.role) {
                                case 'user':
                                    messageClass = 'user-message';
                                    messageBgColor = userMessageBgColor;
                                    messageFontColor = userMessageFontColor;
                                    break;
                                case 'agent':
                                    messageClass = 'agent-message';
                                    messageBgColor = liveAgentMessageBgColor;
                                    messageFontColor = liveAgentMessageFontColor;
                                    break;
                                default:
                                    messageClass = 'bot-message';
                                    messageBgColor = botMessageBgColor;
                                    messageFontColor = botMessageFontColor;
                                    break;
                            }
    
                            var messageElement = $('<div>').addClass(messageClass)
                                .css({
                                    'background': messageBgColor,
                                    'color': messageFontColor
                                });
    
                            var content = message.content;
                            content = content.replace(/\\'/g, "'").replace(/\\"/g, '"');
                            content = decodeHTMLEntities(content);
    
                            if (content.includes("mxchat-product-card") || content.includes("mxchat-image-gallery")) {
                                messageElement.html(content);
                            } else {
                                var formattedContent = linkify(
                                    formatBoldText(
                                        convertNewlinesToBreaks(formatCodeBlocks(content))
                                    )
                                );
                                messageElement.html(formattedContent);
                            }
    
                            $fragment.append(messageElement);
    
                                // In loadChatHistory, change this part:
                                if (message.id) {
                                    highestMessageId = Math.max(highestMessageId, message.id);
                                    processedMessageIds.add(message.id); // Add all message IDs to processed set
                                }
                        });
    
                        $chatBox.append($fragment);
                        scrollToBottom(true);
    
                        if (response.data.conversation.length > 0) {
                            $('#mxchat-popular-questions').hide();
                        }
    
                        // Update lastSeenMessageId after history loads
                        lastSeenMessageId = highestMessageId;
    
                        // Only update chat mode if persistence is enabled
                        if (chatPersistenceEnabled && response.data.conversation.length > 0) {
                            var lastMessage = response.data.conversation[response.data.conversation.length - 1];
                            if (lastMessage.role === 'agent') {
                                updateChatModeIndicator('agent');
                            }
                        }
                    } else {
                        console.warn("No conversation history found.");
                    }
                },
                error: function(xhr, status, error) {
                    //console.error("Error loading chat history:", status, error);
                    appendMessage("bot", "Unable to load chat history.");
                }
            });
        } else {
            console.warn("Chat persistence is disabled or no session ID found. Not loading history.");
        }
    }


    // ====================================
    // FILE UPLOAD FUNCTIONALITY
    // ====================================
    
    function addSafeEventListener(elementId, eventType, handler) {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener(eventType, handler);
        }
    }
    
    function showActivePdf(filename) {
        const container = document.getElementById('active-pdf-container');
        const nameElement = document.getElementById('active-pdf-name');
        
        if (!container || !nameElement) {
            //console.error('PDF container elements not found');
            return;
        }
    
        nameElement.textContent = filename;
        container.style.display = 'flex';
    }
    
    function showActiveWord(filename) {
        const container = document.getElementById('active-word-container');
        const nameElement = document.getElementById('active-word-name');
        
        if (!container || !nameElement) {
            //console.error('Word document container elements not found');
            return;
        }
    
        nameElement.textContent = filename;
        container.style.display = 'flex';
    }
    
    function removeActivePdf() {
        const container = document.getElementById('active-pdf-container');
        const nameElement = document.getElementById('active-pdf-name');
        
        if (!container || !nameElement || !activePdfFile) return;
    
        fetch(mxchatChat.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'mxchat_remove_pdf',
                'session_id': sessionId,
                'nonce': mxchatChat.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.style.display = 'none';
                nameElement.textContent = '';
                activePdfFile = null;
                appendMessage('bot', 'PDF removed.');
            }
        })
        .catch(error => {
            //console.error('Error removing PDF:', error);
        });
    }
    
    function removeActiveWord() {
        const container = document.getElementById('active-word-container');
        const nameElement = document.getElementById('active-word-name');
        
        if (!container || !nameElement || !activeWordFile) return;
    
        fetch(mxchatChat.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'mxchat_remove_word',
                'session_id': sessionId,
                'nonce': mxchatChat.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.style.display = 'none';
                nameElement.textContent = '';
                activeWordFile = null;
                appendMessage('bot', 'Word document removed.');
            }
        })
        .catch(error => {
            //console.error('Error removing Word document:', error);
        });
    }
    
    // ====================================
    // CONSENT & COMPLIANCE (GDPR)
    // ====================================
    
    function initializeChatVisibility() {
        //console.log('Initializing chat visibility');
        const complianzEnabled = mxchatChat.complianz_toggle === 'on' || 
                                mxchatChat.complianz_toggle === '1' || 
                                mxchatChat.complianz_toggle === 1;
    
        if (complianzEnabled && typeof cmplz_has_consent === "function" && typeof complianz !== 'undefined') {
            // Initial check
            checkConsentAndShowChat();
    
            // Listen for consent changes
            $(document).on('cmplz_status_change', function(event) {
                //console.log('Status change detected');
                checkConsentAndShowChat();
            });
        } else {
            // If Complianz is not enabled, always show
            $('#floating-chatbot-button')
                .css('display', 'flex')
                .removeClass('hidden no-consent')
                .fadeTo(500, 1);
                
            // Also check pre-chat message when Complianz is not enabled
            checkPreChatDismissal();
        }
    }

    
    function checkConsentAndShowChat() {
        var consentStatus = cmplz_has_consent('marketing');
        var consentType = complianz.consenttype;
        
        //console.log('Checking consent:', {status: consentStatus,type: consentType});
    
        let $widget = $('#floating-chatbot-button');
        let $chatbot = $('#floating-chatbot');
        let $preChat = $('#pre-chat-message');
        
        if (consentStatus === true) {
            //console.log('Consent granted - showing widget');
            $widget
                .removeClass('no-consent')
                .css('display', 'flex')
                .removeClass('hidden')
                .fadeTo(500, 1);
            $chatbot.removeClass('no-consent');
            
            // Show pre-chat message if not dismissed
            checkPreChatDismissal();
        } else {
            //console.log('No consent - hiding widget');
            $widget
                .addClass('no-consent')
                .fadeTo(500, 0, function() {
                    $(this)
                        .css('display', 'none')
                        .addClass('hidden');
                });
            $chatbot.addClass('no-consent');
            
            // Hide pre-chat message when no consent
            $preChat.hide();
        }
    }


    // ====================================
    // PRE-CHAT MESSAGE HANDLING
    // ====================================
    
    function checkPreChatDismissal() {
        $.ajax({
            url: mxchatChat.ajax_url,
            type: 'POST',
            data: {
                action: 'mxchat_check_pre_chat_message_status',
                _ajax_nonce: mxchatChat.nonce
            },
            success: function(response) {
                if (response.success && !response.data.dismissed) {
                    $('#pre-chat-message').fadeIn(250);
                } else {
                    $('#pre-chat-message').hide();
                }
            },
            error: function() {
                //console.error('Failed to check pre-chat message dismissal status.');
            }
        });
    }
    
    function handlePreChatDismissal() {
        $('#pre-chat-message').fadeOut(200);
        $.ajax({
            url: mxchatChat.ajax_url,
            type: 'POST',
            data: {
                action: 'mxchat_dismiss_pre_chat_message',
                _ajax_nonce: mxchatChat.nonce
            },
            success: function() {
                $('#pre-chat-message').hide();
            },
            error: function() {
                //console.error('Failed to dismiss pre-chat message.');
            }
        });
    }


    // ====================================
    // UTILITY FUNCTIONS
    // ====================================
    
    function copyToClipboard(text) {
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(text).select();
        document.execCommand('copy');
        tempInput.remove();
    }

    
    function isImageHtml(str) {
        return str.startsWith('<img') && str.endsWith('>');
    }


    // ====================================
    // EVENT HANDLERS & INITIALIZATION
    // ====================================

    // Popular questions click handler
    $('.mxchat-popular-question').on('click', function () {
        var question = $(this).text(); // Get the text of the clicked question

        // Append the question as if the user typed it
        appendMessage("user", question);

        // Send the question to the server (backend)
        sendMessageToChatbot(question);
    });
    
    // Pre-chat message dismissal handlers
    $(document).on('click', '.close-pre-chat-message', function(e) {
        e.stopPropagation();
        handlePreChatDismissal();
    });
    
    // Chatbot visibility toggle handlers
    $(document).on('click', '#floating-chatbot-button', function() {
        var chatbot = $('#floating-chatbot');
        if (chatbot.hasClass('hidden')) {
            chatbot.removeClass('hidden').addClass('visible');
            $(this).addClass('hidden');
            $('#chat-notification-badge').hide(); // Hide notification when opening chat
            disableScroll();
            $('#pre-chat-message').fadeOut(250);
        } else {
            chatbot.removeClass('visible').addClass('hidden');
            $(this).removeClass('hidden');
            enableScroll();
            checkPreChatDismissal();
        }
    });
    
    $(document).on('click', '#exit-chat-button', function() {
        $('#floating-chatbot').addClass('hidden').removeClass('visible');
        $('#floating-chatbot-button').removeClass('hidden');
        enableScroll();
    });
    
    $(document).on('click', '.close-pre-chat-message', function(e) {
        e.stopPropagation(); // Prevent triggering the parent .pre-chat-message click
        $('#pre-chat-message').fadeOut(200, function() {
            $(this).remove();
        });
    });
    
    // Add to Cart button handler
    $(document).on('click', '.mxchat-add-to-cart-button', function() {
        var productId = $(this).data('product-id');
        
        // Get the button text instead of hardcoded "add to cart"
        var buttonText = $(this).text() || "add to cart";
        
        // Add a special prefix to indicate this is from button
        appendMessage("user", buttonText);
        sendMessageToChatbot("!addtocart");  // Special command to indicate button click
    });

    // PDF upload button handlers
    if (document.getElementById('pdf-upload-btn')) {
        document.getElementById('pdf-upload-btn').addEventListener('click', function() {
            document.getElementById('pdf-upload').click();
        });
    }
    
    // Word upload button handlers
    if (document.getElementById('word-upload-btn')) {
        document.getElementById('word-upload-btn').addEventListener('click', function() {
            document.getElementById('word-upload').click();
        });
    }
    
    // PDF file input change handler
    addSafeEventListener('pdf-upload', 'change', async function(e) {
        const file = e.target.files[0];
    
        if (!file || file.type !== 'application/pdf') {
            alert('Please select a valid PDF file.');
            return;
        }
    
        if (!sessionId) {
            //console.error('No session ID found');
            alert('Error: No session ID found');
            return;
        }
    
        if (!mxchatChat || !mxchatChat.ajax_url || !mxchatChat.nonce) {
            //console.error('mxchatChat not properly configured:', mxchatChat);
            alert('Error: Ajax configuration missing');
            return;
        }
    
        // Disable buttons and show loading state
        const uploadBtn = document.getElementById('pdf-upload-btn');
        const sendBtn = document.getElementById('send-button');
        const originalBtnContent = uploadBtn.innerHTML;
    
        try {
            const formData = new FormData();
            formData.append('action', 'mxchat_upload_pdf');
            formData.append('pdf_file', file);
            formData.append('session_id', sessionId);
            formData.append('nonce', mxchatChat.nonce);
    
            uploadBtn.disabled = true;
            sendBtn.disabled = true;
            uploadBtn.innerHTML = `<svg class="spinner" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
            </svg>`;
    
            const response = await fetch(mxchatChat.ajax_url, {
                method: 'POST',
                body: formData
            });
    
            const data = await response.json();
    
            if (data.success) {
                // Hide popular questions if they exist
                const popularQuestionsContainer = document.getElementById('mxchat-popular-questions');
                if (popularQuestionsContainer) {
                    popularQuestionsContainer.style.display = 'none';
                }
    
                // Show the active PDF name
                showActivePdf(data.data.filename);
                
                appendMessage('bot', data.data.message);
                scrollToBottom();
                activePdfFile = data.data.filename;
            } else {
                //console.error('Upload failed:', data.data);
                alert('Failed to upload PDF. Please try again.');
            }
        } catch (error) {
            //console.error('Upload error:', error);
            alert('Error uploading file. Please try again.');
        } finally {
            uploadBtn.disabled = false;
            sendBtn.disabled = false;
            uploadBtn.innerHTML = originalBtnContent;
            this.value = ''; // Reset file input
        }
    });
    
    // Word file input change handler
    addSafeEventListener('word-upload', 'change', async function(e) {
        const file = e.target.files[0];
    
        if (!file || file.type !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            alert('Please select a valid Word document (.docx).');
            return;
        }
    
        if (!sessionId) {
            //console.error('No session ID found');
            alert('Error: No session ID found');
            return;
        }
    
        // Disable buttons and show loading state
        const uploadBtn = document.getElementById('word-upload-btn');
        const sendBtn = document.getElementById('send-button');
        const originalBtnContent = uploadBtn.innerHTML;
    
        try {
            const formData = new FormData();
            formData.append('action', 'mxchat_upload_word');
            formData.append('word_file', file);
            formData.append('session_id', sessionId);
            formData.append('nonce', mxchatChat.nonce);
    
            uploadBtn.disabled = true;
            sendBtn.disabled = true;
            uploadBtn.innerHTML = `<svg class="spinner" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
            </svg>`;
    
            const response = await fetch(mxchatChat.ajax_url, {
                method: 'POST',
                body: formData
            });
    
            const data = await response.json();
    
            if (data.success) {
                // Hide popular questions if they exist
                const popularQuestionsContainer = document.getElementById('mxchat-popular-questions');
                if (popularQuestionsContainer) {
                    popularQuestionsContainer.style.display = 'none';
                }
    
                // Show the active Word document name
                showActiveWord(data.data.filename);
                
                appendMessage('bot', data.data.message);
                scrollToBottom();
                activeWordFile = data.data.filename;
            } else {
                //console.error('Upload failed:', data.data);
                alert('Failed to upload Word document. Please try again.');
            }
        } catch (error) {
            //console.error('Upload error:', error);
            alert('Error uploading file. Please try again.');
        } finally {
            uploadBtn.disabled = false;
            sendBtn.disabled = false;
            uploadBtn.innerHTML = originalBtnContent;
            this.value = ''; // Reset file input
        }
    });
    
    // Remove button click handlers
    document.getElementById('remove-pdf-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        removeActivePdf();
    });
    
    document.getElementById('remove-word-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        removeActiveWord();
    });
    
    // Window resize handlers
    $(window).on('resize orientationchange', function() {
        setFullHeight();
    });


    // ====================================
    // TOOLBAR & STYLING SETUP
    // ====================================
    
    // Apply toolbar settings
    if (mxchatChat.chat_toolbar_toggle === 'on') {
        $('.chat-toolbar').show();
    } else {
        $('.chat-toolbar').hide();
    }
    
    // Apply toolbar icon colors
    const toolbarElements = [
        '#mxchat-chatbot .toolbar-btn svg',
        '#mxchat-chatbot .active-pdf-name',
        '#mxchat-chatbot .active-word-name',
        '#mxchat-chatbot .remove-pdf-btn svg',
        '#mxchat-chatbot .remove-word-btn svg',
        '#mxchat-chatbot .toolbar-perplexity svg'
    ];
    
    toolbarElements.forEach(selector => {
        $(selector).css({
            'fill': toolbarIconColor,
            'stroke': toolbarIconColor,
            'color': toolbarIconColor
        });
    });


    // ====================================
    // EMAIL COLLECTION SETUP
    // ====================================
    
    // Email collection form setup and handlers
    const emailForm = document.getElementById('email-collection-form');
    const emailBlocker = document.getElementById('email-blocker');
    const chatbotWrapper = document.getElementById('chat-container');
    
    if (emailForm && emailBlocker && chatbotWrapper) {
        // Check if email exists for the current session
        function checkSessionAndEmail() {
            const sessionId = getChatSession();
            //console.log("[DEBUG JS] checkSessionAndEmail -> sessionId:", sessionId);
    
            fetch(mxchatChat.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'mxchat_check_email_provided',
                    session_id: sessionId,
                    nonce: mxchatChat.nonce,
                }),
            })
            .then((response) => response.json())
            .then((data) => {
                //console.log("[DEBUG JS] mxchat_check_email_provided response:", data);
                
                if (data.success) {
                    if (data.data.logged_in) {
                        //console.log("[DEBUG JS] User is logged in. Hiding email form.");
                        emailBlocker.style.display = 'none';
                        chatbotWrapper.style.display = 'flex';
                    } else if (data.data.email) {
                        //console.log("[DEBUG JS] Email found for session. Hiding email form.");
                        emailBlocker.style.display = 'none';
                        chatbotWrapper.style.display = 'flex';
                    } else {
                        //console.log("[DEBUG JS] No email provided. Showing email form.");
                        emailBlocker.style.display = 'flex';
                        chatbotWrapper.style.display = 'none';
                    }
                } else {
                    //console.log("[DEBUG JS] Error or no data received. Showing email form.");
                    emailBlocker.style.display = 'flex';
                    chatbotWrapper.style.display = 'none';
                }
            })
            .catch((error) => {
                // //console.error("[DEBUG JS] Fetch error -> forcing email form visible:", error);
                emailBlocker.style.display = 'flex';
                chatbotWrapper.style.display = 'none';
            });
        }
    
        // Handle email form submission
        emailForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const userEmail = document.getElementById('user-email').value;
            const sessionId = getChatSession();
    
            if (userEmail) {
                fetch(mxchatChat.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'mxchat_handle_save_email_and_response',
                        email: userEmail,
                        session_id: sessionId,
                        nonce: mxchatChat.nonce,
                    }),
                })
                .then((response) => response.json())
                .then((data) => {
                    //console.log('Backend response:', data);
                    if (data.success) {
                        //console.log('Email saved successfully:', userEmail);
                        emailBlocker.style.display = 'none';
                        chatbotWrapper.style.display = 'flex';
    
                        // Optionally handle bot response
                        if (data.message) {
                            appendMessage('bot', data.message);
                            scrollToBottom();
                        }
                    } else {
                        //console.error('Error saving email:', data.message || 'Unknown error');
                    }
                })
                .catch((error) => {
                    //console.error('AJAX error:', error);
                });
            }
        });
    
        // Check session and email status on page load
        checkSessionAndEmail();
    } else if (mxchatChat.email_collection_enabled) {
        // Only show error if email collection is enabled but elements are missing
        //console.error('Essential elements for email handling are missing.');
    }

    // Open chatbot when pre-chat message is clicked
    $(document).on('click', '#pre-chat-message', function() {
        var chatbot = $('#floating-chatbot');
        if (chatbot.hasClass('hidden')) {
            chatbot.removeClass('hidden').addClass('visible');
            $('#floating-chatbot-button').addClass('hidden');
            $('#pre-chat-message').fadeOut(250); // Hide pre-chat message
            disableScroll(); // Disable scroll when chatbot opens
        }
    });

    var closeButton = document.querySelector('.close-pre-chat-message');
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            $('#pre-chat-message').fadeOut(200); // Hide the message

            // Send an AJAX request to set the transient flag for 24 hours
            $.ajax({
                url: mxchatChat.ajax_url,
                type: 'POST',
                data: {
                    action: 'mxchat_dismiss_pre_chat_message',
                    _ajax_nonce: mxchatChat.nonce
                },
                success: function() {
                    //console.log('Pre-chat message dismissed for 24 hours.');

                    // Ensure the message is hidden after dismissal
                    $('#pre-chat-message').hide();
                },
                error: function() {
                    ////console.error('Failed to dismiss pre-chat message.');
                }
            });
        });
    }
    // ====================================
    // MAIN INITIALIZATION
    // ====================================
    
    if ($('#floating-chatbot').hasClass('hidden')) {
        $('#floating-chatbot-button').removeClass('hidden');
    }
    // Initialize when document is ready
    setFullHeight();
    initializeChatVisibility();
    loadChatHistory();

}); // End of jQuery ready

// ====================================
// GLOBAL EVENT LISTENERS (Outside jQuery)
// ====================================

// Event listener for copy button (code blocks)
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("mxchat-copy-button")) {
        const copyButton = e.target;
        const codeBlock = copyButton
            .closest(".mxchat-code-block-container")
            .querySelector(".mxchat-code-block code");

        if (codeBlock) {
            // Preserve formatting using innerText
            navigator.clipboard.writeText(codeBlock.innerText).then(() => {
                copyButton.textContent = "Copied!";
                copyButton.setAttribute("aria-label", "Copied to clipboard");

                setTimeout(() => {
                    copyButton.textContent = "Copy";
                    copyButton.setAttribute("aria-label", "Copy to clipboard");
                }, 2000);
            });
        }
    }
});






