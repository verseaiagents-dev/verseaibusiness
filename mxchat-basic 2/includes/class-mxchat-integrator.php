<?php
if (!defined('ABSPATH')) {
    exit;
}

class MxChat_Integrator {
    private $options;
    private $prompts_options;
    private $chat_count;
    private $fallbackResponse;
    private $productCardHtml;
    private $word_handler;
    private $last_similarity_analysis = null;


/**
 * Setup the cron jobs for rate limits with error handling
 */
public function setup_rate_limit_cron_jobs() {
    // Clear previous schedules
    wp_clear_scheduled_hook('mxchat_reset_rate_limits');
    wp_clear_scheduled_hook('mxchat_reset_hourly_rate_limits');
    wp_clear_scheduled_hook('mxchat_reset_daily_rate_limits');
    wp_clear_scheduled_hook('mxchat_reset_weekly_rate_limits');
    wp_clear_scheduled_hook('mxchat_reset_monthly_rate_limits');
    
    // Schedule the main rate limit reset check (runs hourly) with error handling
    if (!wp_next_scheduled('mxchat_reset_rate_limits')) {
        $result = wp_schedule_event(time(), 'hourly', 'mxchat_reset_rate_limits');
        
        // Log error but don't break the plugin
        if ($result === false) {
            //error_log('MxChat: Failed to schedule rate limit reset cron. Rate limits will still work but may not auto-reset.');
        }
    }
}

/**
 * Class constructor
 */
public function __construct() {
    $this->options = get_option('mxchat_options');
    $this->prompts_options = get_option('mxchat_prompts_options', array());
    $this->chat_count = get_option('mxchat_chat_count', 0);
    $this->word_handler = new MXChat_Word_Handler($this->options);
    
    // Setup the cron jobs for rate limits
    $this->setup_rate_limit_cron_jobs();
    
    // Add all action hooks
    add_action('wp_enqueue_scripts', array($this, 'mxchat_enqueue_scripts_styles'));
    add_action('wp_ajax_mxchat_handle_chat_request', array($this, 'mxchat_handle_chat_request'));
    add_action('wp_ajax_nopriv_mxchat_handle_chat_request', array($this, 'mxchat_handle_chat_request'));
    add_action('wp_ajax_mxchat_dismiss_pre_chat_message', array($this, 'mxchat_dismiss_pre_chat_message'));
    add_action('wp_ajax_nopriv_mxchat_dismiss_pre_chat_message', array($this, 'mxchat_dismiss_pre_chat_message'));
    
    // Add the AJAX actions for checking if the pre-chat message was dismissed
    add_action('wp_ajax_mxchat_check_pre_chat_message_status', array($this, 'mxchat_check_pre_chat_message_status'));
    add_action('wp_ajax_nopriv_mxchat_check_pre_chat_message_status', array($this, 'mxchat_check_pre_chat_message_status'));
    add_action('wp_ajax_mxchat_fetch_conversation_history', [$this, 'mxchat_fetch_conversation_history']);
    add_action('wp_ajax_nopriv_mxchat_fetch_conversation_history', [$this, 'mxchat_fetch_conversation_history']);
    add_action('wp_ajax_mxchat_add_to_cart', [$this, 'mxchat_add_to_cart']);
    add_action('wp_ajax_nopriv_mxchat_add_to_cart', [$this, 'mxchat_add_to_cart']);
    
    // Add REST API routes registration
    add_action('rest_api_init', array($this, 'register_routes'));
    add_action('wp_ajax_mxchat_fetch_new_messages', array($this, 'mxchat_fetch_new_messages'));
    add_action('wp_ajax_nopriv_mxchat_fetch_new_messages', array($this, 'mxchat_fetch_new_messages'));
    
    // Rate limit action - notice we removed the old schedule setup
    add_action('mxchat_reset_rate_limits', array($this, 'mxchat_reset_rate_limits'));
    
    // File upload and handling actions
    add_action('wp_ajax_mxchat_upload_pdf', [$this, 'handle_pdf_upload']);
    add_action('wp_ajax_nopriv_mxchat_upload_pdf', [$this, 'handle_pdf_upload']);
    add_action('wp_ajax_mxchat_remove_pdf', [$this, 'handle_pdf_remove']);
    add_action('wp_ajax_nopriv_mxchat_remove_pdf', [$this, 'handle_pdf_remove']);
    
    // Word document handling actions
    add_action('wp_ajax_mxchat_upload_word', array($this, 'mxchat_handle_word_upload'));
    add_action('wp_ajax_nopriv_mxchat_upload_word', array($this, 'mxchat_handle_word_upload'));
    add_action('wp_ajax_mxchat_remove_word', array($this, 'mxchat_handle_word_remove'));
    add_action('wp_ajax_nopriv_mxchat_remove_word', array($this, 'mxchat_handle_word_remove'));
    add_action('wp_ajax_mxchat_check_word_status', array($this, 'mxchat_check_word_status'));
    add_action('wp_ajax_nopriv_mxchat_check_word_status', array($this, 'mxchat_check_word_status'));
    
    // Email handling actions
    add_action('wp_ajax_nopriv_mxchat_handle_save_email_and_response', [$this, 'mxchat_handle_save_email_and_response']);
    add_action('wp_ajax_mxchat_handle_save_email_and_response', [$this, 'mxchat_handle_save_email_and_response']);
    add_action('wp_ajax_nopriv_mxchat_check_email_provided', [$this, 'mxchat_check_email_provided']);
    add_action('wp_ajax_mxchat_check_email_provided', [$this, 'mxchat_check_email_provided']);
    
    add_action('wp_ajax_mxchat_stream_chat', array($this, 'mxchat_handle_chat_request'));
    add_action('wp_ajax_nopriv_mxchat_stream_chat', array($this, 'mxchat_handle_chat_request'));
    
    // Testing panel AJAX actions
    add_action('wp_ajax_mxchat_get_system_info', array($this, 'mxchat_get_system_info'));
    add_action('wp_ajax_mxchat_get_similarity_threshold', array($this, 'mxchat_get_similarity_threshold'));
    add_action('wp_ajax_mxchat_get_kb_status', array($this, 'mxchat_get_kb_status'));
    add_action('wp_ajax_mxchat_start_fresh_session', array($this, 'mxchat_start_fresh_session'));

}


    private function mxchat_increment_chat_count() {
        $chat_count = get_option('mxchat_chat_count', 0);
        $chat_count++;
        update_option('mxchat_chat_count', $chat_count);
    }

function mxchat_fetch_conversation_history() {
    if (empty($_POST['session_id'])) {
        wp_send_json_error(['message' => esc_html__('Session ID missing.', 'mxchat')]);
        wp_die();
    }

    $session_id = sanitize_text_field($_POST['session_id']);
    $history = get_option("mxchat_history_{$session_id}", []); // Retrieve stored history
    $chat_mode = get_option("mxchat_mode_{$session_id}", 'ai'); // Get current chat mode

    if (empty($history)) {
        // Even if history is empty, return the chat mode
        wp_send_json_success([
            'conversation' => [],
            'chat_mode' => $chat_mode
        ]);
        wp_die();
    }

    wp_send_json_success([
        'conversation' => $history,
        'chat_mode' => $chat_mode
    ]);
    wp_die();
}

private function mxchat_fetch_conversation_history_for_ai($session_id) {
    $history = get_option("mxchat_history_{$session_id}", []);
    $formatted_history = [];

    // Adjusted for code-heavy conversations
    $max_tokens = 120000;    // Context window size
    $reserved_tokens = 5000; // Space for system prompts + current query
    $current_token_count = 0;

    // Allowed HTML tags for content sanitization
    $allowed_tags = [
        'pre' => ['class' => true],
        'code' => ['class' => true],
        'span' => ['class' => true],
        'div' => ['class' => true],
        'strong' => [],
        'em' => []
    ];

    foreach (array_reverse($history) as $entry) {
        // Preserve code blocks while sanitizing other HTML
        $clean_content = wp_kses($entry['content'], $allowed_tags);

        // Detect code blocks in content
        $has_code = false;
// Replace the HTML check with:
// Allow messages that contain code blocks or are plain text
if (strpos($clean_content, '<pre') === false &&
    strpos($clean_content, '<code') === false &&
    $clean_content !== strip_tags($entry['content'])) {
    continue;
}

        // Skip entries that lost significant content during sanitization
        if (!$has_code && $clean_content !== strip_tags($entry['content'])) {
            continue;
        }

        // More accurate token estimation (1 token ≈ 4 characters)
        $token_estimate = ceil(mb_strlen($clean_content, 'UTF-8') / 4);

        // Check token budget with the new estimate
        if (($current_token_count + $token_estimate + $reserved_tokens) > $max_tokens) {
            // Try to fit partial content if it's the first entry
            if (empty($formatted_history)) {
                $clean_content = mb_substr($clean_content, 0, ($max_tokens - $reserved_tokens) * 4);
                $token_estimate = ceil(mb_strlen($clean_content, 'UTF-8') / 4);
            } else {
                break;
            }
        }

        // Add to formatted history
        $formatted_history[] = [
            'role' => $entry['role'],
            'content' => $clean_content
        ];

        $current_token_count += $token_estimate;
    }

    // Reverse back to maintain chronological order
    $formatted_history = array_reverse($formatted_history);

    // Add system message about code context
    array_unshift($formatted_history, [
        'role' => 'system',
        'content' => 'Preserved code blocks are marked with [CODE BLOCK PRESERVED]. '
                    . 'Maintain formatting and syntax highlighting when referencing code.'
    ]);

    return $formatted_history;
}

public function register_routes() {
    //error_log(esc_html__('Registering MxChat REST routes', 'mxchat'));

    register_rest_route('mxchat/v1', '/stream', [
        'methods'  => 'GET',
        'callback' => [$this, 'mxchat_stream_events'],
        'permission_callback' => [$this, 'verify_chat_session'],
    ]);

    register_rest_route('mxchat/v1', '/agent-response', [
        'methods'  => 'POST',
        'callback' => [$this, 'mxchat_handle_agent_response'],
        'permission_callback' => [$this, 'verify_slack_request'],
    ]);

    register_rest_route('mxchat/v1', '/slack-interaction', [
        'methods'  => 'POST',
        'callback' => [$this, 'handle_slack_interaction'],
        'permission_callback' => [$this, 'verify_slack_request'],
    ]);
    
    register_rest_route('mxchat/v1', '/slack-messages', [
        'methods'  => 'POST',
        'callback' => [$this, 'handle_slack_messages'],
        'permission_callback' => [$this, 'verify_slack_request'],
    ]);

    //error_log(esc_html__('MxChat REST routes registered', 'mxchat'));
}

/**
 * Verify valid chat session
 */
public function verify_chat_session($request) {
    $session_id = $request->get_param('session_id');
    if (empty($session_id)) {
        //error_log(esc_html__('Empty session ID in chat request', 'mxchat'));
        return false;
    }

    $chat_mode = get_option("mxchat_mode_{$session_id}", 'ai');
    return $chat_mode === 'agent';
}

/**
 * Verify request is coming from Slack.
 *
 * @param WP_REST_Request $request
 * @return bool True if valid, false otherwise.
 */
public function verify_slack_request($request) {
    // Get the Slack signing secret from your plugin options
    $valid_key = $this->options['live_agent_secret_key'] ?? '';

    if (empty($valid_key)) {
        //error_log(esc_html__('Slack signing secret not configured', 'mxchat'));
        return false;
    }

    $timestamp = $request->get_header('X-Slack-Request-Timestamp');
    $slack_signature = $request->get_header('X-Slack-Signature');

    // Verify timestamp to prevent replay attacks
    if (abs(time() - intval($timestamp)) > 300) {
        //error_log(esc_html__('Slack request timestamp too old', 'mxchat'));
        return false;
    }

    // Get raw request body
    $request_body = file_get_contents('php://input');

    // Create the signature base string
    $sig_basestring = "v0:{$timestamp}:{$request_body}";

    // Calculate expected signature
    $my_signature = 'v0=' . hash_hmac('sha256', $sig_basestring, $valid_key);

    // Compare signatures
    return hash_equals($my_signature, $slack_signature);
}

public function mxchat_stream_events(WP_REST_Request $request) {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    $session_id = sanitize_text_field($request->get_param('session_id'));
    $last_seen_id = sanitize_text_field($request->get_param('last_seen_id')) ?: '';

    if (empty($session_id)) {
        echo esc_html__("event: error\ndata: ", 'mxchat') . esc_html__('Missing session_id', 'mxchat') . "\n\n";
        flush();
        exit;
    }

    $history = get_option("mxchat_history_{$session_id}", []);

    // Filter only new messages
    $new_messages = array_filter($history, function ($message) use ($last_seen_id) {
        return !empty($message['id']) && $message['id'] > $last_seen_id;
    });

    // Send new messages if available
    if (!empty($new_messages)) {
        echo esc_html__("event: newMessages\ndata: ", 'mxchat') . json_encode(array_values($new_messages)) . "\n\n";
    } else {
        // Keep the connection alive
        echo esc_html__("event: keepAlive\ndata: ", 'mxchat') . "{}\n\n";
    }
    flush();
    exit;
}




private function mxchat_save_chat_message($session_id, $role, $message) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';
    //error_log("[DEBUG] mxchat_save_chat_message -> START for session_id: {$session_id}, role: {$role}");
    
    // Check if this is the first message in a new session (before any other database operations)
    $is_new_session = false;
    if ($role === 'user') { // Only check for user messages, not bot responses
        $existing_messages = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE session_id = %s",
            $session_id
        ));
        $is_new_session = ($existing_messages == 0);
    }
    
    // 1) Extract agent name if present
    $agent_name = '';
    if (preg_match('/^Agent: (.*?) - /', $message, $matches)) {
        $agent_name = $matches[1];
        $message    = str_replace("Agent: $agent_name - ", '', $message);
        $session_meta_key = "mxchat_agent_name_{$session_id}";
        if (empty(get_option($session_meta_key))) {
            update_option($session_meta_key, $agent_name);
            //error_log("[DEBUG] mxchat_save_chat_message -> Stored agent_name in option: {$session_meta_key} => {$agent_name}");
        }
    }
    // 2) Generate unique message_id
    $message_id = uniqid();
    //error_log("[DEBUG] mxchat_save_chat_message -> Generated message_id: {$message_id}");
    // 3) Determine user_id
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    // 4) Determine user_identifier
    $user_identifier = $agent_name
        ? $agent_name
        : MxChat_User::mxchat_get_user_identifier();
    // 5) Determine displayed_name
    $user_email = MxChat_User::mxchat_get_user_email();
    $displayed_name = $agent_name ? $agent_name : ($user_email ?: $user_identifier);
    // 6) Check for a saved email in wp_options
    $email_option_key = "mxchat_email_{$session_id}";
    $saved_email = get_option($email_option_key);
    //error_log("[DEBUG] mxchat_save_chat_message -> Checking wp_options for email_option_key: {$email_option_key}, found: {$saved_email}");
    // If found, update DB user_email
    if ($saved_email) {
        $update_res = $wpdb->update(
            $table_name,
            ['user_email' => $saved_email],
            ['session_id' => $session_id],
            ['%s'],
            ['%s']
        );
        //error_log("[DEBUG] mxchat_save_chat_message -> Attempted DB user_email update for session_id {$session_id}. update_res: {$update_res}");
    }
    // 7) Save to session history in wp_options
    $history_key = "mxchat_history_{$session_id}";
    $history = get_option($history_key, []);
    $history[] = [
        'id' => $message_id,
        'role' => $role,
        'content' => $message,
        'timestamp' => round(microtime(true) * 1000),
        'agent_name' => $displayed_name,
    ];
    update_option($history_key, $history, 'no');
    //error_log("[DEBUG] mxchat_save_chat_message -> Updated session history in option: {$history_key}");
    // 8) Save the message to DB (INSERT)
    $insert_data = [
        'user_id'        => $user_id,
        'user_identifier'=> $user_identifier,
        'user_email'     => $saved_email ?: $user_email,
        'session_id'     => $session_id,
        'role'           => $role,
        'message'        => $message,
        'timestamp'      => current_time('mysql', 1),
    ];
    $wpdb->insert($table_name, $insert_data);
    //error_log("[DEBUG] mxchat_save_chat_message -> Inserted message into DB. row_id: {$wpdb->insert_id}, data: " . print_r($insert_data, true));
    
    // 9) Send notification email if this is the first user message in a new session
    if ($wpdb->insert_id && $is_new_session && $role === 'user') {
        $this->send_new_chat_notification($session_id, array(
            'identifier' => $user_identifier,
            'email' => $saved_email ?: $user_email,
            'ip' => $_SERVER['REMOTE_ADDR']
        ));
    }
    
    //error_log("[DEBUG] mxchat_save_chat_message -> END for session_id: {$session_id}");
    return $message_id;
}
private function send_new_chat_notification($session_id, $user_info = array()) {
    $options = get_option('mxchat_transcripts_options');
    
    // Check if notifications are enabled
    if (empty($options['mxchat_enable_notifications'])) {
        return false;
    }
    
    // Get notification email
    $to = !empty($options['mxchat_notification_email']) ? 
          $options['mxchat_notification_email'] : 
          get_option('admin_email');
    
    if (!is_email($to)) {
        return false;
    }
    
    // Prepare email content
    $subject = sprintf('[%s] New Chat Session Started', get_bloginfo('name'));
    
    $user_identifier = isset($user_info['identifier']) ? $user_info['identifier'] : 'Guest';
    $user_email = isset($user_info['email']) ? $user_info['email'] : 'Not provided';
    $user_ip = isset($user_info['ip']) ? $user_info['ip'] : $_SERVER['REMOTE_ADDR'];
    
    $message = sprintf(
        "A new chat session has started on your website.\n\n" .
        "Session ID: %s\n" .
        "User: %s\n" .
        "Email: %s\n" .
        "IP Address: %s\n" .
        "Time: %s\n\n" .
        "View transcripts: %s",
        $session_id,
        $user_identifier,
        $user_email,
        $user_ip,
        current_time('mysql'),
        admin_url('admin.php?page=mxchat-transcripts')
    );
    
    // Send email
    return wp_mail($to, $subject, $message);
}

public function mxchat_handle_save_email_and_response() {
    //error_log('[DEBUG] ---------- mxchat_handle_save_email_and_response START ----------');

    // Validate nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mxchat_chat_nonce')) {
        //error_log(esc_html__('[ERROR] Invalid nonce in mxchat_handle_save_email_and_response', 'mxchat'));
        wp_send_json_error(['message' => esc_html__('Invalid nonce.', 'mxchat')]);
        wp_die();
    }

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    //error_log("[DEBUG] handle_save_email_and_response -> session_id: {$session_id}, email: {$email}");

    if (empty($session_id) || empty($email)) {
        //error_log("[ERROR] Missing session_id or email: session_id={$session_id}, email={$email}");
        wp_send_json_error(['message' => esc_html__('Session ID or email is missing.', 'mxchat')]);
        wp_die();
    }

    // 1) Always store in wp_options
    $option_key = "mxchat_email_{$session_id}";
    update_option($option_key, $email);
    //error_log("[DEBUG] handle_save_email_and_response -> updated option: {$option_key} => {$email}");

    // 2) (Optional) Also store in DB if a row already exists
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';

    // Make sure we have a valid placeholder in prepare
    $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE session_id = %s", $session_id);
    $session_count = $wpdb->get_var($sql);

    //error_log("[DEBUG] handle_save_email_and_response -> session_count for {$session_id}: {$session_count} (SQL: {$sql})");

    if ($session_count) {
        // Update user_email if row(s) exist
        $update_sql = $wpdb->prepare(
            "UPDATE {$table_name} SET user_email = %s WHERE session_id = %s",
            $email,
            $session_id
        );
        $wpdb->query($update_sql);
        //error_log("[DEBUG] handle_save_email_and_response -> DB updated: {$update_sql}");
    } else {
        //error_log("[INFO] handle_save_email_and_response -> No DB entry for {$session_id}, so email is only in wp_options.");
    }

    // Provide success response
    $bot_message = __('Thanks for providing your email! You can continue chatting now.', 'mxchat');
    //error_log("[DEBUG] handle_save_email_and_response -> success, returning bot_message: {$bot_message}");
    wp_send_json_success(['message' => $bot_message]);
    wp_die();
}

public function mxchat_check_email_provided() {
    //error_log('[DEBUG] ---------- mxchat_check_email_provided START ----------');

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mxchat_chat_nonce')) {
        //error_log('[ERROR] Invalid nonce in mxchat_check_email_provided');
        wp_send_json_error(['message' => esc_html__('Invalid nonce', 'mxchat')]);
    }

    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
    if (empty($session_id)) {
        //error_log('[ERROR] No session ID provided in mxchat_check_email_provided');
        wp_send_json_error(['message' => esc_html__('No session ID provided', 'mxchat')]);
    }

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        //error_log("[DEBUG] User is logged in as {$current_user->user_email}");
        wp_send_json_success(['logged_in' => true, 'email' => $current_user->user_email]);
    }

    $option_key = "mxchat_email_{$session_id}";
    $stored_email = get_option($option_key, '');

    //error_log("[DEBUG] mxchat_check_email_provided -> Checking option: {$option_key}, found: {$stored_email}");

    if (!empty($stored_email)) {
        //error_log("[DEBUG] mxchat_check_email_provided -> Email found, returning success");
        wp_send_json_success(['email' => $stored_email]);
    } else {
        //error_log("[DEBUG] mxchat_check_email_provided -> No email found, returning error");
        wp_send_json_error(['message' => esc_html__('No email found', 'mxchat')]);
    }
}

