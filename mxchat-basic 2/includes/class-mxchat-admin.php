<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MxChat_Admin {
    private $options;
    private $chat_count;
    private $is_activated;
    private $knowledge_manager;

    public function __construct($knowledge_manager = null) {
        $this->options = get_option('mxchat_options');
        $this->chat_count = get_option('mxchat_chat_count', 0);
        $this->is_activated = $this->is_license_active();
        $this->knowledge_manager = $knowledge_manager;

        // Initialize default options if they are not set
        if (!$this->options) {
            $this->initialize_default_options();
        }

        // Add admin menu and initialize settings
        add_action('admin_menu', array($this, 'mxchat_add_plugin_page'));
        add_action('admin_init', array($this, 'mxchat_page_init'));
        add_action('admin_init', array($this, 'mxchat_prompts_page_init'));
        add_action('admin_enqueue_scripts', array($this, 'mxchat_enqueue_admin_assets'));
        add_action('wp_ajax_mxchat_delete_chat_history', array($this, 'mxchat_delete_chat_history'));
        add_action('admin_post_mxchat_delete_prompt', array($this, 'mxchat_handle_delete_prompt'));
        add_action('wp_ajax_mxchat_fetch_chat_history', array($this, 'mxchat_fetch_chat_history'));
        add_action('wp_ajax_nopriv_mxchat_fetch_chat_history', array($this, 'mxchat_fetch_chat_history'));
        add_action('wp_footer', array($this, 'mxchat_append_chatbot_to_body'));
        add_action('admin_head-mxchat-prompts', array($this, 'mxchat_enqueue_admin_assets'));
        add_action('admin_head-toplevel_page_mxchat-max', array($this, 'mxchat_enqueue_admin_assets'));
        add_action('admin_notices', array($this, 'mxchat_display_admin_notice'));
        add_action('admin_post_mxchat_delete_all_prompts', array($this, 'mxchat_handle_delete_all_prompts'));
        add_action('admin_post_mxchat_add_intent', array($this, 'mxchat_handle_add_intent'));
        add_action('admin_post_mxchat_delete_intent', array($this, 'mxchat_handle_delete_intent'));
        add_action('admin_post_mxchat_edit_intent', array($this, 'mxchat_handle_edit_intent'));
        add_action('wp_ajax_mxchat_export_transcripts', array($this, 'export_chat_transcripts'));
        add_action('admin_init', array($this, 'mxchat_transcripts_page_init'));
        add_action('wp_ajax_dismiss_live_agent_notice', array($this, 'dismiss_live_agent_notice'));

         add_action('admin_init', array($this, 'register_pinecone_settings'));

        add_action('admin_notices', array($this, 'display_admin_notices'));

    }

private function is_license_active() {
    // Get the raw value without translation
    $license_status = get_option('mxchat_license_status', 'inactive');

    // Check against multiple possible values, bypassing translation issues
    return ($license_status === 'active' || $license_status === esc_html__('active', 'mxchat'));
}

private function initialize_default_options() {
    $default_options = array(
        'api_key' => '',
        'xai_api_key' => '',
        'claude_api_key' => '',
        'deepseek_api_key' => '',
        'voyage_api_key' => '',
        'gemini_api_key' => '',
        'enable_streaming_toggle' => 'on',
        'embedding_model' => 'text-embedding-ada-002',
        'system_prompt_instructions' => 'You are an AI Chatbot assistant for this website. Your main goal is to assist visitors with questions and provide helpful information. Here are your key guidelines:

        # Response Style - CRITICALLY IMPORTANT
        - MAXIMUM LENGTH: 1-3 short sentences per response
        - Ultra-concise: Get straight to the answer with no filler
        - No introductions like "Sure!" or "I\'d be happy to help"
        - No phrases like "based on my knowledge" or "according to information"
        - No explanatory text before giving the answer
        - No summaries or repetition
        - Hyperlink all URLs
        - Respond in user\'s language
        - Minor chit chat or conversation is okay, but try to keep it focused on [insert topic]

        # Knowledge Base Requirements - PREVENT HALLUCINATIONS
        - ONLY answer using information explicitly provided in OFFICIAL KNOWLEDGE DATABASE CONTENT sections marked with ===== delimiters
        - If required information is NOT in the knowledge database: "I don\'t have enough information in my knowledge base to answer that question accurately."
        - NEVER invent or hallucinate URLs, links, product specs, procedures, dates, statistics, names, contacts, or company information
        - When knowledge base information is unclear or contradictory, acknowledge the limitation rather than guessing
        - Better to admit insufficient information than provide inaccurate answers',
        'model' => esc_html__('gpt-4o', 'mxchat'),
        'rate_limit_logged_out' => esc_html__('100', 'mxchat'),
        'role_rate_limits' => array(),
        'rate_limit_message' => esc_html__('Rate limit exceeded. Please try again later.', 'mxchat'),
        'enable_email_block' => '',
        'email_blocker_header_content' => __("<h2>Welcome to Our Chat!</h2>\n<p>Let's get started. Enter your email to begin chatting with us.</p>", 'mxchat'),
        'email_blocker_button_text' => esc_html__('Start Chat', 'mxchat'),
        'top_bar_title' => esc_html__('MxChat', 'mxchat'),
        'intro_message' => __('Hello! How can I assist you today?', 'mxchat'),
        'ai_agent_text' => esc_html__('AI Agent', 'mxchat'),
        'input_copy' => esc_html__('How can I assist?', 'mxchat'),
        'append_to_body' => esc_html__('off', 'mxchat'),
        'contextual_awareness_toggle' => 'off',
        'close_button_color' => esc_html__('#fff', 'mxchat'),
        'chatbot_bg_color' => esc_html__('#fff', 'mxchat'),
        'user_message_bg_color' => esc_html__('#fff', 'mxchat'),
        'user_message_font_color' => esc_html__('#212121', 'mxchat'),
        'bot_message_bg_color' => esc_html__('#212121', 'mxchat'),
        'bot_message_font_color' => esc_html__('#fff', 'mxchat'),
        'top_bar_bg_color' => esc_html__('#212121', 'mxchat'),
        'send_button_font_color' => esc_html__('#212121', 'mxchat'),
        'chat_input_font_color' => esc_html__('#212121', 'mxchat'),
        'chatbot_background_color' => esc_html__('#212121', 'mxchat'),
        'icon_color' => esc_html__('#fff', 'mxchat'),
        'enable_woocommerce_integration' => esc_html__('0', 'mxchat'),
        'link_target_toggle' => esc_html__('off', 'mxchat'),
        'pre_chat_message' => esc_html__('Hey there! Ask me anything!', 'mxchat'),

        // New fields for Loops Integration
        'loops_api_key' => '',
        'loops_mailing_list' => '',
        'triggered_phrase_response' => __('Would you like to join our mailing list? Please provide your email below.', 'mxchat'),
        'email_capture_response' => __('Thank you for providing your email! You\'ve been added to our list.', 'mxchat'),
        'popular_question_1' => '',
        'popular_question_2' => '',
        'popular_question_3' => '',
        'pdf_intent_trigger_text' => __("Please provide the URL to the PDF you'd like to discuss.", 'mxchat'),
        'pdf_intent_success_text' => __("I've processed the PDF. What questions do you have about it?", 'mxchat'),
        'pdf_intent_error_text' => __("Sorry, I couldn't process the PDF. Please ensure it's a valid file.", 'mxchat'),
        'pdf_max_pages' => 69,
        'show_pdf_upload_button' => 'on',
        'show_word_upload_button' => 'on',

        // Live Agent Integration
        'live_agent_webhook_url' => '',
        'live_agent_secret_key' => '',
        'live_agent_bot_token' => '',
        'live_agent_message_bg_color' => esc_html__('#ffffff', 'mxchat'),
        'live_agent_message_font_color' => esc_html__('#333333', 'mxchat'),
        'chat_toolbar_toggle' => esc_html__('off', 'mxchat'),
        'mode_indicator_bg_color' => esc_html__('#767676', 'mxchat'),
        'mode_indicator_font_color' => esc_html__('#ffffff', 'mxchat'),
        'toolbar_icon_color' => esc_html__('#212121', 'mxchat'),
    );


        // Merge existing options with defaults
        $existing_options = get_option('mxchat_options', array());
        $merged_options = wp_parse_args($existing_options, $default_options);

        // Update the options if they have changed
        if ($existing_options !== $merged_options) {
            update_option('mxchat_options', $merged_options);
        }

            // Add default limits for each role
    $roles = wp_roles()->get_names();
    foreach ($roles as $role_id => $role_name) {
        $default_options['role_rate_limits'][$role_id] = esc_html__('100', 'mxchat');
    }

    return $default_options;

        // Update the $this->options property
        $this->options = $merged_options;
    }

public function mxchat_add_plugin_page() {
    // Main menu page
    add_menu_page(
        esc_html__('MxChat Settings', 'mxchat'),
        esc_html__('MxChat', 'mxchat'),
        'manage_options',
        'mxchat-max',
        array($this, 'mxchat_create_admin_page'),
        'dashicons-testimonial',
        6
    );

    // Submenu page for Knowledge
    add_submenu_page(
        'mxchat-max',
        esc_html__('Prompts', 'mxchat'),
        esc_html__('Knowledge', 'mxchat'),
        'manage_options',
        'mxchat-prompts',
        array($this, 'mxchat_create_prompts_page')
    );

    add_submenu_page(
        'mxchat-max',
        esc_html__('Chat Transcripts', 'mxchat'),
        esc_html__('Transcripts', 'mxchat'),
        'manage_options',
        'mxchat-transcripts',
        array($this, 'mxchat_create_transcripts_page')
    );

    add_submenu_page(
        'mxchat-max',
        esc_html__('MxChat Actions', 'mxchat'),
        esc_html__('Actions', 'mxchat'),
        'manage_options',
        'mxchat-actions',
        array($this, 'mxchat_actions_page_html')
    );

        add_submenu_page(
            'mxchat-max',
            esc_html__('Add Ons', 'mxchat'),
            esc_html__('Add Ons', 'mxchat'),
            'manage_options',
            'mxchat-addons',
            array($this, 'mxchat_create_addons_page')
        );

    // Submenu page for Activation Key
    add_submenu_page(
        'mxchat-max',
        esc_html__('Pro Upgrade', 'mxchat'),
        esc_html__('Pro Upgrade', 'mxchat'),
        'manage_options',
        'mxchat-activation',
        array($this, 'mxchat_create_activation_page')
    );
}

public function mxchat_create_addons_page() {
    require_once plugin_dir_path(__FILE__) . 'class-mxchat-addons.php';
    $addons_page = new MxChat_Addons();
    $addons_page->render_page();
}

public function register_pinecone_settings() {
    register_setting(
        'mxchat_pinecone_addon_options',
        'mxchat_pinecone_addon_options',
        array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_pinecone_settings'),
            'default' => array(
                'mxchat_use_pinecone' => '0',
                'mxchat_pinecone_api_key' => '',
                'mxchat_pinecone_host' => '',
                'mxchat_pinecone_index' => '',
                'mxchat_pinecone_environment' => ''
            )
        )
    );
}

public function sanitize_pinecone_settings($input) {
    $sanitized = array();

    $sanitized['mxchat_use_pinecone'] = isset($input['mxchat_use_pinecone']) ? '1' : '0';
    $sanitized['mxchat_pinecone_api_key'] = sanitize_text_field($input['mxchat_pinecone_api_key'] ?? '');
    $sanitized['mxchat_pinecone_host'] = sanitize_text_field($input['mxchat_pinecone_host'] ?? '');
    $sanitized['mxchat_pinecone_index'] = sanitize_text_field($input['mxchat_pinecone_index'] ?? '');
    $sanitized['mxchat_pinecone_environment'] = sanitize_text_field($input['mxchat_pinecone_environment'] ?? '');

    // Remove https:// from host if present
    $sanitized['mxchat_pinecone_host'] = str_replace(['https://', 'http://'], '', $sanitized['mxchat_pinecone_host']);

    return $sanitized;
}

public function mxchat_display_admin_notice() {
    // Success notice
    if ($message = get_transient('mxchat_admin_notice_success')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'mxchat'); ?></span></button>
        </div>
        <?php
        delete_transient('mxchat_admin_notice_success'); // Clear the transient after displaying
    }

    // Error notice
    if ($message = get_transient('mxchat_admin_notice_error')) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($message); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'mxchat'); ?></span></button>
        </div>
        <?php
        delete_transient('mxchat_admin_notice_error'); // Clear the transient after displaying
    }
}

public function show_live_agent_disabled_banner() {
    $show_disabled_notice = get_option('mxchat_show_live_agent_disabled_notice', false);
    
    if ($show_disabled_notice) {
        ?>
        <div class="mxchat-live-agent-disabled-notice" id="mxchat-disabled-notice">
            <div class="mxchat-pro-notification">
                <button type="button" class="mxchat-dismiss-btn" onclick="dismissLiveAgentNotice()" aria-label="Dismiss notification">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <div class="mxchat-live-agent-content">
                    <h3>🔧 Live Agent Integration Updated!</h3>
                    <p>We've temporarily disabled your Live Agent integration due to recent enhancements that have made it much better! You can easily turn it back on by going to <strong>Toolbar & Components → Live Agent Settings</strong> and reviewing the new configuration options.</p>
                </div>
            </div>
        </div>
        <?php
    }
}
public function mxchat_create_admin_page() {
    $this->add_live_agent_nonce();

    ?>
    <div class="wrap mxchat-wrapper">
        <!-- Hero Section -->
        <div class="mxchat-hero">
            <h1 class="mxchat-main-title">
                <span class="mxchat-gradient-text">MxChat</span> Settings
            </h1>
            <p class="mxchat-hero-subtitle">
                <?php esc_html_e('Configure your AI chatbot, manage integrations and explore tutorials to get the most out of MxChat.', 'mxchat'); ?>
            </p>
        </div>

        <div class="mxchat-content">
            <?php if (!$this->is_activated): ?>
            <div class="mxchat-pro-card">
                <div class="mxchat-pro-notification">
                    <div class="mxchat-pro-content">
                        <h3>🚀 Limited Lifetime Offer: Save 30% on MxChat Pro, Agency, or Agency Plus!</h3>
                    <p>Unlock <strong>unlimited access</strong> to our growing collection of powerful add-ons including Admin AI Assistant (ChatGPT-like experience), Forms Builder, AI Theme Generator, WooCommerce, Perplexity, and more – all included with your <strong>lifetime license!</strong></p>                    </div>
                    <div class="mxchat-pro-cta">
                        <a href="https://mxchat.ai/" target="_blank" class="mxchat-button"><?php echo esc_html__('Upgrade Today', 'mxchat'); ?></a>
                        <a href="<?php echo admin_url('admin.php?page=mxchat-addons'); ?>" class="mxchat-link"><?php echo esc_html__('Preview Add-ons', 'mxchat'); ?></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php 
            $this->show_live_agent_disabled_banner(); 
            ?>

            <!-- Tabs Navigation -->
            <div class="mxchat-tabs">
                <button class="mxchat-tab-button active" data-tab="chatbot"><?php echo esc_html__('Chatbot', 'mxchat'); ?></button>
                <button class="mxchat-tab-button" data-tab="embed"><?php echo esc_html__('Toolbar & Components', 'mxchat'); ?></button>
                <button class="mxchat-tab-button" data-tab="general"><?php echo esc_html__('YouTube Tutorials', 'mxchat'); ?></button>
            </div>

            <!-- Tab Contents -->
            <div id="chatbot" class="mxchat-tab-content active">
                <div class="mxchat-card">
                    <div class="mxchat-autosave-section">
                        <?php do_settings_sections('mxchat-chatbot'); ?>
                    </div>
                </div>
            </div>

            <div id="embed" class="mxchat-tab-content">

                <div class="mxchat-card">
                    <h2><?php esc_html_e('Toolbar Settings', 'mxchat'); ?></h2>
                    <div class="mxchat-autosave-section">
                        <table class="form-table">
                            <?php do_settings_fields('mxchat-embed', 'mxchat_pdf_intent_section'); ?>
                        </table>
                    </div>
                </div>


                <div class="mxchat-card">
                    <h2><?php esc_html_e('Loops Settings', 'mxchat'); ?></h2>
                    <div class="mxchat-autosave-section">
                        <table class="form-table">
                            <?php do_settings_fields('mxchat-embed', 'mxchat_loops_section'); ?>
                        </table>
                    </div>
                </div>

                <div class="mxchat-card">
                    <h2><?php esc_html_e('Brave Search Settings', 'mxchat'); ?></h2>
                    <div class="mxchat-autosave-section">
                        <table class="form-table">
                            <?php do_settings_fields('mxchat-embed', 'mxchat_brave_section'); ?>
                        </table>
                    </div>
                </div>

                <div class="mxchat-card">
                    <h2><?php esc_html_e('Live Agent Settings', 'mxchat'); ?></h2>
                    <div class="mxchat-autosave-section">
<p><?php echo esc_html__('Visit our', 'mxchat'); ?> <a href="https://mxchat.ai/documentation/#slack_integration" target="_blank"><?php echo esc_html__('documentation page', 'mxchat'); ?></a> <?php echo esc_html__('for setup instructions and to see what\'s changed in the latest update.', 'mxchat'); ?></p>                        <table class="form-table">
                            <?php do_settings_fields('mxchat-embed', 'mxchat_live_agent_section'); ?>
                        </table>
                    </div>
                </div>
            </div>

            <div id="general" class="mxchat-tab-content">
                <div class="mxchat-card">
                    <?php do_settings_sections('mxchat-general'); ?>
                    <div class="video-tutorials-section">


     <div class="support-notification">
    <div class="support-notification-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
            <path d="M12 9v4"/>
            <path d="M12 17h.01"/>
        </svg>
    </div>
    <div class="support-notification-content">
        <h3 class="support-notification-title"><?php echo esc_html__('Need Help? We\'re Here for You!', 'mxchat'); ?></h3>
        <p class="support-notification-message">
            <?php echo esc_html__('Our goal is to provide the best experience and AI chatbot plugin available. If you\'re having trouble or believe something is not working as expected, please don\'t hesitate to submit a support ticket.', 'mxchat'); ?>
        </p>
        <a href="https://wordpress.org/support/plugin/mxchat-basic/" target="_blank" rel="noopener noreferrer" class="support-notification-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/>
                <path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/>
            </svg>
            <?php echo esc_html__('Submit Support Ticket', 'mxchat'); ?>
        </a>
    </div>
</div>

    <div class="tutorial-grid">
        
        <div class="tutorial-item">
    <h3><?php echo esc_html__('AI Theme Generator Tutorial', 'mxchat'); ?></h3>
    <div class="video-description">
        <p><?php echo esc_html__('Learn how to instantly restyle your chatbot using plain English prompts. The AI Theme Generator lets you generate and apply beautiful designs with real-time previews—no CSS skills needed.', 'mxchat'); ?></p>
        <a href="https://www.youtube.com/watch?v=rSQDW2qbtRU&t" target="_blank" rel="noopener" class="video-link">
            <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
            <?php echo esc_html__('Watch AI Theme Generator Tutorial', 'mxchat'); ?>
        </a>
    </div>
</div>

        
        <div class="tutorial-item">
            <h3><?php echo esc_html__('MxChat Forms Tutorial', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to create and manage smart forms that automatically trigger during chat conversations.', 'mxchat'); ?></p>
                <a href="https://www.youtube.com/watch?v=3MrWy5dRalA" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch MxChat Forms Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

<div class="tutorial-item">
    <h3><?php echo esc_html__('Admin Assistant Add-on', 'mxchat'); ?></h3>
    <div class="video-description">
        <p><?php echo esc_html__('Discover how to use the MxChat Admin Assistant to bring a ChatGPT-like experience directly inside your WordPress dashboard. Learn to access multiple AI models, save conversations, generate images, and use web search - all without leaving your admin panel.', 'mxchat'); ?></p>
        <a href="https://youtu.be/AdEA1k-UCFM" target="_blank" rel="noopener" class="video-link">
            <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2-3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
            <?php echo esc_html__('Watch Admin Assistant Tutorial', 'mxchat'); ?>
        </a>
    </div>
</div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Intent Tester Guide', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Discover how to use the Intent Tester to fine-tune your chatbot\'s responses and ensure it accurately understands user queries.', 'mxchat'); ?></p>
                <a href="https://www.youtube.com/watch?v=uTr14tn59Hc" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Intent Tester Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Theme Customizer Add-on', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to customize your chatbot appearance with the Theme Customizer add-on. Easily modify colors, fonts, and styles with real-time previews to match your brand perfectly.', 'mxchat'); ?></p>
                <a href="https://youtu.be/MfbB9mZi6ag" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Theme Customizer Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('WooCommerce Integration', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('See how to integrate MxChat with your WooCommerce store to provide product recommendations and shopping assistance to your customers.', 'mxchat'); ?></p>
                <a href="https://www.youtube.com/watch?v=WsqAppHRGdA" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch WooCommerce Integration Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Knowledge Base Setup', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to set up your knowledge base using PDFs, sitemaps, and manual entries to enhance your chatbot\'s responses with site-specific information.', 'mxchat'); ?></p>
                <p><small><?php echo esc_html__('Note: This tutorial uses an older UI, but the process remains the same.', 'mxchat'); ?></small></p>
                <a href="https://www.youtube.com/watch?v=8Ztjs66-VTo" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Knowledge Base Setup Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Toolbar Chat with Documents', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('See how to use the MxChat toolbar to chat with PDF and Word documents for enhanced document analysis and information retrieval.', 'mxchat'); ?></p>
                <a href="https://www.youtube.com/watch?v=j_c45WWCTG0" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Document Chat Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('MxChat Smart Recommender Tutorial', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to create intelligent recommendation flows that guide users to perfect matches based on their preferences.', 'mxchat'); ?></p>
                <a href="https://www.youtube.com/watch?v=8te1KPa238g&t=1s" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Smart Recommender Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Perplexity Integration', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to integrate Perplexity with your chatbot for real-time web search capabilities. This tutorial covers intent recognition, the toolbar toggle button, and how to enable your chatbot to search the web and provide up-to-date information to your visitors.', 'mxchat'); ?></p>
                <a href="https://youtu.be/wpKkbt24-bo" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Perplexity Integration Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Brave Search Intent', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to leverage Brave Search intent capabilities to improve your chatbot\'s understanding of user queries and provide more accurate responses.', 'mxchat'); ?></p>
                <a href="https://www.youtube.com/watch?v=7vDL5H7vToc" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Brave Search Intent Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('Loops Email Capture', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Discover how to set up email capture with MxChat using Loops to grow your mailing list while providing value through your chatbot.', 'mxchat'); ?></p>
                <p><small><?php echo esc_html__('Note: This tutorial uses an older UI, but the process remains the same.', 'mxchat'); ?></small></p>
                <a href="https://www.youtube.com/watch?v=CNgm5TYDyTc" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch Loops Email Capture Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>

        <div class="tutorial-item">
            <h3><?php echo esc_html__('MxChat AI Agent Testing Service', 'mxchat'); ?></h3>
            <div class="video-description">
                <p><?php echo esc_html__('Learn how to use the MxChat AI Agent Testing Service to evaluate and improve your chatbot\'s performance and accuracy.', 'mxchat'); ?></p>
                <p><small><?php echo esc_html__('Note: This tutorial uses an older UI, but the process remains the same.', 'mxchat'); ?></small></p>
                <a href="https://www.youtube.com/watch?v=A0jowbpyX54" target="_blank" rel="noopener" class="video-link">
                    <span class="video-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19c-2.3 0-6.4-.2-8.1-.6-.7-.2-1.2-.7-1.4-1.4-.3-1.1-.5-3.4-.5-5s.2-3.9.5-5c.2-.7.7-1.2 1.4-1.4C5.6 5.2 9.7 5 12 5s6.4.2 8.1.6c.7.2 1.2.7 1.4 1.4.3 1.1.5 3.4.5 5s-.2 3.9-.5 5c-.2.7-.7 1.2-1.4 1.4-1.7.4-5.8.6-8.1.6z"></path><polygon points="10 15 15 12 10 9 10 15"></polygon></svg></span>
                    <?php echo esc_html__('Watch AI Agent Testing Tutorial', 'mxchat'); ?>
                </a>
            </div>
        </div>
    </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
public function dismiss_live_agent_notice() {
    // Add debugging
    //error_log('dismiss_live_agent_notice called');
    //error_log('POST data: ' . print_r($_POST, true));
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'dismiss_live_agent_notice')) {
        //error_log('Nonce verification failed');
        wp_die('Security check failed');
    }
    
    // Remove the notice flag
    $deleted = delete_option('mxchat_show_live_agent_disabled_notice');
    //error_log('Option deleted: ' . ($deleted ? 'yes' : 'no'));
    
    wp_send_json_success();
}
public function add_live_agent_nonce() {
    if (get_option('mxchat_show_live_agent_disabled_notice', false)) {
        // Make sure your admin script is enqueued and localize the data
        wp_localize_script('mxchat-admin-js', 'mxchatLiveAgent', array(
            'nonce' => wp_create_nonce('dismiss_live_agent_notice'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
}


public function mxchat_create_transcripts_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';

    // Get basic stats
    $total_chats = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM $table_name");
    $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Count unique users with detailed breakdown
    $total_users = $wpdb->get_var("
        SELECT COUNT(DISTINCT
            CASE
                WHEN user_email != '' AND user_email IS NOT NULL THEN user_email
                WHEN user_id != 0 THEN CONCAT('user_', user_id)
                WHEN user_identifier NOT LIKE 'Tech-Savvy User'
                     AND user_identifier NOT LIKE 'Detail-Oriented User'
                     AND user_identifier NOT LIKE 'Language Learner'
                     AND user_identifier NOT LIKE 'Casual Browser'
                     AND user_identifier NOT LIKE 'Policy Enforcer'
                     AND user_identifier NOT LIKE 'Researcher'
                     AND user_identifier NOT LIKE 'Loyalty Member'
                     AND user_identifier NOT LIKE 'Gift Buyer'
                     AND user_identifier NOT LIKE 'Parent or Caregiver'
                THEN user_identifier
                ELSE session_id
            END
        )
        FROM $table_name
        WHERE role != 'assistant'
    ");

    // Get user type breakdown
    $registered_users = $wpdb->get_var("
        SELECT COUNT(DISTINCT user_email)
        FROM $table_name
        WHERE user_email != '' AND user_email IS NOT NULL
    ");

    $guest_users = $wpdb->get_var("
        SELECT COUNT(DISTINCT user_identifier)
        FROM $table_name
        WHERE (user_email = '' OR user_email IS NULL)
        AND role != 'assistant'
        AND user_identifier NOT LIKE 'Tech-Savvy User'
        AND user_identifier NOT LIKE 'Detail-Oriented User'
        AND user_identifier NOT LIKE 'Language Learner'
        AND user_identifier NOT LIKE 'Casual Browser'
        AND user_identifier NOT LIKE 'Policy Enforcer'
        AND user_identifier NOT LIKE 'Researcher'
        AND user_identifier NOT LIKE 'Loyalty Member'
        AND user_identifier NOT LIKE 'Gift Buyer'
        AND user_identifier NOT LIKE 'Parent or Caregiver'
    ");

    // Get agent test messages count
    $agent_tests = $wpdb->get_var("
        SELECT COUNT(DISTINCT session_id)
        FROM $table_name
        WHERE user_identifier IN (
            'Tech-Savvy User',
            'Detail-Oriented User',
            'Language Learner',
            'Casual Browser',
            'Policy Enforcer',
            'Researcher',
            'Loyalty Member',
            'Gift Buyer',
            'Parent or Caregiver'
        )
    ");
    ?>
    <div class="wrap mxchat-transcripts-wrapper">
        <!-- Hero Section -->
        <div class="mxchat-transcripts-hero">
            <h1 class="mxchat-main-title">
                Chat <span class="mxchat-gradient-text">Transcripts</span>
            </h1>
            <p class="mxchat-hero-subtitle">
                <?php esc_html_e('Review and manage your chatbot conversations with detailed message history.', 'mxchat'); ?>
            </p>
        </div>
        <div class="mxchat-content">
            <!-- Stats Cards -->
            <div class="mxchat-stats-grid">
                <div class="mxchat-stat-card">
                    <div class="stat-icon">💬</div>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo esc_html($total_chats); ?></span>
                        <span class="stat-label"><?php esc_html_e('Total Chats', 'mxchat'); ?></span>
                    </div>
                </div>
                <div class="mxchat-stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo esc_html($total_messages); ?></span>
                        <span class="stat-label"><?php esc_html_e('Total Messages', 'mxchat'); ?></span>
                    </div>
                </div>
                <div class="mxchat-stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo esc_html($total_users); ?></span>
                        <span class="stat-label"><?php esc_html_e('Unique Users', 'mxchat'); ?></span>
                        <span class="stat-sublabel">
                            <?php
                            echo sprintf(
                                esc_html__('%d registered, %d guests, %d agent tests', 'mxchat'),
                                $registered_users,
                                $guest_users,
                                $agent_tests
                            );
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Controls with Notification Settings Button -->
            <div class="mxchat-controls-wrapper">
                <div class="mxchat-search-box">
                    <input type="text" id="mxchat-search-transcripts"
                           placeholder="<?php esc_attr_e('Search transcripts...', 'mxchat'); ?>"
                           class="regular-text">
                </div>
                <form id="mxchat-delete-form" method="post">
                    <?php wp_nonce_field('mxchat_delete_chat_history', 'mxchat_delete_chat_nonce'); ?>
                    <div class="mxchat-controls">
                        <button type="button" id="mxchat-chat-email-notification-btn" class="mxchat-action-button">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e('Notification Settings', 'mxchat'); ?>
                        </button>
                        <button type="button" id="mxchat-export-transcripts" class="mxchat-action-button">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Export All Chats', 'mxchat'); ?>
                        </button>
                        <button type="button" id="mxchat-select-all-transcripts" class="mxchat-select-button">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <span class="button-text"><?php esc_html_e('Select All', 'mxchat'); ?></span>
                        </button>
                        <button type="submit" class="button delete-chats-button">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e('Delete Selected', 'mxchat'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Transcripts Container -->
            <div id="mxchat-transcripts"></div>
        </div>
    </div>

    <!-- Modal for Chat Email Notification Settings -->
    <div id="mxchat-chat-email-notification-modal" class="mxchat-chat-notification-modal-overlay" style="display: none;">
        <div class="mxchat-chat-notification-modal-content">
            <div class="mxchat-chat-notification-modal-header">
                <h2><?php esc_html_e('Email Notification Settings', 'mxchat'); ?></h2>
                <button type="button" class="mxchat-chat-notification-modal-close">&times;</button>
            </div>
            <div class="mxchat-chat-notification-modal-body">
                <form method="post" action="options.php" id="mxchat-chat-email-notification-form">
                    <?php
                    settings_fields('mxchat_transcripts_options');
                    do_settings_sections('mxchat-transcripts');
                    ?>
                    <div class="mxchat-chat-notification-modal-footer">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Notification Settings', 'mxchat'); ?>
                        </button>
                        <button type="button" class="button mxchat-chat-notification-modal-cancel">
                            <?php esc_html_e('Cancel', 'mxchat'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
public function mxchat_transcripts_notification_section_callback() {
    echo '<p>' . esc_html__('Configure email notifications for new chat transcripts. You will receive an email notification when a new chat session begins.', 'mxchat') . '</p>';
}
public function mxchat_enable_notifications_callback() {
    $options = get_option('mxchat_transcripts_options', array());
    $enabled = isset($options['mxchat_enable_notifications']) ? $options['mxchat_enable_notifications'] : 0;
    ?>
    <label for="mxchat_enable_notifications">
        <input type="checkbox" id="mxchat_enable_notifications"
               name="mxchat_transcripts_options[mxchat_enable_notifications]"
               value="1" <?php checked(1, $enabled); ?>>
        <?php esc_html_e('Send email notification when a new chat session starts', 'mxchat'); ?>
    </label>
    <p class="description">
        <?php esc_html_e('Enable this option to receive email notifications for new chat sessions.', 'mxchat'); ?>
    </p>
    <?php
}
public function mxchat_notification_email_callback() {
    $options = get_option('mxchat_transcripts_options', array());
    $email = isset($options['mxchat_notification_email']) ? $options['mxchat_notification_email'] : get_option('admin_email');
    ?>
    <input type="email" id="mxchat_notification_email"
           name="mxchat_transcripts_options[mxchat_notification_email]"
           value="<?php echo esc_attr($email); ?>"
           class="regular-text">
    <p class="description">
        <?php esc_html_e('Enter the email address where notifications should be sent. Defaults to the admin email address.', 'mxchat'); ?>
    </p>
    <?php
}
public function sanitize_transcripts_options($input) {
    $sanitized = array();

    $sanitized['mxchat_enable_notifications'] = isset($input['mxchat_enable_notifications']) ? 1 : 0;

    if (isset($input['mxchat_notification_email'])) {
        $sanitized['mxchat_notification_email'] = sanitize_email($input['mxchat_notification_email']);
        if (!is_email($sanitized['mxchat_notification_email'])) {
            add_settings_error(
                'mxchat_transcripts_options',
                'invalid_email',
                __('Please enter a valid email address for notifications.', 'mxchat'),
                'error'
            );
            $sanitized['mxchat_notification_email'] = get_option('admin_email');
        }
    }

    return $sanitized;
}
public function export_chat_transcripts() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mxchat'));
    }

    check_ajax_referer('mxchat_export_transcripts', 'security');

    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';

    // Get all transcripts ordered by session and timestamp
    $results = $wpdb->get_results(
        "SELECT session_id, user_email, user_identifier, role, message, timestamp
        FROM {$table_name}
        ORDER BY session_id, timestamp ASC"
    );

    if (empty($results)) {
        wp_send_json_error(array('message' => 'No transcripts found.'));
        wp_die();
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="chat-transcripts-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fputs($output, "\xEF\xBB\xBF");

    // Add CSV headers
    fputcsv($output, array(
        'Session ID',
        'Email',
        'User Identifier',
        'Role',
        'Message',
        'Timestamp'
    ));

    // Add data rows
    foreach ($results as $row) {
        fputcsv($output, array(
            $row->session_id,
            $row->user_email,
            $row->user_identifier,
            $row->role,
            $row->message,
            $row->timestamp
        ));
    }

    fclose($output);
    wp_die();
}
public function mxchat_fetch_chat_history() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to view this page.', 'mxchat'));
    }

    // Get pagination parameters
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 50;
    $offset = ($page - 1) * $per_page;

    // Get search parameter if any
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    // Build the query based on whether we have a search term
    $search_condition = '';
    $search_params = array();

    if (!empty($search)) {
        $search_condition = "WHERE (
            session_id LIKE %s
            OR user_email LIKE %s
            OR user_identifier LIKE %s
            OR message LIKE %s
        )";
        $search_params = array(
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        );
    }

    // First count total sessions for pagination
    if (!empty($search)) {
        $count_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id)
            FROM {$table_name}
            {$search_condition}",
            $search_params
        );
    } else {
        $count_query = "SELECT COUNT(DISTINCT session_id) FROM {$table_name}";
    }

    $total_sessions = $wpdb->get_var($count_query);

    // Get paginated session IDs ordered by most recent message in each session
    if (!empty($search)) {
        $session_query = $wpdb->prepare(
            "SELECT DISTINCT t.session_id
            FROM {$table_name} t
            {$search_condition}
            GROUP BY t.session_id
            ORDER BY MAX(t.timestamp) DESC
            LIMIT %d OFFSET %d",
            array_merge($search_params, array($per_page, $offset))
        );
    } else {
        $session_query = $wpdb->prepare(
            "SELECT DISTINCT session_id
            FROM {$table_name}
            GROUP BY session_id
            ORDER BY MAX(timestamp) DESC
            LIMIT %d OFFSET %d",
            $per_page, $offset
        );
    }

    $session_ids = $wpdb->get_col($session_query);

    if (empty($session_ids)) {
        ob_start();
        echo '<div class="mxchat-no-results">';
        echo esc_html__('No chat history found.', 'mxchat');
        if (!empty($search)) {
            echo ' ' . esc_html__('Try adjusting your search criteria.', 'mxchat');
        }
        echo '</div>';
        $output = ob_get_clean();

        wp_send_json(array(
            'html' => $output,
            'page' => $page,
            'total_pages' => 0,
            'total_sessions' => 0
        ));

        wp_die();
    }

    ob_start();
    echo '<div class="mxchat-transcript">';

    // Iterate through sessions from newest to oldest
    foreach ($session_ids as $session_id) {
        // Get the email associated with this session (if available)
        $email = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_email
                FROM {$table_name}
                WHERE session_id = %s AND user_email != ''
                ORDER BY timestamp ASC
                LIMIT 1",
                $session_id
            )
        );

        // Get messages for this session ordered by timestamp
        $messages = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE session_id = %s
                ORDER BY timestamp ASC",
                $session_id
            )
        );

        // Start session block
        echo '<div class="mxchat-session">';
        echo '<div class="mxchat-session-header">';
        // Wrap checkbox and session ID in one block
        echo '<div class="mxchat-session-id">';
        echo '<input type="checkbox" name="delete_session_ids[]" value="' . esc_attr($session_id) . '"> ';
        echo '<strong>' . esc_html__('Session ID:', 'mxchat') . '</strong> ' . esc_html($session_id);
        echo '</div>';

        // Place email directly below the session ID block
        if (!empty($email)) {
            echo '<div class="mxchat-session-email">';
            echo '<strong>' . esc_html__('Email:', 'mxchat') . '</strong> ' . esc_html($email);
            echo '</div>';
        }
        echo '</div>';

        echo '<div class="mxchat-messages">';

        // Display messages for this session
        foreach ($messages as $transcript) {
            $formatted_timestamp = date_i18n('F j, Y g:i a', strtotime($transcript->timestamp));

            // Determine message styling
            switch ($transcript->role) {
                case 'assistant':
                case 'bot':
                    $message_class = 'bot-message';
                    $display_role = esc_html__('Chatbot', 'mxchat');
                    break;
                case 'user':
                    $message_class = 'user-message';
                    $display_role = !empty($transcript->user_identifier)
                        ? sanitize_text_field($transcript->user_identifier)
                        : esc_html__('User', 'mxchat');
                    break;
                case 'agent':
                    $message_class = 'agent-message';
                    $display_role = esc_html__('Agent', 'mxchat');
                    break;
                default:
                    $message_class = 'unknown-message';
                    $display_role = esc_html__('Unknown', 'mxchat');
            }

            // Process message content
            $message_content = wp_kses(
                stripslashes($transcript->message),
                [
                    'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [],
                    'br' => [], 'p' => [], 'ul' => [], 'ol' => [], 'li' => [],
                    'a' => ['href' => [], 'title' => []]
                ]
            );
            $message_content = nl2br($message_content);

            // Render message
            echo '<div class="mxchat-message ' . esc_attr($message_class) . '">';
            echo '<div class="mxchat-message-header">' . esc_html($display_role) . '</div>';
            echo '<div class="mxchat-message-content">' . $message_content . '</div>';
            echo '<div class="mxchat-timestamp">' . esc_html($formatted_timestamp) . '</div>';
            echo '</div>';
        }

        // Close session block
        echo '</div></div>';
    }

    echo '</div>';

    // Add pagination info and controls
    $total_pages = ceil($total_sessions / $per_page);

    echo '<div class="mxchat-pagination">';
    echo '<div class="mxchat-pagination-info">';
    echo sprintf(
        esc_html__('Showing %1$d to %2$d of %3$d sessions', 'mxchat'),
        $offset + 1,
        min($offset + $per_page, $total_sessions),
        $total_sessions
    );
    echo '</div>';

    if ($total_pages > 1) {
        echo '<div class="mxchat-pagination-controls">';

        // Previous page button
        if ($page > 1) {
            echo '<button class="mxchat-pagination-button" data-page="' . ($page - 1) . '">&laquo; ' . esc_html__('Previous', 'mxchat') . '</button>';
        }

        // Page numbers
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);

        if ($start_page > 1) {
            echo '<button class="mxchat-pagination-button" data-page="1">1</button>';
            if ($start_page > 2) {
                echo '<span class="mxchat-pagination-ellipsis">...</span>';
            }
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            $class = $i === $page ? 'mxchat-pagination-button active' : 'mxchat-pagination-button';
            echo '<button class="' . $class . '" data-page="' . $i . '">' . $i . '</button>';
        }

        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<span class="mxchat-pagination-ellipsis">...</span>';
            }
            echo '<button class="mxchat-pagination-button" data-page="' . $total_pages . '">' . $total_pages . '</button>';
        }

        // Next page button
        if ($page < $total_pages) {
            echo '<button class="mxchat-pagination-button" data-page="' . ($page + 1) . '">' . esc_html__('Next', 'mxchat') . ' &raquo;</button>';
        }

        echo '</div>';
    }
    echo '</div>';

    $output = ob_get_clean();

    wp_send_json(array(
        'html' => $output,
        'page' => $page,
        'total_pages' => $total_pages,
        'total_sessions' => $total_sessions
    ));

    wp_die();
}

