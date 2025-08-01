<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MxChat_Utils {

/**
 * Submit or update content (and its embedding) in the database.
 * Stores in Pinecone if enabled, otherwise stores in WordPress DB.
 *
 * @param string $content    The content to be embedded.
 * @param string $source_url The source URL of the content.
 * @param string $api_key    The API key used for generating embeddings.
 * @param string $vector_id  Optional vector ID for Pinecone (if not provided, will use md5 of URL)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
public static function submit_content_to_db($content, $source_url, $api_key, $vector_id = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
    
    //error_log('[MXCHAT-DB] Starting database submission for URL: ' . $source_url);
    //error_log('[MXCHAT-DB] Content length: ' . strlen($content) . ' bytes');
    
    // Sanitize the source URL
    $source_url = esc_url_raw($source_url);
    
    // Just ensure UTF-8 validity without aggressive escaping
    $safe_content = wp_check_invalid_utf8($content);
    // Remove only null bytes and other control characters, but preserve newlines (\n = \x0A) and carriage returns (\r = \x0D)
    $safe_content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $safe_content);


    // Generate the embedding using the API key
    $embedding_vector = self::generate_embedding($content, $api_key);
    
    if (!is_array($embedding_vector)) {
        //error_log('[MXCHAT-DB] Error: Embedding generation failed');
        return new WP_Error('embedding_failed', 'Failed to generate embedding for content');
    }
    
    //error_log('[MXCHAT-DB] Embedding generated successfully');
    
    // Check if Pinecone is enabled and configured
    if (self::is_pinecone_enabled()) {
        //error_log('[MXCHAT-DB] Pinecone is enabled - using Pinecone storage');
        // Store in Pinecone only
        return self::store_in_pinecone_only($embedding_vector, $content, $source_url, $vector_id);
    } else {
        //error_log('[MXCHAT-DB] Pinecone not enabled - using WordPress storage');
        // Store in WordPress database only
        $embedding_vector_serialized = maybe_serialize($embedding_vector);
        return self::store_in_wordpress_db($safe_content, $source_url, $embedding_vector_serialized, $table_name);
    }
}

/**
 * Check if Pinecone is enabled and properly configured
 */
private static function is_pinecone_enabled() {
    $pinecone_options = get_option('mxchat_pinecone_addon_options');
    
    if (empty($pinecone_options)) {
        return false;
    }
    
    $enabled_check = !empty($pinecone_options['mxchat_use_pinecone']) && $pinecone_options['mxchat_use_pinecone'] !== '0';
    $api_key_check = !empty($pinecone_options['mxchat_pinecone_api_key']);
    $host_check = !empty($pinecone_options['mxchat_pinecone_host']);
    
    return $enabled_check && $api_key_check && $host_check;
}

/**
 * Store content in Pinecone only
 */
private static function store_in_pinecone_only($embedding_vector, $content, $source_url, $vector_id = null) {
    //error_log('[MXCHAT-PINECONE] ===== Using Pinecone-only storage =====');
    
    $pinecone_options = get_option('mxchat_pinecone_addon_options');
    
    $result = self::store_in_pinecone_main(
        $embedding_vector,
        $content,
        $source_url,
        $pinecone_options['mxchat_pinecone_api_key'],
        $pinecone_options['mxchat_pinecone_environment'] ?? '',
        $pinecone_options['mxchat_pinecone_index'] ?? '',
        $vector_id
    );
    
    if (is_wp_error($result)) {
        //error_log('[MXCHAT-PINECONE] Pinecone storage failed: ' . $result->get_error_message());
        return $result;
    }
    
    //error_log('[MXCHAT-PINECONE] Pinecone storage completed successfully');
    return true;
}

/**
 * Store content in WordPress database with progressive fallback
 */
private static function store_in_wordpress_db($safe_content, $source_url, $embedding_vector_serialized, $table_name) {
    global $wpdb;
    
    //error_log('[MXCHAT-DB] ===== Using WordPress-only storage =====');
    
    // ===== UPDATED: Only check for existing entries if we have a real source URL =====
    $existing_id = null;
    
    // Only check for duplicates if we have a valid, non-empty source URL
    if (!empty($source_url) && $source_url !== '' && filter_var($source_url, FILTER_VALIDATE_URL)) {
        $existing_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE source_url = %s LIMIT 1",
                $source_url
            )
        );
        //error_log('[MXCHAT-DB] Checked for existing URL, found ID: ' . ($existing_id ?: 'none'));
    } else {
        //error_log('[MXCHAT-DB] No valid source URL provided - treating as new manual content (will not check for duplicates)');
    }
    // ===== END UPDATE =====
    
    // Progressive fallback mechanism for problematic content
    $attempt = 1;
    $max_attempts = 3;
    $current_content = $safe_content;
    $result = false;
    
    while ($attempt <= $max_attempts && $result === false) {
        try {
            if ($existing_id) {
                //error_log('[MXCHAT-DB] Found existing entry (ID: ' . $existing_id . '). Updating... (Attempt ' . $attempt . ')');
                
                // Update the existing row
                $result = $wpdb->update(
                    $table_name,
                    array(
                        'url'              => $source_url,
                        'article_content'  => $current_content,
                        'embedding_vector' => $embedding_vector_serialized,
                        'source_url'       => $source_url,
                        'timestamp'        => current_time('mysql'),
                    ),
                    array('id' => $existing_id),
                    array('%s','%s','%s','%s','%s'),
                    array('%d')
                );
            } else {
                //error_log('[MXCHAT-DB] No existing entry found. Inserting new row... (Attempt ' . $attempt . ')');
                //error_log('[MXCHAT-DB] Content sample: ' . substr($current_content, 0, 1000));
                
                // Insert a new row (source_url can be empty for manual content)
                $result = $wpdb->insert(
                    $table_name,
                    array(
                        'url'              => $source_url, // Can be empty for manual content
                        'article_content'  => $current_content,
                        'embedding_vector' => $embedding_vector_serialized,
                        'source_url'       => $source_url, // Can be empty for manual content
                        'timestamp'        => current_time('mysql'),
                    ),
                    array('%s','%s','%s','%s','%s')
                );
            }
            
            if ($result === false) {
                //error_log('[MXCHAT-DB] Database operation failed (Attempt ' . $attempt . '): ' . $wpdb->last_error);
                
                // Progressively apply more aggressive sanitization on failure
                if ($attempt === 1) {
                    // First fallback: Use a more aggressive character filter and shorten
                    $current_content = preg_replace('/[^\p{L}\p{N}\s.,;:!?()-]/u', '', $current_content);
                    $current_content = substr($current_content, 0, 50000);
                } else if ($attempt === 2) {
                    // Second fallback: Keep only alphanumeric and basic punctuation, shorten further
                    $current_content = preg_replace('/[^a-zA-Z0-9\s.,;:!?()-]/u', '', $current_content);
                    $current_content = substr($current_content, 0, 30000);
                }
                
                $attempt++;
            }
        } catch (Exception $e) {
            //error_log('[MXCHAT-DB] Exception during database operation: ' . $e->getMessage());
            $attempt++;
        }
    }
    
    if ($result === false) {
        //error_log('[MXCHAT-DB] All database operation attempts failed');
        return new WP_Error('database_failed', 'Failed to store content in WordPress database after ' . $max_attempts . ' attempts');
    }
    
    //error_log('[MXCHAT-DB] WordPress database operation completed successfully (Attempt ' . ($attempt - 1) . ')');
    return true;
}