public function mxchat_handle_chat_request() {
    global $wpdb;

    // NEW: Check if this is a streaming request
    $is_streaming = isset($_POST['action']) && $_POST['action'] === 'mxchat_stream_chat';
    
    // NEW: Set streaming headers if needed
    if ($is_streaming) {
        // Disable output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
    }

    // Check if MX Chat Moderation is active
    if (class_exists('MX_Chat_Moderation')) {
        // Get user email and IP
        $user_email = '';
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // If user is logged in, get their email
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;
        }

        // Create ban handler instance
        $ban_handler = new MX_Chat_Ban_Handler();

        // Check if user is banned by IP
        if ($ban_handler->check_ban($user_ip, 'ip')) {
            wp_send_json([
                'success' => false,
                'message' => esc_html__('Access denied. Your IP address has been banned.', 'mxchat'),
                'status' => 'banned'
            ]);
            wp_die();
        }

        // If user is logged in, also check email
        if (!empty($user_email) && $ban_handler->check_ban($user_email, 'email')) {
            wp_send_json([
                'success' => false,
                'message' => esc_html__('Access denied. Your email address has been banned.', 'mxchat'),
                'status' => 'banned'
            ]);
            wp_die();
        }
    }

    $this->fallbackResponse = ['text' => '', 'html' => '', 'images' => []];
    $this->productCardHtml = '';

    // Get the actual WordPress user ID if logged in
    $is_logged_in = is_user_logged_in();
    if ($is_logged_in) {
        $user_id = get_current_user_id(); // This will get the actual WordPress user ID
    } else {
        // For logged-out users, use your existing identifier method
        $user_id = $this->mxchat_get_user_identifier();
    }

    // Get and sanitize the user identifier
    $user_id = sanitize_key($user_id);

    // Check rate limit using new settings structure
    $rate_limit_result = $this->check_rate_limit();

    if ($rate_limit_result !== true) {
        wp_send_json([
            'success' => false,
            'message' => $rate_limit_result['message'],
            'status' => 'rate_limit_exceeded'
        ]);
        wp_die();
    }

    // Rest of your existing code...
    $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';

    if (empty($session_id)) {
        wp_send_json_error(esc_html__('Session ID is missing.', 'mxchat'));
        wp_die();
    }

    // Validate and sanitize the incoming message
    if (empty($_POST['message'])) {
        wp_send_json_error(esc_html__('No message received.', 'mxchat'));
        wp_die();
    }

    // NEW: Get page context if provided
    $page_context = null;
    if (isset($_POST['page_context']) && !empty($_POST['page_context'])) {
        $page_context_raw = stripslashes($_POST['page_context']);
        $page_context = json_decode($page_context_raw, true);
        
        // Validate page context structure
        if (is_array($page_context) && 
            isset($page_context['url']) && 
            isset($page_context['title']) && 
            isset($page_context['content'])) {
            
            // Sanitize page context
            $page_context['url'] = esc_url_raw($page_context['url']);
            $page_context['title'] = sanitize_text_field($page_context['title']);
            $page_context['content'] = wp_kses_post($page_context['content']);
        } else {
            $page_context = null;
        }
    }

    // Modify the message sanitization to preserve PHP tags in code blocks
    $allowed_tags = [
        'pre' => [],
        'code' => ['class' => true],
        'span' => ['class' => true],
        'div' => ['class' => true],
    ];

    // First preserve code blocks
    $message = preg_replace_callback('/<pre><code.*?>.*?<\/code><\/pre>/s', function($matches) {
        return htmlspecialchars_decode($matches[0]);
    }, $_POST['message']);

    // Then apply sanitization
    $message = wp_kses($message, $allowed_tags);

    // Preserve code blocks from markdown conversion
    $message = preg_replace('/```(\w+)?\s*([\s\S]+?)```/s', '<pre><code class="$1">$2</code></pre>', $message);

    // ===== SIMPLIFIED TESTING PANEL INITIALIZATION =====
    // Always initialize testing data for admins (no toggle needed)
    $testing_data = null;
    if (current_user_can('administrator')) {
        $testing_data = [
            'query' => $message,
            'timestamp' => time(),
            'top_matches' => [],
            'action_matches' => [], // NEW: Initialize action matches array
            'page_context' => $page_context // NEW: Include page context in testing data
        ];
        
        // Get similarity threshold
        $similarity_threshold = isset($this->options['similarity_threshold']) 
            ? ((int) $this->options['similarity_threshold']) / 100 
            : 0.75;
        
        $testing_data['similarity_threshold'] = $similarity_threshold;
        
        // Determine knowledge base type
        $addon_options = get_option('mxchat_pinecone_addon_options', array());
        $use_pinecone = (isset($addon_options['mxchat_use_pinecone']) && $addon_options['mxchat_use_pinecone'] === '1');
        $testing_data['knowledge_base_type'] = $use_pinecone ? 'Pinecone' : 'WordPress Database';
    }
    // ===== END SIMPLIFIED TESTING INITIALIZATION =====

    // Check if any add-ons want to pre-process this message (for web search etc.)
    $pre_processed_result = apply_filters('mxchat_pre_process_message', $message, $user_id, $session_id);

    // If the pre-processing returned a result (not the original message), use it directly
    if (is_array($pre_processed_result) && isset($pre_processed_result['text'])) {
        // Save the AI response
        $this->mxchat_save_chat_message($session_id, 'bot', $pre_processed_result['text']);
        
        // Save HTML content if provided
        if (!empty($pre_processed_result['html'])) {
            $this->mxchat_save_chat_message($session_id, 'bot', $pre_processed_result['html']);
        }
        
        // Add testing data if admin
        $response_data = [
            'text' => $pre_processed_result['text'],
            'html' => $pre_processed_result['html'] ?? '',
            'session_id' => $session_id
        ];
        
        if ($testing_data !== null) {
            $response_data['testing_data'] = $testing_data;
        }
        
        wp_send_json($response_data);
        wp_die();
    }

    // Save the user's message
    $this->mxchat_save_chat_message($session_id, 'user', $message);

    // Check if the message is an email address
    if (is_email($message)) {
        // Add the email to Loops
        $this->add_email_to_loops($message);
    
        // Send success response
        $response_message = $this->options['email_capture_response'] ??
                           esc_html__('Thank you! Your coupon is on the way!', 'mxchat');
    
        // Clear streaming headers if they were set
        if ($is_streaming) {
            header_remove('Content-Type');
            header_remove('Cache-Control');
            header_remove('Connection');
            header_remove('X-Accel-Buffering');
            header('Content-Type: application/json');
        }
    
        $email_response = [
            'success' => true,
            'status' => 'email_captured',
            'message' => $response_message
        ];
        
        if ($testing_data !== null) {
            $email_response['testing_data'] = $testing_data;
        }
        
        wp_send_json($email_response);
        wp_die();
    }

    $intent_info = '';

    // Check chat mode
    $chat_mode = get_option("mxchat_mode_{$session_id}", 'ai');

    // Handle agent mode
// Handle agent mode
    if ($chat_mode === 'agent') {
        // First, check for switch intent before doing anything else
        $intent_matched = $this->mxchat_check_intent_and_invoke_callback($message, $user_id, $session_id);

        // NEW: Capture action analysis for testing panel after intent check
        if ($testing_data !== null && isset($this->last_action_analysis) && !empty($this->last_action_analysis)) {
            $testing_data['action_matches'] = $this->last_action_analysis;
        }

        // If we matched an intent and it's the switch intent, handle it
        if ($intent_matched && !empty($this->fallbackResponse['text'])) {
            // Update chat mode first
            update_option("mxchat_mode_{$session_id}", 'ai');

            // Clear any existing PDF context to start fresh
            $this->clear_pdf_transients($session_id);

            // Prepare clean switch response
            $response_data = [
                'text' => $this->fallbackResponse['text'],
                'html' => '',
                'session_id' => $session_id,
                'chat_mode' => 'ai'
            ];

            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
            }

            // Save the mode switch message
            $this->mxchat_save_chat_message($session_id, 'system', esc_html__('Switched to AI chat mode', 'mxchat'));
            $this->mxchat_save_chat_message($session_id, 'bot', $this->fallbackResponse['text']);

            // Send response and exit
            wp_send_json($response_data);
            wp_die();
        } elseif (!$intent_matched) {
            // No intent matched, handle live agent message
            try {
                $this->mxchat_send_user_message_to_agent($message, $user_id, $session_id);

                $agent_response = [
                    'status' => 'waiting_for_agent',
                    'message' => esc_html__('Message sent to live agent.', 'mxchat')
                ];
                
                if ($testing_data !== null) {
                    $agent_response['testing_data'] = $testing_data;
                }

                wp_send_json_success($agent_response);
            } catch (\Exception $e) {
                wp_send_json_error(esc_html__('Failed to send message to agent', 'mxchat'));
            }
            wp_die();
        }
    }

    // Step 1: Check for new PDF URL in the message
    if (preg_match('/https?:\/\/[^\s"]+/i', $message, $matches)) {
        $new_pdf_url = $matches[0];

        // Check if this is likely a PDF-related request
        $pdf_keywords = ['pdf', 'document', 'read', 'analyze'];
        $is_pdf_request = false;

        foreach ($pdf_keywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                $is_pdf_request = true;
                break;
            }
        }

        // If it looks like a PDF request or we're waiting for a PDF URL
        if ($is_pdf_request || get_transient('mxchat_waiting_for_pdf_url_' . $session_id)) {
            // Validate HTTPS
            if (wp_http_validate_url($new_pdf_url) && parse_url($new_pdf_url, PHP_URL_SCHEME) === 'https') {
                // Extract filename from URL
                $pdf_filename = basename(parse_url($new_pdf_url, PHP_URL_PATH));

                // Clear previous PDF transients
                $this->clear_pdf_transients($session_id);

                // Process new PDF
                $max_pages = $this->options['pdf_max_pages'] ?? 69;
                $embeddings = $this->fetch_and_split_pdf_pages($new_pdf_url, $max_pages);

                if ($embeddings === 'too_many_pages') {
                    $error_text = sprintf(
                        $this->options['pdf_intent_error_text'] ??
                        esc_html__("The provided PDF exceeds the maximum allowed limit of %d pages. Please provide a smaller document.", 'mxchat'),
                        $max_pages
                    );
                    $this->fallbackResponse['text'] = $error_text;
                } elseif ($embeddings) {
                    // Store new PDF information
                    $pdf_filename = basename(parse_url($new_pdf_url, PHP_URL_PATH));

                    // If the filename is generic, create a more descriptive one
                    if (in_array($pdf_filename, ['results_download.php', 'download.php', 'view.php', 'pdf.php']) ||
                        strpos($pdf_filename, '.php') !== false) {
                        $pdf_filename = 'Document_' . date('Y-m-d_H-i') . '.pdf';
                    }

                    set_transient('mxchat_pdf_url_' . $session_id, $new_pdf_url, HOUR_IN_SECONDS);
                    set_transient('mxchat_pdf_filename_' . $session_id, $pdf_filename, HOUR_IN_SECONDS);
                    set_transient('mxchat_pdf_embeddings_' . $session_id, $embeddings, HOUR_IN_SECONDS);
                    set_transient('mxchat_include_pdf_in_context_' . $session_id, true, HOUR_IN_SECONDS);

                    $success_text = $this->options['pdf_intent_success_text'] ??
                        esc_html__("I've processed the new PDF '{$pdf_filename}'. What questions do you have about it?", 'mxchat');

                    $pdf_response = [
                        'success' => true,
                        'message' => $success_text,
                        'data' => [
                            'filename' => $pdf_filename
                        ]
                    ];
                    
                    if ($testing_data !== null) {
                        $pdf_response['testing_data'] = $testing_data;
                    }

                    wp_send_json($pdf_response);
                    wp_die();
                } else {
                    $error_text = $this->options['pdf_intent_error_text'] ??
                        esc_html__("Sorry, I couldn't process the PDF. Please ensure it's a valid file.", 'mxchat');
                    $this->fallbackResponse['text'] = $error_text;
                }

                $pdf_error_response = [
                    'success' => false,
                    'message' => $this->fallbackResponse['text']
                ];
                
                if ($testing_data !== null) {
                    $pdf_error_response['testing_data'] = $testing_data;
                }

                wp_send_json($pdf_error_response);
                wp_die();
            }
        }
    }

    // Check if there's an active recommendation flow session
    $flow_state = get_option("mxchat_sr_flow_state_{$session_id}", array());
    if (!empty($flow_state) && isset($flow_state['flow_id'])) {
        // Create a dummy intent object that matches the original intent
        $dummy_intent = new stdClass();
        $dummy_intent->intent_label = 'Recommendation Flow ' . $flow_state['flow_id'];
        $dummy_intent->phrases = ''; // Empty phrases to avoid matching the original trigger
        
        // Call the recommendation flow handler directly
        $response_data = apply_filters('mxchat_sr_recommendation_flow', false, $message, $user_id, $session_id, $dummy_intent);
        
        // If the handler returned a response, send it
        if (is_array($response_data) && (isset($response_data['text']) || isset($response_data['html']))) {
            // Save the bot's response to the chat history
            if (!empty($response_data['text'])) {
                $this->mxchat_save_chat_message($session_id, 'bot', $response_data['text']);
            }
            if (!empty($response_data['html'])) {
                $this->mxchat_save_chat_message($session_id, 'bot', $response_data['html']);
            }
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
            }
            
            // Send the response
            wp_send_json($response_data);
            wp_die();
        }
    }        

    // Step 2: Detect intent and handle intent-based responses
    $intent_result = $this->mxchat_check_intent_and_invoke_callback($message, $user_id, $session_id);

    // NEW: Capture action analysis for testing panel after intent check
    if ($testing_data !== null && isset($this->last_action_analysis) && !empty($this->last_action_analysis)) {
        $testing_data['action_matches'] = $this->last_action_analysis;
    }

    // Step 3: Handle the intent result appropriately
    if ($intent_result !== false) {
        // Intent was matched - ALWAYS send as JSON response, never streaming
        
        if (is_array($intent_result) && (isset($intent_result['text']) || isset($intent_result['html']))) {
            // Intent returned a direct response array
            $response_data = [
                'text' => $intent_result['text'] ?? '',
                'html' => $intent_result['html'] ?? '',
                'session_id' => $session_id
            ];
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
            }
            
            // Clear streaming headers if they were set
            if ($is_streaming) {
                header_remove('Content-Type');
                header_remove('Cache-Control');
                header_remove('Connection');
                header_remove('X-Accel-Buffering');
                header('Content-Type: application/json');
            }
            
            wp_send_json($response_data);
            wp_die();
        } 
        else if ($intent_result === true && (!empty($this->fallbackResponse['text']) || !empty($this->fallbackResponse['html']))) {
            // Intent returned true and set fallbackResponse
            $response_data = [
                'text' => $this->fallbackResponse['text'] ?? '',
                'html' => $this->fallbackResponse['html'] ?? '',
                'session_id' => $session_id
            ];
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
            }
            
            // Clear streaming headers if they were set
            if ($is_streaming) {
                header_remove('Content-Type');
                header_remove('Cache-Control');
                header_remove('Connection');
                header_remove('X-Accel-Buffering');
                header('Content-Type: application/json');
            }
            
            wp_send_json($response_data);
            wp_die();
        }
    }

    // If we get here, no intent matched OR the intent didn't provide a usable response
    
    // Step 4: Generate AI response
    $conversation_history = $this->mxchat_fetch_conversation_history_for_ai($session_id);
    $this->mxchat_increment_chat_count();
    
    // Generate embedding for the user's query
    $user_message_embedding = $this->mxchat_generate_embedding($message, $this->options['api_key']);
    
    // Check if the embedding generation returned an error
    if (is_array($user_message_embedding) && isset($user_message_embedding['error'])) {
        $error_message = $user_message_embedding['error'];
        $error_code = $user_message_embedding['error_code'] ?? 'embedding_error';
        
        wp_send_json_error([
            'error_message' => $error_message,
            'error_code' => $error_code
        ]);
        wp_die();
    }
    
    // Check if the embedding is valid
    if (!is_array($user_message_embedding) || empty($user_message_embedding)) {
        wp_send_json_error([
            'error_message' => esc_html__('Unable to process your message. The embedding service is not responding correctly.', 'mxchat'),
            'error_code' => 'invalid_embedding'
        ]);
        wp_die();
    }

    // Build context with both knowledge base and PDF content if available
    $context_content = "User asked: '{$message}'\n\n";

    // NEW: Add page context if available and contextual awareness is enabled
    if ($page_context && isset($this->options['contextual_awareness_toggle']) && $this->options['contextual_awareness_toggle'] === 'on') {
        $context_content .= "===== CURRENT PAGE CONTEXT =====\n";
        $context_content .= "Page URL: " . $page_context['url'] . "\n";
        $context_content .= "Page Title: " . $page_context['title'] . "\n";
        $context_content .= "Page Content: " . $page_context['content'] . "\n";
        $context_content .= "===== END CURRENT PAGE CONTEXT =====\n\n";
    }

    // Get relevant content from knowledge base - THIS IS WHERE THE SIMILARITY ANALYSIS HAPPENS
    $relevant_content = $this->mxchat_find_relevant_content($user_message_embedding);
    
    // ===== CAPTURE REAL SIMILARITY DATA FOR ADMINS =====
    if ($testing_data !== null && $this->last_similarity_analysis !== null) {
        // Update testing data with the REAL similarity analysis
        $testing_data['top_matches'] = $this->last_similarity_analysis['top_matches'];
        $testing_data['total_documents_checked'] = $this->last_similarity_analysis['total_checked'] ?? 0;
        $testing_data['knowledge_base_type'] = $this->last_similarity_analysis['knowledge_base_type'];
    }
    // ===== END SIMILARITY DATA CAPTURE =====
    
    if (!empty($relevant_content)) {
        $context_content .= "===== OFFICIAL KNOWLEDGE DATABASE CONTENT =====\n" . $relevant_content . "\n===== END OF OFFICIAL KNOWLEDGE DATABASE CONTENT =====\n\n";
    } else {
        $context_content .= "===== NO RELEVANT CONTENT FOUND IN KNOWLEDGE DATABASE =====\n";
    }

    // Check for and include PDF content
    $pdf_url = get_transient('mxchat_pdf_url_' . $session_id);
    $pdf_embeddings = get_transient('mxchat_pdf_embeddings_' . $session_id);
    $pdf_filename = get_transient('mxchat_pdf_filename_' . $session_id);
    if ($pdf_url && $pdf_embeddings && get_transient('mxchat_include_pdf_in_context_' . $session_id)) {
        $relevant_pdf_pages = $this->find_relevant_pdf_pages($user_message_embedding, $pdf_embeddings);
        if (!empty($relevant_pdf_pages)) {
            $context_content .= "Relevant content from PDF document '{$pdf_filename}':\n";
            foreach ($relevant_pdf_pages as $page_data) {
                $context_content .= "Page {$page_data['page_number']} of '{$pdf_filename}': {$page_data['text']}\n";
            }
            $context_content .= "\n";
        }
    }

    // Check for and include Word content
    $word_url = get_transient('mxchat_word_url_' . $session_id);
    $word_embeddings = get_transient('mxchat_word_embeddings_' . $session_id);
    $word_filename = get_transient('mxchat_word_filename_' . $session_id);
    if ($word_url && $word_embeddings && get_transient('mxchat_include_word_in_context_' . $session_id)) {
        $relevant_word_chunks = $this->word_handler->mxchat_find_relevant_word_chunks($user_message_embedding, $word_embeddings);
        if (!empty($relevant_word_chunks)) {
            $context_content .= "Relevant content from Word document '{$word_filename}':\n";
            foreach ($relevant_word_chunks as $chunk_data) {
                $context_content .= "Section {$chunk_data['chunk_number']} of '{$word_filename}': {$chunk_data['text']}\n";
            }
            $context_content .= "\n";
        }
    }
    
    $context_content = apply_filters('mxchat_prepare_context', $context_content, $session_id);

    // Generate response
    $response = $this->mxchat_generate_response(
        $context_content,
        $this->options['api_key'],
        $this->options['xai_api_key'],
        $this->options['claude_api_key'],
        $this->options['deepseek_api_key'],
        $this->options['gemini_api_key'],
        $conversation_history,
        $is_streaming,
        $session_id,
        $testing_data
    );
    
    // Handle streaming vs non-streaming responses
    if ($is_streaming) {
        // Check if streaming actually happened or if it fell back to regular response
        if ($response === true) {
            wp_die();
        }
        // If we get here, streaming fell back to regular response, continue
    }
        
    // Check if the response is an error array
    if (is_array($response) && isset($response['error'])) {
        wp_send_json_error([
            'error_message' => $response['error'],
            'error_code' => $response['error_code'] ?? 'api_error'
        ]);
        wp_die();
    }
    
    // If we get here, the response is valid text
    $this->mxchat_save_chat_message($session_id, 'bot', $response);

    // Step 5: Save additional content if available
    if (!empty($this->productCardHtml)) {
        $this->mxchat_save_chat_message($session_id, 'bot', $this->productCardHtml);
    }

    if (!empty($this->fallbackResponse['html'])) {
        $this->mxchat_save_chat_message($session_id, 'bot', $this->fallbackResponse['html']);
    }

    // Step 6: Return the response
    $response_data = [
        'text' => $response,
        'html' => !empty($this->productCardHtml) ? $this->productCardHtml : ($this->fallbackResponse['html'] ?? ''),
        'session_id' => $session_id
    ];

    // Always add testing data for admins (no toggle needed)
    if ($testing_data !== null) {
        $response_data['testing_data'] = $testing_data;
    }

    wp_send_json($response_data);
    wp_die();
}


