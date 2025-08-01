<?php
/**
 * File: admin/class-ajax-handler.php
 *
 * Handles all AJAX requests for MxChat admin functionality
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MxChat_Ajax_Handler {
    
    private $pinecone_manager = null;
        
    /**
     * Constructor - Register all AJAX hooks
     */
    public function __construct() {
        $this->mxchat_init_ajax_hooks();
    }
    

    /**
     * Register all AJAX action hooks
     */
    private function mxchat_init_ajax_hooks() {
        // Settings AJAX
        add_action('wp_ajax_mxchat_save_setting', array($this, 'mxchat_save_setting_callback'));
        add_action('wp_ajax_mxchat_save_prompts_setting', array($this, 'mxchat_save_prompts_setting_callback'));
        add_action('wp_ajax_migrate_pinecone_settings', array($this, 'ajax_migrate_pinecone_settings'));

        // License AJAX
        add_action('wp_ajax_mxchat_activate_license', array($this, 'mxchat_handle_activate_license'));
        add_action('wp_ajax_mxchat_check_license', array($this, 'mxchat_check_license_status'));

        // Actions & Intents AJAX
        add_action('wp_ajax_mxchat_toggle_action', array($this, 'mxchat_toggle_action'));
        add_action('wp_ajax_mxchat_update_intent_threshold', array($this, 'mxchat_update_intent_threshold'));
    }

    // ========================================
    // SETTINGS AJAX HANDLERS
    // ========================================

    /**
     * Validates and saves chat settings via AJAX request
     */
     public function mxchat_save_setting_callback() {
         check_ajax_referer('mxchat_save_setting_nonce');
         if (!current_user_can('manage_options')) {
             ('MXChat Save: Unauthorized access attempt');
             wp_send_json_error(['message' => esc_html__('Unauthorized', 'mxchat')]);
         }

         $name = isset($_POST['name']) ? $_POST['name'] : '';
         // Strip slashes from the value before saving
         $value = isset($_POST['value']) ? stripslashes($_POST['value']) : '';

         //error_log('MXChat Save: Processing field name: ' . $name);
         //error_log('MXChat Save: Field value: ' . $value);

         if (empty($name)) {
             //error_log('MXChat Save: Empty field name detected');
             wp_send_json_error(['message' => esc_html__('Invalid field name', 'mxchat')]);
         }

         // Load the full options array
         $options = get_option('mxchat_options', []);
         //error_log('MXChat Save: Current options array: ' . print_r($options, true));

         // Handle special cases
         switch ($name) {
             case 'additional_popular_questions':
                 //error_log('MXChat Save: Processing additional_popular_questions');
                 $questions = json_decode($value, true); // No need for stripslashes here
                 if (is_array($questions)) {
                     $options[$name] = $questions;
                     // Also update old option for backwards compatibility
                     update_option('additional_popular_questions', $questions);
                     //error_log('MXChat Save: Saved ' . count($questions) . ' additional questions');
                 } else {
                     //error_log('MXChat Save: Failed to decode questions JSON');
                 }
                 break;
             case 'email_blocker_header_content':
                 //error_log('MXChat Save: Processing email_blocker_header_content');
                 // Allow HTML content but sanitize it safely
                 $options[$name] = wp_kses_post($value);
                 break;
             case 'similarity_threshold':
                 //error_log('MXChat Save: Processing similarity_threshold');
                 // Save to the options array
                 $options[$name] = $value;
                 break;
             case 'user_message_bg_color':
             case 'user_message_font_color':
             case 'bot_message_bg_color':
             case 'bot_message_font_color':
             case 'top_bar_bg_color':
             case 'send_button_font_color':
             case 'chatbot_background_color':
             case 'icon_color':
             case 'chat_input_font_color':
             case 'live_agent_message_bg_color':
             case 'live_agent_message_font_color':
             case 'mode_indicator_bg_color':
             case 'mode_indicator_font_color':
             case 'toolbar_icon_color':
                 //error_log('MXChat Save: Processing color value: ' . $name);
                 // Store color values directly
                 $options[$name] = $value;
                 break;
             case 'live_agent_status':
                 //error_log('MXChat Save: Processing live_agent_status');
                 // Set the new value
                 $options[$name] = ($value === 'on') ? 'on' : 'off';
                 break;
             case 'enable_woocommerce_integration':
                 //error_log('MXChat Save: Processing enable_woocommerce_integration');
                 // Handle values that used to be 1/0
                 $options[$name] = ($value === 'on' || $value === '1') ? 'on' : 'off';
                 break;
             default:
                 // First check for rate limits settings
                 if (strpos($name, 'mxchat_options[rate_limits]') !== false) {
                     //error_log('MXChat Save: Detected rate_limits field: ' . $name);

                     // Extract role ID and setting from the name
                     preg_match('/\[rate_limits\]\[(.*?)\]\[(.*?)\]/', $name, $matches);
                     //error_log('MXChat Save: Regex matches: ' . print_r($matches, true));

                     if (isset($matches[1]) && isset($matches[2])) {
                         $role_id = $matches[1];
                         $setting_key = $matches[2]; // limit, timeframe, or message

                         //error_log('MXChat Save: Role ID = ' . $role_id . ', Setting Key = ' . $setting_key);

                         // Initialize rate_limits if it doesn't exist
                         if (!isset($options['rate_limits'])) {
                            // //error_log('MXChat Save: Initializing rate_limits array');
                             $options['rate_limits'] = [];
                         }

                         // Initialize role settings if it doesn't exist
                         if (!isset($options['rate_limits'][$role_id])) {
                             //error_log('MXChat Save: Initializing rate_limits for role: ' . $role_id);
                             $options['rate_limits'][$role_id] = [
                                 'limit' => ($role_id === 'logged_out') ? '10' : '100',
                                 'timeframe' => 'daily',
                                 'message' => 'Rate limit exceeded. Please try again later.'
                             ];
                         }

                         // Update the specific setting
                         $options['rate_limits'][$role_id][$setting_key] = $value;
                         //error_log('MXChat Save: Updated rate_limits[' . $role_id . '][' . $setting_key . '] = ' . $value);
                     } else {
                         //error_log('MXChat Save: Failed to parse rate_limits pattern: ' . $name);
                     }
                 }
                 // Then check for role rate limits (old format)
                 else if (strpos($name, 'mxchat_options[role_rate_limits]') !== false) {
                     //error_log('MXChat Save: Processing role_rate_limits field: ' . $name);
                     // Extract role ID from the name
                     preg_match('/\[role_rate_limits\]\[(.*?)\]/', $name, $matches);
                     //error_log('MXChat Save: Regex matches: ' . print_r($matches, true));

                     if (isset($matches[1])) {
                         $role_id = $matches[1];
                         // Initialize role_rate_limits if it doesn't exist
                         if (!isset($options['role_rate_limits'])) {
                             //error_log('MXChat Save: Initializing role_rate_limits array');
                             $options['role_rate_limits'] = [];
                         }
                         // Update the specific role's rate limit
                         $options['role_rate_limits'][$role_id] = sanitize_text_field($value);
                         //error_log('MXChat Save: Updated role_rate_limits[' . $role_id . '] = ' . $value);
                     } else {
                         //error_log('MXChat Save: Failed to parse role_rate_limits pattern: ' . $name);
                     }
                 }
                 // Handle toggles
                else if (strpos($name, 'toggle') !== false || in_array($name, [
                    'chat_persistence_toggle',
                    'privacy_toggle',
                    'complianz_toggle',
                    'chat_toolbar_toggle',
                    'show_pdf_upload_button',
                    'show_word_upload_button',
                    'enable_streaming_toggle',
                    'contextual_awareness_toggle' // Add this line
                ])) {
                    //error_log('MXChat Save: Processing toggle: ' . $name);
                    $options[$name] = ($value === 'on') ? 'on' : 'off';
                } else {
                     //error_log('MXChat Save: Processing standard field: ' . $name);
                     // Store all other values directly
                     $options[$name] = $value;
                 }
                 break;
         }

         // Save all updates to the options array
         $updated = update_option('mxchat_options', $options);
         //error_log('MXChat Save: Update result: ' . ($updated ? 'success' : 'unchanged') . ' for field: ' . $name);
         //error_log('MXChat Save: Updated options array: ' . print_r($options, true));

         // Always return success even if WordPress says nothing changed
         // (which happens when the value is the same as before)
         wp_send_json_success(['message' => esc_html__('Setting saved', 'mxchat')]);
     }


    /**
     * Handles AJAX request for saving chat settings
     */
     public function mxchat_save_prompts_setting_callback() {
         check_ajax_referer('mxchat_prompts_setting_nonce');

         if (!current_user_can('manage_options')) {
             wp_send_json_error(['message' => esc_html__('Unauthorized', 'mxchat')]);
         }

         $name = isset($_POST['name']) ? $_POST['name'] : '';
         $value = isset($_POST['value']) ? stripslashes($_POST['value']) : '';

         //error_log('[MXCHAT-PROMPTS] Saving setting: ' . $name . ' = ' . $value);

         if (empty($name)) {
             wp_send_json_error(['message' => esc_html__('Invalid field name', 'mxchat')]);
         }

// Handle Pinecone settings - BYPASS WORDPRESS SANITIZATION
if (strpos($name, 'mxchat_pinecone_addon_options') !== false) {
    //error_log('[MXCHAT-PROMPTS] Processing Pinecone setting: ' . $name);

    // Extract the field name
    if (preg_match('/mxchat_pinecone_addon_options\[([^\]]+)\]/', $name, $matches)) {
        $field_name = $matches[1];
        //error_log('[MXCHAT-PROMPTS] Extracted field name: ' . $field_name);

        // Get current options directly from database - NO WordPress filters
        global $wpdb;
        $current_options_raw = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
                'mxchat_pinecone_addon_options'
            )
        );

        // FIX: Handle the case where the option doesn't exist yet
        if ($current_options_raw === null) {
            // Option doesn't exist, create it with default values
            $current_options = array(
                'mxchat_use_pinecone' => '0',
                'mxchat_pinecone_api_key' => '',
                'mxchat_pinecone_host' => '',
                'mxchat_pinecone_index' => '',
                'mxchat_pinecone_environment' => ''
            );
            //error_log('[MXCHAT-PROMPTS] Option does not exist, creating with defaults');
        } else {
            // Unserialize the raw data
            $current_options = maybe_unserialize($current_options_raw);
            if (!is_array($current_options)) {
                // Fallback to defaults if unserialization fails
                $current_options = array(
                    'mxchat_use_pinecone' => '0',
                    'mxchat_pinecone_api_key' => '',
                    'mxchat_pinecone_host' => '',
                    'mxchat_pinecone_index' => '',
                    'mxchat_pinecone_environment' => ''
                );
                //error_log('[MXCHAT-PROMPTS] Failed to unserialize, using defaults');
            }
        }

        //error_log('[MXCHAT-PROMPTS] Current options from DB: ' . print_r($current_options, true));

        // Update the specific field with proper sanitization
        switch ($field_name) {
            case 'mxchat_use_pinecone':
                $new_value = ($value === '1') ? '1' : '0';
                break;
            case 'mxchat_pinecone_api_key':
            case 'mxchat_pinecone_host':
            case 'mxchat_pinecone_index':
            case 'mxchat_pinecone_environment':
                $new_value = sanitize_text_field($value);
                if ($field_name === 'mxchat_pinecone_host') {
                    $new_value = str_replace(['https://', 'http://'], '', $new_value);
                }
                break;
            default:
                wp_send_json_error(['message' => esc_html__('Unknown Pinecone field', 'mxchat')]);
        }

        $current_options[$field_name] = $new_value;
        //error_log('[MXCHAT-PROMPTS] New value for ' . $field_name . ': "' . $new_value . '"');
        //error_log('[MXCHAT-PROMPTS] Updated options: ' . print_r($current_options, true));

        // Save directly to database to bypass WordPress sanitization
        $serialized_options = maybe_serialize($current_options);
        
        // FIX: Use INSERT ... ON DUPLICATE KEY UPDATE or separate INSERT/UPDATE logic
        $option_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s",
                'mxchat_pinecone_addon_options'
            )
        );

        if ($option_exists > 0) {
            // Update existing option
            $save_result = $wpdb->update(
                $wpdb->options,
                array('option_value' => $serialized_options),
                array('option_name' => 'mxchat_pinecone_addon_options'),
                array('%s'),
                array('%s')
            );
            //error_log('[MXCHAT-PROMPTS] Updated existing option, result: ' . ($save_result !== false ? 'SUCCESS' : 'FAILED'));
        } else {
            // Insert new option
            $save_result = $wpdb->insert(
                $wpdb->options,
                array(
                    'option_name' => 'mxchat_pinecone_addon_options',
                    'option_value' => $serialized_options,
                    'autoload' => 'yes'
                ),
                array('%s', '%s', '%s')
            );
            //error_log('[MXCHAT-PROMPTS] Inserted new option, result: ' . ($save_result !== false ? 'SUCCESS' : 'FAILED'));
        }

        // Clear any WordPress option cache to ensure get_option() returns fresh data
        wp_cache_delete('mxchat_pinecone_addon_options', 'options');

        // IMPROVED VERIFICATION - Check if the database operation succeeded
        if ($save_result !== false) {
            // Double-check by reading fresh from database
            $verification_raw = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
                    'mxchat_pinecone_addon_options'
                )
            );
            $verification_options = maybe_unserialize($verification_raw);
            $verified_value = isset($verification_options[$field_name]) ? $verification_options[$field_name] : 'NOT_FOUND';

            //error_log('[MXCHAT-PROMPTS] Final verification - Expected: "' . $new_value . '", Got: "' . $verified_value . '"');

            // Use loose comparison (==) instead of strict (===) to avoid type issues
            if ($verified_value == $new_value || $save_result > 0) {
                wp_send_json_success(['message' => esc_html__('Pinecone setting saved', 'mxchat')]);
            } else {
                // Still return success if the DB operation worked, even if verification is quirky
                //error_log('[MXCHAT-PROMPTS] Verification mismatch but DB operation succeeded');
                wp_send_json_success(['message' => esc_html__('Pinecone setting saved (DB success)', 'mxchat')]);
            }
        } else {
            wp_send_json_error(['message' => esc_html__('Database save failed', 'mxchat')]);
        }
    } else {
        wp_send_json_error(['message' => esc_html__('Invalid field name format', 'mxchat')]);
    }

    return; // Exit here for Pinecone settings
}
         // Handle auto-sync settings (existing functionality)
         if (strpos($name, 'mxchat_auto_sync_') === 0) {
             $value = ($value === 'on' || $value === '1') ? '1' : '0';
             $updated = update_option($name, $value);

             if ($updated || get_option($name) === $value) {
                 wp_send_json_success(['message' => esc_html__('Auto-sync setting saved', 'mxchat')]);
             } else {
                 wp_send_json_error(['message' => esc_html__('No changes detected', 'mxchat')]);
             }
         }

         // Handle other prompts options
         $options = get_option('mxchat_prompts_options', []);
         $options[$name] = $value;
         $updated = update_option('mxchat_prompts_options', $options);

         if ($updated) {
             wp_send_json_success(['message' => esc_html__('Setting saved', 'mxchat')]);
         } else {
             wp_send_json_error(['message' => esc_html__('No changes detected', 'mxchat')]);
         }
     }


    /**
     * Handles AJAX request for Pinecone settings migration
     */
     public function ajax_migrate_pinecone_settings() {
         // Verify nonce
         if (!wp_verify_nonce($_POST['_ajax_nonce'] ?? '', 'mxchat_save_setting_nonce')) {
             wp_send_json_error('Invalid nonce');
         }

         // Check permissions
         if (!current_user_can('manage_options')) {
             wp_send_json_error('Unauthorized access');
         }

         // Check if old Pinecone addon options exist
         $old_options = get_option('mxchat_pinecone_addon_options', array());

         if (empty($old_options)) {
             wp_send_json_success(array('migrated' => false, 'message' => 'No old settings found'));
         }

         // Get current core plugin options
         $current_options = get_option('mxchat_pinecone_addon_options', array());

         // Only migrate if core options are empty or if explicitly requested
         $should_migrate = empty($current_options) ||
                          (empty($current_options['mxchat_pinecone_api_key']) && !empty($old_options['mxchat_pinecone_api_key']));

         if ($should_migrate) {
             // Migrate settings with proper sanitization
             $migrated_options = array(
                 'mxchat_use_pinecone' => $old_options['mxchat_use_pinecone'] ?? '0',
                 'mxchat_pinecone_api_key' => sanitize_text_field($old_options['mxchat_pinecone_api_key'] ?? ''),
                 'mxchat_pinecone_host' => sanitize_text_field($old_options['mxchat_pinecone_host'] ?? ''),
                 'mxchat_pinecone_index' => sanitize_text_field($old_options['mxchat_pinecone_index'] ?? ''),
                 'mxchat_pinecone_environment' => sanitize_text_field($old_options['mxchat_pinecone_environment'] ?? '')
             );

             update_option('mxchat_pinecone_addon_options', $migrated_options);

             wp_send_json_success(array(
                 'migrated' => true,
                 'message' => 'Settings migrated successfully from Pinecone add-on'
             ));
         } else {
             wp_send_json_success(array(
                 'migrated' => false,
                 'message' => 'Settings already exist in core plugin'
             ));
         }
     }



    // ========================================
    // LICENSE AJAX HANDLERS
    // ========================================

    /**
     * Validates and activates chat license via AJAX
     */
    public function mxchat_handle_activate_license() {
        // Check nonce
        if (!check_ajax_referer('mxchat_activate_license_nonce', 'security', false)) {
            wp_send_json_error(esc_html__('Invalid security token', 'mxchat'));
            return;
        }
        
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized access', 'mxchat'));
            return;
        }
        
        $license_key = isset($_POST['mxchat_activation_key']) ? sanitize_text_field($_POST['mxchat_activation_key']) : '';
        $customer_email = isset($_POST['mxchat_pro_email']) ? sanitize_email($_POST['mxchat_pro_email']) : '';
        
        if (empty($license_key) || empty($customer_email)) {
            wp_send_json_error(esc_html__('Email or License Key is missing', 'mxchat'));
            return;
        }

        // LOCAL DEVELOPMENT: Bypass API call and always activate
        // Comment out this block for production
        update_option('mxchat_license_status', 'active');
        update_option('mxchat_pro_email', $customer_email);
        update_option('mxchat_activation_key', $license_key);
        delete_option('mxchat_license_error');
        wp_send_json_success(array('message' => esc_html__('License activated successfully (LOCAL MODE)', 'mxchat')));
        return;
        
        $product_id = 'MxChatPRO';
        
        // **CHANGED TO HTTPS**
        $response = wp_remote_get(
            add_query_arg(
                array(
                    'wc-api' => 'software-api',
                    'request' => 'activation',
                    'email' => $customer_email,
                    'license_key' => $license_key,
                    'product_id' => $product_id
                ),
                'https://mxchat.ai/' // **CHANGED FROM HTTP TO HTTPS**
            ),
            array(
                'timeout' => 60, // 60 seconds timeout
                'sslverify' => true // **CHANGED TO TRUE FOR HTTPS**
            )
        );
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            //error_log('MxChat License Activation Error: ' . $error_message);
            wp_send_json_error(esc_html__('Activation failed due to a server error: ', 'mxchat') . $error_message);
            return;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Log response for debugging
        //error_log('MxChat License Response Code: ' . $response_code);
        //error_log('MxChat License Response Body: ' . $body);
        
        if ($response_code !== 200) {
            wp_send_json_error(esc_html__('Server returned error code: ', 'mxchat') . $response_code);
            return;
        }
        
        $data = json_decode($body);
        
        if ($data && isset($data->activated) && $data->activated) {
            update_option('mxchat_license_status', 'active');
            update_option('mxchat_pro_email', $customer_email);
            update_option('mxchat_activation_key', $license_key);
            delete_option('mxchat_license_error'); // Clear any previous errors
            wp_send_json_success(array('message' => esc_html__('License activated successfully', 'mxchat')));
        } else {
            $error_message = isset($data->error) ? $data->error : esc_html__('Activation failed', 'mxchat');
            update_option('mxchat_license_status', 'inactive');
            update_option('mxchat_license_error', $error_message);
            wp_send_json_error($error_message);
        }
    }


    /**
     * Validates license via AJAX with email and key
     */
     public function mxchat_check_license_status() {
         // Verify nonce
         check_ajax_referer($this->mxchat_get_nonce_action(), 'security');

         $email = sanitize_email($_POST['email']);
         $key = sanitize_text_field($_POST['key']);

         // LOCAL DEVELOPMENT: Always return active
         // Comment out this block for production
         wp_send_json(array(
             'is_active' => true
         ));
         return;

         // Check if this license is actually active in your system
         $is_active = (get_option('mxchat_license_status') === 'active' &&
                      get_option('mxchat_pro_email') === $email &&
                      get_option('mxchat_activation_key') === $key);

         wp_send_json(array(
             'is_active' => $is_active
         ));
     }


    // ========================================
    // ACTIONS & INTENTS AJAX HANDLERS
    // ========================================

    /**
     * Validates nonce and returns JSON error on failure
     */
     public function mxchat_toggle_action() {
         // Check nonce
         if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mxchat_actions_nonce')) {
             wp_send_json_error(array('message' => 'Security check failed'));
             return;
         }

         // Check permissions
         if (!current_user_can('manage_options')) {
             wp_send_json_error(array('message' => 'Permission denied'));
             return;
         }

         // Validate params
         $intent_id = isset($_POST['intent_id']) ? intval($_POST['intent_id']) : 0;
         $enabled = isset($_POST['enabled']) ? (bool)$_POST['enabled'] : false;

         if (!$intent_id) {
             wp_send_json_error(array('message' => 'Invalid action ID'));
             return;
         }

         // Update the intent/action status in the database
         global $wpdb;
         $table_name = $wpdb->prefix . 'mxchat_intents';

         // Using the 'enabled' field - add this field if it doesn't exist
         $result = $wpdb->update(
             $table_name,
             array('enabled' => $enabled ? 1 : 0),
             array('id' => $intent_id),
             array('%d'),
             array('%d')
         );

         if ($result === false) {
             wp_send_json_error(array('message' => 'Database error'));
             return;
         }

         wp_send_json_success();
     }


    /**
     * Validates permissions for AJAX request handling
     */
     public function mxchat_update_intent_threshold() {
         // Check permissions
         if (!current_user_can('manage_options')) {
             if (wp_doing_ajax()) {
                 wp_send_json_error(array('message' => 'Unauthorized user'));
                 return;
             }
             wp_die(esc_html__('Unauthorized user', 'mxchat'));
         }

         // Verify nonce
         check_admin_referer('mxchat_update_intent_threshold_nonce');

         // Process the update if we have valid data
         if (isset($_POST['intent_id'], $_POST['intent_threshold'])) {
             global $wpdb;
             $table_name = $wpdb->prefix . 'mxchat_intents';
             $intent_id = intval($_POST['intent_id']);
             $threshold_percentage = max(70, min(95, intval($_POST['intent_threshold'])));
             $similarity_threshold = $threshold_percentage / 100;

             $result = $wpdb->update(
                 $table_name,
                 ['similarity_threshold' => $similarity_threshold],
                 ['id' => $intent_id],
                 ['%f'],
                 ['%d']
             );

             // Handle AJAX requests
             if (wp_doing_ajax()) {
                 if ($result === false) {
                     wp_send_json_error(array('message' => 'Failed to update threshold'));
                 } else {
                     wp_send_json_success(array('threshold' => $threshold_percentage));
                 }
                 return;
             }
         }

         // Redirect for regular form submissions
         wp_safe_redirect(admin_url('admin.php?page=mxchat-actions&updated=true'));
         exit;
     }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Returns a specific nonce action string
     */
     private function mxchat_get_nonce_action() {
         return 'mxchat_license_nonce';
     }

}

// Initialize the AJAX handler
new MxChat_Ajax_Handler();
