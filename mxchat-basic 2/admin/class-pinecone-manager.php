<?php
/**
 * File: admin/class-pinecone-manager.php
 *
 * Handles all Pinecone vector database operations for MxChat
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MxChat_Pinecone_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into WordPress actions if needed
        add_action('mxchat_delete_content', array($this, 'mxchat_delete_from_pinecone_by_url'), 10, 1);
    }

    // ========================================
    // PINECONE FETCH OPERATIONS
    // ========================================
    
    
/**
 * Fetches 1K most recent records from Pinecone
 */
public function mxchat_fetch_pinecone_records($pinecone_options, $search_query = '', $page = 1, $per_page = 20) {
    //error_log('=== DEBUG: mxchat_fetch_pinecone_records started (improved) ===');
    
    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

    if (empty($api_key) || empty($host)) {
        //error_log('DEBUG: Missing required Pinecone parameters');
        return array('data' => array(), 'total' => 0, 'total_in_database' => 0, 'showing_recent_only' => false);
    }

    try {
        // Get total count for the banner message
        $total_in_database = $this->mxchat_get_pinecone_total_count($pinecone_options);
        
        // Check cache first for consistency
        $cache_key = 'mxchat_pinecone_recent_1k_cache';
        $all_records = get_transient($cache_key);
        
        if ($all_records === false) {
            // Cache miss - get fresh data
            $all_records = $this->mxchat_get_recent_1k_entries($pinecone_options);
        }
        
        // Filter by search query if provided
        if (!empty($search_query)) {
            $all_records = array_filter($all_records, function($record) use ($search_query) {
                $content = $record->article_content ?? '';
                $source_url = $record->source_url ?? '';
                return stripos($content, $search_query) !== false || stripos($source_url, $search_query) !== false;
            });
        }

        // Handle pagination
        $total = count($all_records);
        $offset = ($page - 1) * $per_page;
        $paged_records = array_slice($all_records, $offset, $per_page);

        //error_log('DEBUG: Returning ' . count($paged_records) . ' records (page ' . $page . ' of ' . ceil($total / $per_page) . ')');
        
        return array(
            'data' => $paged_records,
            'total' => $total,
            'total_in_database' => $total_in_database,
            'showing_recent_only' => ($total_in_database > 1000)
        );

    } catch (Exception $e) {
        //error_log('DEBUG: Exception: ' . $e->getMessage());
        return array('data' => array(), 'total' => 0, 'total_in_database' => 0, 'showing_recent_only' => false);
    }
}


/**
     * Get embedding dimensions based on the selected model
     * ADD THIS NEW FUNCTION
     */
    private function mxchat_get_embedding_dimensions() {
        $options = get_option('mxchat_options', array());
        $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
        
        // Define dimensions for different models
        $model_dimensions = array(
            'text-embedding-ada-002' => 1536,
            'text-embedding-3-small' => 1536,
            'text-embedding-3-large' => 3072,
            'voyage-2' => 1024,
            'voyage-large-2' => 1536,
            'voyage-3-large' => 2048,
            'gemini-embedding-001' => 1536,
        );
        
        // Check if it's a voyage model with custom dimensions
        if (strpos($selected_model, 'voyage-3-large') === 0) {
            $custom_dimensions = $options['voyage_output_dimension'] ?? 2048;
            return intval($custom_dimensions);
        }
        
        // Check if it's a gemini model with custom dimensions
        if (strpos($selected_model, 'gemini-embedding') === 0) {
            $custom_dimensions = $options['gemini_output_dimension'] ?? 1536;
            return intval($custom_dimensions);
        }
        
        // Return known dimensions or default to 1536
        return $model_dimensions[$selected_model] ?? 1536;
    }

    /**
     * Generate random unit vector with correct dimensions
     * ADD THIS NEW FUNCTION
     */
    private function mxchat_generate_random_vector() {
        $dimensions = $this->mxchat_get_embedding_dimensions();
        
        $random_vector = array();
        for ($i = 0; $i < $dimensions; $i++) {
            $random_vector[] = (rand(-1000, 1000) / 1000.0);
        }
        
        // Normalize the vector to unit length
        $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $random_vector)));
        if ($magnitude > 0) {
            $random_vector = array_map(function($x) use ($magnitude) { return $x / $magnitude; }, $random_vector);
        }
        
        return $random_vector;
    }