// Updated function to check intents and invoke the callback function
private function mxchat_check_intent_and_invoke_callback($message, $user_id, $session_id) {
    global $wpdb;
    $chat_mode = get_option("mxchat_mode_{$session_id}", 'ai');
    
    // Generate the user embedding
    $user_embedding = $this->mxchat_generate_embedding($message, $this->options['api_key']);
    
    // Check if embedding generation returned an error
    if (is_array($user_embedding) && isset($user_embedding['error'])) {
        $error_message = $user_embedding['error'];
        $error_code = $user_embedding['error_code'] ?? 'embedding_error';
        
        wp_send_json_error([
            'error_message' => $error_message,
            'error_code' => $error_code
        ]);
        wp_die();
    }
    
    // Check if embedding is valid
    if (!is_array($user_embedding) || empty($user_embedding)) {
        wp_send_json_error([
            'error_message' => esc_html__('Unable to process your message. The embedding service is not responding correctly.', 'mxchat'),
            'error_code' => 'invalid_embedding'
        ]);
        wp_die();
    }
    
    // Fetch intents from the database
    $table_name = $wpdb->prefix . 'mxchat_intents';
    if ($chat_mode === 'agent') {
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE callback_function = %s AND (enabled = 1 OR enabled IS NULL)",
            'mxchat_handle_switch_to_chatbot_intent'
        );
        $intents = $wpdb->get_results($query);
    } else {
        $intents = $wpdb->get_results("SELECT * FROM $table_name WHERE enabled = 1 OR enabled IS NULL");
    }
    
    if (empty($intents)) {
        return false;
    }
    
    $highest_similarity = -INF;
    $matched_intent = null;
    
    // NEW: Array to store action analysis for testing panel
    $action_analysis = [];
    
    foreach ($intents as $intent) {
        // Additional check for enabled state
        $is_enabled = isset($intent->enabled) ? (bool)$intent->enabled : true;
        if (!$is_enabled) {
            continue;
        }
        
        $intent_embedding_serialized = $intent->embedding_vector;
        $intent_embedding = $intent_embedding_serialized
            ? unserialize($intent_embedding_serialized, ['allowed_classes' => false])
            : null;
            
        if (!is_array($intent_embedding)) {
            continue;
        }
        
        $similarity = $this->mxchat_calculate_cosine_similarity($user_embedding, $intent_embedding);
        $intent_threshold = isset($intent->similarity_threshold) ? $intent->similarity_threshold : 0.85;
        
        // NEW: Store action analysis data for testing panel
        $action_analysis[] = [
            'intent_label' => $intent->intent_label,
            'callback_function' => $intent->callback_function,
            'similarity' => round($similarity, 4),
            'similarity_percentage' => round($similarity * 100, 2),
            'threshold' => $intent_threshold,
            'threshold_percentage' => round($intent_threshold * 100, 2),
            'above_threshold' => $similarity >= $intent_threshold,
            'triggered' => false // Will be updated below if this intent is triggered
        ];
        
        if ($similarity >= $intent_threshold && $similarity > $highest_similarity) {
            $highest_similarity = $similarity;
            $matched_intent = $intent;
        }
    }
    
    // NEW: Mark the triggered action if any
    if ($matched_intent) {
        foreach ($action_analysis as &$action) {
            if ($action['intent_label'] === $matched_intent->intent_label) {
                $action['triggered'] = true;
                break;
            }
        }
    }
    
    // NEW: Sort actions by similarity (highest first) and store for testing panel
    usort($action_analysis, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    // Store action analysis for testing panel capture
    $this->last_action_analysis = $action_analysis;
    
    if ($matched_intent) {
        // If the callback is a method on this instance (core callback), call it directly
        if (method_exists($this, $matched_intent->callback_function)) {
            $callback_result = call_user_func(
                [$this, $matched_intent->callback_function],
                $message,
                $user_id,
                $session_id,
                $matched_intent,
                $user_context ?? null
            );
        } else {
            // Otherwise, use apply_filters for add-on callbacks
            $callback_result = apply_filters(
                $matched_intent->callback_function, 
                false,  // default return value
                $message, 
                $user_id, 
                $session_id,
                $matched_intent
            );
        }
        
        if ($callback_result !== false) {
            $this->fallbackResponse = $callback_result;
            return true;
        }
    }
    
    return false;
}

// Helper function to clear PDF and Word document related transients
private function clear_pdf_transients($session_id) {
    // PDF transients
    delete_transient('mxchat_pdf_url_' . $session_id);
    delete_transient('mxchat_pdf_embeddings_' . $session_id);
    delete_transient('mxchat_include_pdf_in_context_' . $session_id);
    delete_transient('mxchat_waiting_for_pdf_url_' . $session_id);

    // Word document transients
    delete_transient('mxchat_word_url_' . $session_id);
    delete_transient('mxchat_word_filename_' . $session_id);
    delete_transient('mxchat_word_embeddings_' . $session_id);
    delete_transient('mxchat_include_word_in_context_' . $session_id);
    delete_transient('mxchat_waiting_for_word_' . $session_id);
}



//verified good
public function mxchat_handle_email_capture($message, $user_id, $session_id) {
    // Log the message safely
    //error_log("Triggered email capture intent for message: " . sanitize_text_field($message));

    // Initiate email capture flow
    $response = esc_html($this->options['triggered_phrase_response'] ?? esc_html__("Would you like to join our mailing list? Please provide your email below.", 'mxchat'));
    set_transient('mxchat_email_capture_' . $user_id, true, 5 * MINUTE_IN_SECONDS);
    $this->mxchat_save_chat_message($session_id, 'bot', $response);
    
    // FIXED: Return response data instead of sending JSON directly
    // This allows the main chat handler to add testing data before sending
    return [
        'text' => $response,
        'html' => '',
        'session_id' => $session_id
    ];
}

public function mxchat_generate_image($message, $user_id, $session_id) {
    //error_log("Starting image generation for message: " . $message);
    
    // Prepare a prompt for DALL-E
    $prompt = esc_html__('Create an image of ', 'mxchat') . sanitize_text_field($message);
    
    // Use the existing OpenAI API key
    $openai_api_key = sanitize_text_field($this->options['api_key']);
    
    // Call DALL-E to generate an image
    $image_response = $this->mxchat_generate_dalle_image($prompt, $openai_api_key);
    
    // Check if the response contains an image URL
    if (isset($image_response['imageUrl'])) {
        $image_url = esc_url_raw($image_response['imageUrl']);
        
        // Construct the HTML with a CSS class instead of inline styles
        $response_html = '<img src="' . esc_url($image_url) . '" alt="' . esc_attr__('Generated Image', 'mxchat') . '" class="mxchat-generated-image" />';
        $response_text = esc_html__('Here is the image I generated:', 'mxchat');
        
        // Save the bot message with both text and HTML
        $this->mxchat_save_chat_message($session_id, 'bot', $response_text);
        $this->mxchat_save_chat_message($session_id, 'bot', $response_html);
        
        // Set the fallback response for the chat handler
        $this->fallbackResponse = [
            'text' => $response_text,
            'html' => $response_html,
            'images' => [$image_url]
        ];
        
        // For debugging/verification - Use json_encode to verify what's being set
        //error_log("Image generation successful - fallbackResponse set: " . json_encode($this->fallbackResponse));

        // Return the response directly instead of relying on the property
        return $this->fallbackResponse;
    } else {
        $response_text = esc_html__("I'm sorry, but I couldn't generate an image based on your request.", 'mxchat');
        
        // Save the error message
        $this->mxchat_save_chat_message($session_id, 'bot', $response_text);
        
        // Set the fallback response for the chat handler
        $this->fallbackResponse = [
            'text' => $response_text,
            'html' => '',
            'images' => []
        ];
        
        //error_log("DALL-E image generation error: " . esc_html($image_response['error'] ?? 'Unknown error.'));
        //error_log("Error fallbackResponse set: " . json_encode($this->fallbackResponse));
        
        // Return the response directly instead of relying on the property
        return $this->fallbackResponse;
    }
}
private function mxchat_generate_dalle_image($prompt, $api_key, $model = 'dall-e-3', $timeout = 60) {
    $api_url = 'https://api.openai.com/v1/images/generations';
    $body = json_encode([
        'prompt' => sanitize_text_field($prompt),
        'n' => 1,
        'size' => '1024x1024',
        'model' => sanitize_text_field($model),
    ]);

    $args = [
        'body' => $body,
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . sanitize_text_field($api_key),
        ],
        'method' => 'POST',
        'timeout' => absint($timeout),
    ];

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        //error_log("DALL-E request failed: " . $response->get_error_message());
        return ['error' => esc_html__('Error generating image: ', 'mxchat') . $response->get_error_message()];
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['data'][0]['url'])) {
        return ['imageUrl' => esc_url_raw($response_body['data'][0]['url'])];
    } else {
        //error_log("DALL-E response error: " . wp_remote_retrieve_body($response));
        return ['error' => esc_html__('Failed to generate image.', 'mxchat')];
    }
}

/**
 * Handle web search requests.
 *
 * Sends the refined search query to the Brave Search API and uses the
 * results to generate a conversational response with the AI model.
 *
 * @since 1.0.0
 * @param string $message    The user's search query.
 * @param string $user_id    The user identifier.
 * @param string $session_id The current session ID.
 * @return array             Response array containing text with embedded HTML links
 */
public function mxchat_handle_search_request($message, $user_id, $session_id) {
    // Step 1: Interpret and refine the search query
    $refined_search_query = $this->mxchat_interpret_search_query($message);
    if (empty($refined_search_query)) {
        return array(
            'text' => esc_html__('I apologize, but could you please rephrase your search request?', 'mxchat'),
            'html' => ''
        );
    }
    
    // Retrieve and validate API settings
    $options = get_option('mxchat_options');
    $api_key = isset($options['brave_api_key']) ? sanitize_text_field($options['brave_api_key']) : '';
    $results_count = isset($options['brave_results_count']) ? absint($options['brave_results_count']) : 5;
    
    if (empty($api_key)) {
        return array(
            'text' => esc_html__('Search functionality is temporarily unavailable. Please try again later.', 'mxchat'),
            'html' => ''
        );
    }
    
    // Build the API request URL
    $api_url = add_query_arg(
        array(
            'q'                 => rawurlencode($refined_search_query),
            'count'             => $results_count,
            'text_decorations'  => 'true',
            'rich_data'         => 'true',
        ),
        'https://api.search.brave.com/res/v1/web/search'
    );
    
    // Attempt to retrieve cached results first
    $transient_key = 'mxchat_search_' . md5($refined_search_query);
    $results = get_transient($transient_key);
    
    if (false === $results) {
        // Fetch new results from the Brave Search API
        $response = wp_remote_get(
            $api_url,
            array(
                'headers' => array(
                    'Accept'              => 'application/json',
                    'Accept-Encoding'     => 'gzip',
                    'X-Subscription-Token'=> $api_key,
                ),
                'timeout' => 10,
            )
        );
        
        if (is_wp_error($response)) {
            return array(
                'text' => esc_html__('I encountered an error while searching. Please try again.', 'mxchat'),
                'html' => ''
            );
        }
        
        $results = json_decode(wp_remote_retrieve_body($response), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'text' => esc_html__('I received an invalid response from the search service.', 'mxchat'),
                'html' => ''
            );
        }
        
        // Cache results for one hour
        set_transient($transient_key, $results, HOUR_IN_SECONDS);
    }
    
    // Process results
    if (!empty($results['web']['results']) && is_array($results['web']['results'])) {
        // Create a more straightforward summary with HTML links
        $search_results_text = '';
        
        // Add a simple intro
        $search_results_text .= sprintf(
            esc_html__("Here's what I found about '%s':", 'mxchat'),
            esc_html($refined_search_query)
        );
        
        // Add the top results with HTML links
        foreach (array_slice($results['web']['results'], 0, 5) as $result) {
            $title = isset($result['title']) ? wp_strip_all_tags($result['title']) : '';
            $url = isset($result['url']) ? esc_url($result['url']) : '';
            $description = isset($result['description']) ? wp_strip_all_tags($result['description']) : '';
            
            // Add a line break after the intro
            $search_results_text .= '<br><br>';
            
            // Add title as a link
            $search_results_text .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a><br>',
                $url,
                $title
            );
            
            // Add a condensed description
            $search_results_text .= sprintf("%s", $description);
        }
        
        // Save to chat history
        $this->mxchat_save_chat_message($session_id, 'bot', $search_results_text);
        
        // Return the formatted text with embedded HTML links
        return array(
            'text' => $search_results_text,
            'html' => ''
        );
    } else {
        return array(
            'text' => sprintf(
                esc_html__('I searched for "%s" but couldn\'t find any relevant results. Would you like to try different search terms?', 'mxchat'),
                esc_html($refined_search_query)
            ),
            'html' => ''
        );
    }
}

//very good
/**
 * Handle image search requests from the chatbot
 *
 * @param string $message The user's search query
 * @param int $user_id The user's ID
 * @param string $session_id The chat session ID
 * @return array Response array with text and HTML content
 */
public function mxchat_handle_image_search_request($message, $user_id, $session_id) {
    // Step 1: Interpret the search query using the user's selected AI model
    $refined_search_query = $this->mxchat_interpret_search_query($message);

    // If no query was interpreted, return a fallback message
    if (empty($refined_search_query)) {
        return array(
            'text' => __("I'm sorry, I couldn't interpret your search query. Please specify what you'd like to see images of.", 'mxchat'),
            'html' => "",
        );
    }

    // Brave API URL
    $api_url = 'https://api.search.brave.com/res/v1/images/search';

    // Retrieve Brave API settings
    $options = get_option('mxchat_options');
    $api_key = isset($options['brave_api_key']) ? sanitize_text_field($options['brave_api_key']) : '';

    if (empty($api_key)) {
        return array(
            'text' => __("API key is not configured. Please set it in the Brave Search Settings.", 'mxchat'),
            'html' => "",
        );
    }

    $image_count = isset($options['brave_image_count']) ? intval($options['brave_image_count']) : 4;
    $safe_search = isset($options['brave_safe_search']) ? sanitize_text_field($options['brave_safe_search']) : 'strict';

    // Append query parameters based on settings
    $api_url = add_query_arg([
        'q' => rawurlencode($refined_search_query),
        'count' => $image_count,
        'safesearch' => $safe_search,
    ], $api_url);

    // Implement caching
    $transient_key = 'mxchat_image_search_' . md5($refined_search_query);
    $body = get_transient($transient_key);

    if (false === $body) {
        $args = [
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip',
                'X-Subscription-Token' => $api_key,
            ],
            'timeout' => 10,
        ];

        $response = wp_remote_get($api_url, $args);

        if (is_wp_error($response)) {
            return array(
                'text' => __("I'm sorry, I couldn't retrieve any images based on your request.", 'mxchat'),
                'html' => "",
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        set_transient($transient_key, $body, HOUR_IN_SECONDS);
    }

    // Process the API response
    if (isset($body['results']) && is_array($body['results']) && count($body['results']) > 0) {
        $html_output = '<div class="mxchat-image-gallery">';
        
        // Get the configured image count (1-6)
        $display_count = isset($options['brave_image_count']) ? intval($options['brave_image_count']) : 4;
        $display_count = min($display_count, count($body['results'])); // Make sure we don't exceed available images
        
        // Use only the requested number of images
        for ($i = 0; $i < $display_count; $i++) {
            $image = $body['results'][$i];
            $image_url = isset($image['url']) ? esc_url($image['url']) : '';
            $thumbnail_url = isset($image['thumbnail']['src']) ? esc_url($image['thumbnail']['src']) : '';
            $title = isset($image['title']) ? esc_html($image['title']) : esc_html__('Image', 'mxchat');

            if ($image_url && $thumbnail_url) {
                $html_output .= '<div class="mxchat-image-item">';
                $html_output .= '<strong class="mxchat-image-title">' . $title . '</strong>';
                $html_output .= '<a href="' . $image_url . '" target="_blank" rel="noopener noreferrer" class="mxchat-image-link">';
                $html_output .= '<img src="' . $thumbnail_url . '" alt="' . $title . '" class="mxchat-image-thumbnail">';
                $html_output .= '</a></div>';
            }
        }

        $html_output .= '</div>';

        // Create response text
        $response_text = sprintf(__("Here are some images of %s:", 'mxchat'), $refined_search_query);
        
        // Save both response text and HTML to chat history
        $this->mxchat_save_chat_message($session_id, 'bot', $response_text);
        $this->mxchat_save_chat_message($session_id, 'bot', $html_output);

        // Return the combined response
        return array(
            'text' => $response_text,
            'html' => $html_output,
        );
    } else {
        $response_text = __("I'm sorry, I couldn't retrieve any images based on your request.", 'mxchat');
        
        // Save the error message to chat history
        $this->mxchat_save_chat_message($session_id, 'bot', $response_text);
        
        return array(
            'text' => $response_text,
            'html' => "",
        );
    }
}

/**
 * Interpret the search query using the user's selected AI model
 * 
 * @param string $user_query The original query from the user
 * @return string The refined search query
 */
public function mxchat_interpret_search_query($user_query) {
    $system_prompt = esc_html__("Interpret the user's request to provide only the essential keywords or phrases for image searching. Remove conversational language, politeness, or extra context. Return a concise search query that doesn't lose any of the original meaning.", 'mxchat');
    
    // Get options and determine the selected model
    $options = $this->options ?? get_option('mxchat_options');
    $selected_model = isset($options['model']) ? $options['model'] : 'gpt-4o';
    
    // Extract model prefix to determine the provider
    $model_parts = explode('-', $selected_model);
    $provider = strtolower($model_parts[0]);
    
    // Determine which API key to use based on the provider
    switch ($provider) {
        case 'gemini':
            $api_key = isset($options['gemini_api_key']) ? sanitize_text_field($options['gemini_api_key']) : '';
            if (empty($api_key)) {
                return sanitize_text_field($user_query); // Default to original query if API key missing
            }
            return $this->interpret_query_with_gemini($user_query, $system_prompt, $api_key, $selected_model);
            
        case 'claude':
            $api_key = isset($options['claude_api_key']) ? sanitize_text_field($options['claude_api_key']) : '';
            if (empty($api_key)) {
                return sanitize_text_field($user_query);
            }
            return $this->interpret_query_with_claude($user_query, $system_prompt, $api_key, $selected_model);
            
        case 'grok':
            $api_key = isset($options['xai_api_key']) ? sanitize_text_field($options['xai_api_key']) : '';
            if (empty($api_key)) {
                return sanitize_text_field($user_query);
            }
            return $this->interpret_query_with_xai($user_query, $system_prompt, $api_key, $selected_model);
            
        case 'deepseek':
            $api_key = isset($options['deepseek_api_key']) ? sanitize_text_field($options['deepseek_api_key']) : '';
            if (empty($api_key)) {
                return sanitize_text_field($user_query);
            }
            return $this->interpret_query_with_deepseek($user_query, $system_prompt, $api_key, $selected_model);
            
        case 'gpt':
        default:
            // Default to OpenAI for custom models or unrecognized prefixes
            $api_key = isset($options['api_key']) ? sanitize_text_field($options['api_key']) : '';
            if (empty($api_key)) {
                return sanitize_text_field($user_query);
            }
            return $this->interpret_query_with_openai($user_query, $system_prompt, $api_key, $selected_model);
    }
}

/**
 * Interpret query using OpenAI models
 */
private function interpret_query_with_openai($user_query, $system_prompt, $api_key, $model = 'gpt-4o') {
    $url = 'https://api.openai.com/v1/chat/completions';
    $args = [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => sanitize_text_field($user_query)],
            ],
            'temperature' => 0.2,
            'max_tokens' => 20,
        ]),
        'method' => 'POST',
        'timeout' => 15,
    ];

    $response = wp_remote_post($url, $args);
    if (is_wp_error($response)) {
        return sanitize_text_field($user_query);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return isset($body['choices'][0]['message']['content']) 
        ? sanitize_text_field(trim($body['choices'][0]['message']['content'])) 
        : sanitize_text_field($user_query);
}

/**
 * Interpret query using Claude models
 */
private function interpret_query_with_claude($user_query, $system_prompt, $api_key, $model) {
    $url = 'https://api.anthropic.com/v1/messages';
    
    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key,
            'anthropic-version' => '2023-06-01',
        ],
        'body' => wp_json_encode([
            'model' => $model,
            'system' => $system_prompt,
            'messages' => [
                ['role' => 'user', 'content' => sanitize_text_field($user_query)]
            ],
            'max_tokens' => 20,
            'temperature' => 0.2,
        ]),
        'method' => 'POST',
        'timeout' => 15,
    ];

    $response = wp_remote_post($url, $args);
    if (is_wp_error($response)) {
        return sanitize_text_field($user_query);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['content'][0]['text'])) {
        return sanitize_text_field(trim($body['content'][0]['text']));
    }
    
    return sanitize_text_field($user_query);
}

/**
 * Interpret query using Gemini models
 */
private function interpret_query_with_gemini($user_query, $system_prompt, $api_key, $model) {
    // Strip "gemini-" prefix for the API
    $model_version = str_replace('gemini-', '', $model);
    
    $url = "https://generativelanguage.googleapis.com/v1/models/$model_version:generateContent?key=" . urlencode($api_key);
    
    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $system_prompt . "\n\nQuery: " . sanitize_text_field($user_query)]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 20,
            ],
        ]),
        'method' => 'POST',
        'timeout' => 15,
    ];
    
    $response = wp_remote_post($url, $args);
    if (is_wp_error($response)) {
        return sanitize_text_field($user_query);
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['candidates'][0]['content']['parts'][0]['text'])) {
        return sanitize_text_field(trim($body['candidates'][0]['content']['parts'][0]['text']));
    }
    
    return sanitize_text_field($user_query);
}

/**
 * Interpret query using X.AI (Grok) models
 */
private function interpret_query_with_xai($user_query, $system_prompt, $api_key, $model) {
    $url = 'https://api.xai.com/v1/chat/completions';
    
    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => wp_json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => sanitize_text_field($user_query)],
            ],
            'temperature' => 0.2,
            'max_tokens' => 20,
        ]),
        'method' => 'POST',
        'timeout' => 15,
    ];
    
    $response = wp_remote_post($url, $args);
    if (is_wp_error($response)) {
        return sanitize_text_field($user_query);
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['choices'][0]['message']['content'])) {
        return sanitize_text_field(trim($body['choices'][0]['message']['content']));
    }
    
    return sanitize_text_field($user_query);
}

/**
 * Interpret query using DeepSeek models
 */
private function interpret_query_with_deepseek($user_query, $system_prompt, $api_key, $model) {
    $url = 'https://api.deepseek.com/v1/chat/completions';
    
    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => wp_json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => sanitize_text_field($user_query)],
            ],
            'temperature' => 0.2,
            'max_tokens' => 20,
        ]),
        'method' => 'POST',
        'timeout' => 15,
    ];
    
    $response = wp_remote_post($url, $args);
    if (is_wp_error($response)) {
        return sanitize_text_field($user_query);
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['choices'][0]['message']['content'])) {
        return sanitize_text_field(trim($body['choices'][0]['message']['content']));
    }
    
    return sanitize_text_field($user_query);
}