public function mxchat_create_prompts_page() {
    //error_log('=== DEBUG: mxchat_create_prompts_page started ===');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
    
    $knowledge_manager = MxChat_Knowledge_Manager::get_instance();
    $pinecone_manager = MxChat_Pinecone_Manager::get_instance();

    // Display success message if all prompts were deleted
    if (isset($_GET['all_deleted']) && $_GET['all_deleted'] === 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('All knowledge has been deleted successfully.', 'mxchat') . '</p></div>';
    }

    // Set up pagination and search query
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : '';
    $search_query = (!empty($nonce) && wp_verify_nonce($nonce, 'mxchat_prompts_search_nonce') && isset($_GET['search'])) ? sanitize_text_field($_GET['search']) : '';
    $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $per_page = 10;

    //error_log('DEBUG: Search query: ' . $search_query);
    //error_log('DEBUG: Current page: ' . $current_page);
    //error_log('DEBUG: Per page: ' . $per_page);

    // ================================
    // REPLACE THIS SECTION WITH UNIFIED TABLE LOGIC
    // ================================

    // Get Pinecone settings to determine data source
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    //error_log('DEBUG: Pinecone options retrieved: ' . print_r($pinecone_options, true));
    
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';
    //error_log('DEBUG: use_pinecone decision: ' . ($use_pinecone ? 'TRUE' : 'FALSE'));
    
    $pinecone_api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    //error_log('DEBUG: Pinecone API key present: ' . (!empty($pinecone_api_key) ? 'YES' : 'NO'));

   if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
    //error_log('DEBUG: Using PINECONE data source');
    // PINECONE DATA SOURCE
    $data_source = 'pinecone';
    $pinecone_manager = MxChat_Pinecone_Manager::get_instance();
    //error_log('DEBUG: About to call mxchat_fetch_pinecone_records');
    
    $records = $pinecone_manager->mxchat_fetch_pinecone_records($pinecone_options, $search_query, $current_page, $per_page);
    $total_records = $records['total'] ?? 0;
    $prompts = $records['data'] ?? array();
    $total_in_database = $records['total_in_database'] ?? 0;
    $showing_recent_only = $records['showing_recent_only'] ?? false;

    $total_pages = ceil($total_records / $per_page);
    
    //error_log('DEBUG: Pinecone - total_records: ' . $total_records);
    //error_log('DEBUG: Pinecone - prompts count: ' . count($prompts));
    //error_log('DEBUG: Pinecone - total_pages: ' . $total_pages);
    //error_log('DEBUG: Pinecone - showing_recent_only: ' . ($showing_recent_only ? 'TRUE' : 'FALSE'));
    //error_log('DEBUG: Pinecone - total_in_database: ' . $total_in_database);
} else {
    //error_log('DEBUG: Using WORDPRESS DB data source');
    // WORDPRESS DB DATA SOURCE (your existing logic)
    $data_source = 'wordpress';
    
    // Initialize these variables for WordPress DB
    $total_in_database = 0;
    $showing_recent_only = false;

    $offset = ($current_page - 1) * $per_page;

    // Modify query to handle search input
    $sql_search = "";
    if ($search_query) {
        $sql_search = $wpdb->prepare("WHERE article_content LIKE %s", '%' . $wpdb->esc_like($search_query) . '%');
    }

    // Retrieve total number of prompts, considering search filter
    $count_query = "SELECT COUNT(*) FROM {$table_name} {$sql_search}";
    //error_log('DEBUG: WordPress count query: ' . $count_query);
    
    $total_records = $wpdb->get_var($count_query);
    //error_log('DEBUG: WordPress total_records: ' . $total_records);
    
    $total_pages = ceil($total_records / $per_page);

    // Retrieve prompts from the database
    $prompts_query = $wpdb->prepare(
        "SELECT * FROM {$table_name} {$sql_search} ORDER BY timestamp DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    );
    //error_log('DEBUG: WordPress prompts query: ' . $prompts_query);
    
    $prompts = $wpdb->get_results($prompts_query);
    //error_log('DEBUG: WordPress prompts count: ' . count($prompts));
}

//error_log('DEBUG: Final data_source: ' . $data_source);
//error_log('DEBUG: Final total_records: ' . $total_records);
//error_log('DEBUG: Final prompts count: ' . count($prompts));
//error_log('DEBUG: Final showing_recent_only: ' . ($showing_recent_only ? 'TRUE' : 'FALSE'));
//error_log('DEBUG: Final total_in_database: ' . $total_in_database);

    // Add the pagination links generation here
    $page_links = '';
    if ($total_pages > 1) {
        $page_links = paginate_links(array(
            'base'      => add_query_arg(array(
                'paged' => '%#%',
                'search' => urlencode($search_query),
                '_wpnonce' => wp_create_nonce('mxchat_prompts_search_nonce')
            ), admin_url('admin.php?page=mxchat-prompts')),
            'format'    => '',
            'prev_text' => __('&laquo; Previous', 'mxchat'),
            'next_text' => __('Next &raquo;', 'mxchat'),
            'total'     => $total_pages,
            'current'   => $current_page,
        ));
    }

    // ================================
    // END OF REPLACEMENT SECTION
    // ================================

    // Retrieve processing statuses
    $pdf_url     = get_transient('mxchat_last_pdf_url');
    $sitemap_url = get_transient('mxchat_last_sitemap_url');
    $knowledge_manager = MxChat_Knowledge_Manager::get_instance();
    $pdf_status  = $pdf_url ? $knowledge_manager->mxchat_get_pdf_processing_status($pdf_url) : false;
    $sitemap_status = $sitemap_url ? $knowledge_manager->mxchat_get_sitemap_processing_status($sitemap_url) : false;

    $is_processing = ($pdf_status && ($pdf_status['status'] === 'processing' || $pdf_status['status'] === 'error'))
                   || ($sitemap_status && ($sitemap_status['status'] === 'processing' || $sitemap_status['status'] === 'error'));

    //error_log('=== DEBUG: mxchat_create_prompts_page data preparation completed ===');
    
    ?>

<div class="wrap mxchat-wrapper">
        <!-- Hero Section -->
        <div class="mxchat-hero">
            <h1 class="mxchat-main-title">
                <span class="mxchat-gradient-text">Knowledge Base</span> Manager
            </h1>
            <p class="mxchat-hero-subtitle">
                <?php esc_html_e('Enhance your AI chatbot with custom knowledge. Import, manage, and organize your content to keep responses accurate and relevant.', 'mxchat'); ?>
            </p>
        </div>

        <div class="mxchat-content">


<!-- Tab Navigation -->
<div class="mxchat-kb-tabs-nav">
    <button class="mxchat-kb-tab-button active" data-tab="import">
        <?php esc_html_e('Knowledge Import', 'mxchat'); ?>
    </button>
    <button class="mxchat-kb-tab-button" data-tab="sync">
        <?php esc_html_e('Auto-Sync Settings', 'mxchat'); ?>
    </button>
    <button class="mxchat-kb-tab-button" data-tab="pinecone">
        <?php esc_html_e('Pinecone Settings', 'mxchat'); ?>
    </button>
</div>


<div class="mxchat-kb-tabs-content">
    <div id="mxchat-kb-tab-import" class="mxchat-kb-tab-content active">
<!-- Import Options Card -->
<div class="mxchat-card">

   <h2><?php esc_html_e('Knowledge Import Settings', 'mxchat'); ?></h2>

<?php
// Check if the appropriate embedding API key exists
$embedding_model = isset($this->options['embedding_model']) ? esc_attr($this->options['embedding_model']) : 'text-embedding-ada-002';
$has_openai_key = !empty($this->options['api_key']);
$has_voyage_key = !empty($this->options['voyage_api_key']);
$has_gemini_key = !empty($this->options['gemini_api_key']);

// Determine if they have the needed API key for their selected embedding model
$has_required_key = false;
$required_key_type = '';

if (strpos($embedding_model, 'text-embedding-') !== false && $has_openai_key) {
    $has_required_key = true;
    $required_key_type = 'OpenAI';
} elseif (strpos($embedding_model, 'voyage-') !== false && $has_voyage_key) {
    $has_required_key = true;
    $required_key_type = 'Voyage AI';
} elseif (strpos($embedding_model, 'gemini-embedding-') !== false && $has_gemini_key) {
    $has_required_key = true;
    $required_key_type = 'Google Gemini';
} elseif (strpos($embedding_model, 'text-embedding-') !== false) {
    $required_key_type = 'OpenAI';
} elseif (strpos($embedding_model, 'voyage-') !== false) {
    $required_key_type = 'Voyage AI';
} elseif (strpos($embedding_model, 'gemini-embedding-') !== false) {
    $required_key_type = 'Google Gemini';
}
?>

   <div class="mxchat-knowledge-warning <?php echo $has_required_key ? 'success' : 'warning'; ?>">
       <?php if ($has_required_key): ?>
           <p><span class="dashicons dashicons-yes-alt"></span> <?php echo wp_kses_post(sprintf(__('We detected your %s API key. <strong>You must have added credits to your %s account</strong> before using the knowledgebase.', 'mxchat'), $required_key_type, $required_key_type)); ?></p>
       <?php else: ?>
           <p><span class="dashicons dashicons-warning"></span> <strong><?php esc_html_e('Important:', 'mxchat'); ?></strong> <?php echo sprintf(esc_html__('Before importing knowledge, you must add a %s API key with sufficient credits in the Chatbot settings.', 'mxchat'), $required_key_type); ?> <a href="<?php echo admin_url('admin.php?page=mxchat-max'); ?>"><?php esc_html_e('Go to API Key Settings', 'mxchat'); ?></a></p>
       <?php endif; ?>
   </div>



<!-- Import Options Section -->
   <div class="mxchat-import-section">
       <h3><?php esc_html_e('Import Options', 'mxchat'); ?></h3>

       <!-- Import Options Grid -->
       <div class="mxchat-import-options">
           <!-- WordPress Import Option -->
           <button type="button" id="mxchat-open-content-selector" class="mxchat-import-box mxchat-import-wordpress" data-option="wordpress">
               <div class="mxchat-import-icon">
                   <span class="dashicons dashicons-wordpress"></span>
               </div>
               <div class="mxchat-import-content">
                   <h4><?php esc_html_e('WordPress Content', 'mxchat'); ?></h4>
                   <p><?php esc_html_e('Import specific posts and pages to your knowledge base.', 'mxchat'); ?></p>
               </div>
               <div class="mxchat-recommended-tag"><?php esc_html_e('Recommended', 'mxchat'); ?></div>
           </button>

           <!-- Sitemap Import Option -->
           <button type="button" class="mxchat-import-box" data-option="sitemap" data-placeholder="<?php esc_attr_e('Enter sitemap URL here', 'mxchat'); ?>" data-type="sitemap">
               <div class="mxchat-import-icon">
                   <span class="dashicons dashicons-admin-site-alt"></span>
               </div>
               <div class="mxchat-import-content">
                   <h4><?php esc_html_e('Sitemap Import', 'mxchat'); ?></h4>
                   <p><?php esc_html_e('Use a content-specific sub-sitemap, not the sitemap index.', 'mxchat'); ?></p>
               </div>
           </button>

           <!-- Direct URL Import Option -->
           <button type="button" class="mxchat-import-box" data-option="url" data-placeholder="<?php esc_attr_e('Enter webpage URL here', 'mxchat'); ?>" data-type="url">
               <div class="mxchat-import-icon">
                   <span class="dashicons dashicons-admin-links"></span>
               </div>
               <div class="mxchat-import-content">
                   <h4><?php esc_html_e('Direct URL', 'mxchat'); ?></h4>
                   <p><?php esc_html_e('Import content from any webpage.', 'mxchat'); ?></p>
               </div>
           </button>

           <!-- Direct Content Import Option -->
           <button type="button" class="mxchat-import-box" data-option="content" data-type="content">
               <div class="mxchat-import-icon">
                   <span class="dashicons dashicons-editor-paste-text"></span>
               </div>
               <div class="mxchat-import-content">
                   <h4><?php esc_html_e('Direct Content', 'mxchat'); ?></h4>
                   <p><?php esc_html_e('Submit content to be vectorized.', 'mxchat'); ?></p>
               </div>
           </button>
           
                      <!-- PDF Import Option -->
           <button type="button" class="mxchat-import-box" data-option="pdf" data-placeholder="<?php esc_attr_e('Enter PDF URL here', 'mxchat'); ?>" data-type="pdf">
               <div class="mxchat-import-icon">
                   <span class="dashicons dashicons-media-document"></span>
               </div>
               <div class="mxchat-import-content">
                   <h4><?php esc_html_e('PDF Import', 'mxchat'); ?></h4>
                   <p><?php esc_html_e('Import knowledge from PDF documents.', 'mxchat'); ?></p>
               </div>
           </button>
       </div>

       <!-- Input Areas for URL and Content -->
       <div class="mxchat-import-input-area" id="mxchat-url-input-area" style="display: none;">
           <?php if (!$is_processing) : ?>
               <form id="mxchat-url-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=mxchat_submit_sitemap')); ?>">
                   <?php wp_nonce_field('mxchat_submit_sitemap_action', 'mxchat_submit_sitemap_nonce'); ?>
                   <input type="hidden" name="import_type" id="import_type" value="url">
                   <div class="mxchat-url-input-group">
                       <input type="url"
                              name="sitemap_url"
                              id="sitemap_url"
                              placeholder="<?php esc_attr_e('Enter URL here', 'mxchat'); ?>"
                              required />
                       <button type="submit"
                               name="submit_sitemap"
                               class="mxchat-button-primary">
                           <?php esc_html_e('Import', 'mxchat'); ?>
                       </button>
                   </div>
                   <p class="mxchat-url-description" id="url-description-text"></p>
               </form>
           <?php endif; ?>
       </div>

       <div class="mxchat-import-input-area" id="mxchat-content-input-area" style="display: none;">
           <?php if (!$is_processing) : ?>
               <form id="mxchat-content-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=mxchat_submit_content')); ?>">
                   <?php wp_nonce_field('mxchat_submit_content_action', 'mxchat_submit_content_nonce'); ?>
                   <div class="mxchat-form-group">
                       <textarea
                           name="article_content"
                           id="article_content"
                           placeholder="<?php esc_attr_e('Enter your content here...', 'mxchat'); ?>"
                           required
                           rows="6"
                       ></textarea>
                   </div>
                   <div class="mxchat-form-group">
                       <input type="url"
                              name="article_url"
                              id="article_url"
                              placeholder="<?php esc_attr_e('Enter source URL (Optional)', 'mxchat'); ?>">
                   </div>
                   <button type="submit"
                           name="submit_content"
                           class="mxchat-button-primary">
                       <?php esc_html_e('Import Content', 'mxchat'); ?>
                   </button>
               </form>
           <?php endif; ?>
       </div>
   </div>

<!-- PDF Processing Status - ONLY PROCESSING -->
   <?php if ($pdf_status && $pdf_status['status'] === 'processing') : ?>
       <div class="mxchat-status-card" data-card-type="pdf">
           <div class="mxchat-status-header">
               <h4><?php esc_html_e('PDF Processing Status', 'mxchat'); ?></h4>
               
                <!-- Processing controls -->
                <div class="mxchat-processing-controls">
                    <form method="post" class="mxchat-stop-form"
                          action="<?php echo esc_url(admin_url('admin-post.php?action=mxchat_stop_processing')); ?>">
                        <?php wp_nonce_field('mxchat_stop_processing_action', 'mxchat_stop_processing_nonce'); ?>
                        <button type="submit" name="stop_processing" class="mxchat-button-secondary">
                            <?php esc_html_e('Stop Processing', 'mxchat'); ?>
                        </button>
                    </form>
                    
                    <button type="button" class="mxchat-manual-batch-btn" 
                            data-process-type="pdf"
                            data-url="<?php echo esc_attr(get_transient('mxchat_last_pdf_url')); ?>">
                        <?php esc_html_e('Process Batch', 'mxchat'); ?>
                    </button>
                </div>
           </div>
           
           <div class="mxchat-progress-bar">
               <div class="mxchat-progress-fill" style="width: <?php echo esc_attr($pdf_status['percentage']); ?>%"></div>
           </div>
           
           <div class="mxchat-status-details">
               <p><?php printf(
                   esc_html__('Progress: %1$d of %2$d pages (%3$d%%)', 'mxchat'),
                   absint($pdf_status['processed_pages']),
                   absint($pdf_status['total_pages']),
                   absint($pdf_status['percentage'])
               ); ?></p>
               
               <?php if (!empty($pdf_status['failed_pages']) && $pdf_status['failed_pages'] > 0) : ?>
                   <p><strong><?php esc_html_e('Failed pages:', 'mxchat'); ?></strong> <?php echo esc_html($pdf_status['failed_pages']); ?></p>
               <?php endif; ?>
               
               <p><strong><?php esc_html_e('Status:', 'mxchat'); ?></strong> <?php echo esc_html(ucfirst($pdf_status['status'])); ?></p>
               <p><strong><?php esc_html_e('Last update:', 'mxchat'); ?></strong> <?php echo esc_html($pdf_status['last_update']); ?></p>
           </div>
       </div>
   <?php endif; ?>

   <!-- Sitemap Processing Status - ONLY PROCESSING -->
   <?php if ($sitemap_status && $sitemap_status['status'] === 'processing') : ?>
       <div class="mxchat-status-card" data-card-type="sitemap">
           <div class="mxchat-status-header">
               <h4><?php esc_html_e('Sitemap Processing Status', 'mxchat'); ?></h4>
               
                <!-- Processing controls -->
                <div class="mxchat-processing-controls">
                    <form method="post" class="mxchat-stop-form"
                        action="<?php echo esc_url(admin_url('admin-post.php?action=mxchat_stop_processing')); ?>">
                        <?php wp_nonce_field('mxchat_stop_processing_action', 'mxchat_stop_processing_nonce'); ?>
                        <button type="submit" name="stop_processing" class="mxchat-button-secondary">
                            <?php esc_html_e('Stop Processing', 'mxchat'); ?>
                        </button>
                    </form>
                    
                    <button type="button" class="mxchat-manual-batch-btn" 
                            data-process-type="sitemap"
                            data-url="<?php echo esc_attr(get_transient('mxchat_last_sitemap_url')); ?>">
                        <?php esc_html_e('Process Batch', 'mxchat'); ?>
                    </button>
                </div>
           </div>
           
           <div class="mxchat-progress-bar">
               <div class="mxchat-progress-fill" style="width: <?php echo esc_attr($sitemap_status['percentage']); ?>%"></div>
           </div>
           
           <div class="mxchat-status-details">
               <p><?php printf(
                   esc_html__('Progress: %1$d of %2$d URLs (%3$d%%)', 'mxchat'),
                   absint($sitemap_status['processed_urls']),
                   absint($sitemap_status['total_urls']),
                   absint($sitemap_status['percentage'])
               ); ?></p>
               
               <?php if (!empty($sitemap_status['failed_urls']) && $sitemap_status['failed_urls'] > 0) : ?>
                   <p><strong><?php esc_html_e('Failed URLs:', 'mxchat'); ?></strong> <?php echo esc_html($sitemap_status['failed_urls']); ?></p>
               <?php endif; ?>
               
               <p><strong><?php esc_html_e('Status:', 'mxchat'); ?></strong> <?php echo esc_html(ucfirst($sitemap_status['status'])); ?></p>
               <p><strong><?php esc_html_e('Last update:', 'mxchat'); ?></strong> <?php echo esc_html($sitemap_status['last_update']); ?></p>
               
               <?php if (!empty($sitemap_status['error']) || !empty($sitemap_status['last_error'])) : ?>
                   <div class="mxchat-error-notice">
                       <?php if (!empty($sitemap_status['error'])) : ?>
                           <p class="error"><?php echo esc_html($sitemap_status['error']); ?></p>
                       <?php endif; ?>
                       <?php if (!empty($sitemap_status['last_error'])) : ?>
                           <p class="last-error"><?php echo esc_html__('Last error:', 'mxchat') . ' ' . esc_html($sitemap_status['last_error']); ?></p>
                       <?php endif; ?>
                   </div>
               <?php endif; ?>
           </div>
       </div>
   <?php endif; ?>

   <!-- Single URL Submission Status -->
   <?php 
   // Get single URL status
    $single_url_status = $this->knowledge_manager->mxchat_get_single_url_status();
   $is_active_processing = 
       ($sitemap_status && $sitemap_status['status'] === 'processing') || 
       ($pdf_status && $pdf_status['status'] === 'processing');
   ?>

   <div id="mxchat-single-url-status-container" <?php echo $is_active_processing ? 'style="display:none;"' : ''; ?>>
       <?php if ($single_url_status && !$is_active_processing) : ?>
           <div class="mxchat-status-card">
               <div class="mxchat-status-header">
                   <h4><?php esc_html_e('Last URL Submission', 'mxchat'); ?></h4>
                   <?php if ($single_url_status['status'] === 'failed') : ?>
                       <span class="mxchat-status-badge mxchat-status-failed"><?php esc_html_e('Failed', 'mxchat'); ?></span>
                   <?php else : ?>
                       <span class="mxchat-status-badge mxchat-status-success"><?php esc_html_e('Success', 'mxchat'); ?></span>
                   <?php endif; ?>
               </div>
               <div class="mxchat-status-details">
                   <p><strong><?php esc_html_e('URL:', 'mxchat'); ?></strong> 
                       <a href="<?php echo esc_url($single_url_status['url']); ?>" target="_blank">
                           <?php echo esc_html(strlen($single_url_status['url']) > 60 ? substr($single_url_status['url'], 0, 57) . '...' : $single_url_status['url']); ?>
                       </a>
                   </p>
                   <p><strong><?php esc_html_e('Submitted:', 'mxchat'); ?></strong> <?php echo esc_html($single_url_status['human_time']); ?></p>

                   <?php if ($single_url_status['status'] === 'failed' && !empty($single_url_status['error'])) : ?>
                       <div class="mxchat-error-notice">
                           <p class="error"><?php echo esc_html($single_url_status['error']); ?></p>
                       </div>
                   <?php endif; ?>

                   <?php if ($single_url_status['status'] === 'complete') : ?>
                       <p><strong><?php esc_html_e('Content Length:', 'mxchat'); ?></strong> <?php echo esc_html($single_url_status['content_length']); ?> <?php esc_html_e('characters', 'mxchat'); ?></p>
                       <p><strong><?php esc_html_e('Embedding Dimensions:', 'mxchat'); ?></strong> <?php echo esc_html($single_url_status['embedding_dimensions']); ?></p>
                   <?php endif; ?>
               </div>
           </div>
       <?php endif; ?>
   </div>
   
   <!-- Completed Status Cards - Handles completed PDF/Sitemap processing -->
   <?php
   $knowledge_manager = MxChat_Knowledge_Manager::get_instance();
   echo $knowledge_manager->mxchat_render_completed_status_cards();
   ?>


</div>

<!-- Knowledge Base Table Card -->
<div class="mxchat-card">
    <div class="mxchat-card-header">
<h2>
    <?php esc_html_e('Knowledge Base', 'mxchat'); ?>
    <span class="mxchat-record-count">
        <?php if ($showing_recent_only && $total_in_database > 1000): ?>
            (<?php echo esc_html($total_records); ?> of <?php echo esc_html(number_format($total_in_database)); ?> total - recent entries only)
        <?php else: ?>
            (<?php echo esc_html($total_records); ?>)
        <?php endif; ?>
    </span>
    <!-- Keep your existing badge code here -->
    <?php if ($use_pinecone) : ?>
        <span class="mxchat-data-source-badge pinecone" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; margin-left: 8px; background: #e3f2fd; color: #1976d2;">
            <span class="dashicons dashicons-cloud"></span>
            <?php esc_html_e('Pinecone', 'mxchat'); ?>
        </span>
    <?php else : ?>
        <span class="mxchat-data-source-badge wordpress" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; margin-left: 8px; background: #f3e5f5; color: #7b1fa2;">
            <span class="dashicons dashicons-database-view"></span>
            <?php esc_html_e('WordPress DB', 'mxchat'); ?>
        </span>
    <?php endif; ?>
</h2>
        <div class="mxchat-header-actions">
            <!-- Search -->
            <form method="get" id="knowledge-search" class="mxchat-search-form">
                <?php wp_nonce_field('mxchat_prompts_search_nonce'); ?>
                <input type="hidden" name="page" value="mxchat-prompts" />
                <div class="mxchat-search-group">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text"
                           name="search"
                           placeholder="<?php esc_attr_e('Search Knowledge', 'mxchat'); ?>"
                           value="<?php echo esc_attr($search_query); ?>" />
                </div>
            </form>

            <!-- Delete All -->
            <form method="post"
                  action="<?php echo esc_url(admin_url('admin-post.php?action=mxchat_delete_all_prompts')); ?>"
                  class="mxchat-delete-form"
                  onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete all knowledge? This action cannot be undone.', 'mxchat'); ?>');">
                <?php wp_nonce_field('mxchat_delete_all_prompts_action', 'mxchat_delete_all_prompts_nonce'); ?>
                <input type="hidden" name="data_source" value="<?php echo esc_attr($data_source); ?>" />
                <button type="submit" name="delete_all_prompts" class="mxchat-button-danger">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Delete All', 'mxchat'); ?>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Recent Entries Info Banner -->
