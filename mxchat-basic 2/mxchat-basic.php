<?php
/**
 * Plugin Name: MxChat
 * Description: AI chatbot for WordPress with OpenAI, Claude, xAI, DeepSeek, live agent, PDF uploads, WooCommerce, and training on website data.
 * Version: 2.2.8
 * Author: MxChat
 * Author URI: https://mxchat.ai
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mxchat
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin version constant for asset versioning
define('MXCHAT_VERSION', '2.2.8');

function mxchat_load_textdomain() {
    load_plugin_textdomain('mxchat', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mxchat_load_textdomain');

// Include classes with error handling
function mxchat_include_classes() {
    $class_files = array(
        'includes/class-mxchat-integrator.php',
        'includes/class-mxchat-admin.php',
        'includes/class-mxchat-public.php',
        'includes/class-mxchat-utils.php',
        'includes/class-mxchat-user.php',
        'includes/pdf-parser/alt_autoload.php',
        'includes/class-mxchat-word-handler.php',
        'admin/class-ajax-handler.php',
        'admin/class-pinecone-manager.php',
        'admin/class-knowledge-manager.php'
    );

    foreach ($class_files as $file) {
        $file_path = plugin_dir_path(__FILE__) . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        } else {
            error_log('MxChat: Missing class file - ' . $file);
        }
    }
}

function mxchat_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Chat Transcripts Table
    $chat_transcripts_table = $wpdb->prefix . 'mxchat_chat_transcripts';
    $sql_chat_transcripts = "CREATE TABLE $chat_transcripts_table (
        id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
        user_id MEDIUMINT(9) DEFAULT 0,
        session_id VARCHAR(255) NOT NULL,
        role VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        user_email VARCHAR(255) DEFAULT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // System Prompt Content Table
    $system_prompt_table = $wpdb->prefix . 'mxchat_system_prompt_content';
    $sql_system_prompt = "CREATE TABLE $system_prompt_table (
        id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
        url VARCHAR(255) NOT NULL,
        article_content LONGTEXT NOT NULL,
        embedding_vector LONGTEXT,
        source_url VARCHAR(255) DEFAULT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Intents Table
    $intents_table = $wpdb->prefix . 'mxchat_intents';
    $sql_intents_table = "CREATE TABLE $intents_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        intent_label VARCHAR(255) NOT NULL,
        phrases TEXT NOT NULL,
        embedding_vector LONGTEXT NOT NULL,
        callback_function VARCHAR(255) NOT NULL,
        similarity_threshold FLOAT DEFAULT 0.85,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Create or update tables
    dbDelta($sql_chat_transcripts);
    dbDelta($sql_system_prompt);
    dbDelta($sql_intents_table);

    // Ensure additional columns in `mxchat_chat_transcripts`
    mxchat_add_missing_columns($chat_transcripts_table, 'user_identifier', 'VARCHAR(255)');
    mxchat_add_missing_columns($chat_transcripts_table, 'user_email', 'VARCHAR(255) DEFAULT NULL');

    // Ensure additional columns in `mxchat_system_prompt_content`
    mxchat_add_missing_columns($system_prompt_table, 'embedding_vector', 'LONGTEXT');
    mxchat_add_missing_columns($system_prompt_table, 'source_url', 'VARCHAR(255) DEFAULT NULL');

    // Set default thresholds for existing intents
    $wpdb->query("UPDATE {$intents_table} SET similarity_threshold = 0.85 WHERE similarity_threshold IS NULL");
    mxchat_add_missing_columns($intents_table, 'enabled', 'TINYINT(1) NOT NULL DEFAULT 1');

    // Use the constant instead of hardcoded version
    update_option('mxchat_plugin_version', MXCHAT_VERSION);
}

function mxchat_add_missing_columns($table, $column_name, $column_type) {
    global $wpdb;
    $column_exists = $wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM $table LIKE %s", $column_name));
    if (empty($column_exists)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN $column_name $column_type");
    }
}

function mxchat_check_for_update() {
    try {
        $current_version = get_option('mxchat_plugin_version');
        $plugin_version = MXCHAT_VERSION;

        if ($current_version !== $plugin_version) {
            // Run live agent update BEFORE updating the stored version
            mxchat_handle_live_agent_update();
            
            // Run existing update processes
            mxchat_activate();
            mxchat_migrate_live_agent_status();

            // Add the new cleanup function for version 2.1.8
            if (version_compare($current_version, '2.1.8', '<')) {
                $deleted = mxchat_cleanup_orphaned_chat_history();
            }

            // Update version LAST so the live agent update logic can work
            update_option('mxchat_plugin_version', $plugin_version);
        }
    } catch (Exception $e) {
        error_log('MxChat update error: ' . $e->getMessage());
        // Don't update version if there was an error
    }
}

/**
 * Clean up orphaned chat history options from the wp_options table
 * @return int Number of options deleted
 */