//very good
private function add_email_to_loops($email) {
    // Sanitize the email
    $email = sanitize_email($email);

    // Retrieve and sanitize options
    $api_key = isset($this->options['loops_api_key']) ? sanitize_text_field($this->options['loops_api_key']) : '';
    $mailing_list_id = isset($this->options['loops_mailing_list']) ? sanitize_text_field($this->options['loops_mailing_list']) : '';

    // Check for missing API key or mailing list ID
    if (empty($api_key) || empty($mailing_list_id)) {
        //error_log(esc_html__('Loops API key or mailing list ID is missing.', 'mxchat'));
        return;
    }

    $data = array(
        'email'        => $email,
        'subscribed'   => true,
        'source'       => __('MxChat AI Chatbot', 'mxchat'),
        'mailingLists' => array($mailing_list_id => true),
    );

    $url = 'https://app.loops.so/api/v1/contacts/create';
    $args = array(
        'body'    => wp_json_encode($data),
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'method'  => 'POST',
        'timeout' => 45,
    );

    $response = wp_remote_post($url, $args);

    // Handle errors in the API request
    if (is_wp_error($response)) {
        //error_log(esc_html__('Error adding email to Loops: ', 'mxchat') . $response->get_error_message());
        return;
    }

    // Check for non-200 HTTP responses
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code != 200) {
        $response_body = wp_remote_retrieve_body($response);
        //error_log(esc_html__('Loops API responded with code ', 'mxchat') . $response_code . ': ' . $response_body);
    }
}

public function mxchat_handle_pdf_discussion($message, $user_id, $session_id) {
    // Get the maximum number of pages allowed from admin settings
    $max_pages = isset($this->options['pdf_max_pages']) ? intval($this->options['pdf_max_pages']) : 69;

    // Retrieve options for dynamic texts
    $trigger_text = $this->options['pdf_intent_trigger_text'] ?? __("Please provide the URL to the PDF you'd like to discuss.", 'mxchat');
    $success_text = $this->options['pdf_intent_success_text'] ?? __("I've processed the PDF. What questions do you have about it?", 'mxchat');
    $error_text = $this->options['pdf_intent_error_text'] ?? __("Sorry, I couldn't process the PDF. Please ensure it's a valid file.", 'mxchat');

    // Check for explicit request for new PDF
    $new_pdf_requested = stripos($message, 'new') !== false ||
                        stripos($message, 'another') !== false ||
                        stripos($message, 'different') !== false;

    // If user mentions adding/reading a PDF, set waiting flag
    if (stripos($message, 'pdf') !== false ||
        stripos($message, 'document') !== false ||
        stripos($message, 'read') !== false) {
        set_transient('mxchat_waiting_for_pdf_url_' . $session_id, true, HOUR_IN_SECONDS);
        $this->fallbackResponse['text'] = $trigger_text;
        return;
    }

    // If we're waiting for a URL or user requested new PDF
    if ($new_pdf_requested || get_transient('mxchat_waiting_for_pdf_url_' . $session_id)) {
        if (preg_match('/https?:\/\/[^\s"]+/i', $message, $matches)) {
            // Process URL... (rest of your existing URL processing code)
        } else {
            $this->fallbackResponse['text'] = $trigger_text;
        }
        return;
    }

    // Default to proceeding with conversation if no specific PDF action is needed
    $this->fallbackResponse['text'] = '';
}


private function fetch_and_split_pdf_pages($pdf_source, $max_pages) {
    $upload_dir = wp_upload_dir();
    $temp_file = null;

    try {
        // Handle URL vs local file
        if (filter_var($pdf_source, FILTER_VALIDATE_URL)) {
            // Validate and download the file from URL
            $temp_file = wp_tempnam($pdf_source); // Safe temporary file name
            $response = wp_remote_get($pdf_source, ['timeout' => 60]);

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                //error_log(esc_html__("Failed to download PDF. Error: ", 'mxchat') . print_r($response, true));
                return false;
            }

            file_put_contents($temp_file, wp_remote_retrieve_body($response));

            // Validate that the downloaded file is a PDF
            $mime_type = mime_content_type($temp_file);
            if ($mime_type !== 'application/pdf') {
                //error_log(esc_html__("Invalid MIME type detected for PDF: ", 'mxchat') . $mime_type);
                unlink($temp_file);
                return false;
            }
        } else {
            // For local files, use the provided path directly
            $temp_file = $pdf_source;
        }

        // Parse and process the PDF
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($temp_file);
        $pages = $pdf->getPages();

        if (count($pages) > $max_pages) {
            //error_log(esc_html__("PDF exceeds the maximum allowed pages: ", 'mxchat') . count($pages));
            if (filter_var($pdf_source, FILTER_VALIDATE_URL)) {
                unlink($temp_file);
            }
            return esc_html__('too_many_pages', 'mxchat');
        }

        $embeddings = [];
        foreach ($pages as $page_number => $page) {
            $text = $page->getText();

            // Ensure text is non-empty before generating embeddings
            if (empty(trim($text))) {
                //error_log(esc_html__("Skipping empty page: ", 'mxchat') . ($page_number + 1));
                continue;
            }

            $embedding = $this->mxchat_generate_embedding(
                esc_html__("Page ", 'mxchat') . ($page_number + 1) . ": " . $text,
                $this->options['api_key']
            );

            if ($embedding) {
                $embeddings[] = [
                    'page_number' => $page_number + 1,
                    'embedding' => $embedding,
                    'text' => $text,
                ];
            } else {
                //error_log(esc_html__("Failed to generate embedding for page ", 'mxchat') . ($page_number + 1));
            }
        }

        // Clean up downloaded file if it was from URL
        if (filter_var($pdf_source, FILTER_VALIDATE_URL) && $temp_file) {
            unlink($temp_file);
        }

        return $embeddings;

    } catch (\Exception $e) {
       // //error_log(esc_html__("Error parsing or processing PDF: ", 'mxchat') . $e->getMessage());

        // Cleanup in case of exception
        if (filter_var($pdf_source, FILTER_VALIDATE_URL) && $temp_file && file_exists($temp_file)) {
            unlink($temp_file);
        }

        return false;
    }
}
private function find_relevant_pdf_pages($query_embedding, $embeddings) {
    //error_log(esc_html__("find_relevant_pdf_pages called.", 'mxchat'));

    $most_relevant = null;
    $highest_similarity = -INF;

    foreach ($embeddings as $page_data) {
        $similarity = $this->mxchat_calculate_cosine_similarity($query_embedding, $page_data['embedding']);

        if ($similarity > $highest_similarity) {
            $highest_similarity = $similarity;
            $most_relevant = $page_data['page_number'];
        }
    }

    if (!is_null($most_relevant)) {
        $page_numbers = range(max(1, $most_relevant - 1), min(count($embeddings), $most_relevant + 1));
        return array_filter($embeddings, function ($page) use ($page_numbers) {
            return in_array($page['page_number'], $page_numbers);
        });
    }

    return [];
}
// Add this to your class
public function handle_pdf_upload() {
    check_ajax_referer('mxchat_chat_nonce', 'nonce');

    if (!isset($_FILES['pdf_file']) || !isset($_POST['session_id'])) {
        wp_send_json_error(esc_html__('Missing required parameters.', 'mxchat'));
        return;
    }

    $file = $_FILES['pdf_file'];
    $session_id = sanitize_text_field($_POST['session_id']);
    $original_filename = sanitize_text_field($file['name']);

    $file_type = wp_check_filetype($file['name'], ['pdf' => 'application/pdf']);
    if ($file_type['type'] !== 'application/pdf') {
        wp_send_json_error(esc_html__('Invalid file type. Only PDF files are allowed.', 'mxchat'));
        return;
    }

    $upload_dir = wp_upload_dir();
    $pdf_filename = 'mxchat_' . $session_id . '_' . time() . '.pdf';
    $pdf_path = $upload_dir['path'] . '/' . $pdf_filename;

    if (!move_uploaded_file($file['tmp_name'], $pdf_path)) {
        wp_send_json_error(esc_html__('Failed to upload file.', 'mxchat'));
        return;
    }

    $this->clear_pdf_transients($session_id);

    $max_pages = isset($this->options['pdf_max_pages']) ? intval($this->options['pdf_max_pages']) : 69;
    $embeddings = $this->fetch_and_split_pdf_pages($pdf_path, $max_pages);

    if ($embeddings === 'too_many_pages') {
        unlink($pdf_path);
        $error_message = sprintf(
            $this->options['pdf_intent_error_text'] ??
            esc_html__("The provided PDF exceeds the maximum allowed limit of %d pages. Please provide a smaller document.", 'mxchat'),
            $max_pages
        );
        wp_send_json_error($error_message);
        return;
    }

    if ($embeddings === false || empty($embeddings)) {
        unlink($pdf_path);
        $error_message = $this->options['pdf_intent_error_text'] ??
            esc_html__('The uploaded PDF appears to be empty or contains unsupported content.', 'mxchat');
        wp_send_json_error($error_message);
        return;
    }

    if (!empty($embeddings)) {
        set_transient('mxchat_pdf_url_' . $session_id, $pdf_path, HOUR_IN_SECONDS);
        set_transient('mxchat_pdf_filename_' . $session_id, $original_filename, HOUR_IN_SECONDS);
        set_transient('mxchat_pdf_embeddings_' . $session_id, $embeddings, HOUR_IN_SECONDS);
        set_transient('mxchat_include_pdf_in_context_' . $session_id, true, HOUR_IN_SECONDS);

        $success_message = $this->options['pdf_intent_success_text'] ??
            esc_html__("I've processed the PDF. What questions do you have about it?", 'mxchat');

        wp_send_json_success([
            'message' => $success_message,
            'filename' => $original_filename
        ]);
        return;
    }

    unlink($pdf_path);
    $error_message = $this->options['pdf_intent_error_text'] ??
        esc_html__('Sorry, I couldn\'t process the PDF. Please ensure it\'s a valid file.', 'mxchat');
    wp_send_json_error($error_message);
    return;
}
public function handle_pdf_remove() {
    check_ajax_referer('mxchat_chat_nonce', 'nonce');

    if (empty($_POST['session_id'])) {
        wp_send_json_error(esc_html__('Session ID missing.', 'mxchat'));
        wp_die();
    }

    $session_id = sanitize_text_field($_POST['session_id']);
    $pdf_path = get_transient('mxchat_pdf_url_' . $session_id);

    if ($pdf_path && file_exists($pdf_path)) {
        unlink($pdf_path);
    }

    $this->clear_pdf_transients($session_id);

    wp_send_json_success([
        'message' => esc_html__('PDF removed successfully.', 'mxchat')
    ]);
    wp_die();
}




function mxchat_fetch_new_messages() {
    $session_id = sanitize_text_field($_POST['session_id']);
    $last_seen_id = sanitize_text_field($_POST['last_seen_id']);
    $persistence_enabled = $_POST['persistence_enabled'] === 'true';
    $initial_timestamp = isset($_POST['initial_timestamp']) ? intval($_POST['initial_timestamp']) : 0;

    if (empty($session_id)) {
        //error_log(esc_html__('Fetch new messages error: Session ID missing.', 'mxchat'));
        wp_send_json_error(['message' => esc_html__('Session ID missing.', 'mxchat')]);
        wp_die();
    }

    $history = get_option("mxchat_history_{$session_id}", []);

    $new_messages = array_filter($history, function ($message) use ($last_seen_id, $persistence_enabled, $initial_timestamp) {
        // If persistence is enabled, show all new messages
        if ($persistence_enabled) {
            return !empty($message['id']) &&
                   strcmp($message['id'], $last_seen_id) > 0 &&
                   $message['role'] === 'agent';
        }

        // If persistence is disabled, only show messages after initial timestamp
        return !empty($message['id']) &&
               $message['role'] === 'agent' &&
               $message['timestamp'] > $initial_timestamp;
    });

    //error_log(esc_html__("New agent messages fetched for session $session_id. Last seen ID: $last_seen_id", 'mxchat'));

    wp_send_json_success([
        'new_messages' => array_values($new_messages)
    ]);
    wp_die();
}
public function mxchat_live_agent_handover($message, $user_id, $session_id) {
    // First check if live agents are available
    $live_agent_available = $this->options['live_agent_status'] ?? 'off';
    if ($live_agent_available !== 'on') {
        $away_message = $this->options['live_agent_away_message'] ?? 'Sorry, live agents are currently unavailable. I can continue helping you as an AI assistant.';
        $this->fallbackResponse = [
            'text' => $away_message,
            'html' => '',
            'images' => [],
            'chat_mode' => 'ai'
        ];
        wp_send_json([
            'text' => $away_message,
            'html' => '',
            'chat_mode' => 'ai',
            'session_id' => $session_id
        ]);
        wp_die();
    }

    $slack_bot_token = $this->options['live_agent_bot_token'] ?? '';
    
    if (empty($slack_bot_token)) {
        return false;
    }

    // Check if channel already exists for this session
    $channel_id = get_option("mxchat_channel_{$session_id}", '');
    
    if (empty($channel_id)) {
        // Create new channel with session ID as name
        $channel_name = 'chat-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $session_id));
        
        //error_log("Attempting to create channel: $channel_name");
        
        $response = wp_remote_post('https://slack.com/api/conversations.create', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $slack_bot_token
            ],
            'body' => json_encode([
                'name' => $channel_name,
                'is_private' => false // Public channel - anyone in workspace can join
            ])
        ]);
        
        if (!is_wp_error($response)) {
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            //error_log("Channel creation response: " . $response_body);
            
            if (isset($response_data['ok']) && $response_data['ok']) {
                $channel_id = $response_data['channel']['id'];
                $actual_channel_name = $response_data['channel']['name'] ?? 'unknown';
                //error_log("Channel created successfully: ID=$channel_id, Name=$actual_channel_name");
                update_option("mxchat_channel_{$session_id}", $channel_id);
                
                // Auto-invite agents to the channel
                $agent_user_ids = $this->options['live_agent_user_ids'] ?? '';
                
                if (!empty($agent_user_ids)) {
                    // Parse user IDs (one per line)
                    $user_ids = array_filter(array_map('trim', explode("\n", $agent_user_ids)));
                    
                    foreach ($user_ids as $user_id_to_invite) {
                        //error_log("Inviting user to channel: $user_id_to_invite");
                        
                        $invite_response = wp_remote_post('https://slack.com/api/conversations.invite', [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Bearer ' . $slack_bot_token
                            ],
                            'body' => json_encode([
                                'channel' => $channel_id,
                                'users' => $user_id_to_invite
                            ])
                        ]);
                        
                        if (!is_wp_error($invite_response)) {
                            $invite_body = wp_remote_retrieve_body($invite_response);
                            $invite_data = json_decode($invite_body, true);
                            //error_log("Invite response for $user_id_to_invite: " . $invite_body);
                            
                            if (isset($invite_data['ok']) && $invite_data['ok']) {
                                //error_log("Successfully invited user $user_id_to_invite to channel");
                            } else {
                                //error_log("Failed to invite user $user_id_to_invite: " . ($invite_data['error'] ?? 'Unknown error'));
                            }
                        } else {
                            //error_log("WP Error inviting user $user_id_to_invite: " . $invite_response->get_error_message());
                        }
                    }
                } else {
                    //error_log("No agent user IDs configured for auto-invite");
                }
            } else {
                //error_log("Channel creation failed: " . ($response_data['error'] ?? 'Unknown error'));
            }
        } else {
            //error_log("WP Error creating channel: " . $response->get_error_message());
        }
        
        if (empty($channel_id)) {
            return false; // Failed to create channel
        }
    }

    // Get recent chat history
    $history = get_option("mxchat_history_{$session_id}", []);
    $recent_history = array_slice($history, -5);

    // Format conversation context
    $conversation_context = "";
    if (!empty($recent_history)) {
        $conversation_context = "*Recent Conversation:*\n";
        foreach ($recent_history as $hist_message) {
            $role_display = $hist_message['role'] === 'user' ? 'User' : 'AI';
            $conversation_context .= ">{$role_display}: {$hist_message['content']}\n";
        }
        $conversation_context .= "\n";
    }

    update_option("mxchat_mode_{$session_id}", 'agent');

    // Send message to channel
    $channel_message = "🔔 *New Live Agent Request*\n\n";
    $channel_message .= "*Session ID:* `{$session_id}`\n";
    $channel_message .= "*User ID:* `{$user_id}`\n\n";
    
    if (!empty($conversation_context)) {
        $channel_message .= $conversation_context;
    }
    
    $channel_message .= "*Current Message:*\n{$message}\n\n";
    $channel_message .= "_Reply directly in this channel - all messages will go to the user_";

    wp_remote_post('https://slack.com/api/chat.postMessage', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $slack_bot_token
        ],
        'body' => json_encode([
            'channel' => $channel_id,
            'text' => $channel_message,
            'mrkdwn' => true
        ])
    ]);

    $success_message = $this->options['live_agent_notification_message'] ?? 'Live agent has been notified.';
    $this->mxchat_save_chat_message($session_id, 'bot', $success_message);

    $this->fallbackResponse = [
        'text' => $success_message,
        'html' => '',
        'images' => [],
        'chat_mode' => 'agent'
    ];

    wp_send_json([
        'success' => true,
        'text' => $success_message,
        'html' => '',
        'chat_mode' => 'agent',
        'session_id' => $session_id,
        'fallbackResponse' => $this->fallbackResponse
    ]);
    wp_die();
}
public function mxchat_send_user_message_to_agent($message, $user_id, $session_id) {
    $slack_bot_token = $this->options['live_agent_bot_token'] ?? '';
    $channel_id = get_option("mxchat_channel_{$session_id}", '');

    if (empty($slack_bot_token) || empty($channel_id)) {
        return false;
    }

    $user_message = "💬 *User:* {$message}";

    $response = wp_remote_post('https://slack.com/api/chat.postMessage', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $slack_bot_token
        ],
        'body' => json_encode([
            'channel' => $channel_id,
            'text' => $user_message,
            'mrkdwn' => true
        ])
    ]);

    return !is_wp_error($response);
}
public function handle_slack_interaction(WP_REST_Request $request) {
    //error_log('Received Slack interaction');

    $payload = json_decode($request->get_param('payload'), true);
    //error_log('Payload: ' . print_r($payload, true));

    // Handle button click
    if ($payload['type'] === 'block_actions' && $payload['actions'][0]['action_id'] === 'reply_to_user') {
        $session_id = $payload['actions'][0]['value'];
        $trigger_id = $payload['trigger_id'];

        // Get Bot Token from settings
        $slack_token = $this->options['live_agent_bot_token'] ?? '';

        if (empty($slack_token)) {
            //error_log('Slack Bot Token not configured');
            return new WP_REST_Response(['error' => esc_html__('Bot token not configured', 'mxchat')], 400);
        }
        $response = wp_remote_post('https://slack.com/api/views.open', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $slack_token
            ],
            'body' => json_encode([
                'trigger_id' => $trigger_id,
                'view' => [
                    'type' => 'modal',
                    'callback_id' => 'reply_modal',
                    'title' => [
                        'type' => 'plain_text',
                        'text' => __('Reply to User', 'mxchat')
                    ],
                    'submit' => [
                        'type' => 'plain_text',
                        'text' => __('Send', 'mxchat')
                    ],
                    'close' => [
                        'type' => 'plain_text',
                        'text' => __('Cancel', 'mxchat')
                    ],
                    'blocks' => [
                        [
                            'type' => 'input',
                            'block_id' => 'reply_block',
                            'label' => [
                                'type' => 'plain_text',
                                'text' => sprintf(__('Reply to session: %s', 'mxchat'), $session_id)
                            ],
                            'element' => [
                                'type' => 'plain_text_input',
                                'action_id' => 'message',
                                'multiline' => true,
                                'placeholder' => [
                                    'type' => 'plain_text',
                                    'text' => __('Type your message here...', 'mxchat')
                                ]
                            ]
                        ]
                    ],
                    'private_metadata' => $session_id
                ]
            ])
        ]);

        //error_log('Views.open response: ' . print_r($response, true));

        // Return immediate acknowledgment
        return new WP_REST_Response(['ok' => true]);
    }

    // Handle modal submission