/**
 * Store content in Pinecone database
 */
private static function store_in_pinecone_main($embedding_vector, $content, $url, $api_key, $environment, $index_name, $vector_id = null) {
    //error_log('[MXCHAT-PINECONE-MAIN] ===== Starting Pinecone storage =====');
    
    // ===== UPDATED: Handle manual content with unique vector IDs =====
    if ($vector_id) {
        // Use provided vector ID
        //error_log('[MXCHAT-PINECONE-MAIN] Using provided vector ID: ' . $vector_id);
    } elseif (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
        // For valid URLs, use URL-based ID (existing behavior)
        $vector_id = md5($url);
        //error_log('[MXCHAT-PINECONE-MAIN] Generated vector ID from URL: ' . $vector_id);
    } else {
        // For manual content (empty/invalid URL), generate unique ID
        $vector_id = 'manual_' . time() . '_' . substr(md5($content . microtime(true)), 0, 8);
        //error_log('[MXCHAT-PINECONE-MAIN] Generated unique vector ID for manual content: ' . $vector_id);
    }
    // ===== END UPDATE =====
    
    $options = get_option('mxchat_pinecone_addon_options');
    $host = $options['mxchat_pinecone_host'] ?? '';
    
    //error_log('[MXCHAT-PINECONE-MAIN] Host from options: ' . $host);
    //error_log('[MXCHAT-PINECONE-MAIN] API key length: ' . strlen($api_key));
    //error_log('[MXCHAT-PINECONE-MAIN] Environment: ' . $environment);
    //error_log('[MXCHAT-PINECONE-MAIN] Index name: ' . $index_name);

    if (empty($host)) {
        //error_log('[MXCHAT-PINECONE-MAIN] ERROR: Host is empty');
        return new WP_Error('pinecone_config', 'Pinecone host is not configured. Please set the host in your settings.');
    }

    // ===== UPDATED: Determine content type more accurately =====
    $is_product = false;
    $content_type = 'manual'; // Default for manual content
    
    if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
        $is_product = (strpos($url, '/product/') !== false || strpos($url, '/shop/') !== false);
        $content_type = $is_product ? 'product' : 'content';
    }
    
    //error_log('[MXCHAT-PINECONE-MAIN] Content type: ' . $content_type);
    // ===== END UPDATE =====

    $api_endpoint = "https://{$host}/vectors/upsert";
    //error_log('[MXCHAT-PINECONE-MAIN] API endpoint: ' . $api_endpoint);
    
    $request_body = array(
        'vectors' => array(
            array(
                'id' => $vector_id,
                'values' => $embedding_vector,
                'metadata' => array(
                    'text' => $content,
                    'source_url' => $url, // Can be empty for manual content
                    'type' => $content_type, // 'manual', 'content', or 'product'
                    'last_updated' => time(),
                    'created_at' => time() // Add creation timestamp
                )
            )
        )
    );
    
    //error_log('[MXCHAT-PINECONE-MAIN] Request body prepared (embedding dimensions: ' . count($embedding_vector) . ')');

    $response = wp_remote_post($api_endpoint, array(
        'headers' => array(
            'Api-Key' => $api_key,
            'accept' => 'application/json',
            'content-type' => 'application/json'
        ),
        'body' => wp_json_encode($request_body),
        'timeout' => 30,
        'data_format' => 'body'
    ));

    if (is_wp_error($response)) {
        //error_log('[MXCHAT-PINECONE-MAIN] WordPress request error: ' . $response->get_error_message());
        return new WP_Error('pinecone_request', $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    //error_log('[MXCHAT-PINECONE-MAIN] Response code: ' . $response_code);
    
    if ($response_code !== 200) {
        $body = wp_remote_retrieve_body($response);
        //error_log('[MXCHAT-PINECONE-MAIN] API error - Response body: ' . $body);
        return new WP_Error('pinecone_api', sprintf(
            'Pinecone API error (HTTP %d): %s',
            $response_code,
            $body
        ));
    }

    $response_body = wp_remote_retrieve_body($response);
    //error_log('[MXCHAT-PINECONE-MAIN] Success response: ' . $response_body);
    //error_log('[MXCHAT-PINECONE-MAIN] Successfully stored in Pinecone');
    //error_log('[MXCHAT-PINECONE-MAIN] ===== Pinecone storage complete =====');
    
    return true;
}
/**
 * Generate an embedding for the given text using the specified API key.
 *
 * @param string $text    The text to be embedded.
 * @param string $api_key The API key used for generating embeddings.
 * @return array|null     The embedding vector or null on failure.
 */
private static function generate_embedding($text, $api_key) {
    // Get options and selected model
    $options = get_option('mxchat_options');
    $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
    
    // Determine endpoint and API key based on model
    if (strpos($selected_model, 'voyage') === 0) {
        $endpoint = 'https://api.voyageai.com/v1/embeddings';
        $api_key = $options['voyage_api_key'] ?? '';
    } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $selected_model . ':embedContent';
        $api_key = $options['gemini_api_key'] ?? '';
    } else {
        $endpoint = 'https://api.openai.com/v1/embeddings';
        // Use the passed API key for OpenAI
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
    
    $args = [
        'body'        => wp_json_encode($request_body),
        'headers'     => $headers,
        'timeout'     => 60,
        'redirection' => 5,
        'blocking'    => true,
        'httpversion' => '1.0',
        'sslverify'   => true,
    ];
    
    $response = wp_remote_post($endpoint, $args);
    
    if (is_wp_error($response)) {
        //error_log('Error generating embedding: ' . $response->get_error_message());
        return null;
    }
    
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Handle different response formats based on provider
    if (strpos($selected_model, 'gemini-embedding') === 0) {
        // Gemini API response format
        if (isset($response_body['embedding']['values']) && is_array($response_body['embedding']['values'])) {
            return $response_body['embedding']['values'];
        } else {
            //error_log('Invalid response received from Gemini embedding API: ' . wp_json_encode($response_body));
            return null;
        }
    } else {
        // OpenAI/Voyage API response format
        if (isset($response_body['data'][0]['embedding']) && is_array($response_body['data'][0]['embedding'])) {
            return $response_body['data'][0]['embedding'];
        } else {
            //error_log('Invalid response received from embedding API: ' . wp_json_encode($response_body));
            return null;
        }
    }
}  
}