function mxchat_cleanup_orphaned_chat_history() {
    global $wpdb;
    $count = 0;

    // Get all option keys that match our pattern
    $history_options = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE 'mxchat_history_%'"
    );

    if (!empty($history_options)) {
        foreach ($history_options as $option) {
            // Extract the session ID from the option name
            $session_id = str_replace('mxchat_history_', '', $option->option_name);

            // Check if this session still exists in the custom table
            $table_name = $wpdb->prefix . 'mxchat_chat_transcripts';
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE session_id = %s",
                    $session_id
                )
            );

            // If session doesn't exist in the main table, delete the option
            if ($exists == 0) {
                delete_option($option->option_name);
                // Also delete related metadata
                delete_option("mxchat_email_{$session_id}");
                delete_option("mxchat_agent_name_{$session_id}");
                $count++;
            }
        }
    }

    return $count;
}

function mxchat_migrate_live_agent_status() {
    $options = get_option('mxchat_options', []);

    // Check if live_agent_status exists
    if (isset($options['live_agent_status'])) {
        $current_status = $options['live_agent_status'];
        $needs_update = false;

        // Convert to new format if needed
        if ($current_status === 'online') {
            $options['live_agent_status'] = 'on';
            $needs_update = true;
        } else if ($current_status === 'offline') {
            $options['live_agent_status'] = 'off';
            $needs_update = true;
        } else if (!in_array($current_status, ['on', 'off'])) {
            // Default to off for any unexpected values
            $options['live_agent_status'] = 'off';
            $needs_update = true;
        }

        // Only update if needed
        if ($needs_update) {
            update_option('mxchat_options', $options);
        }
    } else {
        // If status doesn't exist, set default to off
        $options['live_agent_status'] = 'off';
        update_option('mxchat_options', $options);
    }
}

function mxchat_handle_live_agent_update() {
    // Get the CURRENT stored version (before it gets updated)
    $current_version = get_option('mxchat_plugin_version', '0.0.0');
    $new_version = '2.2.2';
    
    // Only run this once for the update to 2.2.2
    $update_handled = get_option('mxchat_live_agent_update_2_2_2_handled', false);
    
    // Check if we're upgrading TO 2.2.2 and haven't handled this yet
    if (version_compare($current_version, $new_version, '<') && !$update_handled) {
        $options = get_option('mxchat_options', array());
        
        // Check if live agent was previously enabled
        if (isset($options['live_agent_status']) && $options['live_agent_status'] === 'on') {
            // Disable live agent
            $options['live_agent_status'] = 'off';
            update_option('mxchat_options', $options);
            
            // Set flag to show the notification banner
            update_option('mxchat_show_live_agent_disabled_notice', true);
        }
        
        // Mark this update as handled
        update_option('mxchat_live_agent_update_2_2_2_handled', true);
    }
}

// Initialize plugin safely
function mxchat_init() {
    // Include all class files first
    mxchat_include_classes();
    
    // Run update check
    mxchat_check_for_update();
    
    // Initialize classes with error handling
    try {
        // Initialize admin classes
        if (is_admin()) {
            if (class_exists('MxChat_Knowledge_Manager')) {
                $mxchat_knowledge_manager = new MxChat_Knowledge_Manager();
                
                if (class_exists('MxChat_Admin')) {
                    $mxchat_admin = new MxChat_Admin($mxchat_knowledge_manager);
                }
            }
        }
        
        // Initialize public classes
        if (class_exists('MxChat_Public')) {
            $mxchat_public = new MxChat_Public();
        }
        
        if (class_exists('MxChat_Integrator')) {
            $mxchat_integrator = new MxChat_Integrator();
        }
        
    } catch (Exception $e) {
        error_log('MxChat initialization error: ' . $e->getMessage());
        
        // Show admin notice if there's an error
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>MxChat Error:</strong> Plugin initialization failed. ';
                echo 'Please check error logs or contact support. Error: ' . esc_html($e->getMessage());
                echo '</p></div>';
            });
        }
    }
}

// Run initialization on plugins_loaded
add_action('plugins_loaded', 'mxchat_init');

// Register activation hook
register_activation_hook(__FILE__, 'mxchat_activate');

// Add cron schedule
add_filter('cron_schedules', function($schedules) {
    $schedules['one_minute'] = array(
        'interval' => 60,
        'display' => 'Every Minute'
    );
    return $schedules;
});

// Register deactivation hook - wrap in a callback to ensure the integrator exists
register_deactivation_hook(__FILE__, function() {
    if (class_exists('MxChat_Integrator')) {
        $integrator = new MxChat_Integrator();
        if (method_exists($integrator, 'cleanup_cron_events')) {
            $integrator->cleanup_cron_events();
        }
    }
});