// Handle modal submission
if ($payload['type'] === 'view_submission') {
    $session_id = $payload['view']['private_metadata'];
    $message = $payload['view']['state']['values']['reply_block']['message']['value'];

    // Save the message (keep the message_id but don't include in response)
    $this->mxchat_save_chat_message($session_id, 'agent', $message);

    // Keep the original response format for Slack
    return new WP_REST_Response([
        'response_action' => 'clear'
    ]);
}

    // Default acknowledgment
    return new WP_REST_Response(['ok' => true]);
}
public function mxchat_handle_agent_response(WP_REST_Request $request) {
    //error_log('Received agent response request');
    //error_log('Request data: ' . print_r($request->get_params(), true));
   // //error_log('Raw body: ' . file_get_contents('php://input'));

    // Get the data from Slack's slash command format
    $command_text = $request->get_param('text');
   // //error_log('Command text: ' . $command_text);

    if (empty($command_text)) {
        //error_log(esc_html__('Agent response error: No command text received', 'mxchat'));
        return new WP_REST_Response([
            'error' => esc_html__('Command text is required. Format: /reply session_id message', 'mxchat')
        ], 400);
    }

    // Split the command text into session_id and message
    $parts = explode(' ', $command_text, 2);
    if (count($parts) !== 2) {
        //error_log('Agent response error: Invalid command format');
        return new WP_REST_Response([
            'error' => esc_html__('Invalid format. Use: /reply session_id message', 'mxchat')
        ], 400);
    }

    $session_id = sanitize_text_field($parts[0]);
    $message = sanitize_text_field($parts[1]);

    //error_log("Processing agent response - Session ID: $session_id, Message: $message");

    // Save the message
    $message_id = $this->mxchat_save_chat_message($session_id, 'agent', $message);

    if (!$message_id) {
       // //error_log('Failed to save agent message');
        return new WP_REST_Response([
            'error' => esc_html__('Failed to save message', 'mxchat')
        ], 500);
    }

    // Return success response in Slack's expected format
    return new WP_REST_Response([
        'response_type' => 'in_channel',
        'text' => esc_html__("Message sent successfully to session $session_id", 'mxchat')
    ], 200);
}
public function mxchat_handle_switch_to_chatbot_intent($message, $user_id, $session_id) {
    //error_log(esc_html__("Switching back to chatbot mode via intent.", 'mxchat'));

    // Just update mode to AI
    update_option("mxchat_mode_{$session_id}", 'ai');

    // Initialize states
    $this->fallbackResponse = ['text' => '', 'html' => '', 'images' => []];
    $this->productCardHtml = '';

    // Set the response message
    $this->fallbackResponse['text'] = esc_html__('You are now chatting with the AI chatbot.', 'mxchat');

    return true; // Intent was handled
}
public function handle_slack_messages(WP_REST_Request $request) {
    // Log the incoming request for debugging
    //error_log('Slack events request received: ' . $request->get_body());
    
    $body = $request->get_body();
    $data = json_decode($body, true);
    
    // Handle Slack URL verification
    if (isset($data['type']) && $data['type'] === 'url_verification') {
        //error_log('Slack URL verification challenge: ' . $data['challenge']);
        return new WP_REST_Response($data['challenge'], 200, ['Content-Type' => 'text/plain']);
    }
    
    // IMPORTANT: Handle Slack's event deduplication
    if (isset($data['event_id'])) {
        $event_id = $data['event_id'];
        $processed_events = get_transient('mxchat_slack_events') ?: [];
        
        // Check if we've already processed this event
        if (in_array($event_id, $processed_events)) {
            //error_log("Duplicate event detected: $event_id");
            return new WP_REST_Response(['ok' => true]);
        }
        
        // Add this event to processed list
        $processed_events[] = $event_id;
        // Keep only last 100 events to prevent memory issues
        if (count($processed_events) > 100) {
            $processed_events = array_slice($processed_events, -100);
        }
        // Store for 1 hour
        set_transient('mxchat_slack_events', $processed_events, HOUR_IN_SECONDS);
    }
    
    // Handle message events
    if (isset($data['event']) && $data['event']['type'] === 'message') {
        $event = $data['event'];
        
        // Skip bot messages and messages with subtypes (like bot_message)
        if (isset($event['bot_id']) || isset($event['subtype'])) {
            return new WP_REST_Response(['ok' => true]);
        }
        
        // Additional check: Skip if this is a threaded reply to our confirmation
        if (isset($event['thread_ts']) && $event['thread_ts'] !== $event['ts']) {
            return new WP_REST_Response(['ok' => true]);
        }
        
        $channel_id = $event['channel'];
        $message_text = $event['text'] ?? '';
        $message_ts = $event['ts'] ?? '';
        
        // Find session ID by looking for matching channel
        global $wpdb;
        $session_option = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} 
                 WHERE option_name LIKE 'mxchat_channel_%' 
                 AND option_value = %s",
                $channel_id
            )
        );
        
        if ($session_option) {
            $session_id = str_replace('mxchat_channel_', '', $session_option);
            
            // Create a unique key for this specific message
            $message_key = md5($session_id . $message_ts . $message_text);
            $processed_messages = get_transient('mxchat_processed_messages_' . $session_id) ?: [];
            
            // Check if we've already processed this exact message
            if (in_array($message_key, $processed_messages)) {
                //error_log("Duplicate message detected for session $session_id");
                return new WP_REST_Response(['ok' => true]);
            }
            
            // Add to processed messages
            $processed_messages[] = $message_key;
            // Keep only last 50 messages per session
            if (count($processed_messages) > 50) {
                $processed_messages = array_slice($processed_messages, -50);
            }
            set_transient('mxchat_processed_messages_' . $session_id, $processed_messages, HOUR_IN_SECONDS);
            
            // Save the agent message
            $this->mxchat_save_chat_message($session_id, 'agent', $message_text);
            
            // Send confirmation back to Slack (only once)
            $slack_bot_token = $this->options['live_agent_bot_token'] ?? '';
            if (!empty($slack_bot_token)) {
                // Use a transient to prevent duplicate confirmations
                $confirm_key = 'mxchat_confirm_' . $message_key;
                if (!get_transient($confirm_key)) {
                    wp_remote_post('https://slack.com/api/chat.postMessage', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $slack_bot_token
                        ],
                        'body' => json_encode([
                            'channel' => $channel_id,
                            'text' => "✅ _Message sent to user_",
                            'thread_ts' => $event['ts'] // Reply in thread
                        ])
                    ]);
                    // Set transient to prevent duplicate confirmations
                    set_transient($confirm_key, true, 300); // 5 minutes
                }
            }
        }
    }
    
    return new WP_REST_Response(['ok' => true]);
}

// For the word upload handler
public function mxchat_handle_word_upload() {
    // Delegate to word handler
    $this->word_handler->mxchat_handle_word_upload();
}

// For the word removal handler
public function mxchat_handle_word_remove() {
    // Delegate to word handler
    $this->word_handler->mxchat_handle_word_remove();
}

// For the word status check
public function mxchat_check_word_status() {
    // Delegate to word handler
    $this->word_handler->mxchat_check_word_status();
}


private function mxchat_get_user_identifier() {
    return MxChat_User::mxchat_get_user_identifier();
}

private function mxchat_generate_embedding($text, $api_key) {
    try {
        // Get options and selected model
        $options = get_option('mxchat_options');
        $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
        
        // Determine endpoint and API key based on model
        if (strpos($selected_model, 'voyage') === 0) {
            $endpoint = 'https://api.voyageai.com/v1/embeddings';
            $api_key = $options['voyage_api_key'] ?? '';
            
            // Check if Voyage API key is missing
            if (empty($api_key)) {
                //error_log('Voyage API key is missing');
                return [
                    'error' => esc_html__('Voyage AI API key is not configured', 'mxchat'),
                    'error_code' => 'missing_voyage_api_key'
                ];
            }
        } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $selected_model . ':embedContent';
            $api_key = $options['gemini_api_key'] ?? '';
            
            // Check if Gemini API key is missing
            if (empty($api_key)) {
                //error_log('Gemini API key is missing');
                return [
                    'error' => esc_html__('Google Gemini API key is not configured', 'mxchat'),
                    'error_code' => 'missing_gemini_api_key'
                ];
            }
        } else {
            $endpoint = 'https://api.openai.com/v1/embeddings';
            // Use the passed API key for OpenAI
            
            // Check if OpenAI API key is missing
            if (empty($api_key)) {
                //error_log('OpenAI API key is missing');
                return [
                    'error' => esc_html__('OpenAI API key is not configured', 'mxchat'),
                    'error_code' => 'missing_openai_api_key'
                ];
            }
        }
        
        // Check if text is empty
        if (empty($text)) {
            //error_log('Empty text provided for embedding generation');
            return [
                'error' => esc_html__('No text provided for embedding generation', 'mxchat'),
                'error_code' => 'empty_embedding_text'
            ];
        }
        
        // Prepare request body based on provider
        if (strpos($selected_model, 'gemini-embedding') === 0) {
            // Gemini API format
            $request_body = [
                'model' => 'models/' . $selected_model,
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ],
                'outputDimensionality' => 1536
            ];
            
            // Prepare headers for Gemini (API key as query parameter)
            $endpoint .= '?key=' . $api_key;
            $headers = [
                'Content-Type' => 'application/json'
            ];
        } else {
            // OpenAI/Voyage API format
            $request_body = [
                'input' => $text,
                'model' => $selected_model
            ];
            
            // Add output_dimension for voyage-3-large
            if ($selected_model === 'voyage-3-large') {
                $request_body['output_dimension'] = 2048;
            }
            
            // Prepare headers for OpenAI/Voyage
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ];
        }
        
        // Prepare request arguments
        $args = [
            'body' => wp_json_encode($request_body),
            'headers' => $headers,
            'timeout' => 60,
            'redirection' => 5,
            'blocking' => true,
            'httpversion' => '1.0',
            'sslverify' => true,
        ];
        
        // Make the request
        $response = wp_remote_post($endpoint, $args);
        
        // Handle WordPress errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            //error_log('Embedding Generation Error: ' . $error_message);
            return [
                'error' => esc_html__('Connection error when generating embeddings: ', 'mxchat') . esc_html($error_message),
                'error_code' => 'embedding_connection_error'
            ];
        }
        
        // Check HTTP status code
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            
            $error_message = isset($response_body['error']['message']) 
                ? $response_body['error']['message'] 
                : 'HTTP Error ' . $status_code;
                
            $error_type = isset($response_body['error']['type']) 
                ? $response_body['error']['type'] 
                : 'unknown';
                
            //error_log('Embedding API HTTP Error: ' . $status_code . ' - ' . $error_message);
            
            // Handle specific error types
            switch ($error_type) {
                case 'invalid_request_error':
                    if (strpos($error_message, 'API key') !== false) {
                        return [
                            'error' => esc_html__('Invalid API key for embedding generation. Please check your API key configuration.', 'mxchat'),
                            'error_code' => 'embedding_invalid_api_key'
                        ];
                    }
                    break;
                    
                case 'authentication_error':
                    return [
                        'error' => esc_html__('Authentication failed for embedding generation. Please check your API key.', 'mxchat'),
                        'error_code' => 'embedding_auth_error'
                    ];
                    
                case 'rate_limit_exceeded':
                    return [
                        'error' => esc_html__('Rate limit exceeded for embedding generation. Please try again later.', 'mxchat'),
                        'error_code' => 'embedding_rate_limit'
                    ];
                    
                case 'quota_exceeded':
                    return [
                        'error' => esc_html__('API quota exceeded for embedding generation. Please check your billing details.', 'mxchat'),
                        'error_code' => 'embedding_quota_exceeded'
                    ];
            }
            
            // Generic error fallback
            return [
                'error' => esc_html__('Embedding API error - check embedding API key.: ', 'mxchat') . esc_html($error_message),
                'error_code' => 'embedding_api_error',
                'status_code' => $status_code
            ];
        }
        
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        // Handle different response formats based on provider
        if (strpos($selected_model, 'gemini-embedding') === 0) {
            // Gemini API response format
            if (isset($response_body['embedding']['values']) && is_array($response_body['embedding']['values'])) {
                return $response_body['embedding']['values'];
            } else {
                //error_log('Invalid Gemini embedding response: ' . wp_json_encode($response_body));
                return [
                    'error' => esc_html__('Received invalid embedding data from the Gemini API.', 'mxchat'),
                    'error_code' => 'invalid_gemini_embedding_response'
                ];
            }
        } else {
            // OpenAI/Voyage API response format
            if (isset($response_body['data'][0]['embedding']) && is_array($response_body['data'][0]['embedding'])) {
                return $response_body['data'][0]['embedding'];
            } else {
                //error_log('Invalid embedding response: ' . wp_json_encode($response_body));
                return [
                    'error' => esc_html__('Received invalid embedding data from the API.', 'mxchat'),
                    'error_code' => 'invalid_embedding_response'
                ];
            }
        }
    } catch (Exception $e) {
        //error_log('Embedding Exception: ' . $e->getMessage());
        return [
            'error' => esc_html__('System error when generating embeddings: ', 'mxchat') . esc_html($e->getMessage()),
            'error_code' => 'embedding_exception'
        ];
    }
}
private function mxchat_find_relevant_content($user_embedding) {
    //error_log('MXChat Vector Search: Starting content search...');

    // Retrieve the add-on settings from the database.
    $addon_options = get_option('mxchat_pinecone_addon_options', array());

    // Determine whether Pinecone is enabled.
    $use_pinecone = (isset($addon_options['mxchat_use_pinecone']) && $addon_options['mxchat_use_pinecone'] === '1') ? 1 : 0;

    //error_log('Pinecone enabled flag: ' . $use_pinecone);

    if ($use_pinecone === 1) {
        //error_log('MXChat Vector Search: Using Pinecone database');
        return $this->find_relevant_content_pinecone($user_embedding);
    } else {
        //error_log('MXChat Vector Search: Using WordPress database');
        return $this->find_relevant_content_wordpress($user_embedding);
    }
}