/**
 * Get 1,000 most recent entries from Pinecone (FIXED VERSION - Consistent Results)
 */
private function mxchat_get_recent_1k_entries($pinecone_options) {
    //error_log('=== DEBUG: mxchat_get_recent_1k_entries started (fixed version) ===');
    
    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

    if (empty($api_key) || empty($host)) {
        return array();
    }

    try {
        // First, try to get all vectors using a comprehensive scan approach
        $all_records = array();
        $seen_ids = array();
        $query_url = "https://{$host}/query";

        // Use a more systematic approach - try to get diverse samples that cover more of the database
        $fixed_vectors = $this->mxchat_generate_fixed_query_vectors();
        
        foreach ($fixed_vectors as $vector_index => $query_vector) {
            //error_log('DEBUG: Using fixed query vector ' . ($vector_index + 1) . '/' . count($fixed_vectors));
            
            $query_data = array(
                'includeMetadata' => true,
                'includeValues' => false,
                'topK' => 3000, // Get more per query
                'vector' => $query_vector
            );

            $response = wp_remote_post($query_url, array(
                'headers' => array(
                    'Api-Key' => $api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($query_data),
                'timeout' => 30
            ));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                if (isset($data['matches'])) {
                    foreach ($data['matches'] as $match) {
                        $match_id = $match['id'] ?? '';
                        if (!empty($match_id) && !isset($seen_ids[$match_id])) {
                            $metadata = $match['metadata'] ?? array();
                            
                            // Get created_at timestamp - try multiple possible fields
                            $created_at = $metadata['created_at'] ?? 
                                         $metadata['last_updated'] ?? 
                                         $metadata['timestamp'] ?? 
                                         time();
                            
                            // Ensure valid timestamp
                            if (!is_numeric($created_at)) {
                                $created_at = strtotime($created_at) ?: time();
                            }

                            $all_records[] = (object) array(
                                'id' => $match_id,
                                'article_content' => $metadata['text'] ?? '',
                                'source_url' => $metadata['source_url'] ?? '',
                                'created_at' => $created_at,
                                'data_source' => 'pinecone'
                            );
                            
                            $seen_ids[$match_id] = true;
                        }
                    }
                }
            }
            
            // Small delay between requests
            usleep(200000); // 0.2 second delay
        }

        // Sort by created_at (newest first) and take top 1K
        usort($all_records, function($a, $b) {
            return $b->created_at - $a->created_at;
        });

        $recent_1k = array_slice($all_records, 0, 1000);
        
        //error_log('DEBUG: Found ' . count($all_records) . ' total unique records, returning top ' . count($recent_1k));
        
        // Cache the results for consistency within the same session
        set_transient('mxchat_pinecone_recent_1k_cache', $recent_1k, 300); // Cache for 5 minutes
        
        return $recent_1k;

    } catch (Exception $e) {
        //error_log('DEBUG: Exception in get_recent_1k_entries: ' . $e->getMessage());
        return array();
    }
}

/**
 * Generate fixed query vectors for consistent results
 */