<?php if ($showing_recent_only && $total_in_database > 1000): ?>
    <div class="mxchat-info-banner recent-only" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; background: #e3f2fd; border-left: 4px solid #2196f3; color: #1565c0;">
        <span class="dashicons dashicons-info"></span>
        <span>
            <?php printf(
                esc_html__('Showing your %s most recent entries for faster loading. Your complete database contains %s entries. ', 'mxchat'),
                number_format($total_records),
                number_format($total_in_database)
            ); ?>
            <a href="https://app.pinecone.io/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View complete database in Pinecone →', 'mxchat'); ?></a>
        </span>
    </div>
<?php endif; ?>

    <!-- Data Source Info Banner -->
    <?php if ($use_pinecone) : ?>
        <div class="mxchat-info-banner pinecone" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; background: #e8f5e8; border-left: 4px solid #4caf50; color: #2e7d2e;">
            <span class="dashicons dashicons-cloud"></span>
            <span><?php esc_html_e('Refresh page to see new content, but wait 5-10 seconds first! Refreshing too early means content won\'t show and you\'ll need to refresh again.', 'mxchat'); ?></span>
            </div>
    <?php else : ?>
        <div class="mxchat-info-banner wordpress" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; background: #fff3e0; border-left: 4px solid #ff9800; color: #e65100;">
            <span class="dashicons dashicons-database-view"></span>
            <span><?php esc_html_e('Refresh page to see new content, but wait 5-10 seconds first! Refreshing too early means content won\'t show and you\'ll need to refresh again.', 'mxchat'); ?></span>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="mxchat-table-wrapper">
        <table class="mxchat-records-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'mxchat'); ?></th>
                    <th><?php esc_html_e('Content', 'mxchat'); ?></th>
                    <th><?php esc_html_e('Source', 'mxchat'); ?></th>
                    <?php if ($data_source === 'pinecone') : ?>
                        <th><?php esc_html_e('Vector ID', 'mxchat'); ?></th>
                    <?php endif; ?>
                    <th><?php esc_html_e('Actions', 'mxchat'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($prompts) : ?>
                    <?php foreach ($prompts as $index => $prompt) : ?>
                        <tr id="prompt-<?php echo esc_attr($prompt->id); ?>"
                            data-source="<?php echo esc_attr($data_source); ?>"
                            <?php if ($data_source === 'pinecone') : ?>
                                style="background: rgba(33, 150, 243, 0.02);"
                            <?php endif; ?>>
                            <td>
                                <?php if ($data_source === 'pinecone') : ?>
                                    <?php echo esc_html($index + 1 + (($current_page - 1) * $per_page)); ?>
                                <?php else : ?>
                                    <?php echo esc_html($prompt->id); ?>
                                <?php endif; ?>
                            </td>
                            <td class="mxchat-content-cell">
                                <div class="content-view">
                                    <?php
                                    $content = $prompt->article_content;
                                    // Check if content contains Hebrew characters
                                    if (preg_match('/[\x{0590}-\x{05FF}]/u', $content)) {
                                        // Apply RTL direction for Hebrew content
                                        echo '<div dir="rtl" lang="he" class="rtl-content">';
                                        echo wp_kses_post(wpautop(esc_textarea($content)));
                                        echo '</div>';
                                    } else {
                                        echo wp_kses_post(wpautop(esc_textarea($content)));
                                    }
                                    ?>
                                </div>
                                <?php if ($data_source === 'wordpress') : ?>
                                    <textarea class="content-edit" style="display:none;"
                                              <?php if (preg_match('/[\x{0590}-\x{05FF}]/u', $prompt->article_content)) echo 'dir="rtl" lang="he"'; ?>>
                                        <?php echo esc_textarea($prompt->article_content); ?>
                                    </textarea>
                                <?php endif; ?>
                            </td>
                            <td class="mxchat-url-cell">
                                <div class="url-view">
                                    <?php if (!empty($prompt->source_url)) : ?>
                                        <a href="<?php echo esc_url($prompt->source_url); ?>" target="_blank">
                                            <span class="dashicons dashicons-external"></span>
                                            <?php esc_html_e('View Source', 'mxchat'); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="mxchat-na"><?php esc_html_e('N/A', 'mxchat'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($data_source === 'wordpress') : ?>
                                    <input type="text" class="url-edit" style="display:none;"
                                           value="<?php echo esc_attr($prompt->source_url); ?>" />
                                <?php endif; ?>
                            </td>
                            <?php if ($data_source === 'pinecone') : ?>
                                <td class="mxchat-vector-id-cell">
                                    <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-size: 11px;">
                                        <?php echo esc_html(substr($prompt->id, 0, 12) . '...'); ?>
                                    </code>
                                </td>
                            <?php endif; ?>
                            <td class="mxchat-actions-cell">
                                <?php if ($data_source === 'wordpress') : ?>
                                    <!-- WordPress DB - Full edit capabilities -->
                                    <button class="mxchat-button-icon edit-button"
                                            data-id="<?php echo esc_attr($prompt->id); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button class="mxchat-button-icon save-button"
                                            data-id="<?php echo esc_attr($prompt->id); ?>"
                                            style="display:none;"
                                            data-nonce="<?php echo wp_create_nonce('mxchat_save_inline_nonce'); ?>">
                                        <span class="dashicons dashicons-saved"></span>
                                    </button>
                                <?php else : ?>
                                    <!-- Pinecone - Read-only with note -->
                                    <span class="mxchat-readonly-note"
                                          title="<?php esc_attr_e('Pinecone records are read-only', 'mxchat'); ?>"
                                          style="opacity: 0.6; cursor: help;">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </span>
                                <?php endif; ?>

                                <!-- Delete button for both sources -->
                                <?php if ($data_source === 'pinecone') : ?>
                                    <!-- AJAX Delete for Pinecone -->
                                    <button type="button" 
                                            class="mxchat-button-icon delete-button-ajax"
                                            data-vector-id="<?php echo esc_attr($prompt->id); ?>"
                                            data-nonce="<?php echo wp_create_nonce('mxchat_delete_pinecone_prompt_nonce'); ?>"
                                            title="<?php esc_attr_e('Delete entry', 'mxchat'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                <?php else : ?>
                                    <!-- Regular link delete for WordPress DB -->
                                    <a href="<?php echo esc_url(admin_url(
                                        'admin-post.php?action=mxchat_delete_prompt&id=' . esc_attr($prompt->id)
                                        . '&_wpnonce=' . wp_create_nonce('mxchat_delete_prompt_nonce')
                                    )); ?>"
                                       class="mxchat-button-icon delete-button"
                                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this entry?', 'mxchat'); ?>');">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?php echo $data_source === 'pinecone' ? '5' : '4'; ?>" class="mxchat-no-records">
                            <?php if ($use_pinecone) : ?>
                                <?php esc_html_e('No vectors found in Pinecone database.', 'mxchat'); ?>
                            <?php else : ?>
                                <?php esc_html_e('No knowledge base entries found.', 'mxchat'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($page_links) : ?>
        <div class="mxchat-pagination">
            <?php echo wp_kses_post($page_links); ?>
        </div>
    <?php endif; ?>
</div>

</div>

    <!-- Sync Settings Tab (Initially Hidden) -->
    <div id="mxchat-kb-tab-sync" class="mxchat-kb-tab-content">
<div class="mxchat-card">
   <!-- Auto-Sync Settings -->
   <div class="mxchat-settings-section">
       <h3><?php esc_html_e('Auto-Sync Settings', 'mxchat'); ?></h3>
       <p class="mxchat-description">
           <?php esc_html_e('Note: Auto-sync works only for newly published content. Existing posts and pages must be imported manually below. Works with Pinecone if enabled', 'mxchat'); ?>
       </p>
       <div class="mxchat-autosave-section">
           <div class="mxchat-toggle-group">
               <div class="mxchat-toggle-container">
                   <label class="mxchat-toggle-switch">
                       <input type="checkbox"
                              name="mxchat_auto_sync_posts"
                              class="mxchat-autosave-field"
                              value="1"
                              data-nonce="<?php echo wp_create_nonce('mxchat_prompts_setting_nonce'); ?>"
                              <?php checked(get_option('mxchat_auto_sync_posts', '0'), '1'); ?>>
                       <span class="mxchat-toggle-slider"></span>
                   </label>
                   <span class="mxchat-toggle-label">
                       <?php esc_html_e('Auto-sync Posts', 'mxchat'); ?>
                   </span>
               </div>

               <div class="mxchat-toggle-container">
                   <label class="mxchat-toggle-switch">
                       <input type="checkbox"
                              name="mxchat_auto_sync_pages"
                              class="mxchat-autosave-field"
                              value="1"
                              data-nonce="<?php echo wp_create_nonce('mxchat_prompts_setting_nonce'); ?>"
                              <?php checked(get_option('mxchat_auto_sync_pages', '0'), '1'); ?>>
                       <span class="mxchat-toggle-slider"></span>
                   </label>
                   <span class="mxchat-toggle-label">
                       <?php esc_html_e('Auto-sync Pages', 'mxchat'); ?>
                   </span>
               </div>

               <!-- Custom Post Types Section -->
               <div class="mxchat-section-content">
                   <div class="mxchat-custom-post-types-header">
                       <button id="mxchat-custom-post-types-toggle" class="mxchat-button-secondary">
                           <?php esc_html_e('Advanced Custom Post Sync Settings', 'mxchat'); ?>
                           <span class="mxchat-toggle-icon">▼</span>
                       </button>
                   </div>

                   <div id="mxchat-custom-post-types-container" class="mxchat-custom-post-types-container" style="display: none;">
                       <h3><?php esc_html_e('Sync Custom Post Types', 'mxchat'); ?></h3>
                       <p><?php esc_html_e('Select additional custom post types to automatically sync with the chatbot.', 'mxchat'); ?></p>

                       <div class="mxchat-custom-post-types">
                        <?php
                        $post_types = $knowledge_manager->mxchat_get_public_post_types();
                        // Skip post and page as they're handled separately
                        unset($post_types['post']);
                        unset($post_types['page']);

                           if (!empty($post_types)) {
                               foreach ($post_types as $post_type => $label) {
                                   $option_name = 'mxchat_auto_sync_' . $post_type;
                                   $is_enabled = get_option($option_name, '0');
                                   ?>
                                   <div class="mxchat-toggle-container">
                                       <label class="mxchat-toggle-switch">
                                           <input type="checkbox"
                                                  name="<?php echo esc_attr($option_name); ?>"
                                                  class="mxchat-autosave-field"
                                                  value="1"
                                                  data-nonce="<?php echo wp_create_nonce('mxchat_prompts_setting_nonce'); ?>"
                                                  <?php checked($is_enabled, '1'); ?>>
                                           <span class="mxchat-toggle-slider"></span>
                                       </label>
                                       <span class="mxchat-toggle-label">
                                           <?php echo esc_html($label); ?> (<?php echo esc_html($post_type); ?>)
                                       </span>
                                   </div>
                                   <?php
                               }
                           } else {
                               echo '<p>' . esc_html__('No custom post types found.', 'mxchat') . '</p>';
                           }
                           ?>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
</div>
</div>

<!-- NEW PINECONE TAB -->
    <div id="mxchat-kb-tab-pinecone" class="mxchat-kb-tab-content mxchat-autosave-section">
        <div class="mxchat-card">
            <h2><?php esc_html_e('Pinecone Vector Database Settings', 'mxchat'); ?></h2>
            <p class="mxchat-description">
                <?php echo wp_kses(
                    __('<strong>Pinecone is optional</strong> and not required for MxChat to function. It provides enhanced search performance for larger knowledge bases. When enabled, content will be stored in Pinecone instead of the WordPress database, but you can use MxChat without it.', 'mxchat'),
                    array('strong' => array())
                ); ?>
            </p>

            <div class="mxchat-pinecone-info-box">
                <div class="mxchat-info-icon">
                    <span class="dashicons dashicons-info"></span>
                </div>
                <div class="mxchat-info-content">
                    <h4><?php esc_html_e('What is Pinecone?', 'mxchat'); ?></h4>
                    <p><?php esc_html_e('Pinecone is a specialized vector database designed for AI applications. It provides faster similarity searches and better scalability compared to traditional databases for AI-powered features.', 'mxchat'); ?></p>
                    <p><a href="https://www.pinecone.io/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn more about Pinecone →', 'mxchat'); ?></a></p>
                </div>
            </div>

            <div class="mxchat-database-settings-form">
                <!-- REMOVED the WordPress form - we're using AJAX auto-save instead -->
                <?php
                $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
                $use_pinecone = $pinecone_options['mxchat_use_pinecone'] ?? '0';
                ?>

                <div class="mxchat-toggle-container">
                    <label class="mxchat-toggle-switch">
                        <input type="checkbox"
                               name="mxchat_pinecone_addon_options[mxchat_use_pinecone]"
                               value="1"
                               <?php checked($use_pinecone, '1'); ?>>
                        <span class="mxchat-toggle-slider"></span>
                    </label>
                    <span class="mxchat-toggle-label">
                        <?php esc_html_e('Enable Pinecone Database', 'mxchat'); ?>
                    </span>
                </div>

                <div class="mxchat-pinecone-settings" <?php echo $use_pinecone ? '' : 'style="display: none;"'; ?>>

                    <?php if ($use_pinecone) : ?>
                        <div class="mxchat-knowledge-warning success">
                            <p><span class="dashicons dashicons-yes-alt"></span>
                               <?php esc_html_e('Pinecone is enabled. All new knowledge base content will be stored in Pinecone.', 'mxchat'); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="mxchat-form-group">
                        <label for="mxchat_pinecone_api_key">
                            <?php esc_html_e('Pinecone API Key', 'mxchat'); ?> <span class="required">*</span>
                        </label>
                        <input type="password"
                               id="mxchat_pinecone_api_key"
                               name="mxchat_pinecone_addon_options[mxchat_pinecone_api_key]"
                               value="<?php echo esc_attr($pinecone_options['mxchat_pinecone_api_key'] ?? ''); ?>"
                               class="regular-text"
                               placeholder="pcsk_..." />
                        <p class="description">
                            <?php esc_html_e('Found in your Pinecone dashboard under API Keys.', 'mxchat'); ?>
                            <a href="https://app.pinecone.io/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Pinecone Dashboard', 'mxchat'); ?></a>
                        </p>
                    </div>

                    <div class="mxchat-form-group">
                        <label for="mxchat_pinecone_environment">
                            <?php esc_html_e('Region', 'mxchat'); ?>
                        </label>
                        <input type="text"
                               id="mxchat_pinecone_environment"
                               name="mxchat_pinecone_addon_options[mxchat_pinecone_environment]"
                               value="<?php echo esc_attr($pinecone_options['mxchat_pinecone_environment'] ?? ''); ?>"
                               placeholder="e.g., gcp-starter"
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('Your Pinecone environment/region (e.g., gcp-starter, us-west1-gcp, us-east-1-aws)', 'mxchat'); ?>
                        </p>
                    </div>

                    <div class="mxchat-form-group">
                        <label for="mxchat_pinecone_index">
                            <?php esc_html_e('Index Name', 'mxchat'); ?> <span class="required">*</span>
                        </label>
                        <input type="text"
                               id="mxchat_pinecone_index"
                               name="mxchat_pinecone_addon_options[mxchat_pinecone_index]"
                               value="<?php echo esc_attr($pinecone_options['mxchat_pinecone_index'] ?? ''); ?>"
                               placeholder="e.g., my-wordpress-vectors"
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('The name of your Pinecone index. Must be created in your Pinecone dashboard first.', 'mxchat'); ?>
                        </p>
                    </div>

                    <div class="mxchat-form-group">
                        <label for="mxchat_pinecone_host">
                            <?php esc_html_e('Pinecone Host', 'mxchat'); ?> <span class="required">*</span>
                        </label>
                        <input type="text"
                               id="mxchat_pinecone_host"
                               name="mxchat_pinecone_addon_options[mxchat_pinecone_host]"
                               value="<?php echo esc_attr($pinecone_options['mxchat_pinecone_host'] ?? ''); ?>"
                               placeholder="e.g., my-index-xyz123.svc.pinecone.io"
                               class="regular-text" />
                        <p class="description">
                            <?php esc_html_e('The hostname from your Pinecone index URL (exclude https://). Found in your index details.', 'mxchat'); ?>
                        </p>
                    </div>

<div class="mxchat-setup-steps">
    <h3><?php esc_html_e('Setup Instructions', 'mxchat'); ?></h3>
    <div class="mxchat-setup-step">
        <span class="step-number">1</span>
        <div class="step-content">
            <h4><?php esc_html_e('Create Account', 'mxchat'); ?></h4>
            <p><?php esc_html_e('Create a free account at', 'mxchat'); ?> <a href="https://www.pinecone.io/" target="_blank">pinecone.io</a></p>
        </div>
    </div>
    <div class="mxchat-setup-step">
        <span class="step-number">2</span>
        <div class="step-content">
            <h4><?php esc_html_e('Create Index', 'mxchat'); ?></h4>
            <p><?php esc_html_e('Create a new index with these settings:', 'mxchat'); ?></p>
            <ul>
                <li><?php esc_html_e('Dimensions: Choose based on your embedding model - 1536 (OpenAI Ada 2, TE3 Small), 2048 (Voyage-3 Large), 3072 (TE3 Large, Gemini Embedding), 1536 (Gemini Embedding), or 768 (Gemini Embedding)', 'mxchat'); ?></li>
                <li><?php esc_html_e('Metric: Cosine', 'mxchat'); ?></li>
                <li><?php esc_html_e('Cloud & Region: Your preferred region', 'mxchat'); ?></li>
            </ul>
        </div>
    </div>
    <div class="mxchat-setup-step">
        <span class="step-number">3</span>
        <div class="step-content">
            <h4><?php esc_html_e('Get API Key', 'mxchat'); ?></h4>
            <p><?php esc_html_e('Copy your API key from the API Keys section', 'mxchat'); ?></p>
        </div>
    </div>
    <div class="mxchat-setup-step">
        <span class="step-number">4</span>
        <div class="step-content">
            <h4><?php esc_html_e('Get Index Host', 'mxchat'); ?></h4>
            <p><?php esc_html_e('Copy your index host URL from the index details', 'mxchat'); ?></p>
        </div>
    </div>
    <div class="mxchat-setup-step">
        <span class="step-number">5</span>
        <div class="step-content">
            <h4><?php esc_html_e('Save Settings', 'mxchat'); ?></h4>
            <p><?php esc_html_e('Fill in the form above and save settings', 'mxchat'); ?></p>
        </div>
    </div>
</div>



                </div>


            </div>
        </div>
    </div>

</div>




</div>
</div>


<!-- Content Selector Modal -->
<div id="mxchat-kb-content-selector-modal" class="mxchat-kb-modal">
    <div class="mxchat-kb-modal-content">
<div class="mxchat-kb-modal-header">
    <h3>
        <?php esc_html_e('Select WordPress Content', 'mxchat'); ?><br>
        <span class="mxchat-kb-header-note"><?php esc_html_e('(Content imported here will be tagged "In Knowledge Base")', 'mxchat'); ?></span>
    </h3>
    <span class="mxchat-kb-modal-close">&times;</span>
</div>
        <div class="mxchat-kb-modal-filters">
            <div class="mxchat-kb-search-group">
                <input type="text" id="mxchat-kb-content-search" placeholder="<?php esc_attr_e('Search...', 'mxchat'); ?>">
            </div>

            <div class="mxchat-kb-filter-group">
                <select id="mxchat-kb-content-type-filter">
                    <option value="all"><?php esc_html_e('All Content Types', 'mxchat'); ?></option>
                    <option value="post"><?php esc_html_e('Posts', 'mxchat'); ?></option>
                    <option value="page"><?php esc_html_e('Pages', 'mxchat'); ?></option>
                    <?php
                    // Add other post types dynamically
                    $post_types = get_post_types(array('public' => true), 'objects');
                    foreach ($post_types as $post_type) {
                        // Skip post and page as they're already added
                        if (!in_array($post_type->name, array('post', 'page'))) {
                            echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                        }
                    }
                    ?>
                </select>

                <select id="mxchat-kb-content-status-filter">
                    <option value="publish"><?php esc_html_e('Published', 'mxchat'); ?></option>
                    <option value="draft"><?php esc_html_e('Drafts', 'mxchat'); ?></option>
                    <option value="all"><?php esc_html_e('All Statuses', 'mxchat'); ?></option>
                </select>

                <select id="mxchat-kb-processed-filter">
                    <option value="all"><?php esc_html_e('All Content', 'mxchat'); ?></option>
                    <option value="processed"><?php esc_html_e('In Knowledge Base', 'mxchat'); ?></option>
                    <option value="unprocessed"><?php esc_html_e('Not In Knowledge Base', 'mxchat'); ?></option>
                </select>
            </div>
        </div>

        <div class="mxchat-kb-content-selection">
            <div class="mxchat-kb-selection-header">
                <label>
                    <input type="checkbox" id="mxchat-kb-select-all">
                    <?php esc_html_e('Select All', 'mxchat'); ?>
                </label>
                <span class="mxchat-kb-selection-count">0 <?php esc_html_e('selected', 'mxchat'); ?></span>
            </div>

            <div class="mxchat-kb-content-list">
                <!-- Content will be loaded here via AJAX -->
                <div class="mxchat-kb-loading">
                    <span class="mxchat-kb-spinner is-active"></span>
                    <?php esc_html_e('Loading content...', 'mxchat'); ?>
                </div>
            </div>

            <div class="mxchat-kb-pagination">
                <!-- Pagination will be added here -->
            </div>
        </div>

        <div class="mxchat-kb-modal-footer">
            <button type="button" id="mxchat-kb-process-selected" class="mxchat-kb-button-primary" disabled>
                <?php esc_html_e('Process Selected Content', 'mxchat'); ?>
                <span class="mxchat-kb-selected-count">(0)</span>
            </button>
            <button type="button" class="mxchat-kb-button-secondary mxchat-kb-modal-close">
                <?php esc_html_e('Cancel', 'mxchat'); ?>
            </button>
        </div>
    </div>
</div>

<?php
}
public function mxchat_delete_chat_history() {
    if (!current_user_can('manage_options')) {
        echo wp_json_encode(['error' => esc_html__('You do not have sufficient permissions.', 'mxchat')]);
        wp_die();
    }
    check_ajax_referer('mxchat_delete_chat_history', 'security');
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';

    if (isset($_POST['delete_session_ids']) && is_array($_POST['delete_session_ids'])) {
        $deleted_count = 0;

        foreach ($_POST['delete_session_ids'] as $session_id) {
            $session_id_sanitized = sanitize_text_field($session_id);

            // Clear relevant cache before deletion
            $cache_key = 'chat_session_' . $session_id_sanitized;
            wp_cache_delete($cache_key, 'mxchat_chat_sessions');

            // Perform the deletion from the database table
            $wpdb->delete($table_name, ['session_id' => $session_id_sanitized]);

            // Delete the corresponding option entry from wp_options table
            delete_option("mxchat_history_" . $session_id_sanitized);

            // Delete any associated metadata options
            delete_option("mxchat_email_" . $session_id_sanitized);
            delete_option("mxchat_agent_name_" . $session_id_sanitized);

            $deleted_count++;
        }

        // Optionally, clear a general cache if you have one
        wp_cache_delete('all_chat_sessions', 'mxchat_chat_sessions');

        echo wp_json_encode([
            'success' => sprintf(
                esc_html__('%d chat session(s) have been deleted from all storage locations.', 'mxchat'),
                $deleted_count
            )
        ]);
    } else {
        echo wp_json_encode(['error' => esc_html__('No chat sessions selected for deletion.', 'mxchat')]);
    }

    wp_die();
}

public function display_admin_notices() {
    // Check if we're on a MXChat admin page
    $screen = get_current_screen();
    if (!$screen || strpos($screen->base, 'mxchat') === false) {
        return;
    }

    //error_log('MxChat admin_notices hook fired on screen: ' . $screen->base);

    // Check for error notices
    $error_notice = get_transient('mxchat_admin_notice_error');
    if ($error_notice) {
        //error_log('Found error transient: ' . $error_notice);
        echo '<div class="notice notice-error is-dismissible"><p>' . wp_kses_post($error_notice) . '</p></div>';
        delete_transient('mxchat_admin_notice_error');
        //error_log('Displayed and deleted error transient');
    } else {
        //error_log('No error transient found');
    }

    // Check for success notices
    $success_notice = get_transient('mxchat_admin_notice_success');
    if ($success_notice) {
        //error_log('Found success transient: ' . $success_notice);
        echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post($success_notice) . '</p></div>';
        delete_transient('mxchat_admin_notice_success');
        //error_log('Displayed and deleted success transient');
    }

    // Check for info notices
    $info_notice = get_transient('mxchat_admin_notice_info');
    if ($info_notice) {
        //error_log('Found info transient: ' . $info_notice);
        echo '<div class="notice notice-info is-dismissible"><p>' . wp_kses_post($info_notice) . '</p></div>';
        delete_transient('mxchat_admin_notice_info');
        //error_log('Displayed and deleted info transient');
    }

}