private function find_relevant_content_wordpress($user_embedding) {
    global $wpdb;
    $system_prompt_table = $wpdb->prefix . 'mxchat_system_prompt_content';
    $cache_key = 'mxchat_system_prompt_embeddings';
    $batch_size = 500;

    // Initialize similarity analysis storage
    $this->last_similarity_analysis = [
        'knowledge_base_type' => 'WordPress Database',
        'top_matches' => [],
        'threshold_used' => 0,
        'total_checked' => 0
    ];

    // Retrieve embeddings from cache or database
    $embeddings = wp_cache_get($cache_key, 'mxchat_system_prompts');
    if ($embeddings === false) {
        // Cache miss - load embeddings from database WITH CONTENT for testing
        $embeddings = [];
        $offset = 0;

        do {
            $query = $wpdb->prepare(
                "SELECT id, embedding_vector, article_content, source_url
                FROM {$system_prompt_table}
                LIMIT %d OFFSET %d",
                $batch_size,
                $offset
            );

            $batch = $wpdb->get_results($query);
            if (empty($batch)) {
                break;
            }

            $embeddings = array_merge($embeddings, $batch);
            $offset += $batch_size;
            unset($batch);
        } while (true);

        if (empty($embeddings)) {
            return '';
        }
        
        // Cache embeddings for future use (but note: this now includes content)
        wp_cache_set($cache_key, $embeddings, 'mxchat_system_prompts', 3600);
    }

    // Get configuration options
    $main_options = get_option('mxchat_options', []);
    
    // Get base similarity threshold (default 75%)
    $similarity_threshold = isset($main_options['similarity_threshold']) 
        ? ((int) $main_options['similarity_threshold']) / 100 
        : 0.75;
        
    $this->last_similarity_analysis['threshold_used'] = $similarity_threshold;
    
    // Calculate similarities and build results array
    $all_similarities = [];
    $relevant_results = [];
    
    foreach ($embeddings as $embedding) {
        $database_embedding = $embedding->embedding_vector
            ? unserialize($embedding->embedding_vector, ['allowed_classes' => false])
            : null;
            
        if (is_array($database_embedding) && is_array($user_embedding)) {
            $similarity = $this->mxchat_calculate_cosine_similarity($user_embedding, $database_embedding);
            
            // Store ALL similarities for testing (top 10)
            $source_display = '';
            if (!empty($embedding->source_url) && $embedding->source_url !== '#') {
                $source_display = $embedding->source_url;
            } else {
                $content_preview = strip_tags($embedding->article_content ?? '');
                $content_preview = preg_replace('/\s+/', ' ', $content_preview);
                $source_display = substr(trim($content_preview), 0, 50) . '...';
            }
            
            $all_similarities[] = [
                'document_id' => $embedding->id,
                'similarity' => $similarity,
                'similarity_percentage' => round($similarity * 100, 2),
                'above_threshold' => $similarity >= $similarity_threshold,
                'source_display' => $source_display,
                'content_preview' => substr(strip_tags($embedding->article_content ?? ''), 0, 100) . '...',
                'used_for_context' => false // Initialize as false, we'll update this later
            ];
            
            // Only consider results above threshold for actual content retrieval
            if ($similarity >= $similarity_threshold) {
                $relevant_results[] = [
                    'id' => $embedding->id,
                    'similarity' => $similarity
                ];
            }
        }
        
        unset($database_embedding);
    }

    // Sort ALL similarities for testing display (highest first)
    usort($all_similarities, function ($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    // Sort relevant results by similarity (highest first)
    usort($relevant_results, function ($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    // Get top 5 results for actual content (standard approach)
    $top_results = array_slice($relevant_results, 0, 5);
    
    // NOW mark which documents are actually used for context
    $used_document_ids = [];
    foreach ($top_results as $result) {
        $used_document_ids[] = $result['id'];
    }
    
    // Update the all_similarities array to mark which were actually used
    foreach ($all_similarities as &$similarity_item) {
        $similarity_item['used_for_context'] = in_array($similarity_item['document_id'], $used_document_ids);
    }
    
    // Store top 10 for testing panel (now with correct used_for_context flags)
    $this->last_similarity_analysis['top_matches'] = array_slice($all_similarities, 0, 10);
    $this->last_similarity_analysis['total_checked'] = count($embeddings);
    
    //error_log("MxChat Testing: Stored " . count($this->last_similarity_analysis['top_matches']) . " top matches for testing");
    
    // Initialize final content
    $content = '';
    
    // Track document IDs to avoid duplicates
    $added_document_ids = [];
    
    // Fetch and format content for each selected result
    foreach ($top_results as $index => $result) {
        if (in_array($result['id'], $added_document_ids)) {
            continue;
        }
        
        $chunk_content = $this->fetch_content_with_product_links($result['id']);
        $added_document_ids[] = $result['id'];
        
        $content .= "## Reference " . ($index + 1) . " ##\n";
        $content .= $chunk_content . "\n\n";
        
        // PDF surrounding pages logic (unchanged)
        if (strpos($chunk_content, '{"document_type":"pdf"') !== false) {
            $surrounding_content = $wpdb->get_results($wpdb->prepare(
                "SELECT id, article_content FROM {$system_prompt_table}
                WHERE id IN (
                    (SELECT id FROM {$system_prompt_table} WHERE id < %d ORDER BY id DESC LIMIT 1),
                    (SELECT id FROM {$system_prompt_table} WHERE id > %d ORDER BY id ASC LIMIT 1)
                )",
                $result['id'],
                $result['id']
            ));
            
            if (!empty($surrounding_content[0])) {
                $content .= "## Related Content ##\n";
                $content .= $surrounding_content[0]->article_content . "\n\n";
                $added_document_ids[] = $surrounding_content[0]->id;
            }
            
            if (!empty($surrounding_content[1])) {
                $content .= "## Related Content ##\n";
                $content .= $surrounding_content[1]->article_content . "\n\n";
                $added_document_ids[] = $surrounding_content[1]->id;
            }
        }
    }
    
        // Add response guidelines
        if (empty($top_results)) {
            $content = "No reference information was found for this query.\n\n";
        } else {
            $content .= "\n## Response Guidelines ##\n" .
                       "You are an AI Chatbot. Answer naturally and helpfully using only the information from the references above. " .
                       "Be conversational and friendly, but never mention your knowledge base or training data. " .
                       "If you don't have specific information or are uncertain about any details, it's always " .
                       "better to honestly say you don't know rather than making up or guessing at answers. " .
                       "When information is incomplete, let them know you are unsure.";
        }
    
    return trim($content);
}

private function find_relevant_content_pinecone($user_embedding) {
    $options = get_option('mxchat_pinecone_addon_options', array());
    $api_key = $options['mxchat_pinecone_api_key'] ?? '';
    $host = $options['mxchat_pinecone_host'] ?? '';
    
    // Initialize similarity analysis storage
    $this->last_similarity_analysis = [
        'knowledge_base_type' => 'Pinecone',
        'top_matches' => [],
        'threshold_used' => 0,
        'total_checked' => 0
    ];
    
    if (empty($host) || empty($api_key)) {
        return '';
    }
    
    // Get the similarity threshold from the main options
    $main_options = get_option('mxchat_options', []);
    $similarity_threshold = isset($main_options['similarity_threshold']) 
        ? ((int) $main_options['similarity_threshold']) / 100 
        : 0.75;
    
    $this->last_similarity_analysis['threshold_used'] = $similarity_threshold;
    
    // Prepare the query request for Pinecone (request more for testing)
    $api_endpoint = "https://{$host}/query";
    
    $request_body = array(
        'vector' => $user_embedding,
        'topK' => 20, // Request more to get good testing data
        'includeMetadata' => true,
        'includeValues' => true
    );
    
    $response = wp_remote_post($api_endpoint, array(
        'headers' => array(
            'Api-Key' => $api_key,
            'accept' => 'application/json',
            'content-type' => 'application/json'
        ),
        'body' => wp_json_encode($request_body),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return '';
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return '';
    }
    
    $results = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($results['matches'])) {
        return '';
    }
    
    // First, determine which matches will actually be used for content
    $matches_used_for_context = [];
    $matches_used = 0;
    
    foreach ($results['matches'] as $index => $match) {
        // Skip if similarity is below threshold
        if ($match['score'] < $similarity_threshold) {
            continue;
        }
        
        // Limit to top 5 matches above threshold
        if ($matches_used >= 5) {
            break;
        }
        
        if (!empty($match['metadata']['text'])) {
            $matches_used_for_context[] = $match['id'] ?? $index;
            $matches_used++;
        }
    }
    
    // Process ALL matches for testing data (top 10)
    $all_matches = [];
    foreach ($results['matches'] as $index => $match) {
        if ($index >= 10) break; // Limit to top 10 for testing
        
        $source_display = '';
        if (!empty($match['metadata']['source_url'])) {
            $source_display = $match['metadata']['source_url'];
        } else {
            $content_preview = strip_tags($match['metadata']['text'] ?? '');
            $content_preview = preg_replace('/\s+/', ' ', $content_preview);
            $source_display = substr(trim($content_preview), 0, 50) . '...';
        }
        
        $match_id = $match['id'] ?? $index;
        
        $all_matches[] = [
            'document_id' => $match_id,
            'similarity' => $match['score'],
            'similarity_percentage' => round($match['score'] * 100, 2),
            'above_threshold' => $match['score'] >= $similarity_threshold,
            'source_display' => $source_display,
            'content_preview' => substr(strip_tags($match['metadata']['text'] ?? ''), 0, 100) . '...',
            'used_for_context' => in_array($match_id, $matches_used_for_context) // Correct usage flag
        ];
    }
    
    // Store for testing panel
    $this->last_similarity_analysis['top_matches'] = $all_matches;
    $this->last_similarity_analysis['total_checked'] = count($results['matches']);
    
    //error_log("MxChat Testing: Stored " . count($this->last_similarity_analysis['top_matches']) . " Pinecone matches for testing");
    
    // Initialize the final content
    $content = '';
    $matches_used = 0;
    
    // Process each match for actual content (this is the real content generation)
    foreach ($results['matches'] as $index => $match) {
        // Skip if similarity is below threshold
        if ($match['score'] < $similarity_threshold) {
            continue;
        }
        
        // Limit to top 5 matches above threshold
        if ($matches_used >= 5) {
            break;
        }
        
        if (!empty($match['metadata']['text'])) {
            $content .= "## Reference " . ($matches_used + 1) . " ##\n";
            $content .= $match['metadata']['text'] . "\n\n";
            
            if (!empty($match['metadata']['source_url'])) {
                $content .= "URL: " . $match['metadata']['source_url'] . "\n\n";
            }
            
            $matches_used++;
        }
    }
    
    // Add response guidelines
    if ($matches_used === 0) {
        $content = "No reference information was found for this query.\n\n";
    } else {
         $content .= "\n## Response Guidelines ##\n" .
                       "You are an AI Chatbot. Answer naturally and helpfully using only the information from the references above. " .
                       "Be conversational and friendly, but never mention your knowledge base or training data. " .
                       "If you don't have specific information or are uncertain about any details, it's always " .
                       "better to honestly say you don't know rather than making up or guessing at answers. " .
                       "When information is incomplete, let them know you are unsure.";
    }
    
    return trim($content);
}

private function mxchat_find_relevant_products($user_embedding) {
    //error_log('MXChat Vector Search: Starting product search...');

    // Retrieve the add-on settings from the database
    $addon_options = get_option('mxchat_pinecone_addon_options', array());

    // Determine whether Pinecone is enabled
    $use_pinecone = (isset($addon_options['mxchat_use_pinecone']) && $addon_options['mxchat_use_pinecone'] === '1') ? 1 : 0;

    //error_log('Pinecone enabled flag: ' . $use_pinecone);

    if ($use_pinecone === 1) {
        //error_log('MXChat Vector Search: Using Pinecone database for products');
        return $this->find_relevant_products_pinecone($user_embedding);
    } else {
        //error_log('MXChat Vector Search: Using WordPress database for products');
        return $this->find_relevant_products_wordpress($user_embedding);
    }
}
private function find_relevant_products_wordpress($user_embedding) {
    global $wpdb;
    $system_prompt_table = $wpdb->prefix . 'mxchat_system_prompt_content';
    $cache_key = 'mxchat_system_prompt_embeddings';
    $batch_size = 500;

    // Original WordPress database search logic
    // [Previous implementation remains the same]
    $embeddings = wp_cache_get($cache_key, 'mxchat_system_prompts');
    if ($embeddings === false) {
        $embeddings = [];
        $offset = 0;

        do {
            $query = $wpdb->prepare(
                "SELECT id, embedding_vector
                FROM {$system_prompt_table}
                LIMIT %d OFFSET %d",
                $batch_size,
                $offset
            );

            $batch = $wpdb->get_results($query);
            if (empty($batch)) {
                break;
            }

            $embeddings = array_merge($embeddings, $batch);
            $offset += $batch_size;

            unset($batch);

        } while (true);

        if (empty($embeddings)) {
            return '';
        }
        wp_cache_set($cache_key, $embeddings, 'mxchat_system_prompts', 3600);
    }

    $relevant_results = [];
    foreach ($embeddings as $embedding) {
        $database_embedding = $embedding->embedding_vector
            ? unserialize($embedding->embedding_vector, ['allowed_classes' => false])
            : null;
        if (is_array($database_embedding) && is_array($user_embedding)) {
            $similarity = $this->mxchat_calculate_cosine_similarity($user_embedding, $database_embedding);
            $relevant_results[] = [
                'id' => $embedding->id,
                'similarity' => $similarity
            ];
        }
        unset($database_embedding);
    }

    // Use fixed threshold for products
    $similarity_threshold = 0.85;

    $relevant_results = array_filter($relevant_results, function ($result) use ($similarity_threshold) {
        return $result['similarity'] >= $similarity_threshold;
    });
    usort($relevant_results, function ($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });

    $top_results = array_slice($relevant_results, 0, 5);
    $content = '';

    foreach ($top_results as $result) {
        $chunk_content = $this->fetch_content_with_product_links($result['id']);
        $content .= $chunk_content . "\n\n";
    }

    return trim($content);
}
private function find_relevant_products_pinecone($user_embedding) {
    //error_log('Starting Pinecone product search...');

    $options = get_option('mxchat_pinecone_addon_options', array());
    $api_key = $options['mxchat_pinecone_api_key'] ?? '';
    $host = $options['mxchat_pinecone_host'] ?? '';

    if (empty($host) || empty($api_key)) {
        //error_log('Pinecone credentials not properly configured for product search');
        return '';
    }

    $similarity_threshold = 0.85;
    $api_endpoint = "https://{$host}/query";

    $request_body = array(
        'vector' => $user_embedding,
        'topK' => 5,
        'includeMetadata' => true,
        'includeValues' => true,
        'filter' => array(
            'type' => 'product'
        )
    );

    //error_log('Sending request to Pinecone with body: ' . wp_json_encode($request_body));

    $response = wp_remote_post($api_endpoint, array(
        'headers' => array(
            'Api-Key' => $api_key,
            'accept' => 'application/json',
            'content-type' => 'application/json'
        ),
        'body' => wp_json_encode($request_body),
        'timeout' => 30
    ));

    if (is_wp_error($response)) {
        //error_log('Pinecone product query error: ' . $response->get_error_message());
        return '';
    }

    $response_code = wp_remote_retrieve_response_code($response);
    //error_log('Pinecone response code: ' . $response_code);

    if ($response_code !== 200) {
        //error_log('Pinecone API error during product search: ' . wp_remote_retrieve_body($response));
        return '';
    }

    $results = json_decode(wp_remote_retrieve_body($response), true);
    //error_log('Pinecone raw response: ' . wp_remote_retrieve_body($response));

    if (empty($results['matches'])) {
        //error_log('No matches found in Pinecone response');
        return '';
    }

    $content = '';
    foreach ($results['matches'] as $match) {
        if ($match['score'] < $similarity_threshold) {
            //error_log("Match below threshold: " . $match['score']);
            continue;
        }

        if (!empty($match['metadata']['text'])) {
            $content .= $match['metadata']['text'];
            if (!empty($match['metadata']['source_url'])) {
                $content .= "\n\nFor more details, check out this product: " . esc_url($match['metadata']['source_url']);
            }
            $content .= "\n\n";
        }
    }

    return trim($content);
}
private function fetch_content_with_product_links($most_relevant_id) {
    global $wpdb;
    $system_prompt_table = $wpdb->prefix . 'mxchat_system_prompt_content';

    // Fetch the article content and associated product URL
    $query = $wpdb->prepare("SELECT article_content, source_url FROM {$system_prompt_table} WHERE id = %d", $most_relevant_id);
    $result = $wpdb->get_row($query);

    if ($result) {
        // Append the product link to the content if available
        $content = $result->article_content;
        if (!empty($result->source_url)) {
            $content .= "\n\nFor more details, check out this product: " . esc_url($result->source_url);
        }
        return $content;
    }

    return null;
}

/**
 * Modified streaming functions to include testing data
 */

// 1. Update the main handler to pass testing data to streaming functions
private function mxchat_generate_response($relevant_content, $api_key, $xai_api_key, $claude_api_key, $deepseek_api_key, $gemini_api_key, $conversation_history, $streaming = false, $session_id = '', $testing_data = null) {
    try {
        if (!$relevant_content) {
            $error_response = [
                'error' => esc_html__("I couldn't find relevant information on that topic.", 'mxchat'),
                'error_code' => 'no_relevant_content'
            ];
            
            // Add testing data to error response if available
            if ($testing_data !== null) {
                $error_response['testing_data'] = $testing_data;
                //error_log("MxChat Testing: Added testing data to no_relevant_content error");
            }
            
            return $error_response;
        }
        
        // Ensure conversation_history is an array
        if (!is_array($conversation_history)) {
            $conversation_history = array();
        }
        
        // Get selected model with default fallback
        $selected_model = isset($this->options['model']) ? $this->options['model'] : 'gpt-4o';
        
        // Extract model prefix to determine the provider
        $model_parts = explode('-', $selected_model);
        $provider = strtolower($model_parts[0]);
        
        // Handle model selection based on provider prefix
        switch ($provider) {
            case 'gemini':
                if (empty($gemini_api_key)) {
                    $error_response = [
                        'error' => esc_html__('Google Gemini API key is not configured', 'mxchat'),
                        'error_code' => 'missing_gemini_api_key'
                    ];
                    if ($testing_data !== null) {
                        $error_response['testing_data'] = $testing_data;
                    }
                    return $error_response;
                }
                $response = $this->mxchat_generate_response_gemini(
                    $selected_model,
                    $gemini_api_key,
                    $conversation_history,
                    $relevant_content
                );
                break;
                
            case 'claude':
                if (empty($claude_api_key)) {
                    $error_response = [
                        'error' => esc_html__('Claude API key is not configured', 'mxchat'),
                        'error_code' => 'missing_claude_api_key'
                    ];
                    if ($testing_data !== null) {
                        $error_response['testing_data'] = $testing_data;
                    }
                    return $error_response;
                }
                if ($streaming) {
                    return $this->mxchat_generate_response_claude_stream(
                        $selected_model,
                        $claude_api_key,
                        $conversation_history,
                        $relevant_content,
                        $session_id,
                        $testing_data  // Pass testing data
                    );
                } else {
                    $response = $this->mxchat_generate_response_claude(
                        $selected_model,
                        $claude_api_key,
                        $conversation_history,
                        $relevant_content
                    );
                }
                break;
                
            case 'grok':
                if (empty($xai_api_key)) {
                    $error_response = [
                        'error' => esc_html__('X.AI API key is not configured', 'mxchat'),
                        'error_code' => 'missing_xai_api_key'
                    ];
                    if ($testing_data !== null) {
                        $error_response['testing_data'] = $testing_data;
                    }
                    return $error_response;
                }
                $response = $this->mxchat_generate_response_xai(
                    $selected_model,
                    $xai_api_key,
                    $conversation_history,
                    $relevant_content
                );
                break;
                
            case 'deepseek':
                if (empty($deepseek_api_key)) {
                    $error_response = [
                        'error' => esc_html__('DeepSeek API key is not configured', 'mxchat'),
                        'error_code' => 'missing_deepseek_api_key'
                    ];
                    if ($testing_data !== null) {
                        $error_response['testing_data'] = $testing_data;
                    }
                    return $error_response;
                }
                $response = $this->mxchat_generate_response_deepseek(
                    $selected_model,
                    $deepseek_api_key,
                    $conversation_history,
                    $relevant_content
                );
                break;
                
            case 'gpt':
            case 'o1':
                if (empty($api_key)) {
                    $error_response = [
                        'error' => esc_html__('OpenAI API key is not configured', 'mxchat'),
                        'error_code' => 'missing_openai_api_key'
                    ];
                    if ($testing_data !== null) {
                        $error_response['testing_data'] = $testing_data;
                    }
                    return $error_response;
                }
                if ($streaming) {
                    return $this->mxchat_generate_response_openai_stream(
                        $selected_model,
                        $api_key,
                        $conversation_history,
                        $relevant_content,
                        $session_id,
                        $testing_data  // Pass testing data
                    );
                } else {
                    $response = $this->mxchat_generate_response_openai(
                        $selected_model,
                        $api_key,
                        $conversation_history,
                        $relevant_content
                    );
                }
                break;
                
            default:
                // Default to OpenAI for custom models or unrecognized prefixes
                if (empty($api_key)) {
                    $error_response = [
                        'error' => esc_html__('OpenAI API key is not configured', 'mxchat'),
                        'error_code' => 'missing_openai_api_key'
                    ];
                    if ($testing_data !== null) {
                        $error_response['testing_data'] = $testing_data;
                    }
                    return $error_response;
                }
                if ($streaming) {
                    return $this->mxchat_generate_response_openai_stream(
                        $selected_model,
                        $api_key,
                        $conversation_history,
                        $relevant_content,
                        $session_id,
                        $testing_data  // Pass testing data
                    );
                } else {
                    $response = $this->mxchat_generate_response_openai(
                        $selected_model,
                        $api_key,
                        $conversation_history,
                        $relevant_content
                    );
                }
                break;
        }
        
        // Check if the response is an error array from the provider-specific function
        if (is_array($response) && isset($response['error'])) {
            // Add testing data to error response if available
            if ($testing_data !== null) {
                $response['testing_data'] = $testing_data;
                //error_log("MxChat Testing: Added testing data to provider error response");
            }
            return $response; // Pass through the error with testing data
        }
        
        // For successful non-streaming responses, we don't add testing data here
        // because it will be added in the main handler
        return $response;
        
    } catch (Exception $e) {
        //error_log('MXChat Error: ' . $e->getMessage());
        $error_response = [
            'error' => sprintf(esc_html__('An error occurred: %s', 'mxchat'), esc_html($e->getMessage())),
            'error_code' => 'system_exception',
            'exception_details' => $e->getMessage()
        ];
        
        // Add testing data to exception response if available
        if ($testing_data !== null) {
            $error_response['testing_data'] = $testing_data;
            //error_log("MxChat Testing: Added testing data to exception response");
        }
        
        return $error_response;
    }
}
// 2. Update Claude streaming function
private function mxchat_generate_response_claude_stream($selected_model, $claude_api_key, $conversation_history, $relevant_content, $session_id, $testing_data = null) {
    try {
        // Get system prompt instructions from options
        $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';

        // Ensure conversation_history is an array
        if (!is_array($conversation_history)) {
            $conversation_history = array();
        }

        // Clean and validate conversation history
        foreach ($conversation_history as &$message) {
            // Convert bot and agent roles to assistant
            if ($message['role'] === 'bot' || $message['role'] === 'agent') {
                $message['role'] = 'assistant';
            }
            
            // Remove unsupported roles - Claude only supports 'assistant' and 'user'
            if (!in_array($message['role'], ['assistant', 'user'])) {
                $message['role'] = 'user';
            }

            // Ensure content field exists
            if (!isset($message['content']) || empty($message['content'])) {
                $message['content'] = '';
            }

            // Remove any unsupported fields
            $message = array_intersect_key($message, array_flip(['role', 'content']));
        }

        // Add relevant content as the latest user message
        $conversation_history[] = [
            'role' => 'user',
            'content' => $relevant_content
        ];

        // Prepare the request body with stream: true
        $body = json_encode([
            'model' => $selected_model,
            'messages' => $conversation_history,
            'max_tokens' => 1000,
            'temperature' => 0.8,
            'system' => $system_prompt_instructions,
            'stream' => true
        ]);

        // Check if we can actually stream (headers not sent, etc.)
        if (headers_sent() || !function_exists('curl_init')) {
            // Fallback to regular response with testing data
            //error_log("MxChat: Streaming not possible, falling back to regular response");
            $regular_response = $this->mxchat_generate_response_claude(
                $selected_model,
                $claude_api_key,
                array_slice($conversation_history, 0, -1), // Remove the added content
                $relevant_content
            );
            
            // Return as JSON with testing data
            $response_data = [
                'text' => $regular_response,
                'html' => '',
                'session_id' => $session_id
            ];
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
                //error_log("MxChat Testing: Added testing data to Claude fallback response");
            }
            
            // Clear any streaming headers and send JSON
            if (headers_sent() === false) {
                header('Content-Type: application/json');
            }
            echo json_encode($response_data);
            return true; // Indicate we handled the response
        }

        // Use cURL for streaming support
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.anthropic.com/v1/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'x-api-key: ' . $claude_api_key,
            'anthropic-version: 2023-06-01'
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $full_response = ''; // Accumulate full response for saving
        $stream_started = false;

        // Buffer control for real-time streaming
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$full_response, &$stream_started, $testing_data) {
            // Send testing data as the first event if available
            if (!$stream_started && $testing_data !== null) {
                echo "data: " . json_encode(['testing_data' => $testing_data]) . "\n\n";
                flush();
                $stream_started = true;
                //error_log("MxChat Testing: Sent testing data in Claude stream");
            }
            
            // Process each chunk of data
            $lines = explode("\n", $data);

            foreach ($lines as $line) {
                if (trim($line) === '') {
                    continue;
                }

                // Claude uses event: and data: format
                if (strpos($line, 'event: ') === 0) {
                    // Store the event type for the next data line
                    continue;
                }

                if (strpos($line, 'data: ') === 0) {
                    $json_str = substr($line, 6); // Remove 'data: ' prefix

                    $json = json_decode($json_str, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        continue;
                    }

                    // Handle different event types
                    if (isset($json['type'])) {
                        switch ($json['type']) {
                            case 'content_block_delta':
                                if (isset($json['delta']['text'])) {
                                    $content = $json['delta']['text'];
                                    $full_response .= $content; // Accumulate
                                    // Send as SSE format compatible with your frontend
                                    echo "data: " . json_encode(['content' => $content]) . "\n\n";
                                    flush();
                                }
                                break;

                            case 'message_stop':
                                echo "data: [DONE]\n\n";
                                flush();
                                break;

                            case 'error':
                                echo "data: " . json_encode(['error' => $json['error']['message'] ?? 'Unknown error']) . "\n\n";
                                flush();
                                break;
                        }
                    }
                }
            }

            return strlen($data);
        });

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            throw new Exception('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($http_code !== 200) {
            // Fallback to regular response
            //error_log("MxChat: Claude streaming failed with HTTP $http_code, falling back");
            $regular_response = $this->mxchat_generate_response_claude(
                $selected_model,
                $claude_api_key,
                array_slice($conversation_history, 0, -1), // Remove the added content
                $relevant_content
            );
            
            $response_data = [
                'text' => $regular_response,
                'html' => '',
                'session_id' => $session_id
            ];
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
                //error_log("MxChat Testing: Added testing data to Claude error fallback");
            }
            
            header('Content-Type: application/json');
            echo json_encode($response_data);
            return true;
        }

        // Save the complete response to maintain chat persistence
        if (!empty($full_response) && !empty($session_id)) {
            $this->mxchat_save_chat_message($session_id, 'bot', $full_response);
        }

        return true; // Indicate streaming completed successfully

    } catch (Exception $e) {
        //error_log("MxChat Claude streaming exception: " . $e->getMessage());
        
        // Fallback to regular response on exception
        $regular_response = $this->mxchat_generate_response_claude(
            $selected_model,
            $claude_api_key,
            $conversation_history,
            $relevant_content
        );
        
        $response_data = [
            'text' => $regular_response,
            'html' => '',
            'session_id' => $session_id
        ];
        
        if ($testing_data !== null) {
            $response_data['testing_data'] = $testing_data;
            //error_log("MxChat Testing: Added testing data to Claude exception fallback");
        }
        
        header('Content-Type: application/json');
        echo json_encode($response_data);
        return true;
    }
}

// 3. Update OpenAI streaming function similarly
private function mxchat_generate_response_openai_stream($selected_model, $api_key, $conversation_history, $relevant_content, $session_id, $testing_data = null) {
    try {
        // Get system prompt instructions from options
        $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';
        
        // Ensure conversation_history is an array
        if (!is_array($conversation_history)) {
            $conversation_history = array();
        }

        // Format conversation history for OpenAI
        $formatted_conversation = array();

        $formatted_conversation[] = array(
            'role' => 'system',
            'content' => $system_prompt_instructions . " " . $relevant_content
        );

        foreach ($conversation_history as $message) {
            if (is_array($message) && isset($message['role']) && isset($message['content'])) {
                $role = $message['role'];
                if ($role === 'bot' || $role === 'agent') {
                    $role = 'assistant';
                }
                if (!in_array($role, ['system', 'assistant', 'user', 'function', 'tool'])) {
                    $role = 'user';
                }
                $formatted_conversation[] = array(
                    'role' => $role,
                    'content' => $message['content']
                );
            }
        }

        // Check if we can actually stream
        if (headers_sent() || !function_exists('curl_init')) {
            // Fallback to regular response with testing data
            //error_log("MxChat: OpenAI streaming not possible, falling back to regular response");
            $regular_response = $this->mxchat_generate_response_openai(
                $selected_model,
                $api_key,
                $conversation_history,
                $relevant_content
            );
            
            $response_data = [
                'text' => $regular_response,
                'html' => '',
                'session_id' => $session_id
            ];
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
                //error_log("MxChat Testing: Added testing data to OpenAI fallback response");
            }
            
            header('Content-Type: application/json');
            echo json_encode($response_data);
            return true;
        }

        // Prepare the request body with stream: true
        $body = json_encode([
            'model' => $selected_model,
            'messages' => $formatted_conversation,
            'temperature' => 0.8,
            'stream' => true
        ]);

        // Use cURL for streaming support
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $full_response = ''; // Accumulate full response for saving
        $stream_started = false;
        
        // Buffer control for real-time streaming
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$full_response, &$stream_started, $testing_data) {
            // Send testing data as the first event if available
            if (!$stream_started && $testing_data !== null) {
                echo "data: " . json_encode(['testing_data' => $testing_data]) . "\n\n";
                flush();
                $stream_started = true;
                //error_log("MxChat Testing: Sent testing data in OpenAI stream");
            }
            
            // Process each chunk of data
            $lines = explode("\n", $data);
            
            foreach ($lines as $line) {
                if (trim($line) === '' || strpos($line, 'data: ') !== 0) {
                    continue;
                }
                
                $json_str = substr($line, 6); // Remove 'data: ' prefix
                
                if ($json_str === '[DONE]') {
                    echo "data: [DONE]\n\n";
                    flush();
                    continue;
                }
                
                $json = json_decode($json_str, true);
                if (isset($json['choices'][0]['delta']['content'])) {
                    $content = $json['choices'][0]['delta']['content'];
                    $full_response .= $content; // Accumulate
                    // Send as SSE format
                    echo "data: " . json_encode(['content' => $content]) . "\n\n";
                    flush();
                }
            }
            
            return strlen($data);
        });
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch) || $http_code !== 200) {
            curl_close($ch);
            
            // Fallback to regular response
            //error_log("MxChat: OpenAI streaming failed, falling back");
            $regular_response = $this->mxchat_generate_response_openai(
                $selected_model,
                $api_key,
                $conversation_history,
                $relevant_content
            );
            
            $response_data = [
                'text' => $regular_response,
                'html' => '',
                'session_id' => $session_id
            ];
            
            if ($testing_data !== null) {
                $response_data['testing_data'] = $testing_data;
                //error_log("MxChat Testing: Added testing data to OpenAI error fallback");
            }
            
            header('Content-Type: application/json');
            echo json_encode($response_data);
            return true;
        }
        
        curl_close($ch);
        
        // Save the complete response to maintain chat persistence
        if (!empty($full_response) && !empty($session_id)) {
            $this->mxchat_save_chat_message($session_id, 'bot', $full_response);
        }
        
        return true; // Indicate streaming completed successfully
        
    } catch (Exception $e) {
        //error_log("MxChat OpenAI streaming exception: " . $e->getMessage());
        
        // Fallback to regular response
        $regular_response = $this->mxchat_generate_response_openai(
            $selected_model,
            $api_key,
            $conversation_history,
            $relevant_content
        );
        
        $response_data = [
            'text' => $regular_response,
            'html' => '',
            'session_id' => $session_id
        ];
        
        if ($testing_data !== null) {
            $response_data['testing_data'] = $testing_data;
            //error_log("MxChat Testing: Added testing data to OpenAI exception fallback");
        }
        
        header('Content-Type: application/json');
        echo json_encode($response_data);
        return true;
    }
}

