<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MxChat_Public {
    private $options;

    public function __construct() {
        // Simply get the options without defining duplicated defaults
        $this->options = get_option('mxchat_options', array());
        add_shortcode('mxchat_chatbot', array($this, 'render_chatbot_shortcode'));
        add_action('wp_footer', array($this, 'append_chatbot_to_body'));
        
        // Initialize testing panel for admins
        add_action('wp_enqueue_scripts', array($this, 'enqueue_testing_assets'));
    }

    /**
     * Enqueue testing panel assets for admin users
     */
    public function enqueue_testing_assets() {
        // Only load for admin users or if testing parameter is present
        if (current_user_can('administrator') || isset($_GET['mxchat_test'])) {
            
            // Get plugin URL for assets
            $plugin_url = plugin_dir_url(dirname(__FILE__));
            
            // Enqueue test panel CSS
            wp_enqueue_style(
                'mxchat-test-panel',
                $plugin_url . 'css/test-panel.css',
                array(),
                '1.0.0'
            );
            
            // Enqueue test panel JS
            wp_enqueue_script(
                'mxchat-test-panel',
                $plugin_url . 'js/test-panel.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            // Pass data to JavaScript
            wp_localize_script('mxchat-test-panel', 'mxchatTestData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mxchat_test_nonce'),
                'isAdmin' => current_user_can('administrator'),
                'testingEnabled' => true
            ));
            
            // Add JavaScript flag to enable testing
            add_action('wp_footer', array($this, 'add_testing_flag'));
        }
    }

    /**
     * Add JavaScript flag to enable testing panel
     */
    public function add_testing_flag() {
        echo '<script>window.mxchatTestingEnabled = true;</script>';
    }

    /**
     * Check if testing mode should be enabled
     */
    private function is_testing_mode_enabled() {
        return (current_user_can('administrator') || isset($_GET['mxchat_test']));
    }

    public function append_chatbot_to_body() {
        // Use only the options we need with isset checks
        $consent_category = 'marketing';
        
        if (isset($this->options['append_to_body']) && $this->options['append_to_body'] === 'on') {
            // Store consent status but always render
            $has_consent = true;
            
            if (
                isset($this->options['complianz_toggle']) && 
                $this->options['complianz_toggle'] === 'on' &&
                function_exists('cmplz_has_consent')
            ) {
                $has_consent = cmplz_has_consent($consent_category);
            }
            
            // Add consent status as a parameter to the shortcode
            echo do_shortcode('[mxchat_chatbot floating="yes" has_consent="' . ($has_consent ? 'yes' : 'no') . '"]');
        }
    }

    private function mxchat_get_user_identifier() {
        return sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }

    public function render_chatbot_shortcode($atts) {
        $attributes = shortcode_atts(array(
            'floating' => 'yes',
            'has_consent' => 'yes'  // Add default
        ), $atts);

        $is_floating = $attributes['floating'] === 'yes';
        
        // Check for Complianz consent if the toggle is enabled
        $initial_visibility = 'hidden';
        $additional_class = '';
        
        if (isset($this->options['complianz_toggle']) && $this->options['complianz_toggle'] === 'on') {
            // If Complianz is enabled, add no-consent class by default
            $additional_class = ' no-consent';
        }
        $visibility_class = $initial_visibility . $additional_class;

        // Use null coalescing operator (??) for fallbacks instead of having duplicate defaults
        $bg_color = $this->options['chatbot_background_color'] ?? '#fff';
        $user_message_bg_color = $this->options['user_message_bg_color'] ?? '#fff';
        $user_message_font_color = $this->options['user_message_font_color'] ?? '#212121';
        $bot_message_bg_color = $this->options['bot_message_bg_color'] ?? '#212121';
        $bot_message_font_color = $this->options['bot_message_font_color'] ?? '#fff';
        $top_bar_bg_color = $this->options['top_bar_bg_color'] ?? '#212121';
        $send_button_font_color = $this->options['send_button_font_color'] ?? '#212121';
        $intro_message = $this->options['intro_message'] ?? esc_html__('Hello! How can I assist you today?', 'mxchat');
        $top_bar_title = $this->options['top_bar_title'] ?? esc_html__('MxChat: Basic', 'mxchat');
        $chatbot_background_color = $this->options['chatbot_background_color'] ?? '#212121';
        $icon_color = $this->options['icon_color'] ?? '#fff';
        $chat_input_font_color = $this->options['chat_input_font_color'] ?? '#212121';
        $close_button_color = $this->options['close_button_color'] ?? '#fff';
        $chatbot_bg_color = $this->options['chatbot_bg_color'] ?? '#fff';
        $pre_chat_message = isset($this->options['pre_chat_message']) ? sanitize_text_field(trim($this->options['pre_chat_message'])) : '';
        $user_id = sanitize_key($this->mxchat_get_user_identifier());
        $transient_key = 'mxchat_pre_chat_message_dismissed_' . $user_id;
        $input_copy = isset($this->options['input_copy']) ? esc_attr($this->options['input_copy']) : esc_attr__('How can I assist?', 'mxchat');
        $rate_limit_message = isset($this->options['rate_limit_message']) ? esc_attr($this->options['rate_limit_message']) : esc_attr__('Rate limit exceeded. Please try again later.', 'mxchat');
        $mode_indicator_bg_color = $this->options['mode_indicator_bg_color'] ?? '#212121';
        $mode_indicator_font_color = $this->options['mode_indicator_font_color'] ?? '#fff';
        
        $privacy_toggle = isset($this->options['privacy_toggle']) && $this->options['privacy_toggle'] === 'on';
        $privacy_text = isset($this->options['privacy_text']) ? wp_kses_post($this->options['privacy_text']) : wp_kses_post(__('By chatting, you agree to our <a href="https://example.com/privacy-policy" target="_blank">privacy policy</a>.', 'mxchat'));

        $popular_question_1 = isset($this->options['popular_question_1']) ? esc_html($this->options['popular_question_1']) : '';
        $popular_question_2 = isset($this->options['popular_question_2']) ? esc_html($this->options['popular_question_2']) : '';
        $popular_question_3 = isset($this->options['popular_question_3']) ? esc_html($this->options['popular_question_3']) : '';
        $additional_questions = isset($this->options['additional_popular_questions']) ? $this->options['additional_popular_questions'] : [];
        $custom_icon = isset($this->options['custom_icon']) ? esc_url($this->options['custom_icon']) : '';
        $title_icon = isset($this->options['title_icon']) ? esc_url($this->options['title_icon']) : '';
        $ai_agent_text = isset($this->options['ai_agent_text']) ? esc_html($this->options['ai_agent_text']) : esc_html__('AI Agent', 'mxchat');

        $live_agent_message_bg_color = $this->options['live_agent_message_bg_color'] ?? '#212121';
        $live_agent_message_font_color = $this->options['live_agent_message_font_color'] ?? '#fff';
        $enable_email_block = isset($this->options['enable_email_block']) && 
            ($this->options['enable_email_block'] === '1' || $this->options['enable_email_block'] === 'on');

        ob_start();

        // Check if floating attribute is set to 'yes' and wrap accordingly
        if ($is_floating) {
            echo '<div id="floating-chatbot" class="' . $initial_visibility . $additional_class . '">';
        }

        echo '<div id="mxchat-chatbot-wrapper">';
        echo '  <div class="chatbot-top-bar" id="exit-chat-button" style="background: ' . esc_attr($top_bar_bg_color) . ';">';
        echo '      <div class="chatbot-title-container">';
        echo '          <div class="chatbot-title-group">';
        if (!empty($title_icon)) {
            echo '              <img src="' . esc_url($title_icon) . '" alt="" class="chatbot-title-icon">';
        }
        echo '              <p class="chatbot-title" style="color: ' . esc_attr($close_button_color) . ';">' . esc_html($top_bar_title) . '</p>';
        echo '          </div>';
        echo '<span class="chat-mode-indicator" id="chat-mode-indicator" data-ai-text="' . $ai_agent_text . '" style="color: ' . esc_attr($mode_indicator_font_color) . '; background-color: ' . esc_attr($mode_indicator_bg_color) . ';">' . $ai_agent_text . '</span>';
        echo '      </div>';
        echo '      <button class="exit-chat" type="button" aria-label="' . esc_attr__('Minimize', 'mxchat') . '" style="color: ' . esc_attr($close_button_color) . ';">';
        echo '          <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" id="ic-minimize" style="fill: ' . esc_attr($close_button_color) . ';">';
        echo '              <path d="M0 0h24v24H0z" fill="none"></path>';
        echo '              <path d="M11.67 3.87L9.9 2.1 0 12l9.9 9.9 1.77-1.77L3.54 12z"></path>';
        echo '          </svg>';
        echo '          <span>' . esc_html__('Minimize', 'mxchat') . '</span>';
        echo '      </button>';
        echo '  </div>';
        
        // 3b) Main chatbot container
        echo '  <div id="mxchat-chatbot" style="background-color: ' . esc_attr($chatbot_bg_color) . ';">';
            
        if ($enable_email_block) {
            echo '<div id="email-blocker" class="email-blocker">';
            echo '    <div class="email-blocker-header">';
        
            // Safely echo the user-provided HTML
            // (We rely on wp_kses_post() from the sanitize step to ensure it's safe)
            $header_html = isset($this->options['email_blocker_header_content'])
                ? $this->options['email_blocker_header_content']
                : '';
            echo wp_kses_post($header_html);
        
            echo '    </div>';
            echo '    <form id="email-collection-form">';
            echo '        <label for="user-email" class="sr-only">' . esc_html__('Email Address', 'mxchat') . '</label>';
            echo '        <input type="email" id="user-email" name="user_email" required placeholder="' . esc_attr__('Enter your email address', 'mxchat') . '" />';
            echo '<button type="submit" id="email-submit-button">';
                // If not set, fallback to default. Typically, $this->options includes defaults merged in.
                $button_text = isset($this->options['email_blocker_button_text'])
                    ? $this->options['email_blocker_button_text']
                    : esc_html__('Start Chat', 'mxchat'); // fallback just in case
                echo esc_html($button_text);
            echo '</button>';
            echo '    </form>';
            echo '</div>';
        }
    
        echo '      <div id="chat-container" style="' . ($enable_email_block ? 'display: none;' : '') . '">';
        echo '          <div id="chat-box">';
        echo '              <div class="bot-message" style="background: ' . esc_attr($bot_message_bg_color) . ';">';
        echo '                  <div dir="auto" style="color: ' . esc_attr($bot_message_font_color) . ';">';
        echo                        wp_kses_post($intro_message);  
        echo '                  </div>';
        echo '              </div>';
        echo '          </div>';  // end #chat-box
        
        // Add the popular questions section
        echo '          <div id="mxchat-popular-questions">';
        echo '              <div class="mxchat-popular-questions-container">';
        
        if (!empty($popular_question_1)) {
            echo '<button class="mxchat-popular-question" dir="auto">' . esc_html($popular_question_1) . '</button>';
        }
        if (!empty($popular_question_2)) {
            echo '<button class="mxchat-popular-question" dir="auto">' . esc_html($popular_question_2) . '</button>';
        }
        if (!empty($popular_question_3)) {
            echo '<button class="mxchat-popular-question" dir="auto">' . esc_html($popular_question_3) . '</button>';
        }
        
        if (!empty($additional_questions) && is_array($additional_questions)) {
            foreach ($additional_questions as $index => $question) {
                if (!empty($question)) {
                    echo '<button class="mxchat-popular-question" dir="auto">' . esc_html($question) . '</button>';
                }
            }
        }
        
        echo '              </div>';
        echo '          </div>';

        echo '          <div id="input-container">';
        echo '              <textarea id="chat-input" dir="auto" placeholder="' . esc_attr($input_copy) . '" style="color: ' . esc_attr($chat_input_font_color) . ';"></textarea>';
        echo '              <button id="send-button">';
        echo '                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="fill: ' . esc_attr($send_button_font_color) . ';">';
        echo '                      <path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/>';
        echo '                  </svg>';
        echo '              </button>';
        echo '          </div>';
        
        echo '          <div class="chat-toolbar">';
        
        // PDF Upload Button - wrapped in conditional
        $show_pdf_button = isset($this->options['show_pdf_upload_button']) ? $this->options['show_pdf_upload_button'] : 'on';
        if ($show_pdf_button === 'on') {
            echo '              <input type="file" id="pdf-upload" accept=".pdf" style="display: none;">';
            echo '              <button id="pdf-upload-btn" class="toolbar-btn" title="' . esc_attr__('Upload PDF', 'mxchat') . '">';
            echo '                  <!-- Icon from Font Awesome Free: https://fontawesome.com/license/free -->';
            echo '                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke="currentColor">';
            echo '                      <path d="M64 464l48 0 0 48-48 0c-35.3 0-64-28.7-64-64L0 64C0 28.7 28.7 0 64 0L229.5 0c17 0 33.3 6.7 45.3 18.7l90.5 90.5c12 12 18.7 28.3 18.7 45.3L384 304l-48 0 0-144-80 0c-17.7 0-32-14.3-32-32l0-80L64 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16zM176 352l32 0c30.9 0 56 25.1 56 56s-25.1 56-56 56l-16 0 0 32c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-48 0-80c0-8.8 7.2-16 16-16zm32 80c13.3 0 24-10.7 24-24s-10.7-24-24-24l-16 0 0 48 16 0zm96-80l32 0c26.5 0 48 21.5 48 48l0 64c0 26.5-21.5 48-48 48l-32 0c-8.8 0-16-7.2-16-16l0-128c0-8.8 7.2-16 16-16zm32 128c8.8 0 16-7.2 16-16l0-64c0-8.8-7.2-16-16-16l-16 0 0 96 16 0zm80-112c0-8.8 7.2-16 16-16l48 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 32 32 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-32 0 0 48c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64 0-64z"></path>';
            echo '                  </svg>';
            echo '              </button>';
        }
        
        // Word Upload Button - wrapped in conditional
        $show_word_button = isset($this->options['show_word_upload_button']) ? $this->options['show_word_upload_button'] : 'on';
        if ($show_word_button === 'on') {
            echo '              <input type="file" id="word-upload" accept=".docx" style="display: none;">';
            echo '              <button id="word-upload-btn" class="toolbar-btn" title="' . esc_attr__('Upload Word Document', 'mxchat') . '">';
            echo '                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" stroke="currentColor">';
            echo '                      <path d="M48 448L48 64c0-8.8 7.2-16 16-16l160 0 0 80c0 17.7 14.3 32 32 32l80 0 0 288c0 8.8-7.2 16-16 16L64 464c-8.8 0-16-7.2-16-16zM64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-293.5c0-17-6.7-33.3-18.7-45.3L274.7 18.7C262.7 6.7 246.5 0 229.5 0L64 0zm55 241.1c-3.8-12.7-17.2-19.9-29.9-16.1s-19.9 17.2-16.1 29.9l48 160c3 10.2 12.4 17.1 23 17.1s19.9-7 23-17.1l25-83.4 25 83.4c3 10.2 12.4 17.1 23 17.1s19.9-7 23-17.1l48-160c3.8-12.7-3.4-26.1-16.1-29.9s-26.1 3.4-29.9 16.1l-25 83.4-25-83.4c-3-10.2-12.4-17.1-23-17.1s-19.9 7-23 17.1l-25 83.4-25-83.4z"/></svg>';
            echo '              </button>';
        }
                
        // File containers (unchanged)
        echo '              <div id="active-pdf-container" class="active-pdf-container" style="display: none;">';
        echo '                  <span id="active-pdf-name" class="active-pdf-name"></span>';
        echo '                  <button id="remove-pdf-btn" class="remove-pdf-btn" title="' . esc_attr__('Remove PDF', 'mxchat') . '">';
        echo '                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">';
        echo '                          <line x1="18" y1="6" x2="6" y2="18"></line>';
        echo '                          <line x1="6" y1="6" x2="18" y2="18"></line>';
        echo '                      </svg>';
        echo '                  </button>';
        echo '              </div>';
        
        echo '              <div id="active-word-container" class="active-pdf-container" style="display: none;">';
        echo '                  <span id="active-word-name" class="active-pdf-name"></span>';
        echo '                  <button id="remove-word-btn" class="remove-pdf-btn" title="' . esc_attr__('Remove Word Document', 'mxchat') . '">';
        echo '                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2">';
        echo '                          <line x1="18" y1="6" x2="6" y2="18"></line>';
        echo '                          <line x1="6" y1="6" x2="18" y2="18"></line>';
        echo '                      </svg>';
        echo '                  </button>';
        echo '              </div>';
        
        // Perplexity Button (unchanged except for the conditional)
        if (apply_filters('mxchat_perplexity_should_show_logo', true)) {
            echo '              <button id="perplexity-search-btn" class="toolbar-btn" title="' . esc_attr__('Search with Perplexity', 'mxchat-perplexity') . '">';
            echo '                  <svg fill="currentColor" height="1em" viewBox="0 0 24 24" width="1em" xmlns="http://www.w3.org/2000/svg">';
            echo '                      <path d="M19.785 0v7.272H22.5V17.62h-2.935V24l-7.037-6.194v6.145h-1.091v-6.152L4.392 24v-6.465H1.5V7.188h2.884V0l7.053 6.494V.19h1.09v6.49L19.786 0zm-7.257 9.044v7.319l5.946 5.234V14.44l-5.946-5.397zm-1.099-.08l-5.946 5.398v7.235l5.946-5.234V8.965zm8.136 7.58h1.844V8.349H13.46l6.105 5.54v2.655zm-8.982-8.28H2.59v8.195h1.8v-2.576l6.192-5.62zM5.475 2.476v4.71h5.115l-5.115-4.71zm13.219 0l-5.115 4.71h5.115v-4.71z"></path>';
            echo '                  </svg>';
            echo '              </button>';
        }
                
        echo '          </div>';
        
        echo '          <div class="chatbot-footer">';

                // Output the privacy notice if enabled
                if ($privacy_toggle && !empty($privacy_text)) {
                    echo '<p class="privacy-notice">' . $privacy_text . '</p>';
                }

        echo '          </div>';
        echo '      </div>';
        echo '  </div>';
        echo '</div>';

        if ($is_floating) {
            echo '</div>';

            if (!empty($pre_chat_message) && !get_transient($transient_key)) {
                echo '<div id="pre-chat-message">';
                echo esc_html($pre_chat_message);
                echo '<button class="close-pre-chat-message" aria-label="' . esc_attr__('Close', 'mxchat') . '">&times;</button>';
                echo '</div>';
            }

            echo '<div class="' . esc_attr($visibility_class) . '" id="floating-chatbot-button" style="background: ' . esc_attr($chatbot_background_color) . '; color: ' . esc_attr($send_button_font_color) . ';">';
            echo '<div id="chat-notification-badge" class="chat-notification-badge" style="display: none; position: absolute; top: -8px; right: -8px; background-color: #ff4444; color: white; border-radius: 50%; padding: 4px 8px; font-size: 12px; font-weight: bold; z-index: 10001;">1</div>';

            if (!empty($custom_icon)) {
                echo '<img src="' . $custom_icon . '" alt="' . esc_attr__('Chatbot Icon', 'mxchat') . '" style="height: 48px; width: 48px; object-fit: contain;" />';
            } else {
                echo '<svg id="widget_icon_10" style="height: 48px; width: 48px; fill: ' . esc_attr($icon_color) . '" viewBox="0 0 1120 1120" fill="none" xmlns="http://www.w3.org/2000/svg">';
                echo '  <path fill-rule="evenodd" clip-rule="evenodd" d="M252 434C252 372.144 302.144 322 364 322H770C831.856 322 882 372.144 882 434V614.459L804.595 585.816C802.551 585.06 800.94 583.449 800.184 581.405L763.003 480.924C760.597 474.424 751.403 474.424 748.997 480.924L711.816 581.405C711.06 583.449 709.449 585.06 707.405 585.816L606.924 622.997C600.424 625.403 600.424 634.597 606.924 637.003L707.405 674.184C709.449 674.94 711.06 676.551 711.816 678.595L740.459 756H629.927C629.648 756.476 629.337 756.945 628.993 757.404L578.197 825.082C572.597 832.543 561.403 832.543 555.803 825.082L505.007 757.404C504.663 756.945 504.352 756.476 504.073 756H364C302.144 756 252 705.856 252 644V434ZM633.501 471.462C632.299 468.212 627.701 468.212 626.499 471.462L619.252 491.046C618.874 492.068 618.068 492.874 617.046 493.252L597.462 500.499C594.212 501.701 594.212 506.299 597.462 507.501L617.046 514.748C618.068 515.126 618.874 515.932 619.252 516.954L626.499 536.538C627.701 539.788 632.299 539.788 633.501 536.538L640.748 516.954C641.126 515.932 641.932 515.126 642.954 514.748L662.538 507.501C665.788 506.299 665.788 501.701 662.538 500.499L642.954 493.252C641.932 492.874 641.126 492.068 640.748 491.046L633.501 471.462Z" ></path>';
                echo '  <path d="M771.545 755.99C832.175 755.17 881.17 706.175 881.99 645.545L804.595 674.184C802.551 674.94 800.94 676.551 800.184 678.595L771.545 755.99Z" ></path>';
                echo '</svg>';
            }
            echo '</div>';
        }

        return ob_get_clean();
    }
}
?>