public function mxchat_create_activation_page() {
    $license_status = get_option('mxchat_license_status', 'inactive');
    $license_error = get_option('mxchat_license_error', '');
    ?>
    <div class="wrap mxchat-admin-activation">
        <div class="mxchat-pro-hero">
            <h1 class="pro-title">
                <span class="pro-gradient-text">Activate</span> MxChat Pro
            </h1>
            <p class="pro-subtitle">
                <?php esc_html_e('Enter your license key to unlock premium features, advanced AI capabilities, and priority support.', 'mxchat'); ?>
            </p>
        </div>

        <?php if ($license_status === 'inactive' && !empty($license_error)): ?>
            <div class="error notice">
                <p><?php echo esc_html($license_error); ?></p>
            </div>
        <?php endif; ?>

        <form id="mxchat-activation-form" class="mxchat-pro-form" style="<?php echo $license_status === 'active' ? 'display: none;' : ''; ?>">
            <div class="mxchat-pro-form-container">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Email Address', 'mxchat'); ?></th>
                        <td>
                            <input type="email" id="mxchat_pro_email" name="mxchat_pro_email" value="<?php echo esc_attr(get_option('mxchat_pro_email')); ?>" class="regular-text mxchat-pro-input" required />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Activation Key', 'mxchat'); ?></th>
                        <td>
                            <input type="text" id="mxchat_activation_key" name="mxchat_activation_key" value="<?php echo esc_attr(get_option('mxchat_activation_key')); ?>" class="regular-text mxchat-pro-input" required />
                        </td>
                    </tr>
                </table>
                <?php if ($license_status !== 'active'): ?>
                    <div class="mxchat-pro-button-container">
                        <button type="submit" id="activate_license_button" class="button button-primary mxchat-pro-button"><?php esc_html_e('Activate License', 'mxchat'); ?></button>
                        <div id="mxchat-activation-spinner" class="mxchat-activation-spinner" style="display: none;"></div>
                    </div>
                <?php endif; ?>
            </div>
        </form>
        <!-- License Status Display -->
        <div class="mxchat-pro-status">
            <h3><?php esc_html_e('License Status:', 'mxchat'); ?>
                <span id="mxchat-license-status" class="mxchat-status-badge <?php echo $license_status; ?>">
                    <?php echo $license_status === 'active' ? esc_html__('Active', 'mxchat') : esc_html__('Inactive', 'mxchat'); ?>
                </span>
            </h3>
        </div>

    </div>
    <?php
}

public function mxchat_actions_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Keep existing data fetching logic
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_intents';
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    // Success message
    if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>' .
             esc_html__('Action updated successfully.', 'mxchat') .
             '</p></div>';
    }

    // Filtering logic
    $where = '1=1';
    $search_term = isset($_GET['s']) ? trim($_GET['s']) : '';
    $callback_filter = isset($_GET['callback_filter']) ? sanitize_text_field($_GET['callback_filter']) : '';

    if ($search_term) {
        $search_term_like = '%' . $wpdb->esc_like($search_term) . '%';
        $where .= $wpdb->prepare(' AND (intent_label LIKE %s OR phrases LIKE %s)',
                                $search_term_like, $search_term_like);
    }

    if ($callback_filter) {
        $where .= $wpdb->prepare(' AND callback_function = %s', $callback_filter);
    }

    // Pagination
    $total_intents = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
    $total_pages = ceil($total_intents / $per_page);

    // Get intents (now called actions)
    $actions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where LIMIT %d OFFSET %d",
        $per_page, $offset
    ));

    // Get callbacks
    $available_callbacks = $this->mxchat_get_available_callbacks();

    ?>
    <div class="wrap mxchat-wrapper">
        <!-- Hero Section -->
        <div class="mxchat-hero">
            <h1 class="mxchat-main-title">
                <span class="mxchat-gradient-text">Actions</span> Manager
            </h1>
            <p class="mxchat-hero-subtitle">
                <?php esc_html_e('Create and manage custom actions to enhance your chatbot\'s capabilities.', 'mxchat'); ?>
            </p>
        </div>

        <!-- Actions Header with Search and Filter -->
        <div class="mxchat-actions-header">
            <div class="mxchat-actions-filters">
                <form method="get" class="mxchat-search-form">
                    <input type="hidden" name="page" value="mxchat-actions">
                    <div class="mxchat-search-group">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" name="s" class="mxchat-search-input"
                               placeholder="<?php esc_attr_e('Search Actions', 'mxchat'); ?>"
                               value="<?php echo esc_attr($search_term); ?>">
                    </div>
                    <select name="callback_filter" class="mxchat-action-filter">
                        <option value=""><?php esc_html_e('All Action Types', 'mxchat'); ?></option>
                        <?php foreach ($available_callbacks as $function => $callback_data) :
                            $label = $callback_data['label']; ?>
                            <option value="<?php echo esc_attr($function); ?>"
                                    <?php selected($callback_filter, $function); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="mxchat-button-secondary">
                        <?php esc_html_e('Filter', 'mxchat'); ?>
                    </button>
                </form>
            </div>
            <div class="mxchat-actions-controls">
                <button type="button" id="mxchat-add-action-btn" class="mxchat-button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Add New Action', 'mxchat'); ?>
                </button>
            </div>
        </div>

        <!-- Actions Grid Layout - All actions in a single grid -->
        <div class="mxchat-actions-grid">
            <div class="mxchat-cards-container">
                <?php if (!empty($actions)) : ?>
                    <?php foreach ($actions as $action) :
                        $callback_function = $action->callback_function;
                        $callback_label = isset($available_callbacks[$callback_function]['label'])
                            ? $available_callbacks[$callback_function]['label']
                            : $callback_function;
                        $threshold_value = isset($action->similarity_threshold)
                            ? round($action->similarity_threshold * 100)
                            : 85;

                        // Check if this is a form action
                        $is_form_action = strpos($action->intent_label, 'Form ') === 0;

                        // Get action status (enabled/disabled) - default to true if column doesn't exist
                        $is_enabled = isset($action->enabled) ? (bool)$action->enabled : true;
                    ?>
                        <div class="mxchat-action-card <?php echo $is_form_action ? 'mxchat-form-action' : ''; ?>">
                            <div class="mxchat-card-header">
                                <div class="mxchat-card-title"><?php echo esc_html($action->intent_label); ?></div>
                                <div class="mxchat-card-toggle">
                                    <label class="mxchat-switch">
                                        <input type="checkbox" class="mxchat-action-toggle"
                                               data-action-id="<?php echo esc_attr($action->id); ?>"
                                               <?php checked($is_enabled); ?>>
                                        <span class="mxchat-slider round"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="mxchat-card-body">
                                <div class="mxchat-card-description">
                                    <strong><?php esc_html_e('Type:', 'mxchat'); ?></strong>
                                    <?php echo esc_html($callback_label); ?>
                                </div>

                                <div class="mxchat-card-phrases">
                                    <strong><?php esc_html_e('Trigger phrases:', 'mxchat'); ?></strong>
                                    <div class="mxchat-phrases-preview">
                                        <?php
                                        // Check if the helper function exists, otherwise use a simple substring
                                        if (method_exists($this, 'get_trimmed_phrases')) {
                                            echo esc_html($this->get_trimmed_phrases($action->phrases));
                                        } else {
                                            echo esc_html(strlen($action->phrases) > 100 ?
                                                substr($action->phrases, 0, 97) . '...' :
                                                $action->phrases);
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="mxchat-threshold-control">
                                    <div class="mxchat-threshold-label">
                                        <?php esc_html_e('Similarity Threshold:', 'mxchat'); ?>
                                        <span class="mxchat-threshold-value"><?php echo esc_html($threshold_value); ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mxchat-card-footer">
                                <?php
                                // Check if it's a form action
                                $is_form_action = preg_match('/Form (\d+)/', $action->intent_label, $form_matches);

                                // Check if it's a recommendation flow action
                                $is_flow_action = preg_match('/Recommendation Flow (\d+)/', $action->intent_label, $flow_matches);

                                if ($is_form_action) {
                                    $form_id = isset($form_matches[1]) ? $form_matches[1] : '';
                                ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mxchat-forms&action=edit&form_id=' . $form_id)); ?>"
                                       class="mxchat-button-primary">
                                        <span class="dashicons dashicons-feedback"></span>
                                        <?php esc_html_e('Edit Form', 'mxchat'); ?>
                                    </a>
                                <?php } elseif ($is_flow_action) {
                                ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mxchat-smart-recommender')); ?>"
                                       class="mxchat-button-primary">
                                        <span class="dashicons dashicons-list-view"></span>
                                        <?php esc_html_e('Manage Flows', 'mxchat'); ?>
                                    </a>
                                <?php } else { ?>
                                    <button type="button"
                                            class="mxchat-button-secondary mxchat-edit-button"
                                            data-action-id="<?php echo esc_attr($action->id); ?>"
                                            data-phrases="<?php echo esc_attr($action->phrases); ?>"
                                            data-label="<?php echo esc_attr($action->intent_label); ?>"
                                            data-threshold="<?php echo esc_attr(round($action->similarity_threshold * 100)); ?>"
                                            data-callback-function="<?php echo esc_attr($action->callback_function); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                        <?php esc_html_e('Edit', 'mxchat'); ?>
                                    </button>
                                    <form method="post"
                                          action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                          class="mxchat-delete-form"
                                          onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this action?', 'mxchat'); ?>');">
                                        <?php wp_nonce_field('mxchat_delete_intent_nonce'); ?>
                                        <input type="hidden" name="action" value="mxchat_delete_intent">
                                        <input type="hidden" name="intent_id" value="<?php echo esc_attr($action->id); ?>">
                                        <button type="submit" class="mxchat-button-text mxchat-delete-button">
                                            <span class="dashicons dashicons-trash"></span>
                                            <?php esc_html_e('Delete', 'mxchat'); ?>
                                        </button>
                                    </form>
                                <?php } ?>
                            </div>


                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <!-- If no actions found -->
                    <div class="mxchat-no-actions">
                        <div class="mxchat-empty-state">
                            <span class="dashicons dashicons-format-chat"></span>
                            <h2><?php esc_html_e('No actions found', 'mxchat'); ?></h2>
                            <p><?php esc_html_e('Get started by creating your first action to enhance your chatbot.', 'mxchat'); ?></p>
                            <button type="button" id="mxchat-create-first-action" class="mxchat-button-primary">
                                <?php esc_html_e('Create Your First Action', 'mxchat'); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($total_pages > 1) : ?>
            <div class="mxchat-pagination">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo; Previous', 'mxchat'),
                    'next_text' => __('Next &raquo;', 'mxchat'),
                    'total' => $total_pages,
                    'current' => $page
                ));
                ?>
            </div>
        <?php endif; ?>

<!-- Add/Edit Action Modal with Step-Based Approach -->
<!-- Complete Modal HTML with Defined Groups Variable -->
<div id="mxchat-action-modal" class="mxchat-modal" style="display: none;">
    <div class="mxchat-modal-content">
        <span class="mxchat-modal-close">&times;</span>

        <form id="mxchat-action-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <!-- Dynamic nonce field -->
            <div id="action-nonce-container">
                <?php wp_nonce_field('mxchat_add_intent_nonce', 'add_intent_nonce'); ?>
            </div>
            <input type="hidden" name="action" id="form_action_type" value="mxchat_add_intent">
            <input type="hidden" name="intent_id" id="edit_action_id" value="">
            <input type="hidden" name="callback_function" id="callback_function" value="">

            <!-- Step 1: Action Type Selection -->
            <div id="mxchat-action-step-1" class="mxchat-action-step active">
                <div class="mxchat-step-indicator">
                    <div class="mxchat-step-number">1</div>
                    <div class="mxchat-step-title"><?php esc_html_e('Select Action Type', 'mxchat'); ?></div>
                </div>

                <div id="mxchat-action-type-selector" class="mxchat-action-type-selector">
                    <div class="mxchat-action-type-search">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" id="action-type-search" placeholder="<?php esc_attr_e('Search action types...', 'mxchat'); ?>" class="mxchat-action-type-search-input">
                    </div>

                    <?php
                    // Get the callbacks - IMPORTANT: Define the $groups variable here
                    $groups = $this->mxchat_get_available_callbacks(true, true);
                    ?>

                    <div class="mxchat-action-type-categories">
                        <button type="button" class="mxchat-category-button active" data-category="all"><?php esc_html_e('All', 'mxchat'); ?></button>
                        <?php
                        // Get unique categories from the defined groups
                        foreach ($groups as $group_label => $group_callbacks) :
                            $category_slug = sanitize_title($group_label);
                        ?>
                            <button type="button" class="mxchat-category-button" data-category="<?php echo esc_attr($category_slug); ?>"><?php echo esc_html($group_label); ?></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="mxchat-action-types-grid">
                        <?php
                        // Generate action cards from available callbacks
                        foreach ($groups as $group_label => $group_callbacks) :
                            $category_slug = sanitize_title($group_label);

                            foreach ($group_callbacks as $function => $data) :
                                $label = $data['label'];
                                $pro_only = $data['pro_only'];
                                $icon = isset($data['icon']) ? $data['icon'] : 'admin-generic';
                                $description = isset($data['description']) ? $data['description'] : '';
                                $is_addon = isset($data['addon']) && $data['addon'] !== false;
                                $addon_name = isset($data['addon_name']) ? $data['addon_name'] : '';
                                $is_installed = isset($data['installed']) ? $data['installed'] : true;

                                // Determine card status and styling
                                $card_class = 'mxchat-action-type-card';
                                $icon_class = 'mxchat-action-type-icon';
                                $status_badge = '';

                                if ($pro_only && !$this->is_activated) {
                                    // Pro feature but no Pro license
                                    $icon_class .= ' pro-feature';
                                    $status_badge = '<span class="mxchat-pro-badge">' . esc_html__('Pro', 'mxchat') . '</span>';
                                }

                                if ($is_addon && !$is_installed) {
                                    // Add-on not installed
                                    $card_class .= ' not-installed';
                                    $status_badge .= '<span class="mxchat-addon-badge">' . esc_html__('Add-on Required', 'mxchat') . '</span>';
                                }

                                // Default description if none provided
                                if (empty($description)) {
                                    $description = sprintf(
                                        esc_html__('Use the %s action in your chatbot', 'mxchat'),
                                        $label
                                    );
                                }
                                ?>
                                <div class="<?php echo esc_attr($card_class); ?>"
                                     data-category="<?php echo esc_attr($category_slug); ?>"
                                     data-value="<?php echo esc_attr($function); ?>"
                                     data-label="<?php echo esc_attr($label); ?>"
                                     data-pro="<?php echo $pro_only ? 'true' : 'false'; ?>"
                                     data-addon="<?php echo esc_attr($is_addon ? $data['addon'] : ''); ?>"
                                     data-installed="<?php echo $is_installed ? 'true' : 'false'; ?>">
                                    <div class="<?php echo esc_attr($icon_class); ?>">
                                        <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                                    </div>
                                    <div class="mxchat-action-type-info">
                                        <h4><?php echo esc_html($label); ?></h4>
                                        <p><?php echo esc_html($description); ?></p>
                                        <?php if (!empty($status_badge)) : ?>
                                            <?php echo $status_badge; ?>
                                        <?php endif; ?>

                                        <?php if ($is_addon && !$is_installed) : ?>
                                            <div class="mxchat-addon-info">
                                                <?php echo esc_html(sprintf(
                                                    __('Requires %s', 'mxchat'),
                                                    $addon_name
                                                )); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach;
                        endforeach; ?>
                    </div>
                </div>

                <div class="mxchat-modal-actions">
                    <button type="button" class="mxchat-button-secondary mxchat-modal-cancel">
                        <?php esc_html_e('Cancel', 'mxchat'); ?>
                    </button>
                </div>
            </div>

            <!-- Step 2: Action Configuration -->
            <div id="mxchat-action-step-2" class="mxchat-action-step">
                <div class="mxchat-step-indicator">
                    <div class="mxchat-step-number">2</div>
                    <div class="mxchat-step-title"><?php esc_html_e('Configure Action', 'mxchat'); ?></div>
                </div>

                <div class="mxchat-selected-action">
                    <button type="button" class="mxchat-back-button" id="mxchat-back-to-step-1">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php esc_html_e('Back to Action Types', 'mxchat'); ?>
                    </button>
                    <div class="mxchat-selected-action-info">
                        <div id="selected-action-icon" class="mxchat-action-type-icon">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </div>
                        <div class="mxchat-selected-action-details">
                            <h3 id="selected-action-title"><?php esc_html_e('Selected Action', 'mxchat'); ?></h3>
                            <p id="selected-action-description"><?php esc_html_e('Configure this action for your chatbot', 'mxchat'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mxchat-form-group">
                    <label for="intent_label">
                        <?php esc_html_e('Action Label (For your reference only)', 'mxchat'); ?>
                    </label>
                    <input name="intent_label" type="text" id="intent_label" required
                           class="mxchat-intent-input"
                           placeholder="<?php esc_attr_e('Example: Newsletter Signup', 'mxchat'); ?>">
                </div>

                <div class="mxchat-form-group">
                    <label for="phrases">
                        <?php esc_html_e('Trigger Phrases (comma-separated)', 'mxchat'); ?>
                    </label>
                    <textarea name="phrases" id="action_phrases" rows="5" required
                            class="mxchat-intent-textarea"
                            placeholder="<?php esc_attr_e('Example: sign me up, subscribe me, I want to join, add me to the newsletter', 'mxchat'); ?>"></textarea>
                </div>

                <div class="mxchat-form-group">
                    <label for="similarity_threshold">
                        <?php esc_html_e('Similarity Threshold', 'mxchat'); ?>
                        <span class="mxchat-threshold-value-display">85%</span>
                    </label>
                    <div class="mxchat-slider-group modal-slider">
                        <input type="range"
                               name="similarity_threshold"
                               id="similarity_threshold"
                               min="70"
                               max="95"
                               value="85"
                               class="mxchat-intent-slider"
                               oninput="document.querySelector('.mxchat-threshold-value-display').textContent = this.value + '%'">
                    </div>
                    <div class="mxchat-threshold-hint">
                        <?php esc_html_e('Lower values make the action trigger more easily. Higher values require more exact matches.', 'mxchat'); ?>
                    </div>
                </div>

                <div class="mxchat-modal-actions">
                    <button type="button" class="mxchat-button-secondary mxchat-modal-cancel">
                        <?php esc_html_e('Cancel', 'mxchat'); ?>
                    </button>
                    <button type="submit" class="mxchat-button-primary" id="mxchat-save-action-btn">
                        <?php esc_html_e('Save Action', 'mxchat'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="mxchat-action-loading" class="mxchat-action-loading" style="display: none;">
    <div class="mxchat-action-loading-spinner"></div>
    <div class="mxchat-action-loading-text">
        <?php esc_html_e('Saving action, please wait...', 'mxchat'); ?>
    </div>
</div>
    </div><!-- .mxchat-wrapper -->
    <?php
}

private function get_trimmed_phrases($phrases, $max_length = 100) {
    if (strlen($phrases) <= $max_length) {
        return $phrases;
    }

    $trimmed = substr($phrases, 0, $max_length);
    $last_comma = strrpos($trimmed, ',');

    if ($last_comma !== false) {
        $trimmed = substr($trimmed, 0, $last_comma);
    }

    return $trimmed . '...';
}

public function mxchat_add_enabled_column_to_intents() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_intents';

    // Check if the column already exists
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'enabled'");

    if (empty($columns)) {
        // Add the column with default value of 1 (enabled)
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1");
    }
}

/**
 * Handle embedding generation errors using existing admin notice system
 *
 * @param string $message Error message to display
 * @param bool $redirect Whether to redirect back to the actions page
 * @return void
 */
private function handle_embedding_error($message, $redirect = true) {
    // Store the error message in the existing transient
    set_transient('mxchat_admin_notice_error', $message, 60);

    if ($redirect) {
        // Redirect back to the actions page
        $redirect_url = add_query_arg(
            array(
                'page' => 'mxchat-actions'
            ),
            admin_url('admin.php')
        );
        wp_safe_redirect($redirect_url);
        exit;
    }
}


/**
 * Handle adding new intent - with improved error handling
 *
 * @return void
 */




/**
 * Enhanced get_available_callbacks function with form action exclusion
 *
 * @param bool $grouped Whether to return callbacks grouped by category
 * @param bool $include_all Whether to include all potential actions (even if add-on not installed)
 * @return array Callbacks data with icons, descriptions and availability status
 */