private function mxchat_generate_response_claude($selected_model, $claude_api_key, $conversation_history, $relevant_content) {
    // Get system prompt instructions from options
    $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';

    // Clean and validate conversation history
    foreach ($conversation_history as &$message) {
        // Convert bot and agent roles to assistant
        if ($message['role'] === 'bot' || $message['role'] === 'agent') {
            $message['role'] = 'assistant';
        }
        
        // Remove unsupported roles - Claude only supports 'assistant' and 'user'
        if (!in_array($message['role'], ['assistant', 'user'])) {
            $message['role'] = 'user';
        }

        // Ensure content field exists
        if (!isset($message['content']) || empty($message['content'])) {
            $message['content'] = '';
        }

        // Remove any unsupported fields
        $message = array_intersect_key($message, array_flip(['role', 'content']));
    }

    // Add relevant content as the latest user message
    $conversation_history[] = [
        'role' => 'user',
        'content' => $relevant_content
    ];

    // Build request body
    $body = json_encode([
        'model' => $selected_model,
        'max_tokens' => 1000,
        'temperature' => 0.8,
        'messages' => $conversation_history,
        'system' => $system_prompt_instructions
    ]);

    // Set up API request
    $args = [
        'body' => $body,
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $claude_api_key,
                'anthropic-version' => '2023-06-01'
            ],
        'timeout' => 60,
        'redirection' => 5,
        'blocking' => true,
        'httpversion' => '1.0',
        'sslverify' => true,
    ];

    // Make API request
    $response = wp_remote_post('https://api.anthropic.com/v1/messages', $args);

    // Check for WordPress errors
    if (is_wp_error($response)) {
        //error_log("Claude API request error: " . $response->get_error_message());
        return "Sorry, there was an error connecting to the API.";
    }

    // Check HTTP response code
    $http_code = wp_remote_retrieve_response_code($response);
    if ($http_code !== 200) {
        $error_body = wp_remote_retrieve_body($response);
        //error_log("Claude API HTTP error: " . $http_code . " - " . $error_body);
        
        // Try to extract error message from response
        $error_data = json_decode($error_body, true);
        $error_message = isset($error_data['error']['message']) ? 
            $error_data['error']['message'] : 
            "HTTP error " . $http_code;
            
        return "Sorry, the API returned an error: " . $error_message;
    }

    // Parse response
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        //error_log("Claude API JSON decode error: " . json_last_error_msg());
        return "Sorry, there was an error processing the API response.";
    }

    // Extract and validate response content
    if (isset($response_body['content']) && 
        is_array($response_body['content']) && 
        !empty($response_body['content']) && 
        isset($response_body['content'][0]['text'])) {
        return trim($response_body['content'][0]['text']);
    }

    // Log unexpected response format
    //error_log("Claude API unexpected response format: " . print_r($response_body, true));
    return "Sorry, I received an unexpected response format from the API.";
}
private function mxchat_generate_response_openai($selected_model, $api_key, $conversation_history, $relevant_content) {
    try {
        // Ensure conversation_history is an array
        if (!is_array($conversation_history)) {
            $conversation_history = array();
        }

        // Get system prompt instructions from options
        $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';

        // Create a new array for the formatted conversation
        $formatted_conversation = array();

        // Add system message first
        $formatted_conversation[] = array(
            'role' => 'system',
            'content' => $system_prompt_instructions . " " . $relevant_content
        );

        // Add the rest of the conversation history
        foreach ($conversation_history as $message) {
            if (is_array($message) && isset($message['role']) && isset($message['content'])) {
                $role = $message['role'];

                // Convert roles to supported format
                if ($role === 'bot' || $role === 'agent') {
                    $role = 'assistant';
                }
                if (!in_array($role, ['system', 'assistant', 'user', 'function', 'tool'])) {
                    $role = 'user';
                }

                $formatted_conversation[] = array(
                    'role' => $role,
                    'content' => $message['content']
                );
            }
        }

        $body = json_encode([
            'model' => $selected_model,
            'messages' => $formatted_conversation,
            'temperature' => 0.8,
            'stream' => false
        ]);

        $args = [
            'body'        => $body,
            'headers'     => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => true,
        ];

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            //error_log('OpenAI API Error: ' . $error_message);
            return [
                'error' => esc_html__('Connection error when contacting OpenAI: ', 'mxchat') . esc_html($error_message),
                'error_code' => 'openai_connection_error',
                'provider' => 'openai'
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $response_body = wp_remote_retrieve_body($response);
            $decoded_response = json_decode($response_body, true);
            
            $error_message = isset($decoded_response['error']['message']) 
                ? $decoded_response['error']['message'] 
                : 'HTTP Error ' . $status_code;
            
            $error_type = isset($decoded_response['error']['type']) 
                ? $decoded_response['error']['type'] 
                : 'unknown';
            
            //error_log('OpenAI API HTTP Error: ' . $status_code . ' - ' . $error_message);
            
            // Handle specific error types
            switch ($error_type) {
                case 'invalid_request_error':
                    if (strpos($error_message, 'API key') !== false) {
                        return [
                            'error' => esc_html__('Invalid OpenAI API key. Please check your API key configuration.', 'mxchat'),
                            'error_code' => 'openai_invalid_api_key',
                            'provider' => 'openai'
                        ];
                    }
                    break;
                    
                case 'authentication_error':
                    return [
                        'error' => esc_html__('Authentication failed with OpenAI. Please check your API key.', 'mxchat'),
                        'error_code' => 'openai_auth_error',
                        'provider' => 'openai'
                    ];
                
                case 'rate_limit_exceeded':
                    return [
                        'error' => esc_html__('OpenAI rate limit exceeded. Please try again later.', 'mxchat'),
                        'error_code' => 'openai_rate_limit',
                        'provider' => 'openai'
                    ];
                    
                case 'quota_exceeded':
                    return [
                        'error' => esc_html__('OpenAI API quota exceeded. Please check your billing details.', 'mxchat'),
                        'error_code' => 'openai_quota_exceeded',
                        'provider' => 'openai'
                    ];
            }
            
            // Generic error fallback
            return [
                'error' => esc_html__('OpenAI API error: ', 'mxchat') . esc_html($error_message),
                'error_code' => 'openai_api_error',
                'provider' => 'openai',
                'status_code' => $status_code
            ];
        }

        $response_body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($response_body, true);

        if (isset($decoded_response['choices'][0]['message']['content'])) {
            return trim($decoded_response['choices'][0]['message']['content']);
        } else {
            //error_log('OpenAI API Response Format Error: ' . print_r($decoded_response, true));
            return [
                'error' => esc_html__('Unexpected response format from OpenAI.', 'mxchat'),
                'error_code' => 'openai_response_format_error',
                'provider' => 'openai'
            ];
        }
    } catch (Exception $e) {
        //error_log('OpenAI Exception: ' . $e->getMessage());
        return [
            'error' => esc_html__('System error when processing OpenAI request: ', 'mxchat') . esc_html($e->getMessage()),
            'error_code' => 'openai_exception',
            'provider' => 'openai'
        ];
    }
}

private function mxchat_generate_response_deepseek($selected_model, $deepseek_api_key, $conversation_history, $relevant_content) {
    try {
        // Ensure conversation_history is an array
        if (!is_array($conversation_history)) {
            $conversation_history = array();
        }

        // Get system prompt instructions from options
        $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';

        // Create a new array for the formatted conversation
        $formatted_conversation = array();

        // Add system message first
        $formatted_conversation[] = array(
            'role' => 'system',
            'content' => $system_prompt_instructions . " " . $relevant_content
        );

        // Add the rest of the conversation history
        foreach ($conversation_history as $message) {
            if (is_array($message) && isset($message['role']) && isset($message['content'])) {
                $role = $message['role'];

                // Convert roles to supported format
                if ($role === 'bot' || $role === 'agent') {
                    $role = 'assistant';
                }
                if (!in_array($role, ['system', 'assistant', 'user', 'function', 'tool'])) {
                    $role = 'user';
                }

                $formatted_conversation[] = array(
                    'role' => $role,
                    'content' => $message['content']
                );
            }
        }

        $body = json_encode([
            'model' => $selected_model,
            'messages' => $formatted_conversation,
            'temperature' => 0.8,
            'stream' => false
        ]);

        $args = [
            'body'        => $body,
            'headers'     => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $deepseek_api_key,
            ],
            'timeout'     => 60,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => true,
        ];

        $response = wp_remote_post('https://api.deepseek.com/v1/chat/completions', $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            //error_log('DeepSeek API Error: ' . $error_message);
            return [
                'error' => esc_html__('Connection error when contacting DeepSeek: ', 'mxchat') . esc_html($error_message),
                'error_code' => 'deepseek_connection_error',
                'provider' => 'deepseek'
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $response_body = wp_remote_retrieve_body($response);
            $decoded_response = json_decode($response_body, true);

            $error_message = isset($decoded_response['error']['message']) 
                ? $decoded_response['error']['message'] 
                : 'HTTP Error ' . $status_code;
                
            $error_type = isset($decoded_response['error']['type']) 
                ? $decoded_response['error']['type'] 
                : 'unknown';
                
            //error_log('DeepSeek API HTTP Error: ' . $status_code . ' - ' . $error_message);

            // Handle specific error types
            switch ($status_code) {
                case 401:
                    return [
                        'error' => esc_html__('Authentication failed with DeepSeek. Please check your API key.', 'mxchat'),
                        'error_code' => 'deepseek_auth_error',
                        'provider' => 'deepseek'
                    ];
                
                case 400:
                    if (strpos($error_message, 'API key') !== false) {
                        return [
                            'error' => esc_html__('Invalid DeepSeek API key. Please check your API key configuration.', 'mxchat'),
                            'error_code' => 'deepseek_invalid_api_key',
                            'provider' => 'deepseek'
                        ];
                    }
                    break;
                    
                case 429:
                    if (strpos($error_message, 'quota') !== false) {
                        return [
                            'error' => esc_html__('DeepSeek API quota exceeded. Please check your billing details.', 'mxchat'),
                            'error_code' => 'deepseek_quota_exceeded',
                            'provider' => 'deepseek'
                        ];
                    } else {
                        return [
                            'error' => esc_html__('DeepSeek rate limit exceeded. Please try again later.', 'mxchat'),
                            'error_code' => 'deepseek_rate_limit',
                            'provider' => 'deepseek'
                        ];
                    }
                    
                case 500:
                case 502:
                case 503:
                case 504:
                    return [
                        'error' => esc_html__('DeepSeek service is currently unavailable. Please try again later.', 'mxchat'),
                        'error_code' => 'deepseek_service_unavailable',
                        'provider' => 'deepseek'
                    ];
            }

            // Generic error fallback
            return [
                'error' => esc_html__('DeepSeek API error: ', 'mxchat') . esc_html($error_message),
                'error_code' => 'deepseek_api_error',
                'provider' => 'deepseek',
                'status_code' => $status_code
            ];
        }

        $response_body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($response_body, true);

        if (isset($decoded_response['choices'][0]['message']['content'])) {
            return trim($decoded_response['choices'][0]['message']['content']);
        } else {
            //error_log('DeepSeek API Response Format Error: ' . print_r($decoded_response, true));
            return [
                'error' => esc_html__('Unexpected response format from DeepSeek.', 'mxchat'),
                'error_code' => 'deepseek_response_format_error',
                'provider' => 'deepseek'
            ];
        }
    } catch (Exception $e) {
        //error_log('DeepSeek Exception: ' . $e->getMessage());
        return [
            'error' => esc_html__('System error when processing DeepSeek request: ', 'mxchat') . esc_html($e->getMessage()),
            'error_code' => 'deepseek_exception',
            'provider' => 'deepseek'
        ];
    }
}
private function mxchat_generate_response_xai($selected_model, $xai_api_key, $conversation_history, $relevant_content) {
    try {
        // Get system prompt instructions from options
        $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';

        // Add system prompt to relevant content
    $content_with_instructions = $system_prompt_instructions . " " . $relevant_content;

    // Prepend system instructions to the conversation history
    array_unshift($conversation_history, [
        'role' => 'system',
        'content' => "Here are your instructions: " . $content_with_instructions
    ]);

    // Ensure consistency: Replace 'bot' and 'agent' roles with supported values
    foreach ($conversation_history as &$message) {
        if ($message['role'] === 'bot') {
            $message['role'] = 'assistant';
        } elseif ($message['role'] === 'agent') {
            // Tag the message as coming from a live agent
            $message['role'] = 'assistant';
            if (!isset($message['metadata'])) {
                $message['metadata'] = ['source' => 'live_agent'];
            }
        }

        // Ensure all roles are valid
        if (!in_array($message['role'], ['system', 'assistant', 'user', 'function', 'tool'])) {
            $message['role'] = 'user'; // Default to 'user'
        }
    }

    // Build the request body
    $body = json_encode([
        'model' => $selected_model,
        'messages' => $conversation_history,
        'temperature' => 0.8,
        'stream' => false
    ]);

    // Set up the API request
    $args = [
        'body'        => $body,
        'headers'     => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $xai_api_key,
        ],
        'timeout'     => 60,
        'redirection' => 5,
        'blocking'    => true,
        'httpversion' => '1.0',
        'sslverify'   => true,
    ];

    // Make the API request
    $response = wp_remote_post('https://api.x.ai/v1/chat/completions', $args);

    // Process the response
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        //error_log('X.AI API Error: ' . $error_message);
        return [
            'error' => esc_html__('Connection error when contacting X.AI: ', 'mxchat') . esc_html($error_message),
            'error_code' => 'xai_connection_error',
            'provider' => 'xai'
        ];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        $response_body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($response_body, true);

        // Log the full response for debugging
        //error_log('X.AI Error Response: ' . print_r($decoded_response, true));

        // Extract error message from X.AI's specific format
        $error_message = '';
        
        // Check for direct error string (as seen in your logs)
        if (isset($decoded_response['error']) && is_string($decoded_response['error'])) {
            $error_message = $decoded_response['error'];
        }
        // Check for nested error object (OpenAI style)
        elseif (isset($decoded_response['error']['message'])) {
            $error_message = $decoded_response['error']['message'];
        }
        // Check for top-level message
        elseif (isset($decoded_response['message'])) {
            $error_message = $decoded_response['message'];
        }
        // Fallback
        else {
            $error_message = 'HTTP Error ' . $status_code;
        }

        //error_log('X.AI API HTTP Error: ' . $status_code . ' - ' . $error_message);

        // Check for API key errors using string matching
        if (stripos($error_message, 'api key') !== false || 
            stripos($error_message, 'incorrect api key') !== false ||
            stripos($error_message, 'invalid api key') !== false) {
            return [
                'error' => esc_html__('Invalid X.AI API key. Please check your API key configuration.', 'mxchat'),
                'error_code' => 'xai_invalid_api_key',
                'provider' => 'xai'
            ];
        }

        // Authentication errors
        if ($status_code === 401 || $status_code === 403 || 
            stripos($error_message, 'auth') !== false) {
            return [
                'error' => esc_html__('Authentication failed with X.AI. Please check your API key.', 'mxchat'),
                'error_code' => 'xai_auth_error',
                'provider' => 'xai'
            ];
        }

        // Model errors
        if (stripos($error_message, 'model') !== false) {
            return [
                'error' => esc_html__('Invalid model specified for X.AI. Please check your model configuration.', 'mxchat'),
                'error_code' => 'xai_invalid_model',
                'provider' => 'xai'
            ];
        }

        // Rate limit errors
        if ($status_code === 429 || 
            stripos($error_message, 'rate') !== false || 
            stripos($error_message, 'limit') !== false) {
            return [
                'error' => esc_html__('X.AI rate limit exceeded. Please try again later.', 'mxchat'),
                'error_code' => 'xai_rate_limit',
                'provider' => 'xai'
            ];
        }

        // Quota errors
        if (stripos($error_message, 'quota') !== false || 
            stripos($error_message, 'billing') !== false) {
            return [
                'error' => esc_html__('X.AI API quota exceeded. Please check your billing details.', 'mxchat'),
                'error_code' => 'xai_quota_exceeded',
                'provider' => 'xai'
            ];
        }

        // Server errors
        if ($status_code >= 500) {
            return [
                'error' => esc_html__('X.AI service is currently unavailable. Please try again later.', 'mxchat'),
                'error_code' => 'xai_service_unavailable',
                'provider' => 'xai'
            ];
        }

        // Generic error fallback with the actual error message
        return [
            'error' => esc_html__('X.AI API error: ', 'mxchat') . esc_html($error_message),
            'error_code' => 'xai_api_error',
            'provider' => 'xai',
            'status_code' => $status_code
        ];
    }

    $response_body = wp_remote_retrieve_body($response);
    $decoded_response = json_decode($response_body, true);

    if (isset($decoded_response['choices'][0]['message']['content'])) {
        return trim($decoded_response['choices'][0]['message']['content']);
    } else {
        //error_log('X.AI API Response Format Error: ' . print_r($decoded_response, true));
        return [
            'error' => esc_html__('Unexpected response format from X.AI.', 'mxchat'),
            'error_code' => 'xai_response_format_error',
            'provider' => 'xai'
        ];
    }
} catch (Exception $e) {
    //error_log('X.AI Exception: ' . $e->getMessage());
    return [
        'error' => esc_html__('System error when processing X.AI request: ', 'mxchat') . esc_html($e->getMessage()),
        'error_code' => 'xai_exception',
        'provider' => 'xai'
    ];
}
}
private function mxchat_generate_response_gemini($selected_model, $gemini_api_key, $conversation_history, $relevant_content) {
    // Get system prompt instructions from options
    $system_prompt_instructions = isset($this->options['system_prompt_instructions']) ? $this->options['system_prompt_instructions'] : '';
    
    // Add system prompt to relevant content
    $content_with_instructions = $system_prompt_instructions . " " . $relevant_content;
    
    // Format messages for Gemini API
    $formatted_messages = [];
    
    // Add system message as the first user message with role prefix
    // Note: Gemini doesn't have a dedicated system role, so we use a prefixed user message
    $formatted_messages[] = [
        'role' => 'user',
        'parts' => [
            ['text' => "[System Instructions] " . $content_with_instructions]
        ]
    ];
    
    // Add model response to acknowledge system instructions
    $formatted_messages[] = [
        'role' => 'model',
        'parts' => [
            ['text' => "I understand and will follow these instructions."]
        ]
    ];
    
    // Process the rest of the conversation history
    $current_role = null;
    $current_parts = [];
    
    foreach ($conversation_history as $message) {
        // Skip the first system message as we already handled it
        if ($message['role'] === 'system') {
            continue;
        }
        
        // Map roles to Gemini format
        $gemini_role = '';
        if ($message['role'] === 'user') {
            $gemini_role = 'user';
        } else if (in_array($message['role'], ['assistant', 'bot', 'agent'])) {
            $gemini_role = 'model';
        } else {
            // Skip unsupported roles
            continue;
        }
        
        // If we have a new role, add the previous message
        if ($current_role !== null && $current_role !== $gemini_role && !empty($current_parts)) {
            $formatted_messages[] = [
                'role' => $current_role,
                'parts' => $current_parts
            ];
            $current_parts = [];
        }
        
        // Set current role and add text to parts
        $current_role = $gemini_role;
        $current_parts[] = ['text' => $message['content']];
    }
    
    // Add the last message if there's content
    if ($current_role !== null && !empty($current_parts)) {
        $formatted_messages[] = [
            'role' => $current_role,
            'parts' => $current_parts
        ];
    }
    
    // Build the request body
    $body = json_encode([
        'contents' => $formatted_messages,
        'generationConfig' => [
            'temperature' => 0.7,
            'topP' => 0.95,
            'topK' => 40,
            'maxOutputTokens' => 8192,
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
        ]
    ]);
    
    // Prepare the API endpoint
    $api_endpoint = 'https://generativelanguage.googleapis.com/v1/models/' . $selected_model . ':generateContent?key=' . $gemini_api_key;
    
    // Set up the API request
    $args = [
        'body'        => $body,
        'headers'     => [
            'Content-Type' => 'application/json',
        ],
        'timeout'     => 60,
        'redirection' => 5,
        'blocking'    => true,
        'httpversion' => '1.0',
        'sslverify'   => true,
    ];
    
    // Make the API request
    $response = wp_remote_post($api_endpoint, $args);
    
    // Process the response
    if (is_wp_error($response)) {
        return "Sorry, there was an error processing your request: " . $response->get_error_message();
    }
    
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Handle potential errors in the response
    if (isset($response_body['error'])) {
        //error_log('Gemini API Error: ' . json_encode($response_body['error']));
        return "Sorry, there was an error with the Gemini API: " . 
               (isset($response_body['error']['message']) ? $response_body['error']['message'] : 'Unknown error');
    }
    
    // Extract the response text
    if (isset($response_body['candidates'][0]['content']['parts'][0]['text'])) {
        return trim($response_body['candidates'][0]['content']['parts'][0]['text']);
    } else {
        //error_log('Unexpected Gemini API response format: ' . json_encode($response_body));
        return "Sorry, I couldn't process that request. The response format was unexpected.";
    }
}



public function mxchat_dismiss_pre_chat_message() {
    // Get and sanitize the user identifier
    $user_id = $this->mxchat_get_user_identifier();
    $user_id = sanitize_key($user_id);

    // Set a transient to track that the user has dismissed the pre-chat message
    $transient_key = 'mxchat_pre_chat_message_dismissed_' . $user_id;
    set_transient($transient_key, true, DAY_IN_SECONDS);

    wp_send_json_success();
}

public function mxchat_check_pre_chat_message_status() {
    // Get and sanitize the user identifier
    $user_id = $this->mxchat_get_user_identifier();
    $user_id = sanitize_key($user_id);

    // Check if the transient exists (i.e., if the message was dismissed)
    $transient_key = 'mxchat_pre_chat_message_dismissed_' . $user_id;
    $dismissed = get_transient($transient_key);

    // Log the result to see if it's being set correctly
    //error_log("Check pre-chat message dismissed for $user_id: " . ($dismissed ? 'Yes' : 'No'));

    if ($dismissed) {
        wp_send_json_success(['dismissed' => true]);
    } else {
        wp_send_json_success(['dismissed' => false]);
    }

    wp_die();
}

private function mxchat_calculate_cosine_similarity($vectorA, $vectorB) {
        if (!is_array($vectorA) || !is_array($vectorB) || empty($vectorA) || empty($vectorB)) {
            return 0;
        }

        $dotProduct = array_sum(array_map(function ($a, $b) {
            return $a * $b;
        }, $vectorA, $vectorB));
        $normA = sqrt(array_sum(array_map(function ($a) {
            return $a * $a;
        }, $vectorA)));
        $normB = sqrt(array_sum(array_map(function ($b) {
            return $b * $b;
        }, $vectorB)));

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / ($normA * $normB);
    }