private function mxchat_generate_fixed_query_vectors() {
    $dimensions = $this->mxchat_get_embedding_dimensions();
    $vectors = array();
    
    // Create 5 fixed vectors with different patterns for better coverage
    $patterns = array(
        'zeros_with_ones' => 0.1,      // Mostly zeros with some 1s
        'ascending' => 0.2,            // Ascending pattern
        'descending' => 0.3,           // Descending pattern  
        'alternating' => 0.4,          // Alternating positive/negative
        'center_weighted' => 0.5       // Higher values in center
    );
    
    foreach ($patterns as $pattern_name => $seed) {
        $vector = array();
        
        for ($i = 0; $i < $dimensions; $i++) {
            switch ($pattern_name) {
                case 'zeros_with_ones':
                    $vector[] = ($i % 10 === 0) ? 1.0 : 0.0;
                    break;
                case 'ascending':
                    $vector[] = ($i / $dimensions) * 2 - 1; // Range -1 to 1
                    break;
                case 'descending':
                    $vector[] = (($dimensions - $i) / $dimensions) * 2 - 1;
                    break;
                case 'alternating':
                    $vector[] = ($i % 2 === 0) ? $seed : -$seed;
                    break;
                case 'center_weighted':
                    $center = $dimensions / 2;
                    $distance = abs($i - $center) / $center;
                    $vector[] = (1 - $distance) * $seed;
                    break;
            }
        }
        
        // Normalize the vector to unit length
        $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $vector)));
        if ($magnitude > 0) {
            $vector = array_map(function($x) use ($magnitude) { return $x / $magnitude; }, $vector);
        }
        
        $vectors[] = $vector;
    }
    
    return $vectors;
}


/**
     * Scan Pinecone for processed content (MISSING FUNCTION - ADD THIS)
     */
    public function mxchat_scan_pinecone_for_processed_content($pinecone_options) {
        //error_log('=== DEBUG: Starting mxchat_scan_pinecone_for_processed_content ===');
        
        $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
        $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

        if (empty($api_key) || empty($host)) {
            //error_log('DEBUG: Missing API credentials for scanning');
            return array();
        }

        try {
            // Use multiple random vectors to get better coverage
            $all_matches = array();
            $seen_ids = array();

            // Try 3 different random vectors to get better coverage
            for ($i = 0; $i < 3; $i++) {
                //error_log('DEBUG: Scanning attempt ' . ($i + 1) . '/3');
                
                $query_url = "https://{$host}/query";

                // Generate random vector with CORRECT dimensions
                $random_vector = $this->mxchat_generate_random_vector();

                $query_data = array(
                    'includeMetadata' => true,
                    'includeValues' => false,
                    'topK' => 10000,
                    'vector' => $random_vector
                );

                $response = wp_remote_post($query_url, array(
                    'headers' => array(
                        'Api-Key' => $api_key,
                        'Content-Type' => 'application/json'
                    ),
                    'body' => json_encode($query_data),
                    'timeout' => 30
                ));

                if (is_wp_error($response)) {
                    //error_log('DEBUG: Query attempt ' . ($i + 1) . ' WP error: ' . $response->get_error_message());
                    continue;
                }

                $response_code = wp_remote_retrieve_response_code($response);
                //error_log('DEBUG: Query attempt ' . ($i + 1) . ' response code: ' . $response_code);
                
                if ($response_code !== 200) {
                    $error_body = wp_remote_retrieve_body($response);
                    //error_log('DEBUG: Query attempt ' . ($i + 1) . ' failed with body: ' . substr($error_body, 0, 500));
                    continue;
                }

                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                if (isset($data['matches'])) {
                    //error_log('DEBUG: Query attempt ' . ($i + 1) . ' returned ' . count($data['matches']) . ' matches');
                    foreach ($data['matches'] as $match) {
                        $match_id = $match['id'] ?? '';
                        if (!empty($match_id) && !isset($seen_ids[$match_id])) {
                            $all_matches[] = $match;
                            $seen_ids[$match_id] = true;
                        }
                    }
                } else {
                    //error_log('DEBUG: Query attempt ' . ($i + 1) . ' - no matches key in response');
                }
            }

            //error_log('DEBUG: Total unique matches found: ' . count($all_matches));

            // Convert matches to processed data format
            $processed_data = array();
            $vector_ids_for_cache = array();

            foreach ($all_matches as $match) {
                $metadata = $match['metadata'] ?? array();
                $source_url = $metadata['source_url'] ?? '';
                $match_id = $match['id'] ?? '';

                if (!empty($source_url) && !empty($match_id)) {
                    $post_id = url_to_postid($source_url);
                    if ($post_id) {
                        $created_at = $metadata['created_at'] ?? '';
                        $processed_date = 'Recently';

                        if (!empty($created_at)) {
                            $timestamp = is_numeric($created_at) ? $created_at : strtotime($created_at);
                            if ($timestamp) {
                                $processed_date = human_time_diff($timestamp, current_time('timestamp')) . ' ago';
                            }
                        }

                        $processed_data[$post_id] = array(
                            'db_id' => $match_id,
                            'processed_date' => $processed_date,
                            'url' => $source_url,
                            'source' => 'pinecone',
                            'timestamp' => $timestamp ?? current_time('timestamp')
                        );

                        $vector_ids_for_cache[] = $match_id;
                    }
                }
            }

            // Update the vector IDs cache for future use
            if (!empty($vector_ids_for_cache)) {
                update_option('mxchat_pinecone_vector_ids_cache', $vector_ids_for_cache);
                //error_log('DEBUG: Updated vector IDs cache with ' . count($vector_ids_for_cache) . ' IDs');
            }

            //error_log('DEBUG: Returning ' . count($processed_data) . ' processed items from scanning');
            return $processed_data;

        } catch (Exception $e) {
            //error_log('DEBUG: Exception in scan_pinecone_for_processed_content: ' . $e->getMessage());
            return array();
        }
    }