private function mxchat_get_available_callbacks($grouped = false, $include_all = true) {
    // Load WordPress plugin functions if needed
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Get active plugins
    $active_plugins = get_option('active_plugins', array());

    // Functions to exclude from the action selector only if Pro is activated
    // If user doesn't have Pro, show these so they can see what they're missing
    $excluded_when_pro_active_functions = array(
        'mxchat_handle_form_collection', // Forms add-on action
        'mxchat_sr_recommendation_flow'  // Smart Recommender flow actions
    );

    // Always excluded functions (regardless of Pro status)
    $always_excluded_functions = array();

    // Combine exclusion lists based on Pro activation status
    $excluded_functions = $always_excluded_functions;
    if ($this->is_activated) {
        // Only exclude add-on managed functions if Pro is active
        $excluded_functions = array_merge($excluded_functions, $excluded_when_pro_active_functions);
    }

    // Define add-on plugin files and their corresponding action functions
    $addon_plugins = array(
        'mxchat-woo/mxchat-woo.php' => array(
            'functions' => array(
                'mxchat_handle_product_recommendations',
                'mxchat_handle_order_history',
                'mxchat_show_product_card',
                'mxchat_add_to_cart',
                'mxchat_checkout_redirect'
            ),
            'name' => __('WooCommerce Add-on', 'mxchat'),
            'pro_required' => true
        ),
        'mxchat-perplexity/mxchat-perplexity.php' => array(
            'functions' => array('mxchat_perplexity_research'),
            'name' => __('Perplexity Add-on', 'mxchat'),
            'pro_required' => true
        ),
        'mxchat-forms/mxchat-forms.php' => array(
            'functions' => array('mxchat_handle_form_collection'),
            'name' => __('Forms Add-on', 'mxchat'),
            'pro_required' => true
        ),
        'mxchat-smart-recommender/mxchat-smart-recommender.php' => array(
            'functions' => array('mxchat_sr_recommendation_flow'),
            'name' => __('Smart Recommender Add-on', 'mxchat'),
            'pro_required' => true
        ),
        // Add other add-ons and their functions here
    );

    // Get the functions that are provided by active add-ons
    $addon_provided_functions = array();
    $addon_function_mapping = array(); // Maps functions to their add-on info

    // Check which add-ons are active
    foreach ($addon_plugins as $plugin_file => $addon_info) {
        $is_active = in_array($plugin_file, $active_plugins);

        // For each function in this addon
        foreach ($addon_info['functions'] as $function) {
            // Consider a function installed only if:
            // 1. The add-on is active AND
            // 2. Either it doesn't require Pro OR Pro is activated
            $is_installed = $is_active && (!$addon_info['pro_required'] || $this->is_activated);

            // If the add-on is installed, mark this function as provided by an add-on
            if ($is_installed) {
                $addon_provided_functions[] = $function;
            }

            // Store addon info for this function regardless of installation status
            $addon_function_mapping[$function] = array(
                'addon' => basename(dirname($plugin_file)),
                'addon_name' => $addon_info['name'],
                'pro_required' => $addon_info['pro_required'],
                'is_active' => $is_active,
                'is_installed' => $is_installed
            );
        }
    }

    // Core callbacks - always available in the base plugin
    $core_callbacks = array(
        'mxchat_handle_email_capture' => array(
            'label'       => __('Loops Email Capture', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Customer Engagement', 'mxchat'),
            'icon'        => 'email-alt',
            'description' => __('Collect visitor emails for your mailing list in Loops', 'mxchat'),
            'addon'       => false, // Not from an add-on
            'installed'   => true   // Always installed with base plugin
        ),
        'mxchat_handle_search_request' => array(
            'label'       => __('Brave Web Search', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Search Features', 'mxchat'),
            'icon'        => 'search',
            'description' => __('Let users search the web directly from the chat', 'mxchat'),
            'addon'       => false,
            'installed'   => true
        ),
        'mxchat_handle_image_search_request' => array(
            'label'       => __('Brave Image Search', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Search Features', 'mxchat'),
            'icon'        => 'format-image',
            'description' => __('Search and display images in the chat conversation', 'mxchat'),
            'addon'       => false,
            'installed'   => true
        ),
        // Pro core features - check is_activated property
        'mxchat_generate_image' => array(
            'label'       => __('Generate Image', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Other Features', 'mxchat'),
            'icon'        => 'art',
            'description' => __('Create images with DALL-E 3 from OpenAI (requires OpenAI API key)', 'mxchat'),
            'addon'       => false,
            'installed'   => true
        ),
        'mxchat_handle_pdf_discussion' => array(
            'label'       => __('Chat with PDF', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Other Features', 'mxchat'),
            'icon'        => 'media-document',
            'description' => __('Answer questions about uploaded PDF documents', 'mxchat'),
            'addon'       => false,
            'installed'   => true
        ),
        'mxchat_live_agent_handover' => array(
            'label'       => __('Slack Live Agent', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Customer Engagement', 'mxchat'),
            'icon'        => 'admin-users',
            'description' => __('Transfer conversation to a human support agent on Slack', 'mxchat'),
            'addon'       => false,
            'installed'   => true
        ),
        'mxchat_handle_switch_to_chatbot_intent' => array(
            'label'       => __('Back to Chatbot', 'mxchat'),
            'pro_only'    => false,
            'group'       => __('Customer Engagement', 'mxchat'),
            'icon'        => 'backup',
            'description' => __('Return from live agent mode to AI chatbot', 'mxchat'),
            'addon'       => false,
            'installed'   => true
        ),
    );

    // Add-on callbacks with placeholders - only include if the add-on is NOT active
    $addon_callbacks = array(
        // WooCommerce Add-on
        'mxchat_handle_product_recommendations' => array(
            'label'       => __('Product Recommendations', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('WooCommerce Features', 'mxchat'),
            'icon'        => 'cart',
            'description' => __('Suggest products based on customer preferences', 'mxchat'),
        ),
        'mxchat_handle_order_history' => array(
            'label'       => __('Order History', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('WooCommerce Features', 'mxchat'),
            'icon'        => 'clipboard',
            'description' => __('Allow customers to check their order status', 'mxchat'),
        ),
        'mxchat_show_product_card' => array(
            'label'       => __('Show Product Card', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('WooCommerce Features', 'mxchat'),
            'icon'        => 'products',
            'description' => __('Display product information in the chat', 'mxchat'),
        ),
        'mxchat_add_to_cart' => array(
            'label'       => __('Add to Cart', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('WooCommerce Features', 'mxchat'),
            'icon'        => 'plus-alt',
            'description' => __('Add products to cart directly from chat', 'mxchat'),
        ),
        'mxchat_checkout_redirect' => array(
            'label'       => __('Proceed to Checkout', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('WooCommerce Features', 'mxchat'),
            'icon'        => 'arrow-right-alt',
            'description' => __('Redirect customer to checkout page', 'mxchat'),
        ),

        // Perplexity Add-on
        'mxchat_perplexity_research' => array(
            'label'       => __('Perplexity Research', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('Search Features', 'mxchat'),
            'icon'        => 'book-alt',
            'description' => __('Allows the chatbot to search the web for accurate, up-to-date answers', 'mxchat'),
        ),

        // Forms Add-on (only shown when Pro is not activated)
        'mxchat_handle_form_collection' => array(
            'label'       => __('Form Collection', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('Form Features', 'mxchat'),
            'icon'        => 'feedback',
            'description' => __('Collect user information through custom forms in chat', 'mxchat'),
        ),

        // Smart Recommender Add-on (only shown when Pro is not activated)
        'mxchat_sr_recommendation_flow' => array(
            'label'       => __('Smart Recommender Flow', 'mxchat'),
            'pro_only'    => true,
            'group'       => __('Recommendation Features', 'mxchat'),
            'icon'        => 'cart',
            'description' => __('Create interactive conversation flows that collect user preferences and deliver personalized product or service recommendations', 'mxchat'),
        ),
    );

    // Enhance add-on callbacks with installation status and addon info
    foreach ($addon_callbacks as $function => $data) {
        if (isset($addon_function_mapping[$function])) {
            $addon_info = $addon_function_mapping[$function];

            $addon_callbacks[$function]['addon'] = $addon_info['addon'];
            $addon_callbacks[$function]['addon_name'] = $addon_info['addon_name'];
            $addon_callbacks[$function]['installed'] = $addon_info['is_installed'];

            // Set pro_only based on add-on configuration
            $addon_callbacks[$function]['pro_only'] = $addon_info['pro_required'];
        } else {
            $addon_callbacks[$function]['addon'] = 'unknown';
            $addon_callbacks[$function]['addon_name'] = __('Unknown Add-on', 'mxchat');
            $addon_callbacks[$function]['installed'] = false;
        }
    }

    // Initialize callbacks with core features
    $callbacks = $core_callbacks;

    // Get callbacks from active add-ons
    $active_addon_callbacks = apply_filters('mxchat_available_callbacks', array());

    // Add placeholder callbacks only for add-ons that aren't active
    if ($include_all) {
        foreach ($addon_callbacks as $function => $data) {
            // Skip placeholders for functions provided by active add-ons
            if (in_array($function, $addon_provided_functions)) {
                continue;
            }

            // Skip excluded functions
            if (in_array($function, $excluded_functions)) {
                continue;
            }

            // Add the placeholder
            $callbacks[$function] = $data;
        }
    }

    // Add callbacks from active add-ons (will override placeholders)
    foreach ($active_addon_callbacks as $function => $data) {
        // Skip excluded functions
        if (in_array($function, $excluded_functions)) {
            continue;
        }

        // Always include callbacks from add-ons
        $callbacks[$function] = $data;

        // Ensure they have the proper add-on info
        if (isset($addon_function_mapping[$function])) {
            $addon_info = $addon_function_mapping[$function];
            $callbacks[$function]['addon'] = $addon_info['addon'];
            $callbacks[$function]['addon_name'] = $addon_info['addon_name'];
            $callbacks[$function]['installed'] = $addon_info['is_installed'];
            $callbacks[$function]['pro_only'] = $addon_info['pro_required'];
        }
    }

    // Just before returning callbacks, sort them to prioritize free features
    if (!$grouped) {
        // Create temporary arrays for sorting
        $free_callbacks = array();
        $pro_callbacks = array();

        // Split callbacks into free and pro
        foreach ($callbacks as $key => $data) {
            if (isset($data['pro_only']) && $data['pro_only']) {
                $pro_callbacks[$key] = $data;
            } else {
                $free_callbacks[$key] = $data;
            }
        }

        // Merge with free callbacks first
        $callbacks = array_merge($free_callbacks, $pro_callbacks);
    }

    // Return grouped structure if requested
    if ($grouped) {
        $grouped_callbacks = array();
        foreach ($callbacks as $key => $data) {
            $group_label = isset($data['group']) ? $data['group'] : __('Other Features', 'mxchat');

            // Ensure we carry forward all the new fields in grouped mode
            $callback_data = array(
                'label'       => $data['label'],
                'pro_only'    => isset($data['pro_only']) ? $data['pro_only'] : false,
                'icon'        => isset($data['icon']) ? $data['icon'] : 'admin-generic',
                'description' => isset($data['description']) ? $data['description'] : __('Custom action for your chatbot', 'mxchat'),
                'addon'       => isset($data['addon']) ? $data['addon'] : false,
                'addon_name'  => isset($data['addon_name']) ? $data['addon_name'] : '',
                'installed'   => isset($data['installed']) ? $data['installed'] : true
            );

            $grouped_callbacks[$group_label][$key] = $callback_data;
        }

        // Sort within each group to prioritize free features
        foreach ($grouped_callbacks as $group => $items) {
            $free_items = array();
            $pro_items = array();

            foreach ($items as $key => $data) {
                if (isset($data['pro_only']) && $data['pro_only']) {
                    $pro_items[$key] = $data;
                } else {
                    $free_items[$key] = $data;
                }
            }

            $grouped_callbacks[$group] = array_merge($free_items, $pro_items);
        }

        return $grouped_callbacks;
    }

    return $callbacks;
}

public function mxchat_handle_delete_intent() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__('Unauthorized user', 'mxchat') );
    }

    check_admin_referer('mxchat_delete_intent_nonce');

    if (isset($_POST['intent_id'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_intents';
        $intent_id = intval($_POST['intent_id']);

        $wpdb->delete($table_name, ['id' => $intent_id], ['%d']);
    }

    wp_safe_redirect(admin_url('admin.php?page=mxchat-actions'));
    exit;
}


public function mxchat_page_init() {
    register_setting(
        'mxchat_option_group',
        'mxchat_options',
        array($this, 'mxchat_sanitize')
    );

    register_setting(
        'mxchat_option_group',
        'mxchat_similarity_threshold',
        array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                $value = absint($value);
                return min(max($value, 20), 95);
            },
            'default' => 80,
        )
    );

    // Chatbot Settings Section
    add_settings_section(
        'mxchat_chatbot_section',
        esc_html__('Chatbot Settings', 'mxchat'),
        null,
        'mxchat-chatbot'
    );

    // Similarity Threshold Slider
    add_settings_field(
        'similarity_threshold', // Field ID
        esc_html__('Similarity Threshold', 'mxchat'), // Field title
        array($this, 'mxchat_similarity_threshold_callback'), // Callback function
        'mxchat-chatbot', // Page
        'mxchat_chatbot_section' // Section
    );

    add_settings_field(
        'append_to_body',
        esc_html__('Auto-Display Chatbot', 'mxchat'),
        array($this, 'mxchat_append_to_body_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );
    
    add_settings_field(
        'contextual_awareness_toggle',
        esc_html__('Contextual Awareness', 'mxchat'),
        array($this, 'mxchat_contextual_awareness_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

        add_settings_field(
            'api_key',
            esc_html__('OpenAI API Key', 'mxchat'),
            array($this, 'api_key_callback'),
            'mxchat-chatbot',
            'mxchat_chatbot_section',
            array(
                'class' => 'mxchat-setting-row',
                'data-provider' => 'openai'
            )
        );

        add_settings_field(
            'xai_api_key',
            esc_html__('X.AI API Key', 'mxchat'),
            array($this, 'xai_api_key_callback'),
            'mxchat-chatbot',
            'mxchat_chatbot_section',
            array(
                'class' => 'mxchat-setting-row',
                'data-provider' => 'xai'
            )
        );

        add_settings_field(
            'claude_api_key',
            esc_html__('Claude API Key', 'mxchat'),
            array($this, 'claude_api_key_callback'),
            'mxchat-chatbot',
            'mxchat_chatbot_section',
            array(
                'class' => 'mxchat-setting-row',
                'data-provider' => 'claude'
            )
        );

        add_settings_field(
            'deepseek_api_key',
            esc_html__('DeepSeek API Key', 'mxchat'),
            array($this, 'deepseek_api_key_callback'),
            'mxchat-chatbot',
            'mxchat_chatbot_section',
            array(
                'class' => 'mxchat-setting-row',
                'data-provider' => 'deepseek'
            )
        );

        add_settings_field(
            'gemini_api_key',
            esc_html__('Google Gemini API Key', 'mxchat'),
            array($this, 'gemini_api_key_callback'),
            'mxchat-chatbot',
            'mxchat_chatbot_section',
            array(
                'class' => 'mxchat-setting-row',
                'data-provider' => 'gemini'
            )
        );

        add_settings_field(
            'voyage_api_key',
            esc_html__('Voyage AI API Key', 'mxchat'),
            array($this, 'voyage_api_key_callback'),
            'mxchat-chatbot',
            'mxchat_chatbot_section',
            array(
                'class' => 'mxchat-setting-row',
                'data-provider' => 'voyage'
            )
        );
        
    add_settings_field(
        'enable_streaming_toggle',
        esc_html__('Enable Streaming', 'mxchat'),
        array($this, 'enable_streaming_toggle_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section', // Same section as your working toggle
        array(
            'class' => 'mxchat-setting-row streaming-setting',
            'style' => 'display: none;' // Hidden by default, shown when OpenAI/Claude selected
        )
    );


    add_settings_field(
        'model',
        esc_html__('Chat Model', 'mxchat'),
        array($this, 'mxchat_model_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );
    
    add_settings_field(
        'embedding_model',
        esc_html__('Embedding Model', 'mxchat'),
        array($this, 'embedding_model_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'system_prompt_instructions',
        esc_html__('AI Instructions (Behavior)', 'mxchat'),
        array($this, 'system_prompt_instructions_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );


    add_settings_field(
        'top_bar_title',
        esc_html__('Top Bar Title', 'mxchat'),
        array($this, 'mxchat_top_bar_title_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'ai_agent_text',
        esc_html__('AI Agent Text', 'mxchat'),
        array($this, 'mxchat_ai_agent_text_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'enable_email_block',
        esc_html__('Require Email To Chat', 'mxchat'),
        array($this, 'enable_email_block_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'email_blocker_header_content',
        esc_html__('Require Email Chat Content', 'mxchat'),
        array($this, 'email_blocker_header_content_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'email_blocker_button_text',
        esc_html__('Require Email Chat Button Text', 'mxchat'),
        [$this, 'email_blocker_button_text_callback'],
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'intro_message',
        esc_html__('Introductory Message', 'mxchat'),
        array($this, 'mxchat_intro_message_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'input_copy',
        esc_html__('Input Copy', 'mxchat'),
        array($this, 'mxchat_input_copy_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'pre_chat_message',
        esc_html__('Chat Teaser Pop-up', 'mxchat'),
        array($this, 'mxchat_pre_chat_message_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'privacy_toggle',
        esc_html__('Toggle Privacy Notice', 'mxchat'),
        array($this, 'mxchat_privacy_toggle_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'complianz_toggle',
        esc_html__('Enable Complianz', 'mxchat'),
        array($this, 'mxchat_complianz_toggle_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'link_target_toggle',
        esc_html__('Open Links in a New Tab', 'mxchat'),
        array($this, 'mxchat_link_target_toggle_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'chat_persistence_toggle',
        esc_html__('Enable Chat Persistence', 'mxchat'),
        array($this, 'mxchat_chat_persistence_toggle_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'popular_question_1',
        esc_html__('Quick Question 1', 'mxchat'),
        array($this, 'mxchat_popular_question_1_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'popular_question_2',
        esc_html__('Quick Question 2', 'mxchat'),
        array($this, 'mxchat_popular_question_2_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'popular_question_3',
        esc_html__('Quick Question 3', 'mxchat'),
        array($this, 'mxchat_popular_question_3_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    add_settings_field(
        'additional_popular_questions',
        esc_html__('Additional Quick Questions', 'mxchat'),
        array($this, 'mxchat_additional_popular_questions_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );


    add_settings_field(
        'rate_limits',
        __('Rate Limits Settings', 'mxchat'),
        array($this, 'mxchat_rate_limits_callback'),
        'mxchat-chatbot',
        'mxchat_chatbot_section'
    );

    // Loops Settings Section
    add_settings_section(
        'mxchat_loops_section',
        esc_html__('Loops Settings', 'mxchat'),
        null,
        'mxchat-embed'
    );

    // Loops Settings Fields
    add_settings_field(
        'loops_api_key',
        esc_html__('Loops API Key', 'mxchat'),
        array($this, 'mxchat_loops_api_key_callback'),
        'mxchat-embed',
        'mxchat_loops_section'
    );

    add_settings_field(
        'loops_mailing_list',
        esc_html__('Loops Mailing List', 'mxchat'),
        array($this, 'mxchat_loops_mailing_list_callback'),
        'mxchat-embed',
        'mxchat_loops_section'
    );

    add_settings_field(
        'triggered_phrase_response',
        esc_html__('Triggered Phrase Response', 'mxchat'),
        array($this, 'mxchat_triggered_phrase_response_callback'),
        'mxchat-embed',
        'mxchat_loops_section'
    );

    add_settings_field(
        'email_capture_response',
        esc_html__('Email Capture Response', 'mxchat'),
        array($this, 'mxchat_email_capture_response_callback'),
        'mxchat-embed',
        'mxchat_loops_section'
    );

    // Brave Search Settings Fields
    add_settings_section(
        'mxchat_brave_section',
        __('Brave Search Settings', 'mxchat'),
        array($this, 'mxchat_brave_section_callback'),
        'mxchat-embed'
    );

    add_settings_field(
        'brave_api_key',
        __('Brave API Key', 'mxchat'),
        array($this, 'mxchat_brave_api_key_callback'),
        'mxchat-embed',
        'mxchat_brave_section'
    );

    add_settings_field(
        'brave_image_count',
        __('Number of Images to Return', 'mxchat'),
        array($this, 'mxchat_brave_image_count_callback'),
        'mxchat-embed',
        'mxchat_brave_section'
    );

    add_settings_field(
        'brave_safe_search',
        __('Safe Search', 'mxchat'),
        array($this, 'mxchat_brave_safe_search_callback'),
        'mxchat-embed',
        'mxchat_brave_section'
    );

    add_settings_field(
        'brave_news_count',
        __('Number of News Articles', 'mxchat'),
        array($this, 'mxchat_brave_news_count_callback'),
        'mxchat-embed',
        'mxchat_brave_section'
    );

    add_settings_field(
        'brave_country',
        __('Country', 'mxchat'),
        array($this, 'mxchat_brave_country_callback'),
        'mxchat-embed',
        'mxchat_brave_section'
    );

    add_settings_field(
        'brave_language',
        __('Language', 'mxchat'),
        array($this, 'mxchat_brave_language_callback'),
        'mxchat-embed',
        'mxchat_brave_section'
    );

    // Chat with PDF Intent Settings Fields
    add_settings_section(
        'mxchat_pdf_intent_section',
        __('Toolbar Settings & Intents', 'mxchat'),
        array($this, 'mxchat_pdf_intent_section_callback'),
        'mxchat-embed'
    );

    add_settings_field(
        'chat_toolbar_toggle',
        __('Show Chat Toolbar', 'mxchat'),
        array($this, 'mxchat_chat_toolbar_toggle_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    // PDF Upload Button Toggle
    add_settings_field(
        'show_pdf_upload_button',
        __('Show PDF Upload Button', 'mxchat'),
        array($this, 'mxchat_show_pdf_upload_button_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    // Word Upload Button Toggle
    add_settings_field(
        'show_word_upload_button',
        __('Show Word Upload Button', 'mxchat'),
        array($this, 'mxchat_show_word_upload_button_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    add_settings_field(
        'pdf_intent_trigger_text',
        __('Intent Trigger Text', 'mxchat'),
        array($this, 'mxchat_pdf_intent_trigger_text_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    add_settings_field(
        'pdf_intent_success_text',
        __('Success Text', 'mxchat'),
        array($this, 'mxchat_pdf_intent_success_text_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    add_settings_field(
        'pdf_intent_error_text',
        __('Error Text', 'mxchat'),
        array($this, 'mxchat_pdf_intent_error_text_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    // Add PDF Maximum Pages Field
    add_settings_field(
        'pdf_max_pages',
        __('Maximum Document Pages', 'mxchat'),
        array($this, 'mxchat_pdf_max_pages_callback'),
        'mxchat-embed',
        'mxchat_pdf_intent_section'
    );

    // Live Agent Settings Fields
    add_settings_section(
        'mxchat_live_agent_section',
        __('Live Agent Settings', 'mxchat'),
        array($this, 'mxchat_live_agent_section_callback'),
        'mxchat-embed'
    );

    // Live Agent Status Fields (add at top of live agent settings)
    add_settings_field(
        'live_agent_status',
        __('Live Agent Status', 'mxchat'),
        array($this, 'mxchat_live_agent_status_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );

    add_settings_field(
        'live_agent_notification_message',
        __('Notification Message', 'mxchat'),
        array($this, 'mxchat_live_agent_notification_message_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );

    add_settings_field(
        'live_agent_away_message',
        __('Away Message', 'mxchat'),
        array($this, 'mxchat_live_agent_away_message_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );
    
    add_settings_field(
        'live_agent_user_ids',
        __('Slack Agent User IDs', 'mxchat'),
        array($this, 'mxchat_live_agent_user_ids_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );

    add_settings_field(
        'live_agent_webhook_url',
        __('Slack Webhook URL', 'mxchat'),
        array($this, 'mxchat_live_agent_webhook_url_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );

    add_settings_field(
        'live_agent_secret_key',
        __('Slack Secret Key', 'mxchat'),
        array($this, 'mxchat_live_agent_secret_key_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );

    // Live Agent Integration Fields
    add_settings_field(
        'live_agent_bot_token',
        __('Slack Bot OAuth Token', 'mxchat'),
        array($this, 'mxchat_live_agent_bot_token_callback'),
        'mxchat-embed',
        'mxchat_live_agent_section'
    );




    // General Settings Section
    add_settings_section(
        'mxchat_general_section',
        esc_html__('YouTube Tutorials', 'mxchat'),
        null,
        'mxchat-general'
    );
}

public function mxchat_prompts_page_init() {
        register_setting(
            'mxchat_prompts_options',
            'mxchat_prompts_options',
            array(
                'type' => 'array',
                'description' => __('MXChat Knowledge Base Settings', 'mxchat'),
                'default' => array(
                    'mxchat_auto_sync_posts' => 0,
                    'mxchat_auto_sync_pages' => 0,
                    'mxchat_use_pinecone' => 0,
                    'mxchat_pinecone_api_key' => '',
                    'mxchat_pinecone_environment' => '',
                    'mxchat_pinecone_index' => '',
                    'mxchat_pinecone_host' => '',
                ),
                'sanitize_callback' => array($this, 'sanitize_prompts_options'),
            )
        );

    add_action('admin_notices', array($this, 'sync_settings_notice'));
}

public function mxchat_transcripts_page_init() {
    register_setting(
        'mxchat_transcripts_options',
        'mxchat_transcripts_options',
        array(
            'type' => 'array',
            'description' => __('MXChat Transcripts Notification Settings', 'mxchat'),
            'default' => array(
                'mxchat_enable_notifications' => 0,
                'mxchat_notification_email' => get_option('admin_email'),
            ),
            'sanitize_callback' => array($this, 'sanitize_transcripts_options'),
        )
    );

    add_settings_section(
        'mxchat_transcripts_notification_section',
        esc_html__('Chat Notification Settings', 'mxchat'),
        array($this, 'mxchat_transcripts_notification_section_callback'),
        'mxchat-transcripts'
    );

    add_settings_field(
        'mxchat_enable_notifications',
        esc_html__('Enable Chat Notifications', 'mxchat'),
        array($this, 'mxchat_enable_notifications_callback'),
        'mxchat-transcripts',
        'mxchat_transcripts_notification_section'
    );

    add_settings_field(
        'mxchat_notification_email',
        esc_html__('Notification Email Address', 'mxchat'),
        array($this, 'mxchat_notification_email_callback'),
        'mxchat-transcripts',
        'mxchat_transcripts_notification_section'
    );
}


/**
 * Sanitize all prompts options
 *
 * @param array $input The unsanitized options array
 * @return array The sanitized options array
 */
public function sanitize_prompts_options($input) {
    // Log the incoming input.
    //error_log('Sanitizing inputs: ' . print_r($input, true));

    $sanitized = array();

    // Boolean options
    $sanitized['mxchat_auto_sync_posts'] = isset($input['mxchat_auto_sync_posts']) ? 1 : 0;
    $sanitized['mxchat_auto_sync_pages'] = isset($input['mxchat_auto_sync_pages']) ? 1 : 0;
$sanitized['mxchat_use_pinecone'] = !empty($input['mxchat_use_pinecone']) ? 1 : 0;

    // API Key: if less than 32 characters, flag as invalid.
    $api_key = sanitize_text_field($input['mxchat_pinecone_api_key'] ?? '');
    if (!empty($api_key) && strlen($api_key) < 32) {
        add_settings_error(
            'mxchat_prompts_options',
            'invalid_api_key',
            __('The Pinecone API key appears to be invalid. Please check your API key.', 'mxchat')
        );
        $existing_options = get_option('mxchat_prompts_options', array());
        $sanitized['mxchat_pinecone_api_key'] = $existing_options['mxchat_pinecone_api_key'] ?? '';
    } else {
        $sanitized['mxchat_pinecone_api_key'] = $api_key;
    }

    // Environment and Index Name
    $sanitized['mxchat_pinecone_environment'] = sanitize_text_field($input['mxchat_pinecone_environment'] ?? '');
    $sanitized['mxchat_pinecone_index'] = sanitize_text_field($input['mxchat_pinecone_index'] ?? '');

    // Host: Remove protocol and validate format.
    $host = sanitize_text_field($input['mxchat_pinecone_host'] ?? '');
    $host = preg_replace('#^https?://#', '', $host);
    //error_log('Host after removing protocol: ' . $host);
    if (!empty($host)) {
        if (!preg_match('/^[\w-]+\.svc\.[\w-]+\.pinecone\.io$/', $host)) {
            add_settings_error(
                'mxchat_prompts_options',
                'invalid_host',
                __('The Pinecone host appears to be invalid. It should look like "mxchat-vectors-zrmsquq.svc.aped-4627-b74a.pinecone.io"', 'mxchat')
            );
            $existing_options = get_option('mxchat_prompts_options', array());
            $sanitized['mxchat_pinecone_host'] = $existing_options['mxchat_pinecone_host'] ?? '';
        } else {
            $sanitized['mxchat_pinecone_host'] = $host;
        }
    } else {
        $sanitized['mxchat_pinecone_host'] = '';
    }

    //error_log('Final sanitized array: ' . print_r($sanitized, true));

    return $sanitized;
}


public function sync_settings_notice() {
    // Only show notice on our plugin page
    if (!isset($_GET['page']) || $_GET['page'] !== 'mxchat-prompts') {
        return;
    }

    // Check if settings were updated
    if (isset($_GET['settings-updated'])) {

        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Sync settings updated successfully.', 'mxchat'); ?></p>
        </div>
        <?php

    }
}
// Add this sanitization function to your class
public function sanitize_sync_setting($input) {
    return (bool)$input ? __('1', 'mxchat') : __('', 'mxchat');
}

public function mxchat_rate_limits_callback() {
    $all_options = get_option('mxchat_options', []);

    // Define available rate limits
    $rate_limits = array('1', '3', '5', '10', '15', '20', '50', '100', 'unlimited');

    // Define available timeframes
    $timeframes = array(
        'hourly' => __('Per Hour', 'mxchat'),
        'daily' => __('Per Day', 'mxchat'),
        'weekly' => __('Per Week', 'mxchat'),
        'monthly' => __('Per Month', 'mxchat')
    );

    // Get all roles plus a "logged_out" pseudo-role
    $roles = wp_roles()->get_names();
    $roles['logged_out'] = __('Logged Out Users', 'mxchat');

    // Start the wrapper
    echo '<div class="pro-feature-wrapper active">';
    echo '<div class="mxchat-rate-limits-container">';

    echo '<p class="description" style="margin-bottom: 20px;">' .
         esc_html__('Set message limits for each user role and customize the experience when users reach those limits. You can use {limit}, {timeframe}, {count}, and {remaining} as placeholders.', 'mxchat') .
         '</p>';

    // Add markdown link documentation
    echo '<div class="notice notice-info inline" style="margin-bottom: 20px; padding: 10px;">';
    echo '<p><strong>' . esc_html__('Markdown Links Supported:', 'mxchat') . '</strong></p>';
    echo '<p>' . esc_html__('You can include clickable links in your custom messages using markdown syntax:', 'mxchat') . '</p>';
    echo '<ul style="margin-left: 20px;">';
    echo '<li><code>[Link text](https://example.com)</code> - Creates a clickable link</li>';
    echo '<li><code>[Visit our pricing](https://example.com/pricing)</code> - Link with custom text</li>';
    echo '<li><code>Plain URLs like https://example.com will also become clickable</code></li>';
    echo '</ul>';
    echo '</div>';

    // Output the controls for each role
    foreach ($roles as $role_id => $role_name) {
        // Get saved options or defaults
        $default_limit = ($role_id === 'logged_out') ? '10' : '100';
        $default_timeframe = 'daily';
        $default_message = __('Rate limit exceeded. Please try again later.', 'mxchat');

        $selected_limit = isset($all_options['rate_limits'][$role_id]['limit'])
            ? $all_options['rate_limits'][$role_id]['limit']
            : $default_limit;

        $selected_timeframe = isset($all_options['rate_limits'][$role_id]['timeframe'])
            ? $all_options['rate_limits'][$role_id]['timeframe']
            : $default_timeframe;

        $custom_message = isset($all_options['rate_limits'][$role_id]['message'])
            ? $all_options['rate_limits'][$role_id]['message']
            : $default_message;

        // Output the row
        echo '<div class="mxchat-rate-limit-row mxchat-autosave-section">';

        // Role label
        echo '<div class="mxchat-rate-limit-role">' . esc_html($role_name) . '</div>';

        // Controls section
        echo '<div class="mxchat-rate-limit-controls-wrapper">';

        // Rate limit and timeframe controls
        echo '<div class="mxchat-rate-limit-controls">';

        // Limit dropdown
        echo '<div>';
        echo '<label for="rate_limits_' . esc_attr($role_id) . '_limit">' . esc_html__('Limit:', 'mxchat') . '</label>';
        echo '<select
                id="rate_limits_' . esc_attr($role_id) . '_limit"
                name="mxchat_options[rate_limits][' . esc_attr($role_id) . '][limit]"
                class="mxchat-autosave-field">';
        foreach ($rate_limits as $limit) {
            echo '<option value="' . esc_attr($limit) . '" ' . selected($selected_limit, $limit, false) . '>' . esc_html($limit) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        // Timeframe dropdown
        echo '<div>';
        echo '<label for="rate_limits_' . esc_attr($role_id) . '_timeframe">' . esc_html__('Timeframe:', 'mxchat') . '</label>';
        echo '<select
                id="rate_limits_' . esc_attr($role_id) . '_timeframe"
                name="mxchat_options[rate_limits][' . esc_attr($role_id) . '][timeframe]"
                class="mxchat-autosave-field">';
        foreach ($timeframes as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($selected_timeframe, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '</div>'; // End controls

        // Custom message textarea
        echo '<div class="mxchat-rate-limit-message">';
        echo '<label for="rate_limits_' . esc_attr($role_id) . '_message">' . esc_html__('Custom Message:', 'mxchat') . '</label>';
        echo '<textarea
                id="rate_limits_' . esc_attr($role_id) . '_message"
                name="mxchat_options[rate_limits][' . esc_attr($role_id) . '][message]"
                class="mxchat-autosave-field"
                placeholder="' . esc_attr__('Enter custom message when rate limit is exceeded', 'mxchat') . '">' .
                esc_textarea($custom_message) .
              '</textarea>';
        echo '<p class="description">' . 
             esc_html__('Example: Rate limit reached! [Visit our pricing page](https://example.com/pricing) to upgrade.', 'mxchat') . 
             '</p>';
        echo '</div>'; // End message

        echo '</div>'; // End controls wrapper

        echo '</div>'; // End row
    }

    echo '</div>'; // End container

    echo '</div>'; // End pro-feature-wrapper
}


private function mxchat_add_option_field($id, $title, $callback = '') {
        add_settings_field(
            $id,
            __($title, 'mxchat'),
            $callback ? array($this, $callback) : array($this, $id . '_callback'),
            'mxchat-max',
            'mxchat_setting_section_id',
            $id === 'model' ? ['label_for' => 'model'] : []
        );
    }

// OpenAI API Key
public function api_key_callback() {
    $apiKey = isset($this->options['api_key']) ? esc_attr($this->options['api_key']) : '';

    echo '<div class="api-key-wrapper" data-provider="openai">';
    echo '<input type="password" id="api_key" name="api_key" value="' . $apiKey . '" class="regular-text" autocomplete="off" />';
    echo '<button type="button" id="toggleApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description api-key-notice">' . esc_html__('Required for your selected chat model. Important: You must add credits before use.', 'mxchat') . '</p>';
    echo '</div>';
}

// X.AI API Key
public function xai_api_key_callback() {
    $xaiApiKey = isset($this->options['xai_api_key']) ? esc_attr($this->options['xai_api_key']) : '';

    echo '<div class="api-key-wrapper" data-provider="xai">';
    echo '<input type="password" id="xai_api_key" name="xai_api_key" value="' . $xaiApiKey . '" class="regular-text" autocomplete="off" />';
    echo '<button type="button" id="toggleXaiApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description api-key-notice">' . esc_html__('Required for your selected chat model. Important: You must add credits before use.', 'mxchat') . '</p>';
    echo '</div>';
}
// Claude API Key
public function claude_api_key_callback() {
    $claudeApiKey = isset($this->options['claude_api_key']) ? esc_attr($this->options['claude_api_key']) : '';

    echo '<div class="api-key-wrapper" data-provider="claude">';
    echo '<input type="password" id="claude_api_key" name="claude_api_key" value="' . $claudeApiKey . '" class="regular-text" autocomplete="off" />';
    echo '<button type="button" id="toggleClaudeApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description api-key-notice">' . esc_html__('Required for your selected chat model. Important: You must add credits before use.', 'mxchat') . '</p>';
    echo '</div>';
}

// DeepSeek API Key
public function deepseek_api_key_callback() {
    $apiKey = isset($this->options['deepseek_api_key']) ? esc_attr($this->options['deepseek_api_key']) : '';

    echo '<div class="api-key-wrapper" data-provider="deepseek">';
    echo '<input type="password" id="deepseek_api_key" name="deepseek_api_key" value="' . $apiKey . '" class="regular-text" autocomplete="off" />';
    echo '<button type="button" id="toggleDeepSeekApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description api-key-notice">' . esc_html__('Required for your selected chat model. Important: You must add credits before use.', 'mxchat') . '</p>';
    echo '</div>';
}

// Gemini API Key
public function gemini_api_key_callback() {
    $geminiApiKey = isset($this->options['gemini_api_key']) ? esc_attr($this->options['gemini_api_key']) : '';

    echo '<div class="api-key-wrapper" data-provider="gemini">';
    echo '<input type="password" id="gemini_api_key" name="gemini_api_key" value="' . $geminiApiKey . '" class="regular-text" autocomplete="off" />';
    echo '<button type="button" id="toggleGeminiApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description api-key-notice">' . esc_html__('Required for Google Gemini models. Get your API key from Google AI Studio.', 'mxchat') . '</p>';
    echo '</div>';
}

// Voyage API Key
public function voyage_api_key_callback() {
    $apiKey = isset($this->options['voyage_api_key']) ? esc_attr($this->options['voyage_api_key']) : '';

    echo '<div class="api-key-wrapper" data-provider="voyage">';
    echo '<input type="password" id="voyage_api_key" name="voyage_api_key" value="' . $apiKey . '" class="regular-text" autocomplete="off" />';
    echo '<button type="button" id="toggleVoyageAPIKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description api-key-notice">' . esc_html__('Required for your selected embedding model. Important: You must add credits before use.', 'mxchat') . '</p>';
    echo '</div>';
}

public function mxchat_loops_api_key_callback() {
    $loops_api_key = isset($this->options['loops_api_key']) ? esc_attr($this->options['loops_api_key']) : '';

    // Hidden fields to "trap" autofill
    echo '<input type="text" style="display:none" autocomplete="username" />';
    echo '<input type="password" style="display:none" autocomplete="current-password" />';

    echo '<div class="api-key-wrapper" data-provider="loops">';
    echo sprintf(
        '<input type="password" id="loops_api_key" name="loops_api_key" value="%s" class="regular-text" autocomplete="new-password" />',
        $loops_api_key
    );
    echo '<button type="button" id="toggleLoopsApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '</div>';
    echo '<p class="description">' . esc_html__('Enter your Loops API Key here. Once entered, refreshed page to load list (See FAQ for details)', 'mxchat') . '</p>';
}
public function mxchat_loops_mailing_list_callback() {
    // Add error handling and type checking
    $loops_api_key = '';
    $selected_list = '';

    // Safely get the API key
    if (isset($this->options['loops_api_key']) && is_string($this->options['loops_api_key'])) {
        $loops_api_key = $this->options['loops_api_key'];
    }

    // Safely get the selected list
    if (isset($this->options['loops_mailing_list']) && is_string($this->options['loops_mailing_list'])) {
        $selected_list = $this->options['loops_mailing_list'];
    }

    if (!empty($loops_api_key)) {
        $lists = $this->mxchat_fetch_loops_mailing_lists($loops_api_key);
        if (is_array($lists) && !empty($lists)) {
            echo '<select id="loops_mailing_list" name="loops_mailing_list">';

            // Add a default "Select a list" option
            echo '<option value="" ' . selected($selected_list, '', false) . '>' . esc_html__('Select a list', 'mxchat') . '</option>';

            foreach ($lists as $list) {
                if (is_array($list) && isset($list['id']) && isset($list['name'])) {
                    echo sprintf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($list['id']),
                        selected($selected_list, $list['id'], false),
                        esc_html($list['name'])
                    );
                }
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__('Please select a mailing list to use with Loops.', 'mxchat') . '</p>';
        } else {
            echo '<p class="description">' . esc_html__('No lists found. Please verify your API Key.', 'mxchat') . '</p>';
        }
    } else {
        echo '<p class="description">' . esc_html__('Enter a valid Loops API Key to load mailing lists.', 'mxchat') . '</p>';
    }
}
public function mxchat_triggered_phrase_response_callback() {
    $default_response = __('Would you like to join our mailing list? Please provide your email below.', 'mxchat');
    $triggered_response = isset($this->options['triggered_phrase_response'])
        ? $this->options['triggered_phrase_response']
        : $default_response;

    echo sprintf(
        '<textarea id="triggered_phrase_response" name="triggered_phrase_response" rows="3" cols="50">%s</textarea>',
        esc_textarea($triggered_response)
    );
    echo '<p class="description">' . esc_html__('Enter the chatbot response when a trigger keyword is detected, prompting the user to share their email.', 'mxchat') . '</p>';
}

public function mxchat_email_capture_response_callback() {
    $default_response = __('Thank you for providing your email! You\'ve been added to our list.', 'mxchat');
    $email_capture_response = isset($this->options['email_capture_response'])
        ? $this->options['email_capture_response']
        : $default_response;

    echo sprintf(
        '<textarea id="email_capture_response" name="email_capture_response" rows="3" cols="50">%s</textarea>',
        esc_textarea($email_capture_response)
    );
    echo '<p class="description">' . esc_html__('Enter the message to send when a user provides their email.', 'mxchat') . '</p>';
}

public function mxchat_pre_chat_message_callback() {
    // Load the entire 'mxchat_options' array
    $all_options = get_option('mxchat_options', []);

    // Retrieve the saved message or use the default value
    $default_message = __('Hey there! Ask me anything!', 'mxchat');
    $pre_chat_message = isset($all_options['pre_chat_message']) ? $all_options['pre_chat_message'] : $default_message;

    // Output the textarea
    printf(
        '<textarea id="pre_chat_message" name="pre_chat_message" rows="5" cols="50">%s</textarea>',
        esc_textarea($pre_chat_message)
    );
    echo '<p class="description">' . esc_html__('Set the message displayed to users before they start a chat. Use this to provide a friendly greeting or instructions.', 'mxchat') . '</p>';
}

// Callback for AI Instructions textarea
public function system_prompt_instructions_callback() {
    // Retrieve the current value of the system prompt instructions
    $instructions = isset($this->options['system_prompt_instructions']) ? esc_textarea($this->options['system_prompt_instructions']) : '';
    // Render the textarea field
    printf(
        '<textarea id="system_prompt_instructions" name="system_prompt_instructions" rows="5" cols="50">%s</textarea>',
        $instructions
    );
    // Provide a helpful description with sample instructions button
    echo '<p class="description">' . esc_html__('Provide system-level instructions for the AI to guide its behavior. Be clear and concise for better results.', 'mxchat') . '</p>';
    echo '<div class="mxchat-instructions-container">';
    echo '<button type="button" class="mxchat-instructions-btn" id="mxchatViewSampleBtn">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
    echo '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>';
    echo '<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>';
    echo '</svg>';
    echo esc_html__('View Sample Instructions', 'mxchat');
    echo '</button>';
    echo '</div>';

    // Add modal to WordPress admin footer instead of inline
    add_action('admin_footer', array($this, 'render_sample_instructions_modal'));
}

// New method to render modal in admin footer
public function render_sample_instructions_modal() {
    static $modal_rendered = false;
    if ($modal_rendered) return; // Prevent duplicate modals
    $modal_rendered = true;

    echo '<div class="mxchat-instructions-modal-overlay" id="mxchatSampleModal">';
    echo '<div class="mxchat-instructions-modal-content">';
    echo '<div class="mxchat-instructions-modal-header">';
    echo '<h3 class="mxchat-instructions-modal-title">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
    echo '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>';
    echo '<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>';
    echo '</svg>';
    echo esc_html__('Sample AI Instructions', 'mxchat');
    echo '</h3>';
    echo '<button type="button" class="mxchat-instructions-modal-close" id="mxchatModalClose">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
    echo '<line x1="18" y1="6" x2="6" y2="18"/>';
    echo '<line x1="6" y1="6" x2="18" y2="18"/>';
    echo '</svg>';
    echo '</button>';
    echo '</div>';
    echo '<div class="mxchat-instructions-modal-body">';
    echo '<div class="mxchat-instructions-content">';
    echo esc_html('You are an AI Chatbot assistant for this website. Your main goal is to assist visitors with questions and provide helpful information. Here are your key guidelines:

# Response Style - CRITICALLY IMPORTANT
- MAXIMUM LENGTH: 1-3 short sentences per response
- Ultra-concise: Get straight to the answer with no filler
- No introductions like "Sure!" or "I\'d be happy to help"
- No phrases like "based on my knowledge" or "according to information"
- No explanatory text before giving the answer
- No summaries or repetition
- Hyperlink all URLs
- Respond in user\'s language
- Minor chit chat or conversation is okay, but try to keep it focused on [insert topic]

# Knowledge Base Requirements - PREVENT HALLUCINATIONS
- ONLY answer using information explicitly provided in OFFICIAL KNOWLEDGE DATABASE CONTENT sections marked with ===== delimiters
- If required information is NOT in the knowledge database: "I don\'t have enough information in my knowledge base to answer that question accurately."
- NEVER invent or hallucinate URLs, links, product specs, procedures, dates, statistics, names, contacts, or company information
- When knowledge base information is unclear or contradictory, acknowledge the limitation rather than guessing
- Better to admit insufficient information than provide inaccurate answers');
    echo '</div>';
    echo '<button type="button" class="mxchat-instructions-copy-btn" id="mxchatCopyBtn">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
    echo '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>';
    echo '<path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>';
    echo '</svg>';
    echo esc_html__('Copy Instructions', 'mxchat');
    echo '</button>';
    echo '</div>';
    echo '<div class="mxchat-instructions-modal-footer">';
    echo '<button type="button" class="mxchat-instructions-btn-secondary" id="mxchatCloseBtn">' . esc_html__('Close', 'mxchat') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}


public function mxchat_model_callback() {
    // Define available models grouped by provider
    $models = array(
        esc_html__('Google Gemini Models', 'mxchat') => array(
            'gemini-2.0-flash' => esc_html__('Gemini 2.0 Flash (Next-Gen Features)', 'mxchat'),
            'gemini-2.0-flash-lite' => esc_html__('Gemini 2.0 Flash-Lite (Cost-Efficient)', 'mxchat'),
            'gemini-1.5-pro' => esc_html__('Gemini 1.5 Pro (Complex Reasoning)', 'mxchat'),
            'gemini-1.5-flash' => esc_html__('Gemini 1.5 Flash (Fast & Versatile)', 'mxchat'),
        ),
        esc_html__('X.AI Models', 'mxchat') => array(
            'grok-3-beta' => esc_html__('Grok-3 (Powerful)', 'mxchat'),
            'grok-3-fast-beta' => esc_html__('Grok-3 Fast (High Performance)', 'mxchat'),
            'grok-3-mini-beta' => esc_html__('Grok-3 Mini (Affordable)', 'mxchat'),
            'grok-3-mini-fast-beta' => esc_html__('Grok-3 Mini Fast (Quick Response)', 'mxchat'),
            'grok-2' => esc_html__('Grok 2', 'mxchat')
        ),
        esc_html__('DeepSeek Models', 'mxchat') => array(
            'deepseek-chat' => esc_html__('DeepSeek-V3', 'mxchat'),
        ),
        esc_html__('Claude Models', 'mxchat') => array(
            'claude-opus-4-20250514' => esc_html__('Claude 4 Opus (Most Capable)', 'mxchat'),
            'claude-sonnet-4-20250514' => esc_html__('Claude 4 Sonnet (High Performance)', 'mxchat'),
            'claude-3-7-sonnet-20250219' => esc_html__('Claude 3.7 Sonnet (High Intelligence)', 'mxchat'),
            'claude-3-5-sonnet-20241022' => esc_html__('Claude 3.5 Sonnet (Intelligent)', 'mxchat'),
            'claude-3-opus-20240229' => esc_html__('Claude 3 Opus (Complex Tasks)', 'mxchat'),
            'claude-3-sonnet-20240229' => esc_html__('Claude 3 Sonnet (Balanced)', 'mxchat'),
            'claude-3-haiku-20240307' => esc_html__('Claude 3 Haiku (Fastest)', 'mxchat')
        ),
        esc_html__('OpenAI Models', 'mxchat') => array(
            'gpt-4.1-2025-04-14' => esc_html__('GPT-4.1 (Flagship for Complex Tasks)', 'mxchat'),
            'gpt-4o' => esc_html__('GPT-4o (Recommended)', 'mxchat'),
            'gpt-4o-mini' => esc_html__('GPT-4o Mini (Fast and Lightweight)', 'mxchat'),
            'gpt-4-turbo' => esc_html__('GPT-4 Turbo (High-Performance)', 'mxchat'),
            'gpt-4' => esc_html__('GPT-4 (High Intelligence)', 'mxchat'),
            'gpt-3.5-turbo' => esc_html__('GPT-3.5 Turbo (Affordable and Fast)', 'mxchat')
        )
    );

    // Retrieve the currently selected model from saved options
    $selected_model = isset($this->options['model']) ? esc_attr($this->options['model']) : 'gpt-4o';

    // Begin the select dropdown
    echo '<select id="model" name="model">';

    // Iterate over groups of models
    foreach ($models as $group_label => $group_models) {
        echo '<optgroup label="' . esc_attr($group_label) . '">';

        foreach ($group_models as $model_value => $model_label) {
            // All models enabled - no disabled attribute or Pro Only label
            echo '<option value="' . esc_attr($model_value) . '" ' . selected($selected_model, $model_value, false) . '>' . esc_html($model_label) . '</option>';
        }

        echo '</optgroup>';
    }

    // Close the select dropdown
    echo '</select>';

    // Updated description to remove mention of Pro-only models
    echo '<p class="description">' . esc_html__('Select the AI model your chatbot will use for chatting.', 'mxchat') . '</p>';
}

public function enable_streaming_toggle_callback() {
    // Get value from options array, default to 'on'
    $enabled = isset($this->options['enable_streaming_toggle']) ? $this->options['enable_streaming_toggle'] : 'on';
    $checked = ($enabled === 'on') ? 'checked' : '';
    
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="enable_streaming_toggle" name="enable_streaming_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>'; // Remove 'round' class to match working toggle
    echo '</label>';
    echo '<p class="description">' . 
        esc_html__('Enable real-time streaming responses for supported models (OpenAI and Claude). When disabled, responses will load all at once.', 'mxchat') . 
    '</p>';
}
// Callback function for embedding model selection
public function embedding_model_callback() {
    $models = array(
        esc_html__('OpenAI Embeddings', 'mxchat') => array(
            'text-embedding-3-small' => esc_html__('TE3 Small (1536, Efficient)', 'mxchat'),
            'text-embedding-ada-002' => esc_html__('Ada 2 (1536, Recommended)', 'mxchat'),
            'text-embedding-3-large' => esc_html__('TE3 Large (3072, Powerful)', 'mxchat'),
        ),
        esc_html__('Voyage AI Embeddings', 'mxchat') => array(
            'voyage-3-large' => esc_html__('Voyage-3 Large (2048, Most Capable)', 'mxchat'),
        ),
        esc_html__('Google Gemini Embeddings', 'mxchat') => array(
            'gemini-embedding-exp-03-07' => esc_html__('Gemini Embedding (1536, Experimental)', 'mxchat'),
        )
    );
    $selected_model = isset($this->options['embedding_model']) ? esc_attr($this->options['embedding_model']) : 'text-embedding-ada-002';
    echo '<select id="embedding_model" name="embedding_model">';
    foreach ($models as $group_label => $group_models) {
        echo '<optgroup label="' . esc_attr($group_label) . '">';
        foreach ($group_models as $model_value => $model_label) {
            echo '<option value="' . esc_attr($model_value) . '" ' . selected($selected_model, $model_value, false) . '>' . esc_html($model_label) . '</option>';
        }
        echo '</optgroup>';
    }
    echo '</select>';
    echo '<p class="description"><span class="red-warning">IMPORTANT:</span> Select the model for vector embeddings. Changing models is not recommended; if you do, you must delete all existing knowledge & intent data and reconfigure them.</p>';
}


public function mxchat_top_bar_title_callback() {
    // Retrieve the current value of the top bar title from saved options
    $top_bar_title = isset($this->options['top_bar_title']) ? esc_attr($this->options['top_bar_title']) : '';

    // Render the input field
    echo '<input type="text" id="top_bar_title" name="top_bar_title" value="' . $top_bar_title . '" />';

    // Add a description
    echo '<p class="description">' . esc_html__('Enter the title text that will appear on the top bar of the chatbot.', 'mxchat') . '</p>';
}
public function mxchat_ai_agent_text_callback() {
    // Retrieve the current value of the AI agent text from saved options
    $ai_agent_text = isset($this->options['ai_agent_text']) ? esc_attr($this->options['ai_agent_text']) : '';
    // Render the input field
    echo '<input type="text" id="ai_agent_text" name="ai_agent_text" value="' . $ai_agent_text . '" />';
    // Add a description
    echo '<p class="description">' . esc_html__('Enter the text that will appear for AI agents in the status indicator. Default: "AI Agent"', 'mxchat') . '</p>';
}
public function enable_email_block_callback() {
    // Load full plugin options array
    $all_options = get_option('mxchat_options', []);

    // Get the value, default to 'off'
    $enable_email_block = isset($all_options['enable_email_block']) ? $all_options['enable_email_block'] : 'off';

    // Check if it's 'on'
    $checked = ($enable_email_block === 'on') ? 'checked' : '';

    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="enable_email_block" name="enable_email_block" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '<p class="description">' . esc_html__('Their email will appear at the top of the transcript. Email form will show for users who are not logged in or have not provided an email within 24h.', 'mxchat') . '</p>';
}


public function email_blocker_header_content_callback() {
    // Load the entire 'mxchat_options' array
    $all_options = get_option('mxchat_options', []);

    // Retrieve the saved content or default to empty
    $content = isset($all_options['email_blocker_header_content'])
        ? $all_options['email_blocker_header_content']
        : '';

    // Render the textarea - IMPORTANT: name should be just "email_blocker_header_content"
    echo '<textarea
            id="email_blocker_header_content"
            name="email_blocker_header_content"
            rows="5"
            cols="70"
            data-setting="email_blocker_header_content"
          >' . esc_textarea($content) . '</textarea>';

    echo '<p class="description">';
    echo esc_html__('You may enter HTML here, such as &lt;h2&gt;Welcome&lt;/h2&gt; or &lt;p&gt;Let\'s get started&lt;/p&gt;.', 'mxchat');
    echo '</p>';
}

public function email_blocker_button_text_callback() {
    // Load the entire 'mxchat_options' array
    $all_options = get_option('mxchat_options', []);

    // Retrieve the saved button text or default to empty
    $button_text = isset($all_options['email_blocker_button_text'])
        ? $all_options['email_blocker_button_text']
        : '';

    // Use esc_attr to safely render the existing text
    echo '<input type="text" id="email_blocker_button_text" name="email_blocker_button_text" value="' . esc_attr($button_text) . '" style="width: 300px;" />';

    echo '<p class="description">';
    echo esc_html__('Enter the text you want on the submit button, e.g. "Start Chat".', 'mxchat');
    echo '</p>';
}



public function mxchat_intro_message_callback() {
    // Load the entire 'mxchat_options' array
    $all_options = get_option('mxchat_options', []);
    // Retrieve the saved intro message or use the default
    $default_message = __('Hello! How can I assist you today?', 'mxchat');
    $saved_message = isset($all_options['intro_message']) ? $all_options['intro_message'] : $default_message;
    // Output the textarea with the saved value without escaping HTML
    ?>
    <textarea id="intro_message" name="intro_message" rows="5" cols="50"><?php echo $saved_message; ?></textarea>
    <p class="description">
        <?php esc_html_e('Enter your message. HTML tags and line breaks will be preserved.', 'mxchat'); ?>
    </p>
    <?php
}

public function mxchat_input_copy_callback() {
    // Load the entire 'mxchat_options' array
    $all_options = get_option('mxchat_options', []);

    // Retrieve the saved input copy or use the default value
    $default_copy = __('How can I assist?', 'mxchat');
    $input_copy = isset($all_options['input_copy']) ? $all_options['input_copy'] : $default_copy;

    // Output the input field with the saved value
    printf(
        '<input type="text" id="input_copy" name="input_copy" value="%s" placeholder="%s" />',
        esc_attr($input_copy),
        esc_attr__('How can I assist?', 'mxchat')
    );

    // Output the description
    echo '<p class="description">' . esc_html__('This is the placeholder text for the chat input field.', 'mxchat') . '</p>';
}



public function mxchat_user_message_font_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="user_message_font_color"
               name="user_message_font_color"
               value="%s"
               class="my-color-field"
               data-default-color="#ffffff"
               %s />',
        isset($this->options['user_message_font_color']) ? esc_attr($this->options['user_message_font_color']) : '#ffffff',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_bot_message_bg_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="bot_message_bg_color"
               name="bot_message_bg_color"
               value="%s"
               class="my-color-field"
               data-default-color="#e1e1e1"
               %s />',
        isset($this->options['bot_message_bg_color']) ? esc_attr($this->options['bot_message_bg_color']) : '#e1e1e1',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_bot_message_font_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="bot_message_font_color"
               name="bot_message_font_color"
               value="%s"
               class="my-color-field"
               data-default-color="#333333"
               %s />',
        isset($this->options['bot_message_font_color']) ? esc_attr($this->options['bot_message_font_color']) : '#333333',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_live_agent_message_bg_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="live_agent_message_bg_color"
               name="live_agent_message_bg_color"
               value="%s"
               class="my-color-field"
               data-default-color="#ffffff"
               %s />',
        isset($this->options['live_agent_message_bg_color']) ? esc_attr($this->options['live_agent_message_bg_color']) : '#ffffff',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_live_agent_message_font_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="live_agent_message_font_color"
               name="live_agent_message_font_color"
               value="%s"
               class="my-color-field"
               data-default-color="#333333"
               %s />',
        isset($this->options['live_agent_message_font_color']) ? esc_attr($this->options['live_agent_message_font_color']) : '#333333',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_mode_indicator_bg_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="mode_indicator_bg_color"
               name="mode_indicator_bg_color"
               value="%s"
               class="my-color-field"
               data-default-color="#767676"
               %s />',
        isset($this->options['mode_indicator_bg_color']) ? esc_attr($this->options['mode_indicator_bg_color']) : '#767676',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_mode_indicator_font_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="mode_indicator_font_color"
               name="mode_indicator_font_color"
               value="%s"
               class="my-color-field"
               data-default-color="#ffffff"
               %s />',
        isset($this->options['mode_indicator_font_color']) ? esc_attr($this->options['mode_indicator_font_color']) : '#ffffff',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_toolbar_icon_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="toolbar_icon_color"
               name="toolbar_icon_color"
               value="%s"
               class="my-color-field"
               data-default-color="#212121"
               %s />',
        isset($this->options['toolbar_icon_color']) ? esc_attr($this->options['toolbar_icon_color']) : '#212121',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_top_bar_bg_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="top_bar_bg_color"
               name="top_bar_bg_color"
               value="%s"
               class="my-color-field"
               data-default-color="#00b294"
               %s />',
        isset($this->options['top_bar_bg_color']) ? esc_attr($this->options['top_bar_bg_color']) : '#00b294',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_send_button_font_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="send_button_font_color"
               name="send_button_font_color"
               value="%s"
               class="my-color-field"
               data-default-color="#ffffff"
               %s />',
        isset($this->options['send_button_font_color']) ? esc_attr($this->options['send_button_font_color']) : '#ffffff',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_chatbot_background_color_callback() {
    $disabled = $this->is_activated ? '' : 'disabled';
    $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

    echo '<div class="' . esc_attr($class) . '">';
    echo sprintf(
        '<input type="text"
               id="chatbot_background_color"
               name="chatbot_background_color"
               value="%s"
               class="my-color-field"
               data-default-color="#000000"
               %s />',
        isset($this->options['chatbot_background_color']) ? esc_attr($this->options['chatbot_background_color']) : '#000000',
        esc_attr($disabled)
    );

    if (!$this->is_activated) {
        echo '<div class="pro-feature-overlay">';
        echo '<a href="https://mxchat.ai/" target="_blank">';
        echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
        echo '</a>';
        echo '</div>';
    }
    echo '</div>';
}

public function mxchat_icon_color_callback() {
   $disabled = $this->is_activated ? '' : 'disabled';
   $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

   echo '<div class="' . esc_attr($class) . '">';
   echo sprintf(
       '<input type="text"
              id="icon_color"
              name="icon_color"
              value="%s"
              class="my-color-field"
              data-default-color="#ffffff"
              %s />',
       isset($this->options['icon_color']) ? esc_attr($this->options['icon_color']) : '#ffffff',
       esc_attr($disabled)
   );

   if (!$this->is_activated) {
       echo '<div class="pro-feature-overlay">';
       echo '<a href="https://mxchat.ai/" target="_blank">';
       echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
       echo '</a>';
       echo '</div>';
   }
   echo '</div>';
}

public function mxchat_custom_icon_callback() {
   $disabled = $this->is_activated ? '' : 'disabled';
   $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';
   $custom_icon_url = isset($this->options['custom_icon']) ? esc_url($this->options['custom_icon']) : '';

   echo '<div class="' . esc_attr($class) . '">';
   echo sprintf(
       '<input type="url"
              id="custom_icon"
              name="custom_icon"
              value="%s"
              placeholder="%s"
              class="regular-text"
              %s />',
       $custom_icon_url,
       esc_attr__('Enter PNG URL', 'mxchat'),
       esc_attr($disabled)
   );

   // Preview container for the icon
   if (!empty($custom_icon_url)) {
       echo '<div class="icon-preview" style="margin-top: 10px;">';
       echo '<img src="' . esc_url($custom_icon_url) . '" alt="' . esc_attr__('Custom Icon Preview', 'mxchat') . '" style="max-width: 48px; height: auto;" />';
       echo '</div>';
   }

   echo '<p class="description">' . esc_html__('Upload your PNG icon and paste the URL here. Recommended size: 48x48 pixels.', 'mxchat') . '</p>';

   if (!$this->is_activated) {
       echo '<div class="pro-feature-overlay">';
       echo '<a href="https://mxchat.ai/" target="_blank">';
       echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
       echo '</a>';
       echo '</div>';
   }
   echo '</div>';
}

public function mxchat_title_icon_callback() {
   $disabled = $this->is_activated ? '' : 'disabled';
   $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';
   // Fixed the variable reference - it was using custom_icon instead of title_icon
   $title_icon_url = isset($this->options['title_icon']) ? esc_url($this->options['title_icon']) : '';

   echo '<div class="' . esc_attr($class) . '">';
   echo sprintf(
       '<input type="url"
              id="title_icon"
              name="title_icon"
              value="%s"
              placeholder="%s"
              class="regular-text"
              %s />',
       $title_icon_url,
       esc_attr__('Enter PNG URL', 'mxchat'),
       esc_attr($disabled)
   );

   // Preview container for the icon
   if (!empty($title_icon_url)) {
       echo '<div class="icon-preview" style="margin-top: 10px;">';
       echo '<img src="' . esc_url($title_icon_url) . '" alt="' . esc_attr__('Title Icon Preview', 'mxchat') . '" style="max-width: 48px; height: auto;" />';
       echo '</div>';
   }

   echo '<p class="description">' . esc_html__('Upload your PNG icon and paste the URL here. Recommended size: 48x48 pixels.', 'mxchat') . '</p>';

   if (!$this->is_activated) {
       echo '<div class="pro-feature-overlay">';
       echo '<a href="https://mxchat.ai/" target="_blank">';
       echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
       echo '</a>';
       echo '</div>';
   }
   echo '</div>';
}

public function mxchat_chat_input_font_color_callback() {
   $disabled = $this->is_activated ? '' : 'disabled';
   $class = $this->is_activated ? 'pro-feature-wrapper active' : 'pro-feature-wrapper inactive';

   echo '<div class="' . esc_attr($class) . '">';
   echo sprintf(
       '<input type="text"
              id="chat_input_font_color"
              name="chat_input_font_color"
              value="%s"
              class="my-color-field"
              data-default-color="#555555"
              %s />',
       isset($this->options['chat_input_font_color']) ? esc_attr($this->options['chat_input_font_color']) : '#555555',
       esc_attr($disabled)
   );

   if (!$this->is_activated) {
       echo '<div class="pro-feature-overlay">';
       echo '<a href="https://mxchat.ai/" target="_blank">';
       echo '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../images/pro-only-dark.png') . '" alt="' . esc_attr__('Pro Only', 'mxchat') . '" />';
       echo '</a>';
       echo '</div>';
   }
   echo '</div>';
}

public function mxchat_append_to_body_callback() {
    // Get value from options array, default to 'off'
    $append_to_body = isset($this->options['append_to_body']) ? $this->options['append_to_body'] : 'off';
    $checked = ($append_to_body === 'on') ? 'checked' : '';

    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="append_to_body" name="append_to_body" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '<p class="description">' .
        esc_html__('Show chatbot automatically on all pages. When disabled, you can place the chatbot manually using shortcode [mxchat_chatbot floating="yes"].', 'mxchat') .
    '</p>';

}

public function mxchat_contextual_awareness_callback() {
    // Get value from options array, default to 'off'
    $contextual_awareness = isset($this->options['contextual_awareness_toggle']) ? $this->options['contextual_awareness_toggle'] : 'off';
    $checked = ($contextual_awareness === 'on') ? 'checked' : '';
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="contextual_awareness_toggle" name="contextual_awareness_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '<p class="description">' .
        esc_html__('Enable contextual awareness to allow the chatbot to understand and reference the current page content. When enabled, the chatbot will have access to the page title, content, and URL for more relevant responses.', 'mxchat') .
    '</p>';
}


public function mxchat_privacy_toggle_callback() {
    // Load from mxchat_options array
    $options = get_option('mxchat_options', []);

    // Get privacy toggle value with fallback
    $privacy_toggle = isset($options['privacy_toggle']) ? $options['privacy_toggle'] : 'off';
    $checked = ($privacy_toggle === 'on') ? 'checked' : '';

    // Get privacy text with fallback
    $privacy_text = isset($options['privacy_text'])
        ? $options['privacy_text']
        : __('By chatting, you agree to our <a href="https://example.com/privacy-policy" target="_blank">privacy policy</a>.', 'mxchat');

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="privacy_toggle" name="privacy_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '<p class="description">' . esc_html__('Enable this option to display a privacy notice below the chat widget.', 'mxchat') . '</p>';

    // Output the custom text input field
    echo sprintf(
        '<textarea id="privacy_text" name="privacy_text" rows="5" cols="50" class="regular-text">%s</textarea>',
        esc_textarea($privacy_text)
    );
    echo '<p class="description">' . esc_html__('Enter the privacy policy text. You can include HTML links.', 'mxchat') . '</p>';
}


public function mxchat_complianz_toggle_callback() {
    // Load from mxchat_options array
    $options = get_option('mxchat_options', []);

    // Get complianz toggle value with fallback
    $complianz_toggle = isset($options['complianz_toggle']) ? $options['complianz_toggle'] : 'off';
    $checked = ($complianz_toggle === 'on') ? 'checked' : '';

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="complianz_toggle" name="complianz_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';

    echo '<p class="description">' . esc_html__('Enable this option to apply Complianz consent logic to the chatbot (must have Complianz Plugin).', 'mxchat') . '</p>';
}

public function mxchat_link_target_toggle_callback() {
    // Load from mxchat_options array
    $options = get_option('mxchat_options', []);

    // Get link target toggle value with fallback
    $link_target_toggle = isset($options['link_target_toggle']) ? $options['link_target_toggle'] : 'off';
    $checked = ($link_target_toggle === 'on') ? 'checked' : '';

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="link_target_toggle" name="link_target_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '<p class="description">' . esc_html__('Enable to open links in a new tab (default is to open in the same tab).', 'mxchat') . '</p>';
}

public function mxchat_chat_persistence_toggle_callback() {
    // Load from mxchat_options array
    $options = get_option('mxchat_options', []);

    // Get chat persistence toggle value with fallback
    $chat_persistence_toggle = isset($options['chat_persistence_toggle']) ? $options['chat_persistence_toggle'] : 'off';
    $checked = ($chat_persistence_toggle === 'on') ? 'checked' : '';

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="chat_persistence_toggle" name="chat_persistence_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';

    echo '<p class="description">' . esc_html__('Enable to keep chat history when users navigate tabs or return to the site within 24 hours.', 'mxchat') . '</p>';
}

public function mxchat_popular_question_1_callback() {
    // Load the full plugin options array
    $all_options = get_option('mxchat_options', []);

    // Retrieve the specific option for popular_question_1
    $popular_question_1 = isset($all_options['popular_question_1']) ? $all_options['popular_question_1'] : '';

    // Render the input field
    printf(
        '<input type="text" id="popular_question_1" name="popular_question_1" value="%s" placeholder="%s" class="regular-text" />',
        esc_attr($popular_question_1),
        esc_attr__('Enter Quick Question 1', 'mxchat')
    );

    // Add a description for the field
    echo '<p class="description">' . esc_html__('This will be the first Quick Question in the chatbot, displayed above the input field.', 'mxchat') . '</p>';
}


public function mxchat_popular_question_2_callback() {
    // Load the full plugin options array
    $all_options = get_option('mxchat_options', []);

    // Retrieve the specific option for popular_question_2
    $popular_question_2 = isset($all_options['popular_question_2']) ? $all_options['popular_question_2'] : '';

    // Render the input field
    printf(
        '<input type="text" id="popular_question_2" name="popular_question_2" value="%s" placeholder="%s" class="regular-text" />',
        esc_attr($popular_question_2),
        esc_attr__('Enter Quick Question 2', 'mxchat')
    );

    // Add a description for the field
    echo '<p class="description">' . esc_html__('This will be the second Quick Question in the chatbot.', 'mxchat') . '</p>';
}


public function mxchat_popular_question_3_callback() {
   // Load the full plugin options array
   $all_options = get_option('mxchat_options', []);

   // Retrieve the specific option for popular_question_3
   $popular_question_3 = isset($all_options['popular_question_3']) ? $all_options['popular_question_3'] : '';

   // Render the input field
   printf(
       '<input type="text" id="popular_question_3" name="popular_question_3" value="%s" placeholder="%s" class="regular-text" />',
       esc_attr($popular_question_3),
       esc_attr(__('Enter Quick Question 3', 'mxchat'))
   );

   // Add a description for the field
   echo '<p class="description">' . esc_html__('This will be the third Quick Question in the chatbot.', 'mxchat') . '</p>';
}

public function mxchat_additional_popular_questions_callback() {
    $options = get_option('mxchat_options', []);
    $additional_questions = isset($options['additional_popular_questions'])
        ? $options['additional_popular_questions']
        : get_option('additional_popular_questions', array());

    echo '<div id="mxchat-additional-questions-container">';
    if (!empty($additional_questions)) {
        foreach ($additional_questions as $index => $question) {
            printf(
                '<div class="mxchat-question-row">
                    <input type="text" name="additional_popular_questions[]"
                           value="%s"
                           placeholder="%s"
                           class="regular-text mxchat-question-input"
                           data-question-index="%d" />
                    <button type="button" class="button mxchat-remove-question"
                            aria-label="%s">%s</button>
                </div>',
                esc_attr($question),
                esc_attr(sprintf(__('Enter Additional Quick Question %d', 'mxchat'), $index + 4)),
                $index,
                esc_attr(__('Remove question', 'mxchat')),
                esc_html__('Remove', 'mxchat')
            );
        }
    } else {
        printf(
            '<div class="mxchat-question-row">
                <input type="text" name="additional_popular_questions[]"
                       value=""
                       placeholder="%s"
                       class="regular-text mxchat-question-input"
                       data-question-index="0" />
                <button type="button" class="button mxchat-remove-question"
                        aria-label="%s">%s</button>
            </div>',
            esc_attr(__('Enter Additional Quick Question 4', 'mxchat')),
            esc_attr(__('Remove question', 'mxchat')),
            esc_html__('Remove', 'mxchat')
        );
    }
    echo '</div>';
    printf(
        '<button type="button" class="button mxchat-add-question" aria-label="%s">%s</button>',
        esc_attr(__('Add question', 'mxchat')),
        esc_html__('Add Question', 'mxchat')
    );
    echo '<p class="description">' . esc_html__('Add as many Quick Questions as you need.', 'mxchat') . '</p>';
    echo '</div>';
}

public function mxchat_brave_api_key_callback() {
    $brave_api_key = isset($this->options['brave_api_key']) ? esc_attr($this->options['brave_api_key']) : '';

    echo '<div class="api-key-wrapper">';
    echo sprintf(
        '<input type="password" id="brave_api_key" name="brave_api_key" value="%s" class="regular-text" />',
        $brave_api_key
    );
    echo '<button type="button" id="toggleBraveApiKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '</div>';
    echo '<p class="description">' . __('Enter your Brave Search API Key here. (See FAQ for details)', 'mxchat') . '</p>';
}

public function mxchat_brave_image_count_callback() {
    $brave_image_count = isset($this->options['brave_image_count'])
        ? intval($this->options['brave_image_count'])
        : 4;

    echo sprintf(
        '<input type="number" id="brave_image_count" name="brave_image_count"
               value="%d" min="1" max="6" class="small-text" />',
        $brave_image_count
    );
    echo '<p class="description">' . __('Select the number of images to return (1-6).', 'mxchat') . '</p>';
}

public function mxchat_brave_safe_search_callback() {
    $brave_safe_search = isset($this->options['brave_safe_search'])
        ? esc_attr($this->options['brave_safe_search'])
        : 'strict';

    echo '<select id="brave_safe_search" name="brave_safe_search">';
    echo sprintf(
        '<option value="strict" %s>%s</option>',
        selected($brave_safe_search, 'strict', false),
        __('Strict', 'mxchat')
    );
    echo sprintf(
        '<option value="off" %s>%s</option>',
        selected($brave_safe_search, 'off', false),
        __('Off', 'mxchat')
    );
    echo '</select>';
    echo '<p class="description">' .
         esc_html__('Set the Safe Search level for image searches. Brave Search only supports "Strict" and "Off" options.', 'mxchat') .
         '</p>';
}

public function mxchat_brave_news_count_callback() {
    $brave_news_count = isset($this->options['brave_news_count'])
        ? intval($this->options['brave_news_count'])
        : 3;

    echo sprintf(
        '<input type="number" id="brave_news_count" name="brave_news_count"
               value="%d" min="1" max="10" class="small-text" />',
        $brave_news_count
    );
    echo '<p class="description">' . esc_html__('Select the number of news articles to retrieve (1-10).', 'mxchat') . '</p>';
}

public function mxchat_brave_country_callback() {
    $brave_country = isset($this->options['brave_country'])
        ? esc_attr($this->options['brave_country'])
        : 'us';

    echo sprintf(
        '<input type="text" id="brave_country" name="brave_country"
               value="%s" maxlength="2" class="small-text" />',
        $brave_country
    );
    echo '<p class="description">' . esc_html__('Enter the country code (e.g., "us" for United States).', 'mxchat') . '</p>';
}

public function mxchat_brave_language_callback() {
    $brave_language = isset($this->options['brave_language'])
        ? esc_attr($this->options['brave_language'])
        : 'en';

    echo sprintf(
        '<input type="text" id="brave_language" name="brave_language"
               value="%s" maxlength="2" class="small-text" />',
        $brave_language
    );
    echo '<p class="description">' . esc_html__('Enter the language code (e.g., "en" for English).', 'mxchat') . '</p>';
}





// Section Callback
public function mxchat_pdf_intent_section_callback() {
    echo '<p>' . esc_html__('Configure the intent settings for the Chat with PDF feature.', 'mxchat') . '</p>';
}

public function mxchat_chat_toolbar_toggle_callback() {
    // Get chat toolbar toggle value with fallback
    $chat_toolbar_toggle = isset($this->options['chat_toolbar_toggle']) ? $this->options['chat_toolbar_toggle'] : 'off';
    $checked = ($chat_toolbar_toggle === 'on') ? 'checked' : '';

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="chat_toolbar_toggle" name="chat_toolbar_toggle" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';

    echo '<p class="description">' . esc_html__('Enable to display the chat toolbar, adding two icons below the chatbot input field for uploading PDF and Word documents (default is hidden).', 'mxchat') . '</p>';
}
/**
 * Callback for PDF upload button toggle setting
 */
public function mxchat_show_pdf_upload_button_callback() {
    // Get toggle value with fallback
    $show_pdf_button = isset($this->options['show_pdf_upload_button']) ? $this->options['show_pdf_upload_button'] : 'on';
    $checked = ($show_pdf_button === 'on') ? 'checked' : '';

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="show_pdf_upload_button" name="show_pdf_upload_button" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';

    echo '<p class="description">' . esc_html__('Enable to show the PDF upload button in the chatbot toolbar. Disable to hide it.', 'mxchat') . '</p>';
}
/**
 * Callback for Word upload button toggle setting
 */
public function mxchat_show_word_upload_button_callback() {
    // Get toggle value with fallback
    $show_word_button = isset($this->options['show_word_upload_button']) ? $this->options['show_word_upload_button'] : 'on';
    $checked = ($show_word_button === 'on') ? 'checked' : '';

    // Output the toggle switch
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="show_word_upload_button" name="show_word_upload_button" value="on" %s />',
        esc_attr($checked)
    );
    echo '<span class="slider"></span>';
    echo '</label>';

    echo '<p class="description">' . esc_html__('Enable to show the Word document upload button in the chatbot toolbar. Disable to hide it.', 'mxchat') . '</p>';
}

public function mxchat_pdf_intent_trigger_text_callback() {
    $default_text = __("Please provide the URL to the PDF you'd like to discuss.", 'mxchat');

    echo sprintf(
        '<textarea id="pdf_intent_trigger_text"
                  name="pdf_intent_trigger_text"
                  rows="3"
                  cols="50"
                  placeholder="%s">%s</textarea>',
        esc_attr__('Enter trigger text', 'mxchat'),
        isset($this->options['pdf_intent_trigger_text'])
            ? esc_textarea($this->options['pdf_intent_trigger_text'])
            : esc_textarea($default_text)
    );
    echo '<p class="description">' . esc_html__('Text displayed when the intent is triggered.', 'mxchat') . '</p>';
}

public function mxchat_pdf_intent_success_text_callback() {
    $default_text = __("I've processed the PDF. What questions do you have about it?", 'mxchat');

    echo sprintf(
        '<textarea id="pdf_intent_success_text"
                  name="pdf_intent_success_text"
                  rows="3"
                  cols="50"
                  placeholder="%s">%s</textarea>',
        esc_attr__('Enter success text', 'mxchat'),
        isset($this->options['pdf_intent_success_text'])
            ? esc_textarea($this->options['pdf_intent_success_text'])
            : esc_textarea($default_text)
    );
    echo '<p class="description">' . esc_html__('Text displayed when the intent is successful.', 'mxchat') . '</p>';
}

public function mxchat_pdf_intent_error_text_callback() {
    $default_text = __("Sorry, I couldn't process the PDF. Please ensure it's a valid file.", 'mxchat');

    echo sprintf(
        '<textarea id="pdf_intent_error_text"
                  name="pdf_intent_error_text"
                  rows="3"
                  cols="50"
                  placeholder="%s">%s</textarea>',
        esc_attr__('Enter error text', 'mxchat'),
        isset($this->options['pdf_intent_error_text'])
            ? esc_textarea($this->options['pdf_intent_error_text'])
            : esc_textarea($default_text)
    );
    echo '<p class="description">' . esc_html__('Text displayed when an error occurs during the intent.', 'mxchat') . '</p>';
}

public function mxchat_pdf_max_pages_callback() {
    $max_pages = isset($this->options['pdf_max_pages']) ? intval($this->options['pdf_max_pages']) : 69;

    echo sprintf(
        '<input type="range"
               id="pdf_max_pages"
               name="pdf_max_pages"
               min="1"
               max="69"
               value="%d"
               class="range-slider" />',
        esc_attr($max_pages)
    );
    echo '<span id="pdf_max_pages_output">' . esc_html($max_pages) . '</span>';
    echo '<p class="description">' . esc_html__('Set the maximum number of document pages users can upload for processing. (1-69 pages)', 'mxchat') . '</p>';
}

public function mxchat_live_agent_status_callback() {
    // Always get fresh options instead of using cached $this->options
    $fresh_options = get_option('mxchat_options');
    $status = isset($fresh_options['live_agent_status']) ? $fresh_options['live_agent_status'] : 'off';
    
    echo '<label class="toggle-switch">';
    echo sprintf(
        '<input type="checkbox" id="live_agent_status" name="live_agent_status" value="on" %s />',
        checked($status, 'on', false)
    );
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '<label for="live_agent_status" class="mxchat-status-label">';
    echo '<span class="status-text">' . ($status === 'on' ? esc_html__('Online', 'mxchat') : esc_html__('Offline', 'mxchat')) . '</span>';
    echo '</label>';
}

public function mxchat_live_agent_away_message_callback() {
    $message = isset($this->options['live_agent_away_message'])
        ? $this->options['live_agent_away_message']
        : __('Sorry, live agents are currently unavailable. I can continue helping you as an AI assistant.', 'mxchat');

    printf(
        '<textarea id="live_agent_away_message" name="live_agent_away_message" rows="3" cols="50">%s</textarea>',
        esc_textarea($message)
    );
    echo '<p class="description">' . esc_html__('Message shown when live agents are offline.', 'mxchat') . '</p>';
}

public function mxchat_live_agent_notification_message_callback() {
    $message = isset($this->options['live_agent_notification_message'])
        ? $this->options['live_agent_notification_message']
        : __('Live agent has been notified.', 'mxchat');

    printf(
        '<textarea id="live_agent_notification_message" name="live_agent_notification_message" rows="3" cols="50">%s</textarea>',
        esc_textarea($message)
    );
    echo '<p class="description">' . esc_html__('Message shown when live transfer activated.', 'mxchat') . '</p>';
}

public function mxchat_live_agent_webhook_url_callback() {
    $webhook_url = isset($this->options['live_agent_webhook_url'])
        ? esc_url($this->options['live_agent_webhook_url'])
        : esc_url(get_option('live_agent_webhook_url', ''));

    printf(
        '<input type="password" id="live_agent_webhook_url" name="live_agent_webhook_url" value="%s" class="regular-text" />',
        $webhook_url
    );
    echo '<button type="button" id="toggleWebhookUrlVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description">' . esc_html__('Enter your Slack webhook URL for live agent notifications.', 'mxchat') . '</p>';
}

public function mxchat_live_agent_secret_key_callback() {
    printf(
        '<input type="password" id="live_agent_secret_key" name="live_agent_secret_key" value="%s" class="regular-text" />',
        isset($this->options['live_agent_secret_key']) ? esc_attr($this->options['live_agent_secret_key']) : ''
    );
    echo '<button type="button" id="toggleSecretKeyVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description">' . esc_html__('Secret key for validating Slack requests. Keep this secure.', 'mxchat') . '</p>';
}

public function mxchat_live_agent_bot_token_callback() {
    printf(
        '<input type="password" id="live_agent_bot_token" name="live_agent_bot_token" value="%s" class="regular-text" />',
        isset($this->options['live_agent_bot_token']) ? esc_attr($this->options['live_agent_bot_token']) : ''
    );
    echo '<button type="button" id="toggleBotTokenVisibility">' . esc_html__('Show', 'mxchat') . '</button>';
    echo '<p class="description">' . esc_html__('Your Slack Bot OAuth Token (starts with xoxb-). Keep this secure.', 'mxchat') . '</p>';
}

public function mxchat_live_agent_user_ids_callback() {
    $user_ids = isset($this->options['live_agent_user_ids']) 
        ? esc_textarea($this->options['live_agent_user_ids']) 
        : '';
    
    printf(
        '<textarea id="live_agent_user_ids" name="live_agent_user_ids" rows="4" class="large-text">%s</textarea>',
        $user_ids
    );
    echo '<p class="description">' . esc_html__('Enter Slack User IDs of agents who should be automatically invited to chat channels (one per line). Find user IDs in Slack profiles under "More" → "Copy member ID". Example: U1234567890', 'mxchat') . '</p>';
}

public function mxchat_similarity_threshold_callback() {
    // Load from mxchat_options array
    $options = get_option('mxchat_options', []);

    // Get value from options array with default of 80
    $threshold = isset($options['similarity_threshold']) ? $options['similarity_threshold'] : 35;

    echo '<div class="slider-container">';
    echo sprintf(
        '<input type="range"
               id="similarity_threshold"
               name="similarity_threshold"
               min="20"
               max="85"
               step="1"
               value="%s"
               class="range-slider" />',
        esc_attr($threshold)
    );
    echo sprintf(
        '<span id="threshold_value" class="range-value">%s</span>',
        esc_html($threshold)
    );
    echo '</div>';
    echo '<p class="description">';
    echo sprintf(
    esc_html__('Adjust similarity threshold for optimal content matching. Too high may limit knowledge retrieval. We highly recommend downloading our %sfree Similarity Tester add-on%s to fine-tune your responses.', 'mxchat'),
    '<a href="' . admin_url('admin.php?page=mxchat-addons') . '">',
    '</a>'
    );
    echo '</p>';
}

public function mxchat_enqueue_admin_assets() {
    // Get plugin version (define this in your main plugin file)
    $version = defined('MXCHAT_VERSION') ? MXCHAT_VERSION : '2.2.8';
    
    // Use file modification time for development (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $version = filemtime(plugin_dir_path(__FILE__) . '../mxchat-basic.php');
    }
    
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';
    $plugin_url = plugin_dir_url(__FILE__) . '../';
    
    // Only load on MxChat pages
    if (strpos($current_page, 'mxchat') === false) {
        return;
    }
    
    // Always load these on all MxChat pages
    $this->enqueue_core_admin_assets($plugin_url, $version);
    $this->enqueue_page_specific_assets($current_page, $plugin_url, $version);
    $this->localize_admin_scripts($current_page);
}
private function enqueue_core_admin_assets($plugin_url, $version) {
    // Core admin styles
    wp_enqueue_style('mxchat-admin-css', $plugin_url . 'css/admin-style.css', array(), $version);
    wp_enqueue_style('mxchat-knowledge-css', $plugin_url . 'css/knowledge-style.css', array(), $version);
    
    // Core admin scripts
    wp_enqueue_script('mxchat-admin-js', $plugin_url . 'js/mxchat-admin.js', array('jquery'), $version, true);
}
private function enqueue_page_specific_assets($current_page, $plugin_url, $version) {
    switch ($current_page) {
        case 'mxchat-prompts':
            // Knowledge processing page assets
            wp_enqueue_style('mxchat-content-selector-css', $plugin_url . 'css/content-selector.css', array(), $version);
            wp_enqueue_script('mxchat-content-selector-js', $plugin_url . 'js/content-selector.js', array('jquery'), $version, true);
            // NEW: Add the knowledge processing script
            wp_enqueue_script('mxchat-knowledge-processing', $plugin_url . 'js/knowledge-processing.js', array('jquery'), $version, true);
            break;
            
        case 'mxchat-transcripts':
            wp_enqueue_style('mxchat-chat-transcripts-css', $plugin_url . 'css/chat-transcripts.css', array(), $version);
            wp_enqueue_script('mxchat-transcripts-js', $plugin_url . 'js/mxchat_transcripts.js', array('jquery'), $version, true);
            break;
            
        case 'mxchat-actions':
            wp_enqueue_style('mxchat-intent-css', $plugin_url . 'css/intent-style.css', array(), $version);
            break;
            
        case 'mxchat-activation':
            wp_enqueue_script('mxchat-activation-js', $plugin_url . 'js/activation-script.js', array('jquery'), $version, true);
            break;
            
    }
}
private function localize_admin_scripts($current_page) {
    // Base localization data for main admin script
    $base_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mxchat_status_nonce'),
        'admin_url' => admin_url(),
        'license_nonce' => wp_create_nonce('mxchat_activate_license_nonce'),
        'inline_edit_nonce' => wp_create_nonce('mxchat_save_inline_nonce'),
        'setting_nonce' => wp_create_nonce('mxchat_save_setting_nonce'),
        'export_nonce' => wp_create_nonce('mxchat_export_transcripts'),
        'actions_nonce' => wp_create_nonce('mxchat_actions_nonce'),
        'add_intent_nonce' => wp_create_nonce('mxchat_add_intent_nonce'),
        'edit_intent_nonce' => wp_create_nonce('mxchat_edit_intent'),
        'toggle_action_nonce' => wp_create_nonce('mxchat_actions_nonce'),
        'is_activated' => $this->is_activated ? '1' : '0',
        'status_refresh_interval' => 5000,
        'prompts_setting_nonce' => wp_create_nonce('mxchat_prompts_setting_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php') // For jQuery fallback
    );
    
    // Localize main admin script with base data
    wp_localize_script('mxchat-admin-js', 'mxchatAdmin', $base_data);
    
    // Page-specific localizations
    $this->localize_page_specific_scripts($current_page);
}
private function localize_page_specific_scripts($current_page) {
    switch ($current_page) {
        case 'mxchat-prompts':
            // Status updater localization
            wp_localize_script('mxchat-status-updater', 'mxchat_status_data', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mxchat_status_nonce')
            ));
            
            // Content selector localization
            wp_localize_script('mxchat-content-selector-js', 'mxchatSelector', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mxchat_content_selector_nonce'),
                'i18n' => array(
                    'searchPlaceholder' => __('Search posts and pages...', 'mxchat'),
                    'selectAll' => __('Select All', 'mxchat'),
                    'process' => __('Process Selected', 'mxchat'),
                    'cancel' => __('Cancel', 'mxchat'),
                    'noResults' => __('No content found.', 'mxchat')
                )
            ));
            
            // NEW: Knowledge processing script localization using mxchatAdmin
            wp_localize_script('mxchat-knowledge-processing', 'mxchatAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'status_nonce' => wp_create_nonce('mxchat_status_nonce'), // Note: changed 'nonce' to 'status_nonce'
                'stop_nonce' => wp_create_nonce('mxchat_stop_processing_action'),
                'admin_url' => admin_url(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'status_refresh_interval' => 2000
            ));
            break;
            
        case 'mxchat-activation':
            wp_localize_script('mxchat-activation-js', 'mxchatAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'license_nonce' => wp_create_nonce('mxchat_activate_license_nonce')
            ));
            break;
            
        case 'mxchat-settings':
        default:
            // Color picker and settings localization
            wp_localize_script('mxchat-color-picker', 'mxchatStyleSettings', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'link_target_toggle' => $this->options['link_target_toggle'] ?? 'off',
                'user_message_bg_color' => $this->options['user_message_bg_color'] ?? '#fff',
                'user_message_font_color' => $this->options['user_message_font_color'] ?? '#212121',
                'bot_message_bg_color' => $this->options['bot_message_bg_color'] ?? '#212121',
                'bot_message_font_color' => $this->options['bot_message_font_color'] ?? '#fff',
                'top_bar_bg_color' => $this->options['top_bar_bg_color'] ?? '#212121',
                'send_button_font_color' => $this->options['send_button_font_color'] ?? '#212121',
                'close_button_color' => $this->options['close_button_color'] ?? '#fff',
                'chatbot_background_color' => $this->options['chatbot_background_color'] ?? '#212121',
                'icon_color' => $this->options['icon_color'] ?? '#fff',
                'chat_input_font_color' => $this->options['chat_input_font_color'] ?? '#212121',
                'pre_chat_message' => $this->options['pre_chat_message'] ?? esc_html__('Hey there! Ask me anything!', 'mxchat'),
                'rate_limit_message' => $this->options['rate_limit_message'] ?? esc_html__('Rate limit exceeded. Please try again later.', 'mxchat'),
                'loops_api_key' => $this->options['loops_api_key'] ?? '',
                'loops_mailing_list' => $this->options['loops_mailing_list'] ?? '',
                'triggered_phrase_response' => $this->options['triggered_phrase_response'] ?? esc_html__('Would you like to join our mailing list? Please provide your email below.', 'mxchat'),
                'email_capture_response' => $this->options['email_capture_response'] ?? esc_html__('Thank you for providing your email! You\'ve been added to our list.', 'mxchat'),
                'pdf_intent_trigger_text' => $this->options['pdf_intent_trigger_text'] ?? esc_html__("Please provide the URL to the PDF you'd like to discuss.", 'mxchat'),
                'pdf_intent_success_text' => $this->options['pdf_intent_success_text'] ?? esc_html__("I've processed the PDF. What questions do you have about it?", 'mxchat'),
                'pdf_intent_error_text' => $this->options['pdf_intent_error_text'] ?? esc_html__("Sorry, I couldn't process the PDF. Please ensure it's a valid file.", 'mxchat'),
                'pdf_max_pages' => $this->options['pdf_max_pages'] ?? 69,
                'live_agent_webhook_url' => $this->options['live_agent_webhook_url'] ?? '',
                'live_agent_secret_key' => $this->options['live_agent_secret_key'] ?? '',
                'live_agent_bot_token' => $this->options['live_agent_bot_token'] ?? '',
                'live_agent_message_bg_color' => $this->options['live_agent_message_bg_color'] ?? '#ffffff',
                'live_agent_message_font_color' => $this->options['live_agent_message_font_color'] ?? '#333333',
                'chat_toolbar_toggle' => $this->options['chat_toolbar_toggle'] ?? 'off',
                'show_pdf_upload_button' => $this->options['show_pdf_upload_button'] ?? 'on',
                'show_word_upload_button' => $this->options['show_word_upload_button'] ?? 'on',
                'mode_indicator_bg_color' => $this->options['mode_indicator_bg_color'] ?? '#767676',
                'mode_indicator_font_color' => $this->options['mode_indicator_font_color'] ?? '#ffffff',
                'toolbar_icon_color' => $this->options['toolbar_icon_color'] ?? '#212121',
            ));
            break;
    }
    
    // Additional localization that was in the original code
    wp_localize_script('mxchat-admin-js', 'mxchatPromptsAdmin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'prompts_setting_nonce' => wp_create_nonce('mxchat_prompts_setting_nonce'),
    ));
}

public function mxchat_sanitize($input) {
    $new_input = array();

    if (isset($input['api_key'])) {
        $new_input['api_key'] = sanitize_text_field($input['api_key']);
    }

    if (isset($input['similarity_threshold'])) {
        $new_input['similarity_threshold'] = absint($input['similarity_threshold']); // Ensure it's an integer
        $new_input['similarity_threshold'] = min(max($new_input['similarity_threshold'], 20), 85); // Enforce range
    }

    if (isset($input['xai_api_key'])) {
        $new_input['xai_api_key'] = sanitize_text_field($input['xai_api_key']);
    }

    if (isset($input['claude_api_key'])) {
        $new_input['claude_api_key'] = sanitize_text_field($input['claude_api_key']);
    }
    
    if (isset($input['enable_streaming_toggle'])) {
        $new_input['enable_streaming_toggle'] = ($input['enable_streaming_toggle'] === 'on') ? 'on' : 'off';
    } else {
        // If checkbox not checked, it won't be in $input, so set to 'off'
        $new_input['enable_streaming_toggle'] = 'off';
    }
    

    if (isset($input['deepseek_api_key'])) {
        $new_input['deepseek_api_key'] = sanitize_text_field($input['deepseek_api_key']);
    }

    if (isset($input['gemini_api_key'])) {
        $new_input['gemini_api_key'] = sanitize_text_field($input['gemini_api_key']);
    }

    if (isset($input['enable_woocommerce_integration'])) {
        $new_input['enable_woocommerce_integration'] = $input['enable_woocommerce_integration'] === 'on' ? 'on' : 'off';
    }

    if (isset($input['privacy_toggle'])) {
        $new_input['privacy_toggle'] = $input['privacy_toggle'];
    }

    if (isset($input['complianz_toggle'])) {
        $new_input['complianz_toggle'] = $input['complianz_toggle'];
    }

    // Handle custom privacy text input
    if (isset($input['privacy_text'])) {
        // Allow basic HTML for links
        $new_input['privacy_text'] = wp_kses_post($input['privacy_text']);
    }

    if (isset($input['system_prompt_instructions'])) {
        $new_input['system_prompt_instructions'] = sanitize_textarea_field($input['system_prompt_instructions']);
    }

    if (isset($input['mxchat_pro_email'])) {
        $new_input['mxchat_pro_email'] = sanitize_email($input['mxchat_pro_email']);
    }

    if (isset($input['mxchat_activation_key'])) {
        $new_input['mxchat_activation_key'] = sanitize_text_field($input['mxchat_activation_key']);
    }

    if (isset($input['append_to_body'])) {
        $new_input['append_to_body'] = $input['append_to_body'] === 'on' ? 'on' : 'off';
    }
    
    if (isset($input['contextual_awareness_toggle'])) {
    $new_input['contextual_awareness_toggle'] = $input['contextual_awareness_toggle'] === 'on' ? 'on' : 'off';
}

    if (isset($input['top_bar_title'])) {
        $new_input['top_bar_title'] = sanitize_text_field($input['top_bar_title']);
    }

    if (isset($input['ai_agent_text'])) {
            $new_input['ai_agent_text'] = sanitize_text_field($input['ai_agent_text']);
        }

    if (isset($input['enable_email_block'])) {
        $new_input['enable_email_block'] = sanitize_text_field($input['enable_email_block']);
    }

    if (isset($input['email_blocker_header_content'])) {
        // wp_kses_post() allows standard HTML tags permitted by WordPress
        $new_input['email_blocker_header_content'] = wp_kses_post($input['email_blocker_header_content']);
    }
    if (isset($input['email_blocker_button_text'])) {
        $new_input['email_blocker_button_text'] = sanitize_text_field($input['email_blocker_button_text']);
    }

    if (isset($input['intro_message'])) {
        $new_input['intro_message'] = wp_kses_post($input['intro_message']);  // Use wp_kses_post instead
    }

    if (isset($input['input_copy'])) {
        $new_input['input_copy'] = sanitize_text_field($input['input_copy']);
    }

    if (isset($input['rate_limit_message'])) {
        $new_input['rate_limit_message'] = sanitize_text_field($input['rate_limit_message']);
    }

// Handle the new rate limits format
if (isset($input['rate_limits']) && is_array($input['rate_limits'])) {
    $new_input['rate_limits'] = array();
    $allowed_limits = array('1', '3', '5', '10', '15', '20', '50', '100', 'unlimited');
    $allowed_timeframes = array('hourly', 'daily', 'weekly', 'monthly');

    foreach ($input['rate_limits'] as $role_id => $settings) {
        $new_input['rate_limits'][$role_id] = array();

        // Sanitize limit
        if (isset($settings['limit'])) {
            $limit = sanitize_text_field($settings['limit']);
            if (in_array($limit, $allowed_limits, true)) {
                $new_input['rate_limits'][$role_id]['limit'] = $limit;
            } else {
                $new_input['rate_limits'][$role_id]['limit'] = ($role_id === 'logged_out') ? '10' : '100'; // Default
            }
        }

        // Sanitize timeframe
        if (isset($settings['timeframe'])) {
            $timeframe = sanitize_text_field($settings['timeframe']);
            if (in_array($timeframe, $allowed_timeframes, true)) {
                $new_input['rate_limits'][$role_id]['timeframe'] = $timeframe;
            } else {
                $new_input['rate_limits'][$role_id]['timeframe'] = 'daily'; // Default
            }
        }

        // Sanitize message
        if (isset($settings['message'])) {
            $new_input['rate_limits'][$role_id]['message'] = sanitize_textarea_field($settings['message']);
        }
    }
}

    if (isset($input['pre_chat_message'])) {
        $new_input['pre_chat_message'] = sanitize_textarea_field($input['pre_chat_message']);
    }

    if (isset($input['voyage_api_key'])) {
    $new_input['voyage_api_key'] = sanitize_text_field($input['voyage_api_key']);
    }

    // Add to your sanitize function
    if (isset($input['embedding_model'])) {
        $allowed_models = array(
            'text-embedding-ada-002',
            'text-embedding-3-small',
            'text-embedding-3-large',
            'voyage-3-large',
            'gemini-embedding-exp-03-07'
        );
        if (in_array($input['embedding_model'], $allowed_models)) {
            $new_input['embedding_model'] = sanitize_text_field($input['embedding_model']);
        }
    }

    if (isset($input['model'])) {
        $allowed_models = array(
                    'gemini-2.0-flash',
                    'gemini-2.0-flash-lite',
                    'gemini-1.5-pro',
                    'gemini-1.5-flash',
                    'grok-3-beta',
                    'grok-3-fast-beta',
                    'grok-3-mini-beta',
                    'grok-3-mini-fast-beta',
                    'grok-2',
                    'deepseek-chat',
                    'claude-opus-4-20250514',
                    'claude-sonnet-4-20250514',
                    'claude-3-7-sonnet-20250219',
                    'claude-3-5-sonnet-20241022',
                    'claude-3-opus-20240229',
                    'claude-3-sonnet-20240229',
                    'claude-3-haiku-20240307',
                    'gpt-4o',
                    'gpt-4.1-2025-04-14',
                    'gpt-4o-mini',
                    'gpt-4-turbo',
                    'gpt-4',
                    'gpt-3.5-turbo',
                );
        if (in_array($input['model'], $allowed_models)) {
            $new_input['model'] = sanitize_text_field($input['model']);
        }
    }

    if (isset($input['close_button_color'])) {
        $new_input['close_button_color'] = sanitize_hex_color($input['close_button_color']);
    }

    if (isset($input['chatbot_bg_color'])) {
        $new_input['chatbot_bg_color'] = sanitize_hex_color($input['chatbot_bg_color']);
    }

    if (isset($input['woocommerce_consumer_key'])) {
        $new_input['woocommerce_consumer_key'] = sanitize_text_field($input['woocommerce_consumer_key']);
    }

    if (isset($input['woocommerce_consumer_secret'])) {
        $new_input['woocommerce_consumer_secret'] = sanitize_text_field($input['woocommerce_consumer_secret']);
    }

    if (isset($input['user_message_bg_color'])) {
        $new_input['user_message_bg_color'] = sanitize_hex_color($input['user_message_bg_color']);
    }

    if (isset($input['user_message_font_color'])) {
        $new_input['user_message_font_color'] = sanitize_hex_color($input['user_message_font_color']);
    }

    if (isset($input['bot_message_bg_color'])) {
        $new_input['bot_message_bg_color'] = sanitize_hex_color($input['bot_message_bg_color']);
    }

    if (isset($input['bot_message_font_color'])) {
        $new_input['bot_message_font_color'] = sanitize_hex_color($input['bot_message_font_color']);
    }

    if (isset($input['live_agent_message_bg_color'])) {
        $new_input['live_agent_message_bg_color'] = sanitize_hex_color($input['live_agent_message_bg_color']);
    }

    if (isset($input['live_agent_message_font_color'])) {
        $new_input['live_agent_message_font_color'] = sanitize_hex_color($input['live_agent_message_font_color']);
    }

    if (isset($input['mode_indicator_bg_color'])) {
        $new_input['mode_indicator_bg_color'] = sanitize_hex_color($input['mode_indicator_bg_color']);
    }

    if (isset($input['mode_indicator_font_color'])) {
        $new_input['mode_indicator_font_color'] = sanitize_hex_color($input['mode_indicator_font_color']);
    }

    if (isset($input['toolbar_icon_color'])) {
        $new_input['toolbar_icon_color'] = sanitize_hex_color($input['toolbar_icon_color']);
    }

    if (isset($input['top_bar_bg_color'])) {
        $new_input['top_bar_bg_color'] = sanitize_hex_color($input['top_bar_bg_color']);
    }

    if (isset($input['send_button_font_color'])) {
        $new_input['send_button_font_color'] = sanitize_hex_color($input['send_button_font_color']);
    }

    if (isset($input['chatbot_background_color'])) {
        $new_input['chatbot_background_color'] = sanitize_hex_color($input['chatbot_background_color']);
    }

    if (isset($input['icon_color'])) {
        $new_input['icon_color'] = sanitize_hex_color($input['icon_color']);
    }

    if (isset($input['custom_icon'])) {
        $new_input['custom_icon'] = esc_url_raw($input['custom_icon']);
    }

    if (isset($input['title_icon'])) {
        $new_input['title_icon'] = esc_url_raw($input['title_icon']);
    }

    if (isset($input['chat_input_font_color'])) {
        $new_input['chat_input_font_color'] = sanitize_hex_color($input['chat_input_font_color']);
    }

    // Sanitize link_target_toggle
    if (isset($input['link_target_toggle'])) {
        $new_input['link_target_toggle'] = $input['link_target_toggle'] === 'on' ? 'on' : 'off';
    }

    // Sanitize Loops API Key
    if (isset($input['loops_api_key'])) {
        $new_input['loops_api_key'] = sanitize_text_field($input['loops_api_key']);
    }

    if (isset($input['chat_persistence_toggle'])) {
        $new_input['chat_persistence_toggle'] = $input['chat_persistence_toggle'] === 'on' ? 'on' : 'off';
    }

    if (isset($input['popular_question_1'])) {
        $new_input['popular_question_1'] = sanitize_text_field($input['popular_question_1']);
    }

    if (isset($input['popular_question_2'])) {
        $new_input['popular_question_2'] = sanitize_text_field($input['popular_question_2']);
    }

    if (isset($input['popular_question_3'])) {
        $new_input['popular_question_3'] = sanitize_text_field($input['popular_question_3']);
    }

    if (isset($input['additional_popular_questions']) && is_array($input['additional_popular_questions'])) {
        $new_input['additional_popular_questions'] = array_map('sanitize_text_field', $input['additional_popular_questions']);
    }

    // Sanitize Loops Mailing List
    if (isset($input['loops_mailing_list'])) {
        $new_input['loops_mailing_list'] = sanitize_text_field($input['loops_mailing_list']);
    }

    // Sanitize Triggered Phrase Response
    if (isset($input['triggered_phrase_response'])) {
        $new_input['triggered_phrase_response'] = wp_kses_post($input['triggered_phrase_response']);
    }

    if (isset($input['email_capture_response'])) {
        $new_input['email_capture_response'] = sanitize_textarea_field($input['email_capture_response']);
    }

    // Sanitize Brave Search Settings
    if (isset($input['brave_api_key'])) {
        $new_input['brave_api_key'] = sanitize_text_field($input['brave_api_key']);
    }

    if (isset($input['brave_image_count'])) {
        $image_count = intval($input['brave_image_count']);
        $new_input['brave_image_count'] = ($image_count >=1 && $image_count <=6) ? $image_count : 4;
    }

    if (isset($input['brave_safe_search'])) {
        $allowed = array('strict', 'off');
        $new_input['brave_safe_search'] = in_array($input['brave_safe_search'], $allowed, true) ? $input['brave_safe_search'] : 'strict';
    }

    if (isset($input['brave_news_count'])) {
        $news_count = intval($input['brave_news_count']);
        $new_input['brave_news_count'] = ($news_count >=1 && $news_count <=10) ? $news_count : 3;
    }

    if (isset($input['brave_country'])) {
        $new_input['brave_country'] = sanitize_text_field($input['brave_country']);
    }

    if (isset($input['brave_language'])) {
        $new_input['brave_language'] = sanitize_text_field($input['brave_language']);
    }

    if (isset($input['chat_toolbar_toggle'])) {
        $new_input['chat_toolbar_toggle'] = $input['chat_toolbar_toggle'] === 'on' ? 'on' : 'off';
    }

     // Sanitize PDF upload button toggle
    if (isset($input['show_pdf_upload_button'])) {
        $new_input['show_pdf_upload_button'] = $input['show_pdf_upload_button'] === 'on' ? 'on' : 'off';
    } else {
        $new_input['show_pdf_upload_button'] = 'off'; // If checkbox is unchecked
    }

    // Sanitize Word upload button toggle
    if (isset($input['show_word_upload_button'])) {
        $new_input['show_word_upload_button'] = $input['show_word_upload_button'] === 'on' ? 'on' : 'off';
    } else {
        $new_input['show_word_upload_button'] = 'off'; // If checkbox is unchecked
    }

    if (isset($input['pdf_intent_trigger_text'])) {
        $new_input['pdf_intent_trigger_text'] = sanitize_text_field($input['pdf_intent_trigger_text']);
    }

    if (isset($input['pdf_intent_success_text'])) {
        $new_input['pdf_intent_success_text'] = sanitize_text_field($input['pdf_intent_success_text']);
    }

    if (isset($input['pdf_intent_error_text'])) {
        $new_input['pdf_intent_error_text'] = sanitize_text_field($input['pdf_intent_error_text']);
    }

    if (isset($input['pdf_max_pages'])) {
        $new_input['pdf_max_pages'] = intval($input['pdf_max_pages']);
        if ($new_input['pdf_max_pages'] < 1 || $new_input['pdf_max_pages'] > 69) {
            $new_input['pdf_max_pages'] = 69; // Default to 69 if out of range
        }
    }

    if (isset($input['live_agent_webhook_url'])) {
        $new_input['live_agent_webhook_url'] = esc_url_raw($input['live_agent_webhook_url']);
    }
    if (isset($input['live_agent_secret_key'])) {
        $new_input['live_agent_secret_key'] = sanitize_text_field($input['live_agent_secret_key']);
    }

    // Live Agent Integration
    if (isset($input['live_agent_bot_token'])) {
        $new_input['live_agent_bot_token'] = sanitize_text_field($input['live_agent_bot_token']);
    }
    
    if (isset($input['live_agent_user_ids'])) {
    $new_input['live_agent_user_ids'] = sanitize_textarea_field($input['live_agent_user_ids']);
    }

    if (isset($input['live_agent_status'])) {
        $new_input['live_agent_status'] = ($input['live_agent_status'] === 'on') ? 'on' : 'off';
    }
    if (isset($input['live_agent_away_message'])) {
        $new_input['live_agent_away_message'] = sanitize_textarea_field($input['live_agent_away_message']);
    }
    if (isset($input['live_agent_notification_message'])) {
        $new_input['live_agent_notification_message'] = sanitize_textarea_field($input['live_agent_notification_message']);
    }

    return $new_input;
}


    // Method to append the chatbot to the body
    public function mxchat_append_chatbot_to_body() {
        $options = get_option('mxchat_options');
        if (isset($options['append_to_body']) && $options['append_to_body'] === 'on') {
            echo do_shortcode('[mxchat_chatbot floating="yes"]');
        }
    }





private function mxchat_fetch_loops_mailing_lists($api_key) {
    $url = 'https://app.loops.so/api/v1/lists';
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return array();
    }

    $body = wp_remote_retrieve_body($response);
    $lists = json_decode($body, true);

    return isset($lists) && is_array($lists) ? $lists : array();
}

function mxchat_calculate_cosine_similarity($vec1, $vec2) {
    if (empty($vec1) || empty($vec2)) {
        return 0.0;
    }

    $dot_product = 0.0;
    $norm_a = 0.0;
    $norm_b = 0.0;

    for ($i = 0; $i < count($vec1); $i++) {
        $dot_product += $vec1[$i] * $vec2[$i];
        $norm_a += pow($vec1[$i], 2);
        $norm_b += pow($vec2[$i], 2);
    }

    if ($norm_a == 0.0 || $norm_b == 0.0) {
        return 0.0;
    } else {
        return $dot_product / (sqrt($norm_a) * sqrt($norm_b));
    }
}

/**
 * Validates nonce and deletes all chat prompts
 */
public function mxchat_handle_delete_all_prompts() {
    // Verify nonce
    if (!isset($_POST['mxchat_delete_all_prompts_nonce']) || !wp_verify_nonce($_POST['mxchat_delete_all_prompts_nonce'], 'mxchat_delete_all_prompts_action')) {
        wp_die(__('Nonce verification failed.', 'mxchat'));
    }
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to delete all prompts.', 'mxchat'));
    }
    $success = true;
    $error_messages = array();
    // Check if Pinecone is enabled and configured
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';
    if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
        //error_log('[MXCHAT-DELETE] Pinecone is enabled - deleting all from Pinecone');
        // Get Pinecone configuration
        $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
           $pinecone_manager = new MxChat_Pinecone_Manager();
           $result = $pinecone_manager->mxchat_delete_all_from_pinecone($pinecone_options);
        if (!$result['success']) {
            $success = false;
            $error_messages[] = $result['message'];
        }
        // Clear Pinecone vector cache
        delete_option('mxchat_pinecone_vector_ids_cache');
        // Clear processed content caches
        delete_option('mxchat_pinecone_processed_cache');
        delete_option('mxchat_processed_content_cache');
        
        // CLEAR THE PINECONE RECORDS CACHE (NEW LINE)
        delete_transient('mxchat_pinecone_recent_1k_cache');
        
    } else {
        //error_log('[MXCHAT-DELETE] Pinecone not enabled - deleting from WordPress database');
        // Delete from WordPress database
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
        $result = $wpdb->query("DELETE FROM {$table_name}");
        if ($result === false) {
            $success = false;
            $error_messages[] = 'Failed to delete from WordPress database';
        }
    }
    // Clear relevant cache
    wp_cache_delete('all_prompts', 'mxchat_prompts');
    // Set appropriate admin notice
    if ($success) {
        set_transient('mxchat_admin_notice_success',
            __('All prompts deleted successfully.', 'mxchat'), 30);
    } else {
        set_transient('mxchat_admin_notice_error',
            __('Failed to delete all prompts: ', 'mxchat') . implode(', ', $error_messages), 30);
    }
    // Redirect back with a success message
    $redirect_url = add_query_arg(array(
        'page' => 'mxchat-prompts',
        'all_deleted' => $success ? 'true' : 'false'
    ), admin_url('admin.php'));
    wp_safe_redirect($redirect_url);
    exit;
}

    /**
     * Handles deletion prompt with nonce validation
     */
     public function mxchat_handle_delete_prompt() {
         // Sanitize and validate nonce
         $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
         if (empty($nonce) || !wp_verify_nonce($nonce, 'mxchat_delete_prompt_nonce')) {
             wp_die(esc_html__('Nonce verification failed.', 'mxchat'));
         }

         // Check permissions
         if (!current_user_can('manage_options')) {
             wp_die(esc_html__('You do not have sufficient permissions to delete prompts.', 'mxchat'));
         }

         // Get ID and source parameters
         $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
         $source = isset($_GET['source']) ? sanitize_text_field($_GET['source']) : '';

         if (empty($id)) {
             wp_die(esc_html__('Invalid prompt ID.', 'mxchat'));
         }

         $success = false;
         $error_message = '';

         // Check if Pinecone is enabled and determine source automatically if not specified
         $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
         $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';

         // If source is not specified, determine based on Pinecone configuration
         if (empty($source)) {
             $source = $use_pinecone ? 'pinecone' : 'wordpress';
         }

         if ($source === 'pinecone' || $use_pinecone) {
             //error_log('[MXCHAT-DELETE] Deleting from Pinecone, ID: ' . $id);

             // Handle Pinecone deletion
             if (empty($pinecone_options['mxchat_pinecone_host']) ||
                 empty($pinecone_options['mxchat_pinecone_api_key'])) {
                 wp_die(esc_html__('Pinecone configuration is missing.', 'mxchat'));
             }

             // Delete from Pinecone using the vector ID directly
                $pinecone_manager = MxChat_Pinecone_Manager::get_instance();
                $result = $pinecone_manager->mxchat_delete_from_pinecone_by_vector_id(
                    $id,
                    $pinecone_options['mxchat_pinecone_api_key'],
                    $pinecone_options['mxchat_pinecone_host']
                );
             if ($result['success']) {
                 $success = true;

                 // Remove from vector cache
                    $pinecone_manager->mxchat_remove_from_pinecone_vector_cache($id);
                    $pinecone_manager->mxchat_remove_from_processed_content_caches($id);

                 set_transient('mxchat_admin_notice_success',
                     esc_html__('Vector deleted successfully from Pinecone.', 'mxchat'), 30);
             } else {
                 $error_message = $result['message'];
                 set_transient('mxchat_admin_notice_error',
                     esc_html__('Failed to delete from Pinecone: ', 'mxchat') . esc_html($error_message), 30);
             }
         } else {
             //error_log('[MXCHAT-DELETE] Deleting from WordPress database, ID: ' . $id);

             // Handle WordPress database deletion
             global $wpdb;
             $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';

             // Clear cache and delete prompt
             wp_cache_delete('prompt_' . $id, 'mxchat_prompts');

             $result = $wpdb->delete(
                 $table_name,
                 array('id' => intval($id)),
                 array('%d')
             );

             if ($result !== false) {
                 $success = true;
                 set_transient('mxchat_admin_notice_success',
                     esc_html__('Entry deleted successfully.', 'mxchat'), 30);
             } else {
                 set_transient('mxchat_admin_notice_error',
                     esc_html__('Failed to delete entry from database.', 'mxchat'), 30);
             }
         }

         // Redirect back to the prompts page
         wp_safe_redirect(add_query_arg(
             array(
                 'page' => 'mxchat-prompts',
                 'deleted' => $success ? 'true' : 'false'
             ),
             admin_url('admin.php')
         ));
         exit;
     }

    /**
     * Validates user permissions for editing chat settings
     */
     public function mxchat_handle_edit_intent() {
         // Security checks (nonce and permissions)
         if (!current_user_can('manage_options')) {
             wp_die(esc_html__('Unauthorized user', 'mxchat'));
         }
         check_admin_referer('mxchat_edit_intent');

         // Get POST data
         $intent_id = isset($_POST['intent_id']) ? absint($_POST['intent_id']) : 0;
         $intent_label = isset($_POST['intent_label']) ? sanitize_text_field($_POST['intent_label']) : '';
         $phrases_input = isset($_POST['phrases']) ? sanitize_textarea_field($_POST['phrases']) : '';
         $threshold_percentage = isset($_POST['similarity_threshold']) ? intval($_POST['similarity_threshold']) : 85;
         $similarity_threshold = min(95, max(70, $threshold_percentage)) / 100; // Convert to 0.70–0.95

         // Validate inputs
         if (!$intent_id || empty($intent_label) || empty($phrases_input)) {
             $this->handle_embedding_error(__('Invalid input. Please ensure all fields are filled out.', 'mxchat'));
             return;
         }

         $phrases_array = array_map('sanitize_text_field', array_filter(array_map('trim', explode(',', $phrases_input))));
         if (empty($phrases_array)) {
             $this->handle_embedding_error(__('Please enter at least one valid phrase.', 'mxchat'));
             return;
         }

         // Generate embeddings with improved error handling
         $vectors = [];
         $failed_phrases = [];

         foreach ($phrases_array as $phrase) {
             $embedding_vector = $this->mxchat_generate_embedding($phrase, $this->options['api_key']);
             if (is_array($embedding_vector)) {
                 $vectors[] = $embedding_vector;
             } else {
                 $failed_phrases[] = $phrase;
             }
         }

         if (!empty($failed_phrases)) {
             $this->handle_embedding_error(
                 sprintf(
                     __('Error generating embeddings for phrases: %s. Check your embedding API.', 'mxchat'),
                     implode(', ', $failed_phrases)
                 )
             );
             return;
         }

         if (empty($vectors)) {
             $this->handle_embedding_error(__('No valid embeddings generated. Please check your phrases.', 'mxchat'));
             return;
         }

         $combined_vector = $this->mxchat_average_vectors($vectors);
         $serialized_vector = maybe_serialize($combined_vector);

         // Update the database
         global $wpdb;
         $table_name = $wpdb->prefix . 'mxchat_intents';

         $result = $wpdb->update(
             $table_name,
             array(
                 'intent_label' => $intent_label,
                 'phrases' => implode(', ', $phrases_array),
                 'embedding_vector' => $serialized_vector,
                 'similarity_threshold' => $similarity_threshold
             ),
             array('id' => $intent_id),
             array('%s', '%s', '%s', '%f'), // Format: string, string, string, float
             array('%d')                   // Where format: integer
         );

         if (false === $result) {
             $this->handle_embedding_error(__('Failed to update action in database.', 'mxchat'));
             return;
         }

         // Set success message and redirect
         set_transient('mxchat_admin_notice_success', __('Intent updated successfully!', 'mxchat'), 60);

         $redirect_url = add_query_arg(
             array(
                 'page' => 'mxchat-actions'
             ),
             admin_url('admin.php')
         );
         wp_safe_redirect($redirect_url);
         exit;
     }
/**
 * Checks user permissions for managing options
 */
public function mxchat_handle_add_intent() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized user', 'mxchat'));
    }
    check_admin_referer('mxchat_add_intent_nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_intents';
    
    // Sanitize and get form data
    $intent_label = isset($_POST['intent_label']) ? sanitize_text_field($_POST['intent_label']) : '';
    $phrases_input = isset($_POST['phrases']) ? sanitize_textarea_field($_POST['phrases']) : '';
    $callback_function = isset($_POST['callback_function']) ? sanitize_text_field($_POST['callback_function']) : '';
    
    // Get similarity threshold from form (convert percentage to decimal)
    $similarity_threshold = isset($_POST['similarity_threshold']) ? floatval($_POST['similarity_threshold']) / 100 : 0.85;
    
    // Validate required fields
    if (empty($intent_label) || empty($callback_function) || empty($phrases_input)) {
        $this->handle_embedding_error(__('Invalid input. Please ensure all fields are filled out.', 'mxchat'));
        return;
    }
    
    // Validate callback function
    $available_callbacks = $this->mxchat_get_available_callbacks();
    if (!array_key_exists($callback_function, $available_callbacks)) {
        $this->handle_embedding_error(__('Invalid callback function selected.', 'mxchat'));
        return;
    }
    
    // Check Pro requirements
    $is_pro_only = $available_callbacks[$callback_function]['pro_only'];
    if ($is_pro_only && !$this->is_activated) {
        $this->handle_embedding_error(__('This callback function is available in the Pro version only.', 'mxchat'));
        return;
    }
    
    // Process phrases
    $phrases_array = array_map('sanitize_text_field', array_filter(array_map('trim', explode(',', $phrases_input))));
    if (empty($phrases_array)) {
        $this->handle_embedding_error(__('Please enter at least one valid phrase.', 'mxchat'));
        return;
    }
    
    // Generate embeddings with improved error handling
    $vectors = [];
    $failed_phrases = [];
    foreach ($phrases_array as $phrase) {
        $embedding_vector = $this->mxchat_generate_embedding($phrase, $this->options['api_key']);
        if (is_array($embedding_vector)) {
            $vectors[] = $embedding_vector;
        } else {
            $failed_phrases[] = $phrase;
        }
    }
    
    // Check for embedding failures
    if (!empty($failed_phrases)) {
        $this->handle_embedding_error(
            sprintf(
                __('Error generating embeddings for phrases: %s. Check your embedding API.', 'mxchat'),
                implode(', ', $failed_phrases)
            )
        );
        return;
    }
    
    if (empty($vectors)) {
        $this->handle_embedding_error(__('No valid embeddings generated. Please check your phrases.', 'mxchat'));
        return;
    }
    
    // Create combined vector and insert into database
    $combined_vector = $this->mxchat_average_vectors($vectors);
    $serialized_vector = maybe_serialize($combined_vector);
    
    $result = $wpdb->insert($table_name, [
        'intent_label'         => $intent_label,
        'phrases'              => implode(', ', $phrases_array),
        'embedding_vector'     => $serialized_vector,
        'callback_function'    => $callback_function,
        'similarity_threshold' => $similarity_threshold,  // Now uses the actual form value
    ]);
    
    if ($result === false) {
        $this->handle_embedding_error(__('Database error: ', 'mxchat') . $wpdb->last_error);
        return;
    }
    
    // Set success message and redirect
    set_transient('mxchat_admin_notice_success', __('New intent added successfully!', 'mxchat'), 60);
    wp_safe_redirect(admin_url('admin.php?page=mxchat-actions'));
    exit;
}
     
        /**
     * Averages multiple vectors into a single vector
     */
     private function mxchat_average_vectors($vectors) {
         $vector_length = count($vectors[0]);
         $sum_vector = array_fill(0, $vector_length, 0);

         foreach ($vectors as $vector) {
             for ($i = 0; $i < $vector_length; $i++) {
                 $sum_vector[$i] += $vector[$i];
             }
         }

         // Divide each component by the number of vectors to get the average
         $num_vectors = count($vectors);
         for ($i = 0; $i < $vector_length; $i++) {
             $sum_vector[$i] /= $num_vectors;
         }

         return $sum_vector;
     }
 
     
     
     
         /**
     * Generates embeddings from input text for MXChat
     */
     public function mxchat_generate_embedding($text) {
         // Enable detailed logging for debugging
         //error_log('[MXCHAT-EMBED] Starting embedding generation. Text length: ' . strlen($text) . ' bytes');
         //error_log('[MXCHAT-EMBED] Text preview: ' . substr($text, 0, 100) . '...');

         $options = get_option('mxchat_options');
         $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
         //error_log('[MXCHAT-EMBED] Selected embedding model: ' . $selected_model);

         // Determine provider and endpoint
         if (strpos($selected_model, 'voyage') === 0) {
             $api_key = $options['voyage_api_key'] ?? '';
             $endpoint = 'https://api.voyageai.com/v1/embeddings';
             $provider_name = 'Voyage AI';
             //error_log('[MXCHAT-EMBED] Using Voyage AI API');
         } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
             $api_key = $options['gemini_api_key'] ?? '';
             $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $selected_model . ':embedContent';
             $provider_name = 'Google Gemini';
             //error_log('[MXCHAT-EMBED] Using Google Gemini API');
         } else {
             $api_key = $options['api_key'] ?? '';
             $endpoint = 'https://api.openai.com/v1/embeddings';
             $provider_name = 'OpenAI';
             //error_log('[MXCHAT-EMBED] Using OpenAI API');
         }

         //error_log('[MXCHAT-EMBED] Using endpoint: ' . $endpoint);

         if (empty($api_key)) {
             $error_message = sprintf('Missing %s API key. Please configure your API key in the MxChat settings.', $provider_name);
             //error_log('[MXCHAT-EMBED] Error: ' . $error_message);
             return $error_message;
         }

         // Check if text is too long (for OpenAI, roughly estimate tokens as words/0.75)
         $estimated_tokens = ceil(str_word_count($text) / 0.75);
         //error_log('[MXCHAT-EMBED] Estimated token count: ~' . $estimated_tokens);

         if ($estimated_tokens > 8000 && strpos($selected_model, 'voyage') === false && strpos($selected_model, 'gemini-embedding') === false) {
             //error_log('[MXCHAT-EMBED] Warning: Text may exceed OpenAI token limits (8K for most models)');
             // Consider truncating text here
         }

         // Prepare request body based on provider
         if (strpos($selected_model, 'gemini-embedding') === 0) {
             // Gemini API format
             $request_body = array(
                 'model' => 'models/' . $selected_model,
                 'content' => array(
                     'parts' => array(
                         array('text' => $text)
                     )
                 )
             );

             // Set output dimensionality to 1536 for consistency with other models
             $request_body['outputDimensionality'] = 1536;
         } else {
             // OpenAI/Voyage API format
             $request_body = array(
                 'model' => $selected_model,
                 'input' => $text
             );

             // Add output_dimension for voyage-3-large model
             if ($selected_model === 'voyage-3-large') {
                 $request_body['output_dimension'] = 2048;
             }
         }

         //error_log('[MXCHAT-EMBED] Request prepared with model: ' . $selected_model);

         // Prepare headers based on provider
         if (strpos($selected_model, 'gemini-embedding') === 0) {
             // Gemini uses API key as query parameter
             $endpoint .= '?key=' . $api_key;
             $headers = array(
                 'Content-Type' => 'application/json'
             );
         } else {
             // OpenAI/Voyage use Bearer token
             $headers = array(
                 'Authorization' => 'Bearer ' . $api_key,
                 'Content-Type' => 'application/json'
             );
         }

         // Make API request
         //error_log('[MXCHAT-EMBED] Sending API request to: ' . $endpoint);
         $response = wp_remote_post($endpoint, array(
             'body' => wp_json_encode($request_body),
             'headers' => $headers,
             'timeout' => 60 // Increased timeout for large inputs
         ));

         // Handle wp_remote_post errors
         if (is_wp_error($response)) {
             $error_message = $response->get_error_message();
             //error_log('[MXCHAT-EMBED] WP Remote Post Error: ' . $error_message);
             return 'Connection error: ' . $error_message;
         }

         // Get and check HTTP response code
         $http_code = wp_remote_retrieve_response_code($response);
         //error_log('[MXCHAT-EMBED] API Response Code: ' . $http_code);

         if ($http_code !== 200) {
             $error_body = wp_remote_retrieve_body($response);
             //error_log('[MXCHAT-EMBED] API Error Response Body: ' . $error_body);

             // Try to parse error for more details
             $error_json = json_decode($error_body, true);
             if (json_last_error() === JSON_ERROR_NONE && isset($error_json['error'])) {
                 $error_type = $error_json['error']['type'] ?? 'unknown';
                 $error_message = $error_json['error']['message'] ?? 'No message';
                 //error_log('[MXCHAT-EMBED] API Error Type: ' . $error_type);
                 //error_log('[MXCHAT-EMBED] API Error Message: ' . $error_message);

                 // Customize error message for common API errors
                 if ($error_type === 'invalid_request_error' && strpos($error_message, 'API key') !== false) {
                     $error_message = sprintf('Invalid %s API key. Please check your API key in the MxChat settings.', $provider_name);
                 } elseif ($error_type === 'authentication_error') {
                     $error_message = sprintf('%s authentication failed. Please verify your API key in the MxChat settings.', $provider_name);
                 }

                 //error_log('[MXCHAT-EMBED] Returning error: ' . $error_message);
                 return $error_message;
             }

             $error_message = sprintf("API Error (HTTP %d): Unable to generate embedding", $http_code);
             //error_log('[MXCHAT-EMBED] Returning error: ' . $error_message);
             return $error_message;
         }

         // Parse response body
         $response_body = wp_remote_retrieve_body($response);
         //error_log('[MXCHAT-EMBED] Received response length: ' . strlen($response_body) . ' bytes');

         $response_data = json_decode($response_body, true);

         if (json_last_error() !== JSON_ERROR_NONE) {
             $error = json_last_error_msg();
             //error_log('[MXCHAT-EMBED] JSON Parse Error: ' . $error);
             //error_log('[MXCHAT-EMBED] Response preview: ' . substr($response_body, 0, 200));
             return "Failed to parse API response: $error";
         }

         // Handle different response formats based on provider
         if (strpos($selected_model, 'gemini-embedding') === 0) {
             // Gemini API response format
             if (isset($response_data['embedding']['values'])) {
                 $embedding_dimensions = count($response_data['embedding']['values']);
                 //error_log('[MXCHAT-EMBED] Successfully extracted Gemini embedding with ' . $embedding_dimensions . ' dimensions');

                 // Check if embedding dimensions are as expected (should be 1536)
                 if ($embedding_dimensions !== 1536) {
                     //error_log('[MXCHAT-EMBED] Warning: Unexpected Gemini embedding dimensions: ' . $embedding_dimensions);
                 }

                 return $response_data['embedding']['values'];
             } else {
                 //error_log('[MXCHAT-EMBED] Error: No embedding found in Gemini response');
                 //error_log('[MXCHAT-EMBED] Response structure: ' . wp_json_encode(array_keys($response_data)));

                 if (isset($response_data['error'])) {
                     $error_message = "Gemini API Error in response: " . wp_json_encode($response_data['error']);
                     //error_log('[MXCHAT-EMBED] ' . $error_message);
                     return $error_message;
                 }

                 $error_message = "Invalid Gemini API response format: No embedding found";
                 //error_log('[MXCHAT-EMBED] ' . $error_message);
                 return $error_message;
             }
         } else {
             // OpenAI/Voyage API response format
             if (isset($response_data['data'][0]['embedding'])) {
                 $embedding_dimensions = count($response_data['data'][0]['embedding']);
                 //error_log('[MXCHAT-EMBED] Successfully extracted embedding with ' . $embedding_dimensions . ' dimensions');

                 // Check if embedding dimensions are as expected
                 if (($selected_model === 'text-embedding-ada-002' && $embedding_dimensions !== 1536) ||
                     ($selected_model === 'voyage-3-large' && $embedding_dimensions !== 2048)) {
                     //error_log('[MXCHAT-EMBED] Warning: Unexpected embedding dimensions');
                 }

                 return $response_data['data'][0]['embedding'];
             } else {
                 //error_log('[MXCHAT-EMBED] Error: No embedding found in response');
                 //error_log('[MXCHAT-EMBED] Response structure: ' . wp_json_encode(array_keys($response_data)));

                 if (isset($response_data['error'])) {
                     $error_message = "API Error in response: " . wp_json_encode($response_data['error']);
                     //error_log('[MXCHAT-EMBED] ' . $error_message);
                     return $error_message;
                 }

                 $error_message = "Invalid API response format: No embedding found";
                 //error_log('[MXCHAT-EMBED] ' . $error_message);
                 return $error_message;
             }
         }
     }



}
?>