public function mxchat_enqueue_scripts_styles() {
    // Define version numbers for the styles and scripts
    $chat_style_version = '2.2.8';
    $chat_script_version = '2.2.8';
    // Enqueue the script
    wp_enqueue_script(
        'mxchat-chat-js',
        plugin_dir_url(__FILE__) . '../js/chat-script.js',
        array('jquery'),
        $chat_script_version,
        true
    );
    // Enqueue the CSS
    wp_enqueue_style(
        'mxchat-chat-css',
        plugin_dir_url(__FILE__) . '../css/chat-style.css',
        array(),
        $chat_style_version
    );
    // Fetch options from the database
    $this->options = get_option('mxchat_options');
    $prompts_options = get_option('mxchat_prompts_options', array());
    
    // Prepare settings for JavaScript
    $style_settings = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mxchat_chat_nonce'),
        'model' => isset($this->options['model']) ? $this->options['model'] : 'gpt-4o',
        'enable_streaming_toggle' => isset($this->options['enable_streaming_toggle']) ? $this->options['enable_streaming_toggle'] : 'on',
        'contextual_awareness_toggle' => isset($this->options['contextual_awareness_toggle']) ? $this->options['contextual_awareness_toggle'] : 'off', // ADD THIS LINE
        'link_target_toggle' => $this->options['link_target_toggle'] ?? 'off',
        'rate_limit_message' => $this->options['rate_limit_message'] ?? 'Rate limit exceeded. Please try again later.',
        'complianz_toggle' => isset($this->options['complianz_toggle']) && $this->options['complianz_toggle'] === 'on',
        'user_message_bg_color' => $this->options['user_message_bg_color'] ?? '#fff',
        'user_message_font_color' => $this->options['user_message_font_color'] ?? '#212121',
        'bot_message_bg_color' => $this->options['bot_message_bg_color'] ?? '#212121',
        'bot_message_font_color' => $this->options['bot_message_font_color'] ?? '#fff',
        'top_bar_bg_color' => $this->options['top_bar_bg_color'] ?? '#212121',
        'send_button_font_color' => $this->options['send_button_font_color'] ?? '#212121',
        'close_button_color' => $this->options['close_button_color'] ?? '#fff',
        'chatbot_background_color' => $this->options['chatbot_background_color'] ?? '#212121',
        'chatbot_bg_color' => $this->options['chatbot_bg_color'] ?? '#fff',
        'icon_color' => $this->options['icon_color'] ?? '#fff',
        'chat_input_font_color' => $this->options['chat_input_font_color'] ?? '#212121',
        'chat_persistence_toggle' => $this->options['chat_persistence_toggle'] ?? 'off',
        'appendWidgetToBody' => $this->options['append_to_body'] ?? 'off',
        'live_agent_message_bg_color' => $this->options['live_agent_message_bg_color'] ?? '#ffffff',
        'live_agent_message_font_color' => $this->options['live_agent_message_font_color'] ?? '#333333',
        'chat_toolbar_toggle' => $this->options['chat_toolbar_toggle'] ?? 'off',
        'mode_indicator_bg_color' => $this->options['mode_indicator_bg_color'] ?? '#767676',
        'mode_indicator_font_color' => $this->options['mode_indicator_font_color'] ?? '#ffffff',
        'toolbar_icon_color' => $this->options['toolbar_icon_color'] ?? '#212121',
        'use_pinecone' => $prompts_options['mxchat_use_pinecone'] ?? '0',
        'pinecone_enabled' => isset($prompts_options['mxchat_use_pinecone']) && $prompts_options['mxchat_use_pinecone'] === '1'
    );
    // Pass the settings to the script
    wp_localize_script('mxchat-chat-js', 'mxchatChat', $style_settings);
}


// Modify the mxchat_reset_rate_limits function to handle different timeframes
public function mxchat_reset_rate_limits() {
    try {
        global $wpdb;
        $all_options = get_option('mxchat_options', []);
        $current_time = time();
        
        // Get rate limit options with a limit to avoid memory issues
        $option_names = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE 'mxchat_chat_limit_%' 
             LIMIT 500"
        );
        
        if (empty($option_names)) {
            return;
        }
        
        foreach ($option_names as $option_name) {
            // Parse the option name to extract role and user ID
            $parts = explode('_', $option_name);
            
            // Skip if the option name doesn't match our expected format
            if (count($parts) < 4) {
                continue;
            }
            
            // Extract role (may be multiple parts like 'shop_manager')
            $role_parts = array_slice($parts, 3, -1);
            $role = implode('_', $role_parts);
            
            // Skip if role doesn't exist in our settings
            if (!isset($all_options['rate_limits'][$role])) {
                continue;
            }
            
            $timeframe = $all_options['rate_limits'][$role]['timeframe'] ?? 'daily';
            $limit_data = get_option($option_name);
            
            if (!$limit_data || !is_array($limit_data) || !isset($limit_data['timestamp'])) {
                continue;
            }
            
            $timestamp = $limit_data['timestamp'];
            $should_reset = false;
            
            // Determine if we should reset based on the timeframe
            switch ($timeframe) {
                case 'hourly':
                    $should_reset = ($current_time - $timestamp) >= 3600;
                    break;
                case 'daily':
                    $should_reset = ($current_time - $timestamp) >= 86400;
                    break;
                case 'weekly':
                    $should_reset = ($current_time - $timestamp) >= 604800;
                    break;
                case 'monthly':
                    $should_reset = ($current_time - $timestamp) >= 2592000;
                    break;
            }
            
            // Reset the counter if the timeframe has passed
            if ($should_reset) {
                delete_option($option_name);
                wp_cache_delete($option_name, 'options');
            }
        }
        
        // Clean up any orphaned entries
        wp_cache_delete('mxchat_all_chat_limits', 'options');
        
    } catch (Exception $e) {
        //error_log('MxChat: Rate limit reset error: ' . $e->getMessage());
    }
}
/**
 * Clean up cron events on deactivation
 */
public function cleanup_cron_events() {
    wp_clear_scheduled_hook('mxchat_reset_rate_limits');
}

/**
 * Check if the current user has exceeded their rate limit based on role
 * 
 * @return true|array True if limit not exceeded, or array with error message if exceeded
 */
public function check_rate_limit() {
    $all_options = get_option('mxchat_options', []);
    //error_log('MXChat Rate Limit: Starting check');
    //error_log('MXChat Rate Limit: Options: ' . print_r($all_options, true));
    
    // Determine user role or if logged out
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_id = $user->ID;
        
        // Get the user's primary role using reset() to safely get the first element
        $user_roles = $user->roles;
        
        // Safely get the first role regardless of array key structure
        if (!empty($user_roles) && is_array($user_roles)) {
            $role = reset($user_roles); // This safely gets the first element regardless of key
        } else {
            $role = 'subscriber'; // Default to subscriber if no role found
        }
        
        //error_log('MXChat Rate Limit: User ID: ' . $user_id . ', Role: ' . $role);
    } else {
        $role = 'logged_out';
        // Use IP address for non-logged-in users
        $user_id = $this->get_client_ip();
        //error_log('MXChat Rate Limit: Logged out user IP: ' . $user_id);
    }
    
    // Check if rate limits are configured for this role
    if (!isset($all_options['rate_limits'][$role])) {
        //error_log('MXChat Rate Limit: No rate limit configured for role: ' . $role);
        return true; // No limit set for this role
    }
    
    $limit = $all_options['rate_limits'][$role]['limit'];
    //error_log('MXChat Rate Limit: Limit for role ' . $role . ': ' . $limit);
    
    // If unlimited, return true immediately
    if ($limit === 'unlimited') {
        //error_log('MXChat Rate Limit: Unlimited setting, no limit applied');
        return true;
    }
    
    // Get the option name for this user/role
    $option_name = 'mxchat_chat_limit_' . $role . '_' . $user_id;
    //error_log('MXChat Rate Limit: Option name: ' . $option_name);
    
    // Get the counter data
    $limit_data = get_option($option_name, ['count' => 0, 'timestamp' => time()]);
    //error_log('MXChat Rate Limit: Current limit data: ' . print_r($limit_data, true));
    
    // If first request or counter reset needed, set the initial timestamp
    if ($limit_data['count'] === 0) {
        $limit_data['timestamp'] = time();
        update_option($option_name, $limit_data);
        //error_log('MXChat Rate Limit: First request, initialized timestamp');
    }
    
    // Get the timeframe
    $timeframe = isset($all_options['rate_limits'][$role]['timeframe']) ? 
        $all_options['rate_limits'][$role]['timeframe'] : 'daily';
    //error_log('MXChat Rate Limit: Timeframe: ' . $timeframe);
    
    // Check if the counter needs to be reset based on timeframe
    $current_time = time();
    $timestamp = $limit_data['timestamp'];
    $should_reset = false;
    
    switch ($timeframe) {
        case 'hourly':
            $should_reset = ($current_time - $timestamp) >= 3600; // 1 hour
            break;
        case 'daily':
            $should_reset = ($current_time - $timestamp) >= 86400; // 24 hours
            break;
        case 'weekly':
            $should_reset = ($current_time - $timestamp) >= 604800; // 7 days
            break;
        case 'monthly':
            $should_reset = ($current_time - $timestamp) >= 2592000; // 30 days
            break;
    }
    
    //error_log('MXChat Rate Limit: Current time: ' . $current_time . ', Last timestamp: ' . $timestamp);
    //error_log('MXChat Rate Limit: Time elapsed: ' . ($current_time - $timestamp) . ' seconds');
    //error_log('MXChat Rate Limit: Should reset: ' . ($should_reset ? 'Yes' : 'No'));
    
    // Reset the counter if the timeframe has passed
    if ($should_reset) {
        $limit_data = ['count' => 0, 'timestamp' => $current_time];
        update_option($option_name, $limit_data);
        //error_log('MXChat Rate Limit: Reset counter to 0');
    }
    
    // Check if user has exceeded their limit
    if ($limit_data['count'] >= intval($limit)) {
        // Get the custom message for this role
        $message = !empty($all_options['rate_limits'][$role]['message']) 
            ? $all_options['rate_limits'][$role]['message'] 
            : __('Rate limit exceeded. Please try again later.', 'mxchat');
        
        // Add timeframe information to the message if placeholders exist
        $timeframe_label = '';
        switch ($timeframe) {
            case 'hourly':
                $timeframe_label = __('hour', 'mxchat');
                break;
            case 'daily':
                $timeframe_label = __('day', 'mxchat');
                break;
            case 'weekly':
                $timeframe_label = __('week', 'mxchat');
                break;
            case 'monthly':
                $timeframe_label = __('month', 'mxchat');
                break;
        }
        
        // Replace placeholders in the message
        $message = str_replace(
            ['{limit}', '{count}', '{remaining}', '{timeframe}'],
            [intval($limit), $limit_data['count'], max(0, intval($limit) - $limit_data['count']), $timeframe_label],
            $message
        );
        
        // NEW: Process HTML links in the message
        $message = $this->process_rate_limit_message_html($message);
        
        //error_log('MXChat Rate Limit: Limit exceeded. Message: ' . $message);
        
        // Return error with the processed message
        return [
            'error' => true,
            'message' => $message
        ];
    }
    
    // Increment the counter
    $limit_data['count']++;
    update_option($option_name, $limit_data);
    //error_log('MXChat Rate Limit: Incremented counter to ' . $limit_data['count']);
    
    return true;
}


/**
 * Process HTML links in rate limit messages
 * 
 * @param string $message The rate limit message
 * @return string The processed message with safe HTML links
 */
private function process_rate_limit_message_html($message) {
    // Return original message if empty
    if (empty($message)) {
        return $message;
    }
    
    // First, convert markdown links to HTML
    $message = $this->convert_markdown_links($message);
    
    // Then, auto-convert any remaining plain URLs to links
    $message = $this->auto_link_urls($message);
    
    // Allow basic HTML tags for links and formatting
    $allowed_tags = [
        'a' => [
            'href' => true,
            'target' => true,
            'rel' => true,
            'title' => true,
            'class' => true
        ],
        'strong' => [],
        'em' => [],
        'br' => [],
        'b' => [],
        'i' => [],
        'span' => ['class' => true]
    ];
    
    // Sanitize but allow the specified HTML tags
    $processed_message = wp_kses($message, $allowed_tags);
    
    // If wp_kses stripped everything, return the original message as plain text
    if (empty($processed_message) && !empty($message)) {
        // Strip all HTML and return plain text as fallback
        return wp_strip_all_tags($message);
    }
    
    return $processed_message;
}

/**
 * Convert markdown links to HTML
 * 
 * @param string $text The text to process
 * @return string The text with markdown links converted to HTML
 */
private function convert_markdown_links($text) {
    // Return original text if empty
    if (empty($text)) {
        return $text;
    }
    
    // Pattern to match markdown links: [text](url)
    $pattern = '/\[([^\]]+)\]\(([^)]+)\)/';
    
    $processed_text = preg_replace_callback($pattern, function($matches) {
        $link_text = $matches[1];
        $url = $matches[2];
        
        // Clean up any trailing punctuation from the URL
        $url = rtrim($url, '.,;:!?');
        
        // Sanitize the link text and URL
        $safe_text = esc_html($link_text);
        $safe_url = esc_url($url);
        
        // Create the HTML link
        return '<a href="' . $safe_url . '" target="_blank" rel="noopener noreferrer">' . $safe_text . '</a>';
    }, $text);
    
    // If preg_replace_callback failed, return original text
    if ($processed_text === null) {
        return $text;
    }
    
    return $processed_text;
}

/**
 * Auto-convert plain URLs to clickable links
 * 
 * @param string $text The text to process
 * @return string The text with URLs converted to links
 */
private function auto_link_urls($text) {
    // Return original text if empty
    if (empty($text)) {
        return $text;
    }
    
    // Simple pattern that avoids complex lookbehinds
    // This will match URLs that are not already inside href attributes or markdown links
    $pattern = '/(?<!href=["\'])(?<!\]\()https?:\/\/[^\s<>"\')\]]+/i';
    
    $processed_text = preg_replace_callback($pattern, function($matches) {
        $url = $matches[0];
        // Clean up any trailing punctuation that might have been captured
        $url = rtrim($url, '.,;:!?');
        
        // Add target="_blank" and rel="noopener noreferrer" for security
        return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($url) . '</a>';
    }, $text);
    
    // If preg_replace_callback failed, return original text
    if ($processed_text === null) {
        return $text;
    }
    
    return $processed_text;
}


// Helper function to get client IP address
private function get_client_ip() {
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    }
    
    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Use the first value in the comma-separated list
        $forwarded_for = explode(',', sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']));
        return trim($forwarded_for[0]);
    }
    
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        return sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }
    
    // Fallback
    return 'unknown';
}

/**
 * AJAX handler to get system information for testing panel
 */
public function mxchat_get_system_info() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'mxchat_test_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }
    
    // Only allow admin users
    if (!current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    
    // Get system prompt from options
    $system_prompt = isset($this->options['system_prompt_instructions']) 
        ? $this->options['system_prompt_instructions'] 
        : 'No system prompt configured';
    
    // Get selected model
    $selected_model = isset($this->options['model']) ? $this->options['model'] : 'gpt-4o';
    
    // Get API key status (just check if they exist, don't expose the keys)
    $api_status = [];
    $api_status['openai'] = !empty($this->options['api_key']);
    $api_status['claude'] = !empty($this->options['claude_api_key']);
    $api_status['gemini'] = !empty($this->options['gemini_api_key']);
    $api_status['xai'] = !empty($this->options['xai_api_key']);
    $api_status['deepseek'] = !empty($this->options['deepseek_api_key']);
    
    wp_send_json_success([
        'system_prompt' => $system_prompt,
        'selected_model' => $selected_model,
        'api_status' => $api_status
    ]);
}

/**
 * AJAX handler to get similarity threshold
 */
public function mxchat_get_similarity_threshold() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'mxchat_test_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }
    
    // Only allow admin users
    if (!current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    
    // Get similarity threshold from main options (default 75%)
    $similarity_threshold = isset($this->options['similarity_threshold']) 
        ? ((int) $this->options['similarity_threshold']) / 100 
        : 0.75;
    
    wp_send_json_success([
        'threshold' => $similarity_threshold,
        'threshold_percentage' => ($similarity_threshold * 100) . '%'
    ]);
}

/**
 * AJAX handler to get knowledge base status
 */
public function mxchat_get_kb_status() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'mxchat_test_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }
    
    // Only allow admin users
    if (!current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    
    // Check Pinecone vs WordPress
    $addon_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = (isset($addon_options['mxchat_use_pinecone']) && $addon_options['mxchat_use_pinecone'] === '1');
    
    $kb_info = [
        'type' => $use_pinecone ? 'Pinecone' : 'WordPress Database',
        'status' => 'Active'
    ];
    
    // Get document count
    if ($use_pinecone) {
        $kb_info['documents'] = 'Connected to Pinecone';
        $kb_info['api_configured'] = !empty($addon_options['mxchat_pinecone_api_key']);
    } else {
        // Count documents in WordPress database
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        $kb_info['documents'] = $count ? $count . ' documents' : 'No documents';
    }
    
    wp_send_json_success($kb_info);
}

/**
 * AJAX handler to start a completely fresh session (NEW - replaces old clear session)
 */
public function mxchat_start_fresh_session() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'mxchat_test_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }
    
    // Only allow admin users
    if (!current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Unauthorized']);
        return;
    }
    
    $old_session_id = isset($_POST['old_session_id']) ? sanitize_text_field($_POST['old_session_id']) : '';
    $new_session_id = isset($_POST['new_session_id']) ? sanitize_text_field($_POST['new_session_id']) : '';
    
    if (empty($old_session_id)) {
        wp_send_json_error(['message' => 'Old session ID required']);
        return;
    }
    
    // If no new session ID provided, generate one
    if (empty($new_session_id)) {
        $new_session_id = 'mxchat_chat_' . substr(md5(uniqid()), 0, 9);
    }
    
    // Clear ALL data associated with the old session
    $this->clear_complete_session_data($old_session_id);
    
    // Initialize the new session
    $this->initialize_fresh_session($new_session_id);
    
    wp_send_json_success([
        'message' => 'Fresh session started successfully',
        'new_session_id' => $new_session_id,
        'old_session_id' => $old_session_id
    ]);
}

/**
 * Clear ALL data associated with a session (ENHANCED)
 */
private function clear_complete_session_data($session_id) {
    // Clear chat history
    delete_option("mxchat_history_{$session_id}");
    
    // Clear chat mode
    delete_option("mxchat_mode_{$session_id}");
    
    // Clear any PDF/Word transients
    $this->clear_pdf_transients($session_id);
    if (method_exists($this, 'clear_word_transients')) {
        $this->clear_word_transients($session_id);
    }
    
    // Clear agent-related data
    delete_option("mxchat_channel_{$session_id}");
    delete_option("mxchat_agent_name_{$session_id}");
    delete_option("mxchat_email_{$session_id}");
    
    // Clear any recommendation flow state
    delete_option("mxchat_sr_flow_state_{$session_id}");
    
    // Clear any cached embeddings or context
    delete_transient("mxchat_context_{$session_id}");
    delete_transient("mxchat_last_query_{$session_id}");
    
    // Clear any testing data
    delete_transient("mxchat_testing_data_{$session_id}");
    
    // Clear any rate limiting data for this session
    delete_transient("mxchat_rate_limit_{$session_id}");
    
    // Clear any other session-specific transients
    delete_transient("mxchat_waiting_for_pdf_url_{$session_id}");
    delete_transient("mxchat_include_pdf_in_context_{$session_id}");
    delete_transient("mxchat_include_word_in_context_{$session_id}");
    
    //error_log("MxChat: Cleared all data for session: {$session_id}");
}

/**
 * Initialize a fresh session with default data
 */
private function initialize_fresh_session($session_id) {
    // Set default chat mode
    update_option("mxchat_mode_{$session_id}", 'ai');
    
    //error_log("MxChat: Initialized fresh session: {$session_id}");
}

/**
 * Helper method to clear Word document transients (if you have Word support)
 */
private function clear_word_transients($session_id) {
    delete_transient('mxchat_word_url_' . $session_id);
    delete_transient('mxchat_word_filename_' . $session_id);
    delete_transient('mxchat_word_embeddings_' . $session_id);
    delete_transient('mxchat_include_word_in_context_' . $session_id);
}

/**
 * Simplified testing data capture method (CLEANED UP)
 */
private function capture_testing_data($user_embedding, $message, $session_id) {
    // Only capture for admin users
    if (!current_user_can('administrator')) {
        return null;
    }
    
    $testing_data = [
        'query' => $message,
        'timestamp' => time(),
        'top_matches' => [],
        'action_matches' => [] // NEW: Add action matches
    ];
    
    // Get similarity threshold
    $similarity_threshold = isset($this->options['similarity_threshold']) 
        ? ((int) $this->options['similarity_threshold']) / 100 
        : 0.75;
    
    $testing_data['similarity_threshold'] = $similarity_threshold;
    
    // Use the real similarity analysis if available
    if ($this->last_similarity_analysis !== null) {
        $testing_data['knowledge_base_type'] = $this->last_similarity_analysis['knowledge_base_type'];
        $testing_data['top_matches'] = $this->last_similarity_analysis['top_matches'];
        $testing_data['total_documents_checked'] = $this->last_similarity_analysis['total_checked'] ?? 0;
    } else {
        // Fallback: determine knowledge base type
        $addon_options = get_option('mxchat_pinecone_addon_options', array());
        $use_pinecone = (isset($addon_options['mxchat_use_pinecone']) && $addon_options['mxchat_use_pinecone'] === '1');
        
        $testing_data['knowledge_base_type'] = $use_pinecone ? 'Pinecone' : 'WordPress Database';
    }
    
    // NEW: Include action analysis if available
    if (isset($this->last_action_analysis) && !empty($this->last_action_analysis)) {
        $testing_data['action_matches'] = $this->last_action_analysis;
        
        // Clear it after capturing to avoid stale data
        $this->last_action_analysis = null;
    }
    
    return $testing_data;
}


}
?>