/**
 * Get total count of vectors in Pinecone (SIMPLE VERSION)
 */
private function mxchat_get_pinecone_total_count($pinecone_options) {
    // First try the stats API
    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

    if (empty($api_key) || empty($host)) {
        return 0;
    }

    try {
        $stats_url = "https://{$host}/describe_index_stats";
        
        // Try GET request
        $response = wp_remote_get($stats_url, array(
            'headers' => array(
                'Api-Key' => $api_key,
                'Accept' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $stats_data = json_decode($body, true);
            
            $total_count = $stats_data['totalVectorCount'] ?? 0;
            if ($total_count > 0) {
                //error_log('DEBUG: Got total count from stats API: ' . $total_count);
                return intval($total_count);
            }
        }

        // Fallback: estimate from previous scans
        $cached_vector_ids = get_option('mxchat_pinecone_vector_ids_cache', array());
        if (!empty($cached_vector_ids)) {
            $estimated_count = count($cached_vector_ids);
            //error_log('DEBUG: Using estimated count from cache: ' . $estimated_count);
            return intval($estimated_count);
        }

    } catch (Exception $e) {
        //error_log('DEBUG: Exception getting total count: ' . $e->getMessage());
    }

    // If all else fails, return 0
    return 0;
}

/**
 * Call this after adding new content to refresh the view
 */
public function mxchat_refresh_after_new_content($pinecone_options) {
    //error_log('DEBUG: Refreshing after new content added');
    
    // Clear all relevant caches
    delete_transient('mxchat_pinecone_recent_1k_cache');  // Add this line
    delete_transient('mxchat_pinecone_recent_1k');
    delete_transient('mxchat_pinecone_total_count');
    
    // Force fresh fetch on next page load
    return true;
}   
    
    
/**
 * Fetches vectors from Pinecone using provided IDs (for content selection feature)
 */
public function fetch_pinecone_vectors_by_ids($pinecone_options, $vector_ids) {
    //error_log('=== DEBUG: fetch_pinecone_vectors_by_ids started (content selection method) ===');
    
    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

    //error_log('DEBUG: API key present: ' . (!empty($api_key) ? 'YES' : 'NO'));
    //error_log('DEBUG: Host: ' . $host);
    //error_log('DEBUG: Vector IDs count: ' . count($vector_ids));

    if (empty($api_key) || empty($host) || empty($vector_ids)) {
        //error_log('DEBUG: Missing parameters for fetch by IDs (content selection)');
        return array();
    }

    try {
        $fetch_url = "https://{$host}/vectors/fetch";
        //error_log('DEBUG: Fetch URL: ' . $fetch_url);

        // Pinecone fetch API allows fetching specific vectors by ID
        $fetch_data = array(
            'ids' => array_values($vector_ids)
        );

        $response = wp_remote_post($fetch_url, array(
            'headers' => array(
                'Api-Key' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($fetch_data),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            //error_log('DEBUG: Fetch by IDs WP error (content selection): ' . $response->get_error_message());
            return array();
        }

        $response_code = wp_remote_retrieve_response_code($response);
        //error_log('DEBUG: Fetch response code (content selection): ' . $response_code);
        
        if ($response_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            //error_log('DEBUG: Fetch failed with body (content selection): ' . $error_body);
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['vectors'])) {
            //error_log('DEBUG: No vectors key in response (content selection)');
            return array();
        }

        $processed_data = array();

        foreach ($data['vectors'] as $vector_id => $vector_data) {
            $metadata = $vector_data['metadata'] ?? array();
            $source_url = $metadata['source_url'] ?? '';

            if (!empty($source_url)) {
                $post_id = url_to_postid($source_url);
                if ($post_id) {
                    $created_at = $metadata['created_at'] ?? '';
                    $processed_date = 'Recently'; // Default

                    if (!empty($created_at)) {
                        $timestamp = is_numeric($created_at) ? $created_at : strtotime($created_at);
                        if ($timestamp) {
                            $processed_date = human_time_diff($timestamp, current_time('timestamp')) . ' ago';
                        }
                    }

                    $processed_data[$post_id] = array(
                        'db_id' => $vector_id,
                        'processed_date' => $processed_date,
                        'url' => $source_url,
                        'source' => 'pinecone',
                        'timestamp' => $timestamp ?? current_time('timestamp')
                    );
                }
            }
        }

        //error_log('DEBUG: Processed ' . count($processed_data) . ' records (content selection method)');
        //error_log('=== DEBUG: fetch_pinecone_vectors_by_ids completed (content selection) ===');

        return $processed_data;

    } catch (Exception $e) {
        //error_log('DEBUG: Exception in fetch_pinecone_vectors_by_ids (content selection): ' . $e->getMessage());
        return array();
    }
}


    // ========================================
    // PINECONE DELETE OPERATIONS
    // ========================================

public function mxchat_delete_all_from_pinecone($pinecone_options) {
    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

    if (empty($api_key) || empty($host)) {
        return array(
            'success' => false,
            'message' => 'Missing Pinecone API credentials'
        );
    }

    try {
        // First, get all vector IDs
        $all_vector_ids = array();

        // Try to get from cache first
        $cached_vector_ids = get_option('mxchat_pinecone_vector_ids_cache', array());
        if (!empty($cached_vector_ids)) {
            $all_vector_ids = $cached_vector_ids;
        } else {
            // Use the correct method name that exists in your class
            $records = $this->mxchat_get_recent_1k_entries($pinecone_options);
            foreach ($records as $record) {
                if (!empty($record->id)) {
                    $all_vector_ids[] = $record->id;
                }
            }
        }

        if (empty($all_vector_ids)) {
            return array(
                'success' => true,
                'message' => 'No vectors found to delete'
            );
        }

        // Delete vectors in batches (Pinecone has limits on batch operations)
        $batch_size = 100;
        $batches = array_chunk($all_vector_ids, $batch_size);
        $deleted_count = 0;
        $failed_batches = 0;

        foreach ($batches as $batch) {
            $result = $this->mxchat_delete_pinecone_batch($batch, $api_key, $host);
            if ($result['success']) {
                $deleted_count += count($batch);
            } else {
                $failed_batches++;
                //error_log('Failed to delete Pinecone batch: ' . $result['message']);
            }
        }

        // CLEAR ALL RELEVANT CACHES - EXACTLY like your single delete
        delete_transient('mxchat_pinecone_recent_1k_cache');
        delete_option('mxchat_pinecone_vector_ids_cache');
        delete_option('mxchat_pinecone_processed_cache');
        delete_option('mxchat_processed_content_cache');
        
        // Also force refresh for next page load - EXACTLY like your single delete
        $this->mxchat_refresh_after_new_content($pinecone_options);

        if ($failed_batches > 0) {
            return array(
                'success' => false,
                'message' => sprintf('Deleted %d vectors, but %d batches failed', $deleted_count, $failed_batches)
            );
        }

        return array(
            'success' => true,
            'message' => "Successfully deleted {$deleted_count} vectors from Pinecone"
        );

    } catch (Exception $e) {
        //error_log('Pinecone delete all exception: ' . $e->getMessage());
        return array(
            'success' => false,
            'message' => $e->getMessage()
        );
    }
}


    /**
     * Deletes batch of vectors from Pinecone database
     */
     private function mxchat_delete_pinecone_batch($vector_ids, $api_key, $host) {
         // Build the API endpoint
         $api_endpoint = "https://{$host}/vectors/delete";

         // Prepare the request body with the IDs
         $request_body = array(
             'ids' => $vector_ids
         );

         // Make the deletion request
         $response = wp_remote_post($api_endpoint, array(
             'headers' => array(
                 'Api-Key' => $api_key,
                 'accept' => 'application/json',
                 'content-type' => 'application/json'
             ),
             'body' => wp_json_encode($request_body),
             'timeout' => 60, // Increased timeout for batch operations
             'method' => 'POST'
         ));

         // Handle WordPress HTTP API errors
         if (is_wp_error($response)) {
             return array(
                 'success' => false,
                 'message' => $response->get_error_message()
             );
         }

         // Check response status
         $response_code = wp_remote_retrieve_response_code($response);
         $response_body = wp_remote_retrieve_body($response);

         // Pinecone returns 200 for successful deletion
         if ($response_code !== 200) {
             //error_log('Pinecone batch deletion failed: HTTP ' . $response_code . ' - ' . $response_body);
             return array(
                 'success' => false,
                 'message' => sprintf(
                     'Pinecone API error (HTTP %d): %s',
                     $response_code,
                     $response_body
                 )
             );
         }

         return array(
             'success' => true,
             'message' => 'Batch deleted successfully from Pinecone'
         );
     }


    /**
     * Deletes vector from Pinecone using API request
     */
public function mxchat_delete_from_pinecone_by_vector_id($vector_id, $api_key, $host) {
    // Build the API endpoint
    $api_endpoint = "https://{$host}/vectors/delete";

    // Prepare the request body with just the ID
    $request_body = array(
        'ids' => array($vector_id)
    );

    // Make the deletion request
    $response = wp_remote_post($api_endpoint, array(
        'headers' => array(
            'Api-Key' => $api_key,
            'accept' => 'application/json',
            'content-type' => 'application/json'
        ),
        'body' => wp_json_encode($request_body),
        'timeout' => 30,
        'method' => 'POST'
    ));

    // Handle WordPress HTTP API errors
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }

    // Check response status
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    // Pinecone returns 200 for successful deletion
    if ($response_code !== 200) {
        return array(
            'success' => false,
            'message' => sprintf(
                'Pinecone API error (HTTP %d): %s',
                $response_code,
                $response_body
            )
        );
    }

    return array(
        'success' => true,
        'message' => 'Vector deleted successfully from Pinecone'
    );
}
    /**
     * Deletes data from Pinecone using a source URL
     */
     public function mxchat_delete_from_pinecone_by_url($source_url, $pinecone_options) {
         $host = $pinecone_options['mxchat_pinecone_host'] ?? '';
         $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';

         if (empty($host) || empty($api_key)) {
             //error_log('MXChat: Pinecone deletion failed - missing configuration');
             return false;
         }

         $api_endpoint = "https://{$host}/vectors/delete";
         $vector_id = md5($source_url);

         $request_body = array(
             'ids' => array($vector_id)
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
             //error_log('MXChat: Pinecone deletion error - ' . $response->get_error_message());
             return false;
         }

         $response_code = wp_remote_retrieve_response_code($response);
         if ($response_code !== 200) {
             //error_log('MXChat: Pinecone deletion failed with status ' . $response_code);
             return false;
         }

         return true;
     }


    /**
     * Deletes data from Pinecone index using API key
     */
     private function mxchat_delete_from_pinecone($urls, $api_key, $environment, $index_name) {
         // Get the Pinecone host from options (matching your store_in_pinecone_main pattern)
         $options = get_option('mxchat_pinecone_addon_options');
         $host = $options['mxchat_pinecone_host'] ?? '';

         if (empty($host)) {
             return array(
                 'success' => false,
                 'message' => 'Pinecone host is not configured. Please set the host in your settings.'
             );
         }

         // Build API endpoint using the configured host
         $api_endpoint = "https://{$host}/vectors/delete";

         // Create vector IDs from URLs (matching your store method's ID generation)
         $vector_ids = array_map('md5', $urls);

         // Prepare the delete request body
         $request_body = array(
             'ids' => $vector_ids,
             'filter' => array(
                 'source_url' => array(
                     '$in' => $urls
                 )
             )
         );

         // Make the deletion request
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

         // Handle WordPress HTTP API errors
         if (is_wp_error($response)) {
             return array(
                 'success' => false,
                 'message' => $response->get_error_message()
             );
         }

         // Check response status
         $response_code = wp_remote_retrieve_response_code($response);
         if ($response_code !== 200) {
             $body = wp_remote_retrieve_body($response);
             return array(
                 'success' => false,
                 'message' => sprintf(
                     'Pinecone API error (HTTP %d): %s',
                     $response_code,
                     $body
                 )
             );
         }

         // Parse response body
         $body = wp_remote_retrieve_body($response);
         $response_data = json_decode($body, true);

         // Final validation of the response
         if (json_last_error() !== JSON_ERROR_NONE) {
             return array(
                 'success' => false,
                 'message' => 'Failed to parse Pinecone response: ' . json_last_error_msg()
             );
         }

         return array(
             'success' => true,
             'message' => sprintf('Successfully deleted %d vectors from Pinecone', count($vector_ids))
         );
     }


    // ========================================
    // VECTOR CACHE MANAGEMENT
    // ========================================


    /**
     * Removes vector ID from cache array option
     */
     public function mxchat_remove_from_pinecone_vector_cache($vector_id) {
         $cached_ids = get_option('mxchat_pinecone_vector_ids_cache', array());
         $key = array_search($vector_id, $cached_ids);
         if ($key !== false) {
             unset($cached_ids[$key]);
             update_option('mxchat_pinecone_vector_ids_cache', array_values($cached_ids));
         }
     }

    /**
     * Removes vector ID from processed content caches
     */
     public function mxchat_remove_from_processed_content_caches($vector_id) {
         // Get all caches
         $pinecone_cache = get_option('mxchat_pinecone_processed_cache', array());
         $processed_cache = get_option('mxchat_processed_content_cache', array());

         // We need to find the post ID that corresponds to this vector ID
         // Vector ID is typically md5 of the source URL
         $post_id_to_remove = null;

         // Search through caches to find matching post
         foreach ($pinecone_cache as $post_id => $cache_data) {
             if (isset($cache_data['db_id']) && $cache_data['db_id'] === $vector_id) {
                 $post_id_to_remove = $post_id;
                 break;
             }
         }

         // Also check the processed cache
         if (!$post_id_to_remove) {
             foreach ($processed_cache as $post_id => $cache_data) {
                 if (isset($cache_data['db_id']) && $cache_data['db_id'] === $vector_id) {
                     $post_id_to_remove = $post_id;
                     break;
                 }
             }
         }

         // If we found the post ID, remove it from both caches
         if ($post_id_to_remove) {
             unset($pinecone_cache[$post_id_to_remove]);
             unset($processed_cache[$post_id_to_remove]);

             update_option('mxchat_pinecone_processed_cache', $pinecone_cache);
             update_option('mxchat_processed_content_cache', $processed_cache);

             //error_log('Removed post ID ' . $post_id_to_remove . ' from processed content caches');
         } else {
             // If we can't find by vector ID, we might need to reconstruct the URL
             // and find the post ID that way
             //error_log('Could not find post ID for vector ID: ' . $vector_id);
         }
     }


    /**
     * Retrieves and caches Pinecone API processed content
     */
     public function mxchat_get_pinecone_processed_content($pinecone_options) {
         // First check local cache for immediate updates
         $cached_data = get_option('mxchat_pinecone_processed_cache', array());

         $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
         $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

         if (empty($api_key) || empty($host)) {
             // Return only cached data if API credentials are missing
             return $cached_data;
         }

         $pinecone_data = array();

         try {
             // Method 1: Try to get vectors using cached vector IDs first
             $cached_vector_ids = get_option('mxchat_pinecone_vector_ids_cache', array());

             if (!empty($cached_vector_ids)) {
                 $pinecone_data = $this->fetch_pinecone_vectors_by_ids($pinecone_options, $cached_vector_ids);
             }

             // Method 2: If no cached IDs or fetch failed, use scanning approach
             if (empty($pinecone_data)) {
                 $pinecone_data = $this->mxchat_scan_pinecone_for_processed_content($pinecone_options);
             }

             // Method 3: Final fallback - try stats endpoint (if available)
             if (empty($pinecone_data)) {
                 $stats_url = "https://{$host}/describe_index_stats";

                 $response = wp_remote_post($stats_url, array(
                     'headers' => array(
                         'Api-Key' => $api_key,
                         'Content-Type' => 'application/json'
                     ),
                     'body' => json_encode(array()),
                     'timeout' => 30
                 ));

                 if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                     $body = wp_remote_retrieve_body($response);
                     $stats_data = json_decode($body, true);

                     // Log stats for debugging but don't rely on them for vector listing
                     //error_log('Pinecone index stats: ' . print_r($stats_data, true));
                 }
             }

         } catch (Exception $e) {
             //error_log('Pinecone processed content exception: ' . $e->getMessage());
         }

         // Merge cached data with Pinecone data
         // Cache takes priority for recent updates (within last 5 minutes)
         $merged_data = $pinecone_data;

         foreach ($cached_data as $post_id => $cache_item) {
             $cache_timestamp = $cache_item['timestamp'] ?? 0;
             $time_diff = current_time('timestamp') - $cache_timestamp;

             // If cache item is recent (less than 5 minutes), prioritize it
             if ($time_diff < 300) { // 5 minutes = 300 seconds
                 $merged_data[$post_id] = $cache_item;
             } else {
                 // If not in Pinecone data and cache is old, keep cache but mark as potentially stale
                 if (!isset($merged_data[$post_id])) {
                     $merged_data[$post_id] = $cache_item;
                 }
             }
         }

         return $merged_data;
     }


    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Validates Pinecone API credentials
     */
    private function mxchat_validate_pinecone_credentials($api_key, $host) {
        if (empty($api_key) || empty($host)) {
            return false;
        }
        return true;
    }

    /**
     * Get Pinecone API credentials from options
     */
    private function mxchat_get_pinecone_credentials() {
        $options = get_option('mxchat_options', array());
        return array(
            'api_key' => isset($options['pinecone_api_key']) ? $options['pinecone_api_key'] : '',
            'host' => isset($options['pinecone_host']) ? $options['pinecone_host'] : ''
        );
    }

    /**
     * Log Pinecone operation errors
     */
    private function log_pinecone_error($operation, $error_message) {
        //error_log("MxChat Pinecone {$operation} Error: " . $error_message);
    }

    // ========================================
    // STATIC ACCESS METHODS (for backward compatibility)
    // ========================================

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
}

// Initialize the Pinecone manager
$mxchat_pinecone_manager = MxChat_Pinecone_Manager::get_instance();
