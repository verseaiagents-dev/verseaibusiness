<?php
/**
 * File: admin/class-knowledge-manager.php
 * 
 * Handles all knowledge base content processing for MxChat
 * Including PDF, sitemap, content processing, and WordPress post management
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MxChat_Knowledge_Manager {
    
    private $options;
    
    /**
     * Constructor - Register hooks for content processing
     */
    public function __construct() {
        $this->options = get_option('mxchat_options', array());
        $this->mxchat_init_hooks();
    }
    
    /**
     * Initialize WordPress hooks for content processing
     */
    private function mxchat_init_hooks() {
        // Admin post handlers for form submissions
        add_action('admin_post_mxchat_submit_content', array($this, 'mxchat_handle_content_submission'));
        add_action('admin_post_mxchat_submit_sitemap', array($this, 'mxchat_handle_sitemap_submission'));
        add_action('admin_post_mxchat_stop_processing', array($this, 'mxchat_stop_processing'));
        
        // AJAX handlers for real-time processing and status updates
        add_action('wp_ajax_mxchat_get_status_updates', array($this, 'mxchat_ajax_get_status_updates'));
        add_action('wp_ajax_mxchat_dismiss_completed_status', array($this, 'mxchat_ajax_dismiss_completed_status')); // NEW
        add_action('wp_ajax_mxchat_get_content_list', array($this, 'ajax_mxchat_get_content_list'));
        add_action('wp_ajax_mxchat_process_selected_content', array($this, 'ajax_mxchat_process_selected_content'));
       add_action('wp_ajax_mxchat_manual_batch_process', array($this, 'ajax_manual_batch_process'));
        add_action('mxchat_delete_content', array($this, 'mxchat_delete_from_pinecone_by_url'), 10, 1);

        
        // Cron handlers for background processing
        add_action('mxchat_process_sitemap_urls', array($this, 'mxchat_process_sitemap_urls_cron'), 10, 5);
        add_action('mxchat_process_pdf_pages', array($this, 'mxchat_process_pdf_pages_cron'), 10, 5);
        
        // WordPress post management hooks
        add_action('post_updated', array($this, 'mxchat_handle_post_update'), 10, 3);
        add_action('before_delete_post', array($this, 'mxchat_handle_post_delete'));
        add_action('wp_trash_post', array($this, 'mxchat_handle_post_delete'));
        add_action('wp_ajax_mxchat_save_inline_prompt', array($this, 'mxchat_save_inline_prompt'));
        add_action('admin_post_mxchat_delete_pinecone_prompt', array($this, 'mxchat_handle_pinecone_prompt_delete'));
        add_action('wp_ajax_mxchat_delete_pinecone_prompt', array($this, 'ajax_mxchat_delete_pinecone_prompt'));
            
        // WooCommerce product hooks (if WooCommerce is active)
        if (class_exists('WooCommerce')) {
            add_action('save_post_product', array($this, 'mxchat_handle_product_change'), 10, 3);
            add_action('wp_trash_post', array($this, 'mxchat_handle_product_delete'));
            add_action('before_delete_post', array($this, 'mxchat_handle_product_delete'));
        }
        
    }
    
    /**
     * Get current options (refreshed)
     */
    private function mxchat_get_options() {
        if (empty($this->options)) {
            $this->options = get_option('mxchat_options', array());
        }
        return $this->options;
    }
    
    
    /**
 * Handle manual batch processing via AJAX
 */
public function ajax_manual_batch_process() {
    try {
        // Verify nonce and permissions
        check_ajax_referer('mxchat_status_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }
        
        $process_type = sanitize_text_field($_POST['process_type'] ?? '');
        $url = sanitize_text_field($_POST['url'] ?? '');
        
        if (empty($process_type) || empty($url)) {
            wp_send_json_error('Missing required parameters');
        }
        
        $processed = 0;
        
        if ($process_type === 'pdf') {
            $processed = $this->mxchat_manual_process_pdf_batch($url);
        } elseif ($process_type === 'sitemap') {
            $processed = $this->mxchat_manual_process_sitemap_batch($url);
        }
        
        if ($processed > 0) {
            wp_send_json_success(array(
                'message' => "Processed {$processed} items successfully",
                'processed' => $processed
            ));
        } else {
            wp_send_json_error('No items were processed');
        }
        
    } catch (Exception $e) {
        //error_log('Manual batch process error: ' . $e->getMessage());
        wp_send_json_error('Processing failed: ' . $e->getMessage());
    }
}

/**
 * Process a small PDF batch manually - DIRECT PROCESSING
 */
private function mxchat_manual_process_pdf_batch($pdf_url) {
    try {
        $status_key = sanitize_key('mxchat_pdf_status_' . md5($pdf_url));
        $status = get_transient($status_key);
        
        if (!$status || $status['status'] !== 'processing') {
            //error_log('Manual PDF: No processing status found');
            return 0;
        }
        
        //error_log('Manual PDF: Starting direct processing for ' . $pdf_url);
        
        // Get current progress
        $current_page = $status['processed_pages'] ?? 0;
        $total_pages = $status['total_pages'] ?? 0;
        
        if ($current_page >= $total_pages) {
            //error_log('Manual PDF: Already completed');
            return 0;
        }
        
        // Try to download the PDF again for processing
        $response = wp_remote_get($pdf_url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            //error_log('Manual PDF: Failed to download PDF: ' . $response->get_error_message());
            return 0;
        }
        
        $pdf_content = wp_remote_retrieve_body($response);
        if (empty($pdf_content)) {
            //error_log('Manual PDF: Empty PDF content');
            return 0;
        }
        
        // Save PDF temporarily
        $upload_dir = wp_upload_dir();
        $temp_pdf_path = trailingslashit($upload_dir['path']) . 'temp_manual_' . time() . '.pdf';
        file_put_contents($temp_pdf_path, $pdf_content);
        
        // Process 2 pages directly
        $processed = $this->mxchat_process_pdf_pages_direct($temp_pdf_path, $pdf_url, $current_page, 5);
        
        // Clean up temp file
        if (file_exists($temp_pdf_path)) {
            wp_delete_file($temp_pdf_path);
        }
        
        //error_log('Manual PDF: Processed ' . $processed . ' pages');
        return $processed;
        
    } catch (Exception $e) {
        //error_log('Manual PDF batch error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Process PDF pages directly without cron
 */
private function mxchat_process_pdf_pages_direct($pdf_path, $pdf_url, $start_page, $batch_size) {
    try {
        if (!file_exists($pdf_path)) {
            //error_log('Direct PDF: File not found at ' . $pdf_path);
            return 0;
        }
        
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdf_path);
        $pages = $pdf->getPages();
        
        $status_key = sanitize_key('mxchat_pdf_status_' . md5($pdf_url));
        $status = get_transient($status_key);
        
        if (!$status) {
            return 0;
        }
        
        $options = get_option('mxchat_options');
        $api_key = $options['api_key'] ?? '';
        
        if (empty($api_key)) {
            //error_log('Direct PDF: No API key');
            return 0;
        }
        
        $processed = 0;
        $end_page = min($start_page + $batch_size, count($pages));
        
        for ($i = $start_page; $i < $end_page; $i++) {
            try {
                $page_number = $i + 1;
                $text = $pages[$i]->getText();
                
                if (empty($text)) {
                    //error_log('Direct PDF: Empty text on page ' . $page_number);
                    continue;
                }
                
                $sanitized_content = $this->mxchat_sanitize_content_for_api($text);
                if (empty($sanitized_content)) {
                    //error_log('Direct PDF: No content after sanitization on page ' . $page_number);
                    continue;
                }
                
                // Generate embedding
                $embedding_vector = $this->mxchat_generate_embedding($sanitized_content);
                if (is_string($embedding_vector)) {
                    //error_log('Direct PDF: Embedding failed on page ' . $page_number . ': ' . $embedding_vector);
                    continue;
                }
                
                // Create metadata
                $metadata = array(
                    'document_type' => 'pdf',
                    'total_pages' => count($pages),
                    'current_page' => $page_number,
                    'source_url' => $pdf_url
                );
                
                $content_with_metadata = wp_json_encode($metadata) . "\n---\n" . $sanitized_content;
                $page_url = esc_url($pdf_url . "#page=" . $page_number);
                
                // Store in database
                $db_result = MxChat_Utils::submit_content_to_db($content_with_metadata, $page_url, $api_key);
                
                if (is_wp_error($db_result)) {
                    //error_log('Direct PDF: DB error on page ' . $page_number . ': ' . $db_result->get_error_message());
                    continue;
                }
                
                $processed++;
                //error_log('Direct PDF: Successfully processed page ' . $page_number);
                
                // Update status
                $status['processed_pages'] = $i + 1;
                $status['last_update'] = time();
                $status['percentage'] = round(($status['processed_pages'] / $status['total_pages']) * 100);
                set_transient($status_key, $status, DAY_IN_SECONDS);
                
            } catch (Exception $e) {
                //error_log('Direct PDF: Error processing page ' . ($i + 1) . ': ' . $e->getMessage());
                continue;
            }
        }
        
        // Check if completed
        if ($status['processed_pages'] >= $status['total_pages']) {
            $status['status'] = 'complete';
            set_transient($status_key, $status, DAY_IN_SECONDS);
            //error_log('Direct PDF: Processing completed');
        }
        
        return $processed;
        
    } catch (Exception $e) {
        //error_log('Direct PDF processing error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Process a small sitemap batch manually - DIRECT PROCESSING
 */
private function mxchat_manual_process_sitemap_batch($sitemap_url) {
    try {
        $status_key = sanitize_key('mxchat_sitemap_status_' . md5($sitemap_url));
        $status = get_transient($status_key);
        
        if (!$status || $status['status'] !== 'processing') {
            return 0;
        }
        
        //error_log('Manual Sitemap: Starting direct processing for ' . $sitemap_url);
        
        // Re-fetch the sitemap to get URLs
        $response = wp_remote_get($sitemap_url, array('timeout' => 30));
        if (is_wp_error($response)) {
            //error_log('Manual Sitemap: Failed to fetch sitemap');
            return 0;
        }
        
        $sitemap_content = wp_remote_retrieve_body($response);
        $xml = simplexml_load_string($sitemap_content);
        
        if (!$xml) {
            //error_log('Manual Sitemap: Invalid XML');
            return 0;
        }
        
        $urls = array();
        foreach ($xml->url as $url_element) {
            $urls[] = (string)$url_element->loc;
        }
        
        $current_processed = $status['processed_urls'] ?? 0;
        $batch_size = 5;
        $processed = 0;
        
        // Process next 2 URLs
        for ($i = $current_processed; $i < min($current_processed + $batch_size, count($urls)); $i++) {
            $url = $urls[$i];
            
            if ($this->mxchat_process_single_url_direct($url)) {
                $processed++;
            }
            
            // Update status
            $status['processed_urls'] = $i + 1;
            $status['last_update'] = time();
            $status['percentage'] = round(($status['processed_urls'] / $status['total_urls']) * 100);
            set_transient($status_key, $status, DAY_IN_SECONDS);
        }
        
        // Check if completed
        if ($status['processed_urls'] >= $status['total_urls']) {
            $status['status'] = 'complete';
            set_transient($status_key, $status, DAY_IN_SECONDS);
        }
        
        //error_log('Manual Sitemap: Processed ' . $processed . ' URLs');
        return $processed;
        
    } catch (Exception $e) {
        //error_log('Manual sitemap batch error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Process a single URL directly
 */
private function mxchat_process_single_url_direct($url) {
    try {
        $response = wp_remote_get($url, array('timeout' => 30));
        if (is_wp_error($response)) {
            return false;
        }
        
        $html = wp_remote_retrieve_body($response);
        $content = $this->mxchat_extract_main_content($html);
        $sanitized = $this->mxchat_sanitize_content_for_api($content);
        
        if (empty($sanitized)) {
            return false;
        }
        
        $options = get_option('mxchat_options');
        $api_key = $options['api_key'] ?? '';
        
        $result = MxChat_Utils::submit_content_to_db($sanitized, $url, $api_key);
        
        return !is_wp_error($result);
        
    } catch (Exception $e) {
        //error_log('Single URL processing error: ' . $e->getMessage());
        return false;
    }
}


    // ========================================
    // MAIN CONTENT SUBMISSION HANDLERS
    // ========================================
    
public function mxchat_handle_content_submission() {
    // Check if the form was submitted and the user has permission.
    if (!isset($_POST['submit_content']) || !current_user_can('manage_options')) {
        return;
    }
    
    // Verify the nonce.
    $nonce = isset($_POST['mxchat_submit_content_nonce']) ? sanitize_text_field(wp_unslash($_POST['mxchat_submit_content_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'mxchat_submit_content_action')) {
        wp_die(esc_html__('Nonce verification failed.', 'mxchat'));
    }
    
    // Sanitize the inputs.
    $article_content = sanitize_textarea_field($_POST['article_content']);
    $article_url = isset($_POST['article_url']) ? esc_url_raw($_POST['article_url']) : '';
    
    // Get API key for submission
    $options = get_option('mxchat_options');
    $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
    
    if (strpos($selected_model, 'voyage') === 0) {
        $api_key = $options['voyage_api_key'] ?? '';
    } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
        $api_key = $options['gemini_api_key'] ?? '';
    } else {
        $api_key = $options['api_key'] ?? '';
    }
    
    if (empty($api_key)) {
        set_transient('mxchat_admin_notice_error',
            esc_html__('API key is not configured. Please add your API key in the settings before submitting content.', 'mxchat'),
            30
        );
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }
    
    // Use centralized utility function for storage
    $result = MxChat_Utils::submit_content_to_db($article_content, $article_url, $api_key);
    
    if (is_wp_error($result)) {
        set_transient('mxchat_admin_notice_error',
            esc_html__('Error storing content: ', 'mxchat') . $result->get_error_message(),
            30
        );
    } else {
        set_transient('mxchat_admin_notice_success',
            esc_html__('Content successfully submitted!', 'mxchat'),
            30
        );
    }
    
    wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
    exit;
}
public function mxchat_is_pdf_url($url, $response) {
    $content_type = wp_remote_retrieve_header($response, 'content-type');
    $file_extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));

    return strpos($content_type, 'pdf') !== false || $file_extension === 'pdf';
}
public function mxchat_handle_pdf_for_knowledge_base($pdf_url, $response) {
    if (!current_user_can('manage_options')) {
        //error_log(esc_html__('Unauthorized PDF processing attempt', 'mxchat'));
        return false;
    }

    $pdf_url = esc_url_raw($pdf_url);
    $upload_dir = wp_upload_dir();

    if (isset($upload_dir['error']) && $upload_dir['error'] !== false) {
        //error_log(sprintf(esc_html__('Upload directory error: %s', 'mxchat'), esc_html($upload_dir['error'])));
        return false;
    }

    $pdf_filename = sanitize_file_name('mxchat_kb_' . time() . '.pdf');
    $pdf_path = trailingslashit($upload_dir['path']) . $pdf_filename;

    $response_body = wp_remote_retrieve_body($response);
    if (empty($response_body)) {
        //error_log(esc_html__('Empty PDF response body', 'mxchat'));
        return false;
    }

    if (!wp_mkdir_p(dirname($pdf_path))) {
        //error_log(sprintf(esc_html__('Failed to create directory for PDF: %s', 'mxchat'), esc_html($pdf_path)));
        return false;
    }

    try {
        file_put_contents($pdf_path, $response_body);

        if (!file_exists($pdf_path)) {
            throw new Exception(__('Failed to save PDF file', 'mxchat'));
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdf_path);
        $total_pages = absint(count($pdf->getPages()));

        if ($total_pages < 1) {
            throw new Exception(__('Invalid PDF: no pages found', 'mxchat'));
        }

        wp_schedule_single_event(time(), 'mxchat_process_pdf_pages', array(
            'pdf_path' => $pdf_path,
            'pdf_url' => $pdf_url,
            'total_pages' => $total_pages,
            'batch_size' => absint(15),
            'batch_pause' => absint(10)
        ));

        $status_data = array(
            'total_pages' => $total_pages,
            'processed_pages' => 0,
            'status' => 'processing',
            'last_update' => time()
        );

        set_transient(
            sanitize_key('mxchat_pdf_status_' . md5($pdf_url)),
            array_map('sanitize_text_field', $status_data),
            DAY_IN_SECONDS
        );

        return __('scheduled', 'mxchat');

    } catch (Exception $e) {
        //error_log(sprintf(esc_html__('Error preparing PDF for processing: %s', 'mxchat'), esc_html($e->getMessage())));
        if (file_exists($pdf_path)) {
            wp_delete_file($pdf_path);
        }
        return false;
    }
}

public function mxchat_process_pdf_pages_cron($pdf_path, $pdf_url, $total_pages, $batch_size, $batch_pause) {
    // Validate inputs
    $pdf_path = sanitize_text_field($pdf_path);
    $pdf_url = esc_url_raw($pdf_url);
    $total_pages = absint($total_pages);
    $batch_size = absint($batch_size);
    $batch_pause = absint($batch_pause);

    try {
        if (!file_exists($pdf_path)) {
            throw new Exception(sprintf('PDF file not found at path: %s', esc_html($pdf_path)));
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdf_path);
        $pages = $pdf->getPages();

        // Get current progress
        $status_key = sanitize_key('mxchat_pdf_status_' . md5($pdf_url));
        $status = get_transient($status_key);

        if (!$status || !is_array($status)) {
            throw new Exception('Invalid status data retrieved from transient');
        }

        // Initialize failed pages list if it doesn't exist
        if (!isset($status['failed_pages_list']) || !is_array($status['failed_pages_list'])) {
            $status['failed_pages_list'] = [];
        }

        $start_page = absint($status['processed_pages']);
        $end_page = min($start_page + $batch_size, $total_pages);
        $options = get_option('mxchat_options');
        
        if (empty($options['api_key'])) {
            throw new Exception('API key is missing or invalid');
        }

        $successful_pages = 0;
        $failed_pages = 0;

        for ($i = $start_page; $i < $end_page; $i++) {
            $page_number = $i + 1;
            $max_retries = 3;
            $retry_count = 0;
            $page_processed = false;
            $last_error = '';

            while (!$page_processed && $retry_count < $max_retries) {
                try {
                    $text = $pages[$i]->getText();
                    
                    if (empty($text)) {
                        throw new Exception("Empty text on page {$page_number}");
                    }
                    
                    $sanitized_content = $this->mxchat_sanitize_content_for_api($text);

                    if (empty($sanitized_content)) {
                        throw new Exception("No valid content after sanitization on page {$page_number}");
                    }

                    $embedding_vector = $this->mxchat_generate_embedding($sanitized_content);
                    
                    if (is_string($embedding_vector)) {
                        throw new Exception("Embedding generation failed: " . $embedding_vector);
                    }
                    
                    if (!is_array($embedding_vector)) {
                        throw new Exception("Embedding generation returned unexpected result type: " . gettype($embedding_vector));
                    }
                    
                    $metadata = array(
                        'document_type' => 'pdf',
                        'total_pages' => $total_pages,
                        'current_page' => $page_number,
                        'prev_page' => $i > 0 ? $i : null,
                        'next_page' => $i < ($total_pages - 1) ? $i + 2 : null,
                        'source_url' => $pdf_url
                    );

                    $content_with_metadata = wp_json_encode($metadata) . "\n---\n" . $sanitized_content;
                    $page_url = esc_url($pdf_url . "#page=" . $page_number);

                    $db_result = MxChat_Utils::submit_content_to_db($content_with_metadata, $page_url, $options['api_key']);
                    
                    if (is_wp_error($db_result)) {
                        throw new Exception("Database submission failed: " . $db_result->get_error_message());
                    }

                    // Success!
                    $page_processed = true;
                    $successful_pages++;
                    
                } catch (Exception $e) {
                    $retry_count++;
                    $last_error = $e->getMessage();
                    
                    //error_log("PDF page {$page_number} failed (attempt {$retry_count}/{$max_retries}): " . $last_error);
                    
                    if ($retry_count < $max_retries) {
                        // Wait before retry (exponential backoff: 1s, 2s, 4s)
                        sleep(pow(2, $retry_count - 1));
                    }
                }
            }

            // If page still not processed after all retries, mark as failed
            if (!$page_processed) {
                $failed_pages++;
                $status['failed_pages_list'][] = [
                    'page' => $page_number,
                    'error' => $last_error,
                    'time' => time(),
                    'retries' => $max_retries
                ];
                
                // Limit failed pages list to prevent memory issues
                if (count($status['failed_pages_list']) > 50) {
                    $status['failed_pages_list'] = array_slice($status['failed_pages_list'], -50);
                }
                
                //error_log("PDF page {$page_number} permanently failed after {$max_retries} attempts: " . $last_error);
            }

            // Update progress
            $status['processed_pages'] = absint($page_number);
            $status['last_update'] = time();
            $status['failed_pages'] = absint($status['failed_pages'] ?? 0) + ($page_processed ? 0 : 1);
            
            set_transient($status_key, array_map('sanitize_text_field', $status), DAY_IN_SECONDS);
        }

        // Schedule next batch if needed
        if ($end_page < $total_pages) {
            wp_schedule_single_event(time() + $batch_pause, 'mxchat_process_pdf_pages', array(
                'pdf_path' => $pdf_path,
                'pdf_url' => $pdf_url,
                'total_pages' => $total_pages,
                'batch_size' => $batch_size,
                'batch_pause' => $batch_pause
            ));
        } else {
            // Processing complete
            $status['status'] = 'complete';
            $status['processed_pages'] = $total_pages;
            
            // Add completion summary
            $status['completion_summary'] = [
                'total_pages' => $total_pages,
                'successful_pages' => $total_pages - absint($status['failed_pages'] ?? 0),
                'failed_pages' => absint($status['failed_pages'] ?? 0),
                'completion_time' => current_time('mysql')
            ];
            
            // Save the completed status (don't delete it - let user dismiss manually)
            set_transient($status_key, array_map('sanitize_text_field', $status), DAY_IN_SECONDS);
            
            // Clean up the temporary PDF file
            if (file_exists($pdf_path)) {
                wp_delete_file($pdf_path);
            }
            
            // DON'T delete the status transients here - let user dismiss manually
        }

    } catch (\Exception $e) {
        //error_log(sprintf('[MXCHAT-PDF] Error processing PDF: %s', $e->getMessage()));
        
        $status_key = sanitize_key('mxchat_pdf_status_' . md5($pdf_url));
        $status = get_transient($status_key);
        
        if (!$status || !is_array($status)) {
            $status = array(
                'total_pages' => $total_pages,
                'processed_pages' => 0,
                'status' => 'error',
                'error' => sanitize_text_field($e->getMessage()),
                'last_update' => time()
            );
        } else {
            $status['status'] = 'error';
            $status['error'] = sanitize_text_field($e->getMessage());
            $status['last_update'] = time();
        }
        
        set_transient($status_key, array_map('sanitize_text_field', $status), DAY_IN_SECONDS);
        
        if (file_exists($pdf_path)) {
            wp_delete_file($pdf_path);
        }
    }
}

public function mxchat_save_inline_prompt() {
    // DEBUG: Log what we're receiving
    error_log('=== MXCHAT DEBUG ===');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('Nonce from POST: ' . ($_POST['_ajax_nonce'] ?? 'NOT FOUND'));
    
    // Check for nonce security
    check_ajax_referer('mxchat_save_inline_nonce', '_ajax_nonce');
    
    // If we get here, nonce passed
    error_log('Nonce verification PASSED');

         // Verify permissions
         if (!current_user_can('manage_options')) {
             wp_send_json_error(esc_html__('Permission denied.', 'mxchat'));
             return;
         }

         global $wpdb;
         $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';

         // Validate and sanitize input data
         $prompt_id = isset($_POST['id']) ? absint($_POST['id']) : 0;
         $article_content = isset($_POST['article_content']) ? sanitize_textarea_field($_POST['article_content']) : '';
         $article_url = isset($_POST['article_url']) ? esc_url_raw($_POST['article_url']) : '';

         if ($prompt_id > 0 && !empty($article_content)) {
             // Re-generate the embedding vector for the updated content
             $embedding_vector = $this->mxchat_generate_embedding($article_content);

             if (is_array($embedding_vector)) {
                 // Serialize the embedding vector before storing it
                 $embedding_vector_serialized = serialize($embedding_vector);

                 // Update the prompt in the database
                 $updated = $wpdb->update(
                     $table_name,
                     array(
                         'article_content'   => $article_content,
                         'embedding_vector'  => $embedding_vector_serialized,
                         'source_url'        => $article_url,
                     ),
                     array('id' => $prompt_id),
                     array('%s', '%s', '%s'),
                     array('%d')
                 );

                 if ($updated !== false) {
                     wp_send_json_success();
                 } else {
                     wp_send_json_error(esc_html__('Database update failed.', 'mxchat'));
                 }
             } else {
                 wp_send_json_error(esc_html__('Embedding generation failed.', 'mxchat'));
             }
         } else {
             wp_send_json_error(esc_html__('Invalid data.', 'mxchat'));
         }
     }


public function mxchat_get_pdf_processing_status($pdf_url) {
    $pdf_url = esc_url_raw($pdf_url);
    $status = get_transient(sanitize_key('mxchat_pdf_status_' . md5($pdf_url)));

    if (!$status || !is_array($status)) {
        return false;
    }

    // Check for stalled processing (no updates for 5 minutes)
    if ($status['status'] === 'processing' && (time() - absint($status['last_update'])) > 300) {
        $status['status'] = 'error';
        $status['error'] = __('PDF processing appears to be stalled. No updates for over 5 minutes.', 'mxchat');
        
        // Save the updated status
        set_transient(
            sanitize_key('mxchat_pdf_status_' . md5($pdf_url)),
            array_map('sanitize_text_field', $status),
            DAY_IN_SECONDS
        );
    }

    $result = array(
        'total_pages' => absint($status['total_pages']),
        'processed_pages' => absint($status['processed_pages']),
        'failed_pages' => absint($status['failed_pages'] ?? 0),
        'percentage' => ($status['total_pages'] > 0)
            ? round((absint($status['processed_pages']) / absint($status['total_pages'])) * 100)
            : 0,
        'status' => sanitize_text_field($status['status']),
        'last_update' => human_time_diff(absint($status['last_update']), time()) . ' ' . esc_html__('ago', 'mxchat'),
        'failed_pages_list' => isset($status['failed_pages_list']) ? $status['failed_pages_list'] : array(),
        'completion_summary' => isset($status['completion_summary']) ? $status['completion_summary'] : null
    );
    
    // Add error message if present
    if (isset($status['error']) && !empty($status['error'])) {
        $result['error'] = sanitize_text_field($status['error']);
    }
    
    return $result;
}

    
public function mxchat_handle_sitemap_submission() {
    // Start logging the submission process
    //error_log('[MXCHAT-URL] ===== Starting URL submission process =====');

    // Check if the form was submitted and verify permissions
    if (!isset($_POST['submit_sitemap']) || !current_user_can('manage_options')) {
        //error_log('[MXCHAT-URL] Error: Unauthorized access or form not submitted properly');
        wp_die(esc_html__('Unauthorized access', 'mxchat'));
    }

    // Verify nonce
    //error_log('[MXCHAT-URL] Verifying nonce');
    check_admin_referer('mxchat_submit_sitemap_action', 'mxchat_submit_sitemap_nonce');

    // Validate URL
    if (!isset($_POST['sitemap_url']) || empty($_POST['sitemap_url'])) {
        //error_log('[MXCHAT-URL] Error: Empty or missing URL');
        set_transient('mxchat_admin_notice_error',
            esc_html__('Please provide a valid URL.', 'mxchat'),
            30
        );
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    $submitted_url = esc_url_raw($_POST['sitemap_url']);
    //error_log('[MXCHAT-URL] Processing URL: ' . $submitted_url);

    // Validate API key first
    $options = get_option('mxchat_options');
    $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
    
    if (strpos($selected_model, 'voyage') === 0) {
        $api_key = $options['voyage_api_key'] ?? '';
        $provider_name = 'Voyage AI';
    } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
        $api_key = $options['gemini_api_key'] ?? '';
        $provider_name = 'Google Gemini';
    } else {
        $api_key = $options['api_key'] ?? '';
        $provider_name = 'OpenAI';
    }
    
    if (empty($api_key)) {
        $error_message = sprintf(
            esc_html__('%s API key is not configured. Please add your API key in the settings before submitting content.', 'mxchat'),
            $provider_name
        );
        //error_log('[MXCHAT-URL] Error: ' . $error_message);
        set_transient('mxchat_admin_notice_error', $error_message, 30);
        //error_log('[MXCHAT-URL] Set error transient: ' . $error_message);
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    //error_log('[MXCHAT-URL] Fetching URL content');
    $response = wp_remote_get($submitted_url, array('timeout' => 30));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP Status: ' . wp_remote_retrieve_response_code($response);
        //error_log('[MXCHAT-URL] Error fetching URL: ' . $error_message);
        set_transient('mxchat_admin_notice_error',
            sprintf(
                esc_html__('Failed to fetch the URL: %s', 'mxchat'),
                esc_html($error_message)
            ),
            30
        );
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    $content_type = wp_remote_retrieve_header($response, 'content-type');
    //error_log('[MXCHAT-URL] Content type: ' . $content_type);
    $body_content = wp_remote_retrieve_body($response);

    if (empty($body_content)) {
        //error_log('[MXCHAT-URL] Error: Empty response body');
        set_transient('mxchat_admin_notice_error',
            esc_html__('Empty response received from URL.', 'mxchat'),
            30
        );
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }
    //error_log('[MXCHAT-URL] Retrieved body content length: ' . strlen($body_content) . ' bytes');

    // Handle PDF URL
    if ($this->mxchat_is_pdf_url($submitted_url, $response)) {
        //error_log('[MXCHAT-URL] Detected PDF URL, handling PDF for knowledge base');
        $result = $this->mxchat_handle_pdf_for_knowledge_base($submitted_url, $response);
        //error_log('[MXCHAT-URL] PDF handling result: ' . $result);

        if ($result === 'scheduled') {
            set_transient(
                'mxchat_last_pdf_url',
                sanitize_text_field($submitted_url),
                DAY_IN_SECONDS
            );
            set_transient('mxchat_admin_notice_info',
                esc_html__('PDF processing has started in the background. You can check the progress in the Knowledge Base section.', 'mxchat'),
                30
            );
        } else {
            set_transient('mxchat_admin_notice_error',
                esc_html__('Failed to start PDF processing: ', 'mxchat') . esc_html($result),
                30
            );
        }

        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    // Handle Sitemap XML
    if (strpos($content_type, 'xml') !== false || strpos($body_content, '<urlset') !== false) {
        //error_log('[MXCHAT-URL] Detected XML content, processing as sitemap');
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body_content);
        $xml_errors = libxml_get_errors();
        libxml_clear_errors();

        if ($xml === false || !empty($xml_errors)) {
            //error_log('[MXCHAT-URL] Error: Invalid XML format');
            if (!empty($xml_errors)) {
                foreach ($xml_errors as $error) {
                    //error_log('[MXCHAT-URL] XML Error: ' . $error->message);
                }
            }

            set_transient('mxchat_admin_notice_error',
                esc_html__('Invalid sitemap XML. Please provide a valid sitemap.', 'mxchat'),
                30
            );
            wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
            exit;
        }

        //error_log('[MXCHAT-URL] Valid XML found, handling sitemap for knowledge base');
        $result = $this->mxchat_handle_sitemap_for_knowledge_base($xml, $submitted_url);
        //error_log('[MXCHAT-URL] Sitemap handling result: ' . $result);

        if ($result === 'scheduled') {
            set_transient(
                'mxchat_last_sitemap_url',
                sanitize_text_field($submitted_url),
                DAY_IN_SECONDS
            );
            set_transient('mxchat_admin_notice_info',
                esc_html__('Sitemap processing has started in the background. You can check the progress in the Knowledge Base section.', 'mxchat'),
                30
            );
        } else {
            // Return to the admin page without a redirect for better error display
            // The error is already stored in the sitemap status transient
            set_transient('mxchat_admin_notice_error',
                esc_html__('Failed to start sitemap processing. Please check the status below for details.', 'mxchat'),
                30
            );
        }

        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    // Handle Regular URL
    //error_log('[MXCHAT-URL] Processing as regular webpage');
    $page_content = $this->mxchat_extract_main_content($body_content);
    //error_log('[MXCHAT-URL] Extracted content length: ' . strlen($page_content) . ' bytes');

    $sanitized_content = $this->mxchat_sanitize_content_for_api($page_content);
    //error_log('[MXCHAT-URL] Sanitized content length: ' . strlen($sanitized_content) . ' bytes');

    if (empty($sanitized_content)) {
        //error_log('[MXCHAT-URL] Error: No valid content after sanitization');
        
        // Set both transients - the error notice and the URL status
        set_transient('mxchat_admin_notice_error',
            esc_html__('No valid content found on the provided URL.', 'mxchat'),
            30
        );
        
        // Set URL status transient
        set_transient('mxchat_single_url_status', [
            'url' => $submitted_url,
            'timestamp' => current_time('mysql'),
            'status' => 'failed',
            'error' => esc_html__('No valid content found on the provided URL.', 'mxchat')
        ], DAY_IN_SECONDS);
        
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    //error_log('[MXCHAT-URL] Generating embedding for content');
    $embedding_vector = $this->mxchat_generate_embedding($sanitized_content);

    // Check if embedding_vector is a string (error message)
    if (is_string($embedding_vector)) {
        //error_log('[MXCHAT-URL] Error generating embedding: ' . $embedding_vector);
        $error_message = esc_html__('Failed to generate embedding: ', 'mxchat') . esc_html($embedding_vector);
        //error_log('[MXCHAT-URL] Setting error transient: ' . $error_message);
        
        // Set both transients
        set_transient('mxchat_admin_notice_error', $error_message, 30);
        
        // Set URL status transient
        set_transient('mxchat_single_url_status', [
            'url' => $submitted_url,
            'timestamp' => current_time('mysql'),
            'status' => 'failed',
            'error' => $error_message
        ], DAY_IN_SECONDS);
        
        wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
        exit;
    }

    if (is_array($embedding_vector)) {
        //error_log('[MXCHAT-URL] Successfully generated embedding with ' . count($embedding_vector) . ' dimensions');

        $db_result = MxChat_Utils::submit_content_to_db(
            $sanitized_content,
            $submitted_url,
            $api_key
        );

        if (is_wp_error($db_result)) {
            //error_log('[MXCHAT-URL] Error: Failed to store content in database: ' . $db_result->get_error_message());
            $error_message = esc_html__('Failed to store content in database: ', 'mxchat') . esc_html($db_result->get_error_message());
            //error_log('[MXCHAT-URL] Setting error transient: ' . $error_message);
            
            // Set both transients
            set_transient('mxchat_admin_notice_error', $error_message, 30);
            
            // Set URL status transient
            set_transient('mxchat_single_url_status', [
                'url' => $submitted_url,
                'timestamp' => current_time('mysql'),
                'status' => 'failed',
                'error' => $error_message
            ], DAY_IN_SECONDS);
            
            wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
            exit;
        }

        //error_log('[MXCHAT-URL] Successfully stored content in database');
        $success_message = esc_html__('URL content successfully submitted!', 'mxchat');
        //error_log('[MXCHAT-URL] Setting success transient: ' . $success_message);
        
        // Set both transients
        set_transient('mxchat_admin_notice_success', $success_message, 30);
        
        // Set URL status transient with success
        set_transient('mxchat_single_url_status', [
            'url' => $submitted_url,
            'timestamp' => current_time('mysql'),
            'status' => 'complete',
            'content_length' => strlen($sanitized_content),
            'embedding_dimensions' => count($embedding_vector)
        ], DAY_IN_SECONDS);
        
    } else {
        //error_log('[MXCHAT-URL] Error: Failed to generate embedding. Unexpected result type: ' . gettype($embedding_vector));
        $error_message = esc_html__('Failed to generate embedding: Unexpected result type. Please check your API key and try again.', 'mxchat');
        //error_log('[MXCHAT-URL] Setting error transient: ' . $error_message);
        
        // Set both transients
        set_transient('mxchat_admin_notice_error', $error_message, 30);
        
        // Set URL status transient
        set_transient('mxchat_single_url_status', [
            'url' => $submitted_url,
            'timestamp' => current_time('mysql'),
            'status' => 'failed',
            'error' => $error_message
        ], DAY_IN_SECONDS);
    }

    //error_log('[MXCHAT-URL] ===== Completed URL submission process =====');
    wp_safe_redirect(esc_url(admin_url('admin.php?page=mxchat-prompts')));
    exit;
}
public function mxchat_get_single_url_status() {
    $status = get_transient('mxchat_single_url_status');
    if (!$status) {
        return null;
    }
    
    // Add human-readable time
    if (isset($status['timestamp'])) {
        $status['human_time'] = human_time_diff(strtotime($status['timestamp']), current_time('timestamp')) . ' ' . __('ago', 'mxchat');
    }
    
    return $status;
}
public function mxchat_handle_sitemap_for_knowledge_base($xml, $sitemap_url) {
    // Clear any single URL status when starting sitemap processing
    delete_transient('mxchat_single_url_status');
    if (!current_user_can('manage_options')) {
        //error_log(esc_html__('Unauthorized sitemap processing attempt', 'mxchat'));
        return false;
    }

    try {
        $sitemap_url = esc_url_raw($sitemap_url);

        if (!$xml || !is_object($xml)) {
            throw new Exception(__('Invalid XML object provided', 'mxchat'));
        }

        // Add embedding validation before processing
        // Test embedding with a small sample text to verify API key is working
        $test_result = $this->mxchat_generate_embedding("This is a test to verify the embedding API key is working.");
        
        // Check if test_result is a string (error message) rather than an array (valid embedding)
        if (is_string($test_result)) {
            //error_log('[MXCHAT-URL] Embedding API validation failed: ' . $test_result);
            
            // Store the error in the status transient so it can be displayed later
            $status_data = array(
                'total_urls' => 0,
                'processed_urls' => 0,
                'status' => 'error',
                'error' => __('Embedding API validation failed: ', 'mxchat') . $test_result,
                'last_update' => time()
            );
            
            set_transient(
                sanitize_key('mxchat_sitemap_status_' . md5($sitemap_url)),
                array_map('sanitize_text_field', $status_data),
                DAY_IN_SECONDS
            );
            
            throw new Exception(__('Embedding API validation failed: ', 'mxchat') . $test_result);
        }
        
        // Make sure it's an array (valid embedding)
        if (!is_array($test_result)) {
            //error_log('[MXCHAT-URL] Embedding API returned unexpected result type: ' . gettype($test_result));
            throw new Exception(__('Embedding API returned unexpected result type. Please check your configuration.', 'mxchat'));
        }

        $urls = [];
        foreach ($xml->url as $url_element) {
            $url = esc_url_raw((string)$url_element->loc);
            if ($url) {
                $urls[] = $url;
            }
        }

        $total_urls = absint(count($urls));

        if ($total_urls < 1) {
            throw new Exception(__('No valid URLs found in sitemap', 'mxchat'));
        }

        wp_schedule_single_event(time(), 'mxchat_process_sitemap_urls', array(
            'urls' => $urls,
            'sitemap_url' => $sitemap_url,
            'total_urls' => $total_urls,
            'batch_size' => absint(10),
            'batch_pause' => absint(5)
        ));

        $status_data = array(
            'total_urls' => $total_urls,
            'processed_urls' => 0,
            'status' => 'processing',
            'last_update' => time()
        );

        set_transient(
            sanitize_key('mxchat_sitemap_status_' . md5($sitemap_url)),
            array_map('sanitize_text_field', $status_data),
            DAY_IN_SECONDS
        );

        return __('scheduled', 'mxchat');

    } catch (\Exception $e) {
        $error_message = $e->getMessage();
        //error_log(sprintf(esc_html__('Error preparing sitemap for processing: %s', 'mxchat'), esc_html($error_message)));
        
        // Store the sitemap URL and error in transients so they can be displayed
        set_transient(
            'mxchat_last_sitemap_url',
            sanitize_text_field($sitemap_url),
            DAY_IN_SECONDS
        );
        
        $status_data = array(
            'total_urls' => 0,
            'processed_urls' => 0,
            'status' => 'error',
            'error' => $error_message,
            'last_update' => time()
        );
        
        set_transient(
            sanitize_key('mxchat_sitemap_status_' . md5($sitemap_url)),
            array_map('sanitize_text_field', $status_data),
            DAY_IN_SECONDS
        );
        
        return $error_message;
    }
}

public function mxchat_process_sitemap_urls_cron($urls, $sitemap_url, $total_urls, $batch_size, $batch_pause) {
    // Validate inputs
    $sitemap_url = esc_url_raw($sitemap_url);
    $total_urls = absint($total_urls);
    $batch_size = absint($batch_size);
    $batch_pause = absint($batch_pause);

    if (!is_array($urls) || empty($urls)) {
        return;
    }

    try {
        $status_key = sanitize_key('mxchat_sitemap_status_' . md5($sitemap_url));
        $status = get_transient($status_key);

        if (!$status || !is_array($status)) {
            throw new Exception('Invalid status data retrieved from transient');
        }

        // Initialize failed_urls array if it doesn't exist
        if (!isset($status['failed_urls_list']) || !is_array($status['failed_urls_list'])) {
            $status['failed_urls_list'] = [];
        }

        $start_url = absint($status['processed_urls']);
        $end_url = min($start_url + $batch_size, $total_urls);

        // Track batch statistics
        $batch_stats = [
            'processed' => 0,
            'failed' => 0,
            'last_error' => '',
            'embedding_errors' => 0
        ];

        // Check embedding configuration with first URL (only on first batch)
        if ($start_url === 0) {
            $test_url = esc_url_raw($urls[0]);
            $test_response = wp_remote_get($test_url);
            
            if (!is_wp_error($test_response) && wp_remote_retrieve_response_code($test_response) === 200) {
                $test_html = wp_remote_retrieve_body($test_response);
                $test_content = $this->mxchat_extract_main_content($test_html);
                $test_sanitized = $this->mxchat_sanitize_content_for_api($test_content);
                
                if (!empty($test_sanitized)) {
                    $test_embedding = $this->mxchat_generate_embedding($test_sanitized);
                    
                    if (is_string($test_embedding)) {
                        throw new Exception('Embedding generation failed: ' . $test_embedding);
                    }
                    
                    if (!is_array($test_embedding)) {
                        throw new Exception('Embedding generation returned unexpected result type: ' . gettype($test_embedding));
                    }
                }
            }
        }

        for ($i = $start_url; $i < $end_url; $i++) {
            $page_url = esc_url_raw($urls[$i]);
            $max_retries = 3;
            $retry_count = 0;
            $url_processed = false;
            $last_error = '';

            while (!$url_processed && $retry_count < $max_retries) {
                try {
                    // Attempt to fetch the URL
                    $page_response = wp_remote_get($page_url, array('timeout' => 30));

                    if (is_wp_error($page_response)) {
                        throw new Exception('HTTP request failed: ' . $page_response->get_error_message());
                    }

                    $response_code = wp_remote_retrieve_response_code($page_response);
                    if ($response_code !== 200) {
                        throw new Exception('HTTP Status: ' . $response_code);
                    }

                    $page_html = wp_remote_retrieve_body($page_response);
                    
                    if (empty($page_html)) {
                        throw new Exception('Empty response body');
                    }

                    $page_content = $this->mxchat_extract_main_content($page_html);
                    $sanitized_content = $this->mxchat_sanitize_content_for_api($page_content);

                    if (empty($sanitized_content)) {
                        throw new Exception('No valid content found after processing');
                    }

                    $embedding_vector = $this->mxchat_generate_embedding($sanitized_content);

                    if (is_string($embedding_vector)) {
                        throw new Exception('Embedding generation failed: ' . $embedding_vector);
                    }
                    
                    if (!is_array($embedding_vector)) {
                        throw new Exception('Embedding generation returned unexpected result type: ' . gettype($embedding_vector));
                    }

                    // Submit to database
                    $options = get_option('mxchat_options');
                    $submission_result = MxChat_Utils::submit_content_to_db($sanitized_content, $page_url, $options['api_key']);

                    if (is_wp_error($submission_result)) {
                        throw new Exception('Database submission failed: ' . $submission_result->get_error_message());
                    }

                    // Success!
                    $url_processed = true;
                    $batch_stats['processed']++;
                    
                } catch (Exception $e) {
                    $retry_count++;
                    $last_error = $e->getMessage();
                    
                    //error_log("URL {$page_url} failed (attempt {$retry_count}/{$max_retries}): " . $last_error);
                    
                    // Track embedding errors specifically
                    if (strpos($last_error, 'Embedding') !== false) {
                        $batch_stats['embedding_errors']++;
                    }
                    
                    if ($retry_count < $max_retries) {
                        // Wait before retry (exponential backoff: 1s, 2s, 4s)
                        sleep(pow(2, $retry_count - 1));
                    }
                }
            }

            // If URL still not processed after all retries, mark as failed
            if (!$url_processed) {
                $batch_stats['failed']++;
                $batch_stats['last_error'] = $last_error;
                
                // Add to failed URLs list
                $status['failed_urls_list'][] = [
                    'url' => $page_url,
                    'error' => $last_error,
                    'time' => time(),
                    'retries' => $max_retries
                ];
                
                // Limit the number of failed URLs we store to prevent transient size issues
                if (count($status['failed_urls_list']) > 100) {
                    $status['failed_urls_list'] = array_slice($status['failed_urls_list'], -100);
                }
            }

            // Update progress
            $status['processed_urls'] = absint($i + 1);
            $status['last_update'] = time();
            $status['failed_urls'] = absint($status['failed_urls'] ?? 0) + ($url_processed ? 0 : 1);
            $status['last_error'] = $batch_stats['last_error'];

            set_transient($status_key, $status, DAY_IN_SECONDS);
            
            // If we have too many consecutive embedding errors, stop processing
            if ($batch_stats['embedding_errors'] >= 10) {
                throw new Exception('Too many consecutive embedding failures detected. Please check your API configuration.');
            }
        }

        // If all URLs in this batch failed, stop processing
        if ($batch_stats['processed'] === 0 && $batch_stats['failed'] > 0) {
            $status['status'] = 'error';
            $status['error'] = sprintf(
                'Processing stopped: %d consecutive failures in batch. Last error: %s',
                $batch_stats['failed'],
                $batch_stats['last_error']
            );
            set_transient($status_key, $status, DAY_IN_SECONDS);
            return;
        }

        // Update final progress
        $status['processed_urls'] = min($end_url, $total_urls);
        $status['last_update'] = time();
        set_transient($status_key, $status, DAY_IN_SECONDS);

        // Check if we've processed all URLs
        if ($end_url >= $total_urls) {
            // All URLs have been processed - mark as complete
            $status['status'] = 'complete';
            $status['processed_urls'] = $total_urls;
            
            // Add completion summary
            $status['completion_summary'] = [
                'total_urls' => $total_urls,
                'successful_urls' => $total_urls - absint($status['failed_urls'] ?? 0),
                'failed_urls' => absint($status['failed_urls'] ?? 0),
                'completion_time' => current_time('mysql')
            ];
            
            // Save the completed status (don't delete it - let user dismiss manually)
            set_transient($status_key, $status, DAY_IN_SECONDS);
            
            // DON'T delete the status transients here - let user dismiss manually
        } else {
            // Schedule next batch
            wp_schedule_single_event(time() + $batch_pause, 'mxchat_process_sitemap_urls', array(
                'urls' => $urls,
                'sitemap_url' => $sitemap_url,
                'total_urls' => $total_urls,
                'batch_size' => $batch_size,
                'batch_pause' => $batch_pause,
            ));
        }
    } catch (\Exception $e) {
        $status['status'] = 'error';
        $status['error'] = $e->getMessage();
        set_transient($status_key, $status, DAY_IN_SECONDS);
    }
}
public function mxchat_sanitize_content_for_api($content) {
    //error_log('[MXCHAT-SANITIZE] Original content preview: ' . substr($content, 0, 500) . '...');
    
    // Remove script, style tags, and HTML comments
    $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
    $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
    $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);
    
    // Remove all HTML tags and decode HTML entities
    $content = wp_strip_all_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
    
    // Normalize whitespace but preserve paragraph breaks
    // First, normalize line endings to \n
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    // Replace multiple spaces/tabs with single space, but preserve newlines
    $content = preg_replace('/[ \t]+/', ' ', $content);
    // Replace 3+ newlines with 2 newlines (max 2 blank lines)
    $content = preg_replace('/\n{3,}/', "\n\n", $content);
    // Trim each line
    $lines = explode("\n", $content);
    $lines = array_map('trim', $lines);
    $content = implode("\n", $lines);
    // Final trim
    $content = trim($content);
    
    // Remove control characters (which can cause database issues) but preserve \n (0x0A) and \r (0x0D)
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
    
    // Remove NULL bytes which can cause database errors
    $content = str_replace("\0", "", $content);
    
    // Ensure valid UTF-8 encoding
    $content = wp_check_invalid_utf8($content);
    
    // Remove any extremely long strings without spaces (often garbage)
    $content = preg_replace('/\S{300,}/', ' ', $content);
    
    // Replace problematic characters that often cause database issues
    $content = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $content); // Remove emoji and other high Unicode characters
    
    // Replace any remaining potentially problematic characters with spaces
    // BUT preserve newlines by temporarily replacing them
    $content = str_replace("\n", "NEWLINE_PLACEHOLDER", $content);
    $content = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}\p{Sm}]/u', ' ', $content);
    $content = str_replace("NEWLINE_PLACEHOLDER", "\n", $content);
    
    // Limit to reasonable length if needed
    $max_length = 65000; // Just under MySQL TEXT field limit
    if (strlen($content) > $max_length) {
        $content = substr($content, 0, $max_length);
    }
    
    //error_log('[MXCHAT-SANITIZE] Sanitized content preview: ' . substr($content, 0, 500) . '...');
    return $content;
}
public function mxchat_extract_main_content($html) {
    if (empty($html)) {
        return '';
    }
    try {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true); // Suppress HTML parsing errors
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);
        
        // For debugging purposes
        $debugEnabled = false; // Set to true to enable debugging output
        $debug = function($message) use ($debugEnabled) {
            if ($debugEnabled) {
                //error_log('[MXCHAT-DEBUG] ' . $message);
            }
        };
        
        // Direct targeting for Gerow theme posts
        $post_text = $xpath->query('//div[contains(@class, "post-text")]');
        if ($post_text && $post_text->length > 0) {
            $debug("Found post-text directly");
            $content = '';
            foreach ($post_text as $node) {
                $content .= $dom->saveHTML($node);
            }
            if (!empty($content)) {
                $debug("Returning post-text content");
                return $content;
            }
        }
        
        // Try to get the blog details content which contains the post-text
        $blog_details = $xpath->query('//div[contains(@class, "blog-details-content")]');
        if ($blog_details && $blog_details->length > 0) {
            $debug("Found blog-details-content");
            $content = '';
            foreach ($blog_details as $node) {
                $content .= $dom->saveHTML($node);
            }
            if (!empty($content)) {
                $debug("Returning blog-details-content");
                return $content;
            }
        }
        
        // Try to get the article which contains the blog details
        $article = $xpath->query('//article[contains(@class, "blog-details-wrap")]');
        if ($article && $article->length > 0) {
            $debug("Found article with blog-details-wrap");
            $content = '';
            foreach ($article as $node) {
                $content .= $dom->saveHTML($node);
            }
            if (!empty($content)) {
                $debug("Returning article content");
                return $content;
            }
        }
        
        // Try even broader with the blog-item-wrap
        $blog_item = $xpath->query('//div[contains(@class, "blog-item-wrap")]');
        if ($blog_item && $blog_item->length > 0) {
            $debug("Found blog-item-wrap");
            $content = '';
            foreach ($blog_item as $node) {
                $content .= $dom->saveHTML($node);
            }
            if (!empty($content)) {
                $debug("Returning blog-item-wrap content");
                return $content;
            }
        }
        
        // Specific Gerow theme path
        $gerow_path = $xpath->query('//section[contains(@class, "blog-area")]//div[contains(@class, "post-text")]');
        if ($gerow_path && $gerow_path->length > 0) {
            $debug("Found Gerow theme path to post-text");
            $content = '';
            foreach ($gerow_path as $node) {
                $content .= $dom->saveHTML($node);
            }
            if (!empty($content)) {
                $debug("Returning Gerow post-text content");
                return $content;
            }
        }
        
        // Generic blog post selectors
        $selectors = [
            // Blog post specific selectors
            '//div[contains(@class, "post-text")]',
            '//article[contains(@class, "blog-post-item")]//div[contains(@class, "post-text")]',
            '//div[contains(@class, "blog-details-content")]',
            '//article[contains(@class, "blog-details-wrap")]',
            '//div[contains(@class, "entry-content")]',
            '//div[contains(@class, "blog-content")]',
            '//div[contains(@class, "blog-item-wrap")]',
            
            // More general content selectors
            '//div[contains(@class, "page__content")]',
            '//div[contains(@class, "elementor-widget-container")]',
            '//div[contains(@class, "elementor-text-editor")]',
            '//div[contains(@class, "elementor-widget-text-editor")]',
            '//*[contains(@class, "entry-content")]',
            '//*[contains(@class, "post-content")]',
            '//*[contains(@class, "article-content")]',
            '//*[@id="content"]',
            '//*[@id="main-content"]',
            '//section[contains(@class, "blog-area")]',
            '//article',
            '//main',
            '//div[contains(@class, "content")]'
        ];
        
        // First handle Elementor content
        $debug("Checking for Elementor content");
        $elementor_widgets = $xpath->query('//div[contains(@class, "elementor-element")]//div[contains(@class, "elementor-widget-container")]');
        if ($elementor_widgets && $elementor_widgets->length > 0) {
            $debug("Found Elementor widgets");
            $combined_content = '';
            foreach ($elementor_widgets as $widget) {
                $widget_content = $dom->saveHTML($widget);
                if (!empty($widget_content)) {
                    $combined_content .= $widget_content;
                }
            }
            if (!empty($combined_content)) {
                $debug("Returning Elementor content");
                return $combined_content;
            }
        }
        
        // Try standard selectors one by one
        foreach ($selectors as $selector) {
            $debug("Trying selector: " . $selector);
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $debug("Found matches for selector: " . $selector);
                $content = '';
                foreach ($nodes as $node) {
                    $content .= $dom->saveHTML($node);
                }
                if (!empty($content)) {
                    $debug("Returning content from selector: " . $selector);
                    return $content;
                }
            }
        }
        
        // Manual regex fallback for post-text if DOM methods fail
        $debug("Trying regex fallback");
        if (preg_match('/<div class="post-text">(.*?)<\/div>\s*<\/div>\s*<\/div>/s', $html, $matches)) {
            $debug("Found post-text via regex");
            return '<div class="post-text">' . $matches[1] . '</div>';
        }
        
        // Try to extract the blog section as a whole
        $blog_section = $xpath->query('//section[contains(@class, "blog-area")]');
        if ($blog_section && $blog_section->length > 0) {
            $debug("Found blog-area section");
            $content = '';
            foreach ($blog_section as $node) {
                $content .= $dom->saveHTML($node);
            }
            if (!empty($content)) {
                $debug("Returning blog-area section content");
                return $content;
            }
        }
        
        // Fallback: Return the body content if no specific selector matches
        $debug("Using body fallback");
        $body = $dom->getElementsByTagName('body');
        if ($body->length > 0) {
            return $dom->saveHTML($body->item(0));
        }
        
        // Last resort: return the original HTML
        $debug("Returning original HTML");
        return $html;
    } catch (Exception $e) {
        //error_log('[MXCHAT-ERROR] Content extraction failed: ' . $e->getMessage());
        return $html; // Return original HTML if parsing fails
    } finally {
        libxml_clear_errors();
    }
}
public function mxchat_get_sitemap_processing_status($sitemap_url) {
    $sitemap_url = esc_url_raw($sitemap_url);
    $status_key = sanitize_key('mxchat_sitemap_status_' . md5($sitemap_url));
    $status = get_transient($status_key);
    
    if (!$status || !is_array($status)) {
        return false;
    }
    
    // Auto-complete check: if all URLs are processed but status isn't complete
    if (isset($status['processed_urls']) && isset($status['total_urls']) && 
        $status['processed_urls'] >= $status['total_urls'] && 
        isset($status['status']) && $status['status'] !== 'complete' && 
        $status['status'] !== 'error') {
        
        // Mark as complete
        $status['status'] = 'complete';
        $status['processed_urls'] = $status['total_urls']; // Ensure exact match
        
        // Update the transient with the corrected status
        set_transient($status_key, $status, DAY_IN_SECONDS);
    }
    
    return array(
        'total_urls' => absint($status['total_urls']),
        'processed_urls' => absint($status['processed_urls']),
        'failed_urls' => absint($status['failed_urls'] ?? 0),
        'percentage' => ($status['total_urls'] > 0)
            ? round((absint($status['processed_urls']) / absint($status['total_urls'])) * 100)
            : 0,
        'status' => sanitize_text_field($status['status']),
        'last_update' => human_time_diff(absint($status['last_update']), time()) . ' ' . esc_html__('ago', 'mxchat'),
        'error' => isset($status['error']) ? sanitize_text_field($status['error']) : '',
        'last_error' => isset($status['last_error']) ? sanitize_text_field($status['last_error']) : '',
        'failed_urls_list' => isset($status['failed_urls_list']) ? $status['failed_urls_list'] : array()
    );
}

public function mxchat_ajax_get_status_updates() {
    try {
        // Verify the request
        check_ajax_referer('mxchat_status_nonce', 'nonce');
        
        // Get the status just like in your admin page
        $pdf_url     = get_transient('mxchat_last_pdf_url');
        $sitemap_url = get_transient('mxchat_last_sitemap_url');
        
        $pdf_status  = $pdf_url ? $this->mxchat_get_pdf_processing_status($pdf_url) : false;
        $sitemap_status = $sitemap_url ? $this->mxchat_get_sitemap_processing_status($sitemap_url) : false;
        
        // Add the PDF URL to the status object
        if ($pdf_status && $pdf_url) {
            $pdf_status['pdf_url'] = $pdf_url;
        }
        
        // Set the current PDF URL for the manual batch processing button
        $current_pdf_url = $pdf_url;
        
        // Check for true processing status, not just presence of status
        $is_active_processing = 
            ($sitemap_status && isset($sitemap_status['status']) && $sitemap_status['status'] === 'processing') || 
            ($pdf_status && isset($pdf_status['status']) && $pdf_status['status'] === 'processing');
        
        // Get single URL status, but only if no processing is active
        $single_url_status = !$is_active_processing ? $this->mxchat_get_single_url_status() : false;
        
        // REMOVED: Auto-clearing of completed status - now only done via dismiss button
        
        // Return JSON response with the status data
        wp_send_json(array(
            'pdf_status' => $pdf_status,
            'sitemap_status' => $sitemap_status,
            'single_url_status' => $single_url_status,
            'is_processing' => $is_active_processing,
            'current_pdf_url' => $current_pdf_url  
        ));
        
    } catch (Exception $e) {
        // Log the error
        //error_log('MxChat Status Update Error: ' . $e->getMessage());
        
        // Return a friendly error response
        wp_send_json_error(array(
            'message' => 'Error getting status updates: ' . $e->getMessage(),
            'status' => 'error'
        ));
    }
}
public function mxchat_stop_processing() {
    // Verify permissions
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized access', 'mxchat'));
    }

    // Verify nonce
    check_admin_referer('mxchat_stop_processing_action', 'mxchat_stop_processing_nonce');

    // Get the last sitemap URL and clear its transient
    $sitemap_url = get_transient('mxchat_last_sitemap_url');
    if ($sitemap_url) {
        delete_transient('mxchat_sitemap_status_' . md5($sitemap_url));
        delete_transient('mxchat_last_sitemap_url');
    }

    // Get the last PDF URL and clear its transient
    $pdf_url = get_transient('mxchat_last_pdf_url');
    if ($pdf_url) {
        delete_transient('mxchat_pdf_status_' . md5($pdf_url));
        delete_transient('mxchat_last_pdf_url');
    }

    // Unschedule any pending sitemap events
    $timestamp = wp_next_scheduled('mxchat_process_sitemap_urls');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'mxchat_process_sitemap_urls');
    }

    // Redirect back with a success message
    set_transient('mxchat_admin_notice_success',
        esc_html__('Processing has been stopped successfully.', 'mxchat'),
        30
    );
    wp_safe_redirect(admin_url('admin.php?page=mxchat-prompts'));
    exit;
}
public function ajax_mxchat_get_content_list() {
    // Verify the nonce
    check_ajax_referer('mxchat_content_selector_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Unauthorized access', 'mxchat'));
    }
    
    $page = isset($_GET['page']) ? absint($_GET['page']) : 1;
    $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 50;
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : 'all';
    $post_status = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : 'publish';
    $processed_filter = isset($_GET['processed_filter']) ? sanitize_text_field($_GET['processed_filter']) : 'all';
    
    // Build query args
    $args = array(
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => $post_status !== 'all' ? $post_status : array('publish', 'draft', 'pending'),
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    // Handle post types
    if ($post_type !== 'all') {
        $args['post_type'] = $post_type;
    } else {
        // Default to post and page if we can't get post types
        $args['post_type'] = array('post', 'page');
        
        // Try to get public post types
        $public_types = $this->mxchat_get_public_post_types();
        if (is_array($public_types) && !empty($public_types)) {
            $args['post_type'] = array_keys($public_types);
        }
    }
    
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // ================================
    // FIXED: Check only the ACTIVE storage method
    // ================================
    
    $processed_data = array();
    
    $pinecone_manager = MxChat_Pinecone_Manager::get_instance();
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $pinecone_manager->mxchat_refresh_after_new_content($pinecone_options);
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';

    if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
        // ONLY check Pinecone if it's enabled
        $processed_data = $this->mxchat_get_pinecone_processed_content($pinecone_options);
    } else {
        // ONLY check WordPress DB if Pinecone is not enabled
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
        $processed_items = $wpdb->get_results("SELECT id, source_url, timestamp FROM {$table_name}");
        
        if (!empty($processed_items)) {
            foreach ($processed_items as $item) {
                $post_id = url_to_postid($item->source_url);
                if ($post_id) {
                    $processed_data[$post_id] = array(
                        'db_id' => $item->id,
                        'timestamp' => $item->timestamp,
                        'url' => $item->source_url,
                        'source' => 'wordpress'
                    );
                }
            }
        }
    }
    
    // ================================
    
    // Get processed IDs as a simple array for in_array checks
    $processed_ids = array_keys($processed_data);
    
    // Handle processed/unprocessed filter
    if ($processed_filter === 'processed' && !empty($processed_ids)) {
        $args['post__in'] = $processed_ids;
    } elseif ($processed_filter === 'unprocessed' && !empty($processed_ids)) {
        $args['post__not_in'] = $processed_ids;
    }
    
    // Run the query
    $query = new WP_Query($args);
    $content_items = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();
            $post_date = get_the_date();
            $excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
            $word_count = str_word_count(strip_tags(get_the_content()));
            
            $is_processed = in_array($id, $processed_ids);
            $processed_date = '';
            $db_record_id = 0;
            $data_source = 'none';
            
            if ($is_processed && isset($processed_data[$id])) {
                $item_data = $processed_data[$id];
                $data_source = $item_data['source'];
                
                if ($data_source === 'wordpress' && isset($item_data['timestamp'])) {
                    // WordPress DB format
                    $timestamp = strtotime($item_data['timestamp']);
                    $processed_date = human_time_diff($timestamp, current_time('timestamp')) . ' ago';
                    $db_record_id = $item_data['db_id'];
                } elseif ($data_source === 'pinecone') {
                    // Pinecone format
                    $processed_date = $item_data['processed_date'];
                    $db_record_id = $item_data['db_id'];
                }
            }
            
            $content_items[] = array(
                'id' => $id,
                'title' => get_the_title(),
                'permalink' => get_permalink(),
                'date' => $post_date,
                'type' => get_post_type(),
                'status' => get_post_status(),
                'excerpt' => $excerpt,
                'word_count' => $word_count,
                'already_processed' => $is_processed,
                'processed_date' => $processed_date,
                'db_record_id' => $db_record_id,
                'data_source' => $data_source
            );
        }
        wp_reset_postdata();
    }
    
    $response = array(
        'items' => $content_items,
        'total' => $query->found_posts,
        'total_pages' => $query->max_num_pages,
        'current_page' => $page,
        'processed_count' => count($processed_ids)
    );
    
    wp_send_json_success($response);
    exit;
}

public function ajax_mxchat_process_selected_content() {
    // Basic request validation
    if (!check_ajax_referer('mxchat_content_selector_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        exit;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
        exit;
    }
    
    // Get post IDs - safely parse the array
    $post_ids = array();
    if (isset($_POST['post_ids']) && is_array($_POST['post_ids'])) {
        foreach ($_POST['post_ids'] as $id) {
            $post_ids[] = absint($id);
        }
    }
    
    if (empty($post_ids)) {
        wp_send_json_error('No content selected');
        exit;
    }
    
    // Process only ONE post at a time to avoid request size issues
    $post_id = reset($post_ids);
    $post = get_post($post_id);
    
    if (!$post) {
        wp_send_json_error('Post not found');
        exit;
    }
    
    // Get content including ACF fields
    $content = $post->post_title . "\n\n" . wp_strip_all_tags($post->post_content);
    
    // ADD ACF FIELDS SUPPORT
    $acf_fields = $this->mxchat_get_acf_fields_for_post($post_id);
    if (!empty($acf_fields)) {
        $acf_content_parts = array();
        
        foreach ($acf_fields as $field_name => $field_value) {
            $formatted_value = $this->mxchat_format_acf_field_value($field_value, $field_name, $post_id);
            
            if (!empty($formatted_value)) {
                // Convert field name to readable label
                $field_label = ucwords(str_replace('_', ' ', $field_name));
                $acf_content_parts[] = $field_label . ": " . $formatted_value;
            }
        }
        
        if (!empty($acf_content_parts)) {
            $content .= "\n\n" . implode("\n", $acf_content_parts);
        }
    }
    
    $content = substr($content, 0, 10000); // Limit content size
    
    // Get API key with proper model detection
    $options = get_option('mxchat_options');
    $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
    
    if (strpos($selected_model, 'voyage') === 0) {
        $api_key = $options['voyage_api_key'] ?? '';
        $provider_name = 'Voyage AI';
    } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
        $api_key = $options['gemini_api_key'] ?? '';
        $provider_name = 'Google Gemini';
    } else {
        $api_key = $options['api_key'] ?? '';
        $provider_name = 'OpenAI';
    }
    
    if (empty($api_key)) {
        wp_send_json_error($provider_name . ' API key not configured');
        exit;
    }
    
    $source_url = get_permalink($post_id);
    $vector_id = md5($source_url); // Vector ID for Pinecone
    
    // Check for existing content in ONLY the active storage method
    $is_update = false;
    
    // Check if Pinecone is enabled
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';
    
    if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
        // ONLY check Pinecone if it's enabled
        $pinecone_data = $this->mxchat_get_pinecone_processed_content($pinecone_options);
        if (isset($pinecone_data[$post_id])) {
            $is_update = true;
        }
    } else {
        // ONLY check WordPress DB if Pinecone is not enabled
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
        $existing_record = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE source_url = %s",
            $source_url
        ));
        
        if ($existing_record) {
            $is_update = true;
        }
    }
    
    // Use the centralized utility function for storage
    $result = MxChat_Utils::submit_content_to_db(
        $content, 
        $source_url, 
        $api_key,
        $vector_id
    );
    
    if (is_wp_error($result)) {
        wp_send_json_error('Storage failed: ' . $result->get_error_message());
        exit;
    }
    
    // Update caches if Pinecone is enabled
    if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
        // Update vector ID cache for improved fetching
        $this->mxchat_update_pinecone_vector_cache($vector_id);
        
        // Update local processed content cache for immediate UI feedback
        $pinecone_cache = get_option('mxchat_pinecone_processed_cache', array());
        $pinecone_cache[$post_id] = array(
            'db_id' => $vector_id,
            'processed_date' => 'Just now',
            'url' => $source_url,
            'source' => 'pinecone',
            'timestamp' => current_time('timestamp')
        );
        update_option('mxchat_pinecone_processed_cache', $pinecone_cache);
        
        // Also update the general processed content cache
        $processed_cache = get_option('mxchat_processed_content_cache', array());
        $processed_cache[$post_id] = array(
            'db_id' => $vector_id,
            'timestamp' => current_time('timestamp'),
            'url' => $source_url,
            'source' => 'pinecone'
        );
        update_option('mxchat_processed_content_cache', $processed_cache);
    }
    
    $operation_type = $is_update ? 'update' : 'new';
    
    // Count ACF fields for debugging
    $acf_field_count = count($acf_fields);
    
    // Success response with minimal data
    wp_send_json_success(array(
        'message' => $operation_type === 'update' ? 'Content updated successfully' : 'Content processed successfully',
        'post_id' => $post_id,
        'title'   => $post->post_title,
        'operation_type' => $operation_type,
        'vector_id' => $vector_id,
        'cache_updated' => $use_pinecone,
        'acf_fields_found' => $acf_field_count,
        'content_preview' => substr($content, 0, 100) . '...'
    ));
    exit;
}



    /**
     * Updates cache with new vector ID if absent
     */
     public function mxchat_update_pinecone_vector_cache($vector_id) {
         $cached_ids = get_option('mxchat_pinecone_vector_ids_cache', array());
         if (!in_array($vector_id, $cached_ids)) {
             $cached_ids[] = $vector_id;
             update_option('mxchat_pinecone_vector_ids_cache', $cached_ids);
         }
     }
public function mxchat_get_public_post_types() {
    $post_types = get_post_types(array('public' => true), 'objects');
    $post_type_options = array();
    
    foreach ($post_types as $post_type) {
        $post_type_options[$post_type->name] = $post_type->label;
    }
    
    return $post_type_options;
}
public function mxchat_get_pinecone_processed_content($pinecone_options) {
    //error_log('=== DEBUG: Starting mxchat_get_pinecone_processed_content ===');
    
    // First check local cache for immediate updates
    $cached_data = get_option('mxchat_pinecone_processed_cache', array());
    //error_log('DEBUG: Found ' . count($cached_data) . ' items in local cache');

    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';
    
    //error_log('DEBUG: API key present: ' . (!empty($api_key) ? 'YES' : 'NO'));
    //error_log('DEBUG: Host: ' . $host);

    if (empty($api_key) || empty($host)) {
        //error_log('DEBUG: Missing API credentials, returning cached data only');
        return $cached_data;
    }

    $pinecone_data = array();

    try {
        // Method 1: Try to get vectors using cached vector IDs first
        $cached_vector_ids = get_option('mxchat_pinecone_vector_ids_cache', array());
        //error_log('DEBUG: Found ' . count($cached_vector_ids) . ' cached vector IDs');

        if (!empty($cached_vector_ids)) {
            //error_log('DEBUG: Trying to fetch by cached vector IDs...');
            $pinecone_data = $this->mxchat_fetch_pinecone_vectors_by_ids($pinecone_options, $cached_vector_ids);
            //error_log('DEBUG: Fetch by IDs returned ' . count($pinecone_data) . ' items');
        }

        // Method 2: If no cached IDs or fetch failed, use scanning approach
        if (empty($pinecone_data)) {
            //error_log('DEBUG: Trying scanning approach...');
            $pinecone_data = $this->mxchat_scan_pinecone_for_processed_content($pinecone_options);
            //error_log('DEBUG: Scanning returned ' . count($pinecone_data) . ' items');
        }

        // Method 3: Final fallback - try stats endpoint
        if (empty($pinecone_data)) {
            //error_log('DEBUG: Trying stats endpoint...');
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
                //error_log('DEBUG: Pinecone stats: ' . print_r($stats_data, true));
            } else {
                if (is_wp_error($response)) {
                    //error_log('DEBUG: Stats endpoint error: ' . $response->get_error_message());
                } else {
                    //error_log('DEBUG: Stats endpoint failed with code: ' . wp_remote_retrieve_response_code($response));
                }
            }
        }

    } catch (Exception $e) {
        //error_log('DEBUG: Exception in get_pinecone_processed_content: ' . $e->getMessage());
    }

    // Merge cached data with Pinecone data
    $merged_data = $pinecone_data;

    foreach ($cached_data as $post_id => $cache_item) {
        $cache_timestamp = $cache_item['timestamp'] ?? 0;
        $time_diff = current_time('timestamp') - $cache_timestamp;

        if ($time_diff < 300) { // 5 minutes = 300 seconds
            $merged_data[$post_id] = $cache_item;
        } else {
            if (!isset($merged_data[$post_id])) {
                $merged_data[$post_id] = $cache_item;
            }
        }
    }

    //error_log('DEBUG: Final merged data count: ' . count($merged_data));
    //error_log('=== DEBUG: End mxchat_get_pinecone_processed_content ===');

    return $merged_data;
}

public function mxchat_fetch_pinecone_vectors_by_ids($pinecone_options, $vector_ids) {
    //error_log('=== DEBUG: Starting mxchat_fetch_pinecone_vectors_by_ids ===');
    
    $api_key = $pinecone_options['mxchat_pinecone_api_key'] ?? '';
    $host = $pinecone_options['mxchat_pinecone_host'] ?? '';

    if (empty($api_key) || empty($host) || empty($vector_ids)) {
        //error_log('DEBUG: Missing parameters for fetch by IDs');
        return array();
    }

    try {
        $fetch_url = "https://{$host}/vectors/fetch";
        //error_log('DEBUG: Fetch URL: ' . $fetch_url);
        //error_log('DEBUG: Fetching ' . count($vector_ids) . ' vector IDs');

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
            //error_log('DEBUG: Fetch by IDs WP error: ' . $response->get_error_message());
            return array();
        }

        $response_code = wp_remote_retrieve_response_code($response);
        //error_log('DEBUG: Fetch response code: ' . $response_code);
        
        if ($response_code !== 200) {
            $error_body = wp_remote_retrieve_body($response);
            //error_log('DEBUG: Fetch failed with body: ' . $error_body);
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        //error_log('DEBUG: Fetch response structure: ' . print_r(array_keys($data), true));

        if (!isset($data['vectors'])) {
            //error_log('DEBUG: No vectors key in response');
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
                    $processed_date = 'Recently';

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

        //error_log('DEBUG: Processed ' . count($processed_data) . ' vectors from fetch');
        return $processed_data;

    } catch (Exception $e) {
        //error_log('DEBUG: Exception in fetch_pinecone_vectors_by_ids: ' . $e->getMessage());
        return array();
    }
}

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

            // Generate a random unit vector instead of zeros
            $random_vector = array();
            for ($j = 0; $j < 1536; $j++) {
                $random_vector[] = (rand(-1000, 1000) / 1000.0);
            }

            // Normalize the vector to unit length
            $magnitude = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $random_vector)));
            if ($magnitude > 0) {
                $random_vector = array_map(function($x) use ($magnitude) { return $x / $magnitude; }, $random_vector);
            }

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
     * Generates embeddings from input text for MXChat
     */
     private function mxchat_generate_embedding($text) {
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
public function mxchat_ajax_dismiss_completed_status() {
    try {
        // Verify the request
        check_ajax_referer('mxchat_status_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            exit;
        }
        
        $card_type = isset($_POST['card_type']) ? sanitize_text_field($_POST['card_type']) : '';
        
        if ($card_type === 'pdf') {
            // Clear PDF status
            $pdf_url = get_transient('mxchat_last_pdf_url');
            if ($pdf_url) {
                delete_transient('mxchat_pdf_status_' . md5($pdf_url));
                delete_transient('mxchat_last_pdf_url');
            }
        } elseif ($card_type === 'sitemap') {
            // Clear sitemap status
            $sitemap_url = get_transient('mxchat_last_sitemap_url');
            if ($sitemap_url) {
                delete_transient('mxchat_sitemap_status_' . md5($sitemap_url));
                delete_transient('mxchat_last_sitemap_url');
            }
        }
        
        wp_send_json_success(array('message' => 'Status dismissed successfully'));
        
    } catch (Exception $e) {
        wp_send_json_error(array('message' => 'Error dismissing status: ' . $e->getMessage()));
    }
}

/**
 * Render completed status cards on page load
 * This ensures completed processing status persists through page refreshes
 */
public function mxchat_render_completed_status_cards() {
    $output = '';
    
    // Check for completed PDF status
    $pdf_url = get_transient('mxchat_last_pdf_url');
    if ($pdf_url) {
        $pdf_status = $this->mxchat_get_pdf_processing_status($pdf_url);
        if ($pdf_status && ($pdf_status['status'] === 'complete' || $pdf_status['status'] === 'error')) {
            $output .= $this->mxchat_render_pdf_status_card($pdf_status, $pdf_url);
        }
    }
    
    // Check for completed sitemap status
    $sitemap_url = get_transient('mxchat_last_sitemap_url');
    if ($sitemap_url) {
        $sitemap_status = $this->mxchat_get_sitemap_processing_status($sitemap_url);
        if ($sitemap_status && ($sitemap_status['status'] === 'complete' || $sitemap_status['status'] === 'error')) {
            $output .= $this->mxchat_render_sitemap_status_card($sitemap_status, $sitemap_url);
        }
    }
    
    return $output;
}

/**
 * Render PDF status card HTML
 */
private function mxchat_render_pdf_status_card($status, $pdf_url) {
    $html = '<div class="mxchat-status-card" data-card-type="pdf">';
    $html .= '<div class="mxchat-status-header">';
    $html .= '<h4>' . esc_html__('PDF Processing Status', 'mxchat') . '</h4>';
    
    // Add dismiss button for completed status
    if ($status['status'] === 'complete' || $status['status'] === 'error') {
        $html .= '<button type="button" class="mxchat-dismiss-button">' . esc_html__('Dismiss', 'mxchat') . '</button>';
    }
    
    // Process Batch button for processing status
    if ($status['status'] === 'processing') {
        $html .= '<button type="button" class="mxchat-manual-batch-btn" 
                  data-process-type="pdf"
                  data-url="' . esc_attr($pdf_url) . '">
                  ' . esc_html__('Process Batch', 'mxchat') . '</button>';
    }
    
    // Add status badges
    if ($status['status'] === 'error') {
        $html .= '<span class="mxchat-status-badge mxchat-status-failed">' . esc_html__('Error', 'mxchat') . '</span>';
    } elseif ($status['status'] === 'complete') {
        if ($status['failed_pages'] > 0) {
            $html .= '<span class="mxchat-status-badge mxchat-status-warning">' . 
                     sprintf(esc_html__('Completed with %d failures', 'mxchat'), $status['failed_pages']) . '</span>';
        } else {
            $html .= '<span class="mxchat-status-badge mxchat-status-success">' . esc_html__('Complete', 'mxchat') . '</span>';
        }
    }
    
    $html .= '</div>'; // End header
    
    // Progress bar
    $html .= '<div class="mxchat-progress-bar">';
    $html .= '<div class="mxchat-progress-fill" style="width: ' . esc_attr($status['percentage']) . '%"></div>';
    $html .= '</div>';
    
    // Status details
    $html .= '<div class="mxchat-status-details">';
    $html .= '<p>' . sprintf(
        esc_html__('Progress: %d of %d pages (%d%%)', 'mxchat'),
        $status['processed_pages'],
        $status['total_pages'],
        $status['percentage']
    ) . '</p>';
    
    // Show failed pages count if any
    if ($status['failed_pages'] > 0) {
        $html .= '<p><strong>' . esc_html__('Failed pages:', 'mxchat') . '</strong> ' . esc_html($status['failed_pages']) . '</p>';
    }
    
    $html .= '<p><strong>' . esc_html__('Status:', 'mxchat') . '</strong> ' . esc_html(ucfirst($status['status'])) . '</p>';
    $html .= '<p><strong>' . esc_html__('Last update:', 'mxchat') . '</strong> ' . esc_html($status['last_update']) . '</p>';
    
    // Add completion summary if available AND it's an array
    if (isset($status['completion_summary']) && is_array($status['completion_summary']) && !empty($status['completion_summary'])) {
        $summary = $status['completion_summary'];
        $html .= '<div class="mxchat-completion-summary">';
        $html .= '<h5>' . esc_html__('Processing Summary', 'mxchat') . '</h5>';
        $html .= '<p><strong>' . esc_html__('Total Pages:', 'mxchat') . '</strong> ' . esc_html($summary['total_pages']) . '</p>';
        $html .= '<p><strong>' . esc_html__('Successful:', 'mxchat') . '</strong> ' . esc_html($summary['successful_pages']) . '</p>';
        $html .= '<p><strong>' . esc_html__('Failed:', 'mxchat') . '</strong> ' . esc_html($summary['failed_pages']) . '</p>';
        $html .= '<p><strong>' . esc_html__('Completed:', 'mxchat') . '</strong> ' . esc_html($summary['completion_time']) . '</p>';
        $html .= '</div>';
    }
    
    // Add failed pages list if any AND it's an array
    if (isset($status['failed_pages_list']) && is_array($status['failed_pages_list']) && !empty($status['failed_pages_list'])) {
        $html .= $this->mxchat_render_failed_pages_list($status['failed_pages_list']);
    }
    
    // Add error message if any
    if (isset($status['error']) && !empty($status['error'])) {
        $html .= '<div class="mxchat-error-notice">';
        $html .= '<p class="error">' . esc_html($status['error']) . '</p>';
        $html .= '</div>';
    }
    
    $html .= '</div>'; // End details
    $html .= '</div>'; // End card
    
    return $html;
}
/**
 * Render sitemap status card HTML
 */
private function mxchat_render_sitemap_status_card($status, $sitemap_url) {
    $html = '<div class="mxchat-status-card" data-card-type="sitemap">';
    $html .= '<div class="mxchat-status-header">';
    $html .= '<h4>' . esc_html__('Sitemap Processing Status', 'mxchat') . '</h4>';
    
    // Add dismiss button for completed status  
    if ($status['status'] === 'complete' || $status['status'] === 'error') {
        $html .= '<button type="button" class="mxchat-dismiss-button">' . esc_html__('Dismiss', 'mxchat') . '</button>';
    }
    
    // Process Batch button for processing status
    if ($status['status'] === 'processing') {
        $html .= '<button type="button" class="mxchat-manual-batch-btn" 
                  data-process-type="sitemap"
                  data-url="' . esc_attr($sitemap_url) . '">
                  ' . esc_html__('Process Batch', 'mxchat') . '</button>';
    }

    // Add status badges
    if ($status['status'] === 'error') {
        $html .= '<span class="mxchat-status-badge mxchat-status-failed">' . esc_html__('Error', 'mxchat') . '</span>';
    } elseif ($status['status'] === 'complete') {
        if ($status['failed_urls'] > 0) {
            $html .= '<span class="mxchat-status-badge mxchat-status-warning">' . 
                     sprintf(esc_html__('Completed with %d failures', 'mxchat'), $status['failed_urls']) . '</span>';
        } else {
            $html .= '<span class="mxchat-status-badge mxchat-status-success">' . esc_html__('Complete', 'mxchat') . '</span>';
        }
    }
    
    $html .= '</div>'; // End header
    
    // Progress bar
    $html .= '<div class="mxchat-progress-bar">';
    $html .= '<div class="mxchat-progress-fill" style="width: ' . esc_attr($status['percentage']) . '%"></div>';
    $html .= '</div>';
    
    // Status details
    $html .= '<div class="mxchat-status-details">';
    $html .= '<p>' . sprintf(
        esc_html__('Progress: %d of %d URLs (%d%%)', 'mxchat'),
        $status['processed_urls'],
        $status['total_urls'],
        $status['percentage']
    ) . '</p>';
    
    // Show failed URLs count if any
    if ($status['failed_urls'] > 0) {
        $html .= '<p><strong>' . esc_html__('Failed URLs:', 'mxchat') . '</strong> ' . esc_html($status['failed_urls']) . '</p>';
    }
    
    $html .= '<p><strong>' . esc_html__('Status:', 'mxchat') . '</strong> ' . esc_html(ucfirst($status['status'])) . '</p>';
    $html .= '<p><strong>' . esc_html__('Last update:', 'mxchat') . '</strong> ' . esc_html($status['last_update']) . '</p>';
    
    // Add completion summary if available AND it's an array
    if (isset($status['completion_summary']) && is_array($status['completion_summary']) && !empty($status['completion_summary'])) {
        $summary = $status['completion_summary'];
        $html .= '<div class="mxchat-completion-summary">';
        $html .= '<h5>' . esc_html__('Processing Summary', 'mxchat') . '</h5>';
        $html .= '<p><strong>' . esc_html__('Total URLs:', 'mxchat') . '</strong> ' . esc_html($summary['total_urls']) . '</p>';
        $html .= '<p><strong>' . esc_html__('Successful:', 'mxchat') . '</strong> ' . esc_html($summary['successful_urls']) . '</p>';
        $html .= '<p><strong>' . esc_html__('Failed:', 'mxchat') . '</strong> ' . esc_html($summary['failed_urls']) . '</p>';
        $html .= '<p><strong>' . esc_html__('Completed:', 'mxchat') . '</strong> ' . esc_html($summary['completion_time']) . '</p>';
        $html .= '</div>';
    }
    
    // Add error messages if any (but not the failed URLs list)
    if (!empty($status['error']) || !empty($status['last_error'])) {
        $html .= '<div class="mxchat-error-notice">';
        
        if (!empty($status['error'])) {
            $html .= '<p class="error">' . esc_html($status['error']) . '</p>';
        }
        
        if (!empty($status['last_error'])) {
            $html .= '<p class="last-error">' . esc_html__('Last error:', 'mxchat') . ' ' . esc_html($status['last_error']) . '</p>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>'; // End details
    $html .= '</div>'; // End card
    
    return $html;
}


/**
 * Render failed pages list
 */
private function mxchat_render_failed_pages_list($failed_pages_list) {
    // Validate that $failed_pages_list is an array and not empty
    if (!is_array($failed_pages_list) || empty($failed_pages_list)) {
        return '';
    }
    
    $html = '<div class="mxchat-error-notice">';
    $html .= '<div class="mxchat-failed-pages-container">';
    $html .= '<h5>' . sprintf(esc_html__('Failed Pages (%d)', 'mxchat'), count($failed_pages_list)) . '</h5>';
    $html .= '<details>';
    $html .= '<summary>' . esc_html__('Show Failed Pages', 'mxchat') . '</summary>';
    $html .= '<div class="mxchat-failed-pages-list">';
    
    // Create table for failed pages
    $html .= '<table class="widefat striped">';
    $html .= '<thead><tr>';
    $html .= '<th>' . esc_html__('Page', 'mxchat') . '</th>';
    $html .= '<th>' . esc_html__('Error', 'mxchat') . '</th>';
    $html .= '<th>' . esc_html__('Retries', 'mxchat') . '</th>';
    $html .= '<th>' . esc_html__('Time', 'mxchat') . '</th>';
    $html .= '</tr></thead><tbody>';
    
    // Sort failed pages by most recent
    $sorted_failed_pages = $failed_pages_list;
    usort($sorted_failed_pages, function($a, $b) {
        return ($b['time'] ?? 0) - ($a['time'] ?? 0);
    });
    
    foreach ($sorted_failed_pages as $item) {
        // Ensure $item is an array before accessing its elements
        if (!is_array($item)) {
            continue;
        }
        
        $time_ago = isset($item['time']) ? human_time_diff($item['time'], current_time('timestamp')) . ' ' . esc_html__('ago', 'mxchat') : 'Unknown';
        $html .= '<tr>';
        $html .= '<td>' . esc_html__('Page', 'mxchat') . ' ' . esc_html($item['page'] ?? 'Unknown') . '</td>';
        $html .= '<td style="word-break: break-word;">' . esc_html($item['error'] ?? 'Unknown error') . '</td>';
        $html .= '<td>' . esc_html($item['retries'] ?? 'N/A') . '</td>';
        $html .= '<td>' . esc_html($time_ago) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    $html .= '</div></details></div></div>';
    
    return $html;
}

/**
 * Render failed URLs list
 */
private function mxchat_render_failed_urls_list($failed_urls_list) {
    // Validate that $failed_urls_list is an array and not empty
    if (!is_array($failed_urls_list) || empty($failed_urls_list)) {
        return '';
    }
    
    $html = '<div class="mxchat-failed-urls-container">';
    $html .= '<h5>' . sprintf(esc_html__('Failed URLs (%d)', 'mxchat'), count($failed_urls_list)) . '</h5>';
    $html .= '<details>';
    $html .= '<summary>' . esc_html__('Show Failed URLs', 'mxchat') . '</summary>';
    $html .= '<div class="mxchat-failed-urls-list">';
    
    // Create table for failed URLs
    $html .= '<table class="widefat striped">';
    $html .= '<thead><tr>';
    $html .= '<th>' . esc_html__('URL', 'mxchat') . '</th>';
    $html .= '<th>' . esc_html__('Error', 'mxchat') . '</th>';
    $html .= '<th>' . esc_html__('Retries', 'mxchat') . '</th>';
    $html .= '<th>' . esc_html__('Time', 'mxchat') . '</th>';
    $html .= '</tr></thead><tbody>';
    
    // Sort failed URLs by most recent
    $sorted_failed_urls = $failed_urls_list;
    usort($sorted_failed_urls, function($a, $b) {
        return ($b['time'] ?? 0) - ($a['time'] ?? 0);
    });
    
    // Show up to 50 failed URLs
    $display_urls = array_slice($sorted_failed_urls, 0, 50);
    
    foreach ($display_urls as $item) {
        // Ensure $item is an array before accessing its elements
        if (!is_array($item)) {
            continue;
        }
        
        $url = $item['url'] ?? '';
        $time_ago = isset($item['time']) ? human_time_diff($item['time'], current_time('timestamp')) . ' ' . esc_html__('ago', 'mxchat') : 'Unknown';
        
        // Truncate URL for display
        $display_url = strlen($url) > 50 ? substr($url, 0, 47) . '...' : $url;
        
        $html .= '<tr>';
        $html .= '<td style="word-break: break-all;">';
        if (!empty($url)) {
            $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($display_url) . '</a>';
        } else {
            $html .= esc_html__('Unknown URL', 'mxchat');
        }
        $html .= '</td>';
        $html .= '<td style="word-break: break-word;">' . esc_html($item['error'] ?? 'Unknown error') . '</td>';
        $html .= '<td>' . esc_html($item['retries'] ?? 'N/A') . '</td>';
        $html .= '<td>' . esc_html($time_ago) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    if (count($failed_urls_list) > 50) {
        $html .= '<div class="mxchat-failed-urls-more">+ ' . 
                 (count($failed_urls_list) - 50) . 
                 ' ' . esc_html__('more failed URLs not shown', 'mxchat') . '</div>';
    }
    
    $html .= '</div></details></div>';
    
    return $html;
}

/**
 * Get all ACF fields for a specific post
 */
public function mxchat_get_acf_fields_for_post($post_id) {
    if (!function_exists('get_fields')) {
        return array();
    }
    
    $fields = get_fields($post_id);
    if (!$fields || !is_array($fields)) {
        return array();
    }
    
    return $fields;
}

/**
 * Format ACF field values for content extraction
 */
public function mxchat_format_acf_field_value($value, $field_name = '', $post_id = 0) {
    if (empty($value)) {
        return '';
    }
    
    // Handle different ACF field types
    if (is_array($value)) {
        // Check if it's an image/file field
        if (isset($value['url'])) {
            // Image field - return alt text, title, or caption
            if (!empty($value['alt'])) {
                return $value['alt'];
            } elseif (!empty($value['title'])) {
                return $value['title'];
            } elseif (!empty($value['caption'])) {
                return $value['caption'];
            } else {
                return ''; // Don't include just the URL
            }
        }
        
        // Check if it's a post object or relationship field
        if (isset($value['post_title'])) {
            return $value['post_title'];
        }
        
        // Check if it's a user field
        if (isset($value['display_name'])) {
            return $value['display_name'];
        }
        
        // Check if it's a taxonomy term
        if (isset($value['name']) && isset($value['taxonomy'])) {
            return $value['name'];
        }
        
        // Check if it's a select field with label
        if (isset($value['label'])) {
            return $value['label'];
        }
        
        // Check for repeater field or flexible content
        if (is_numeric(key($value))) {
            $sub_values = array();
            foreach ($value as $sub_item) {
                if (is_array($sub_item)) {
                    // For repeater/flexible content, extract text values
                    $sub_text = $this->mxchat_extract_text_from_acf_array($sub_item);
                    if (!empty($sub_text)) {
                        $sub_values[] = $sub_text;
                    }
                } else {
                    $sub_values[] = (string) $sub_item;
                }
            }
            return implode(', ', array_filter($sub_values));
        }
        
        // For other arrays, try to extract meaningful text
        $text_values = array();
        foreach ($value as $key => $val) {
            if (is_string($val) && !empty(trim($val))) {
                $text_values[] = trim($val);
            } elseif (is_array($val) && isset($val['post_title'])) {
                $text_values[] = $val['post_title'];
            } elseif (is_array($val) && isset($val['name'])) {
                $text_values[] = $val['name'];
            }
        }
        
        return implode(', ', array_filter($text_values));
    }
    
    // Handle object values
    if (is_object($value)) {
        if (isset($value->post_title)) {
            return $value->post_title;
        } elseif (isset($value->display_name)) {
            return $value->display_name;
        } elseif (isset($value->name)) {
            return $value->name;
        } elseif (method_exists($value, '__toString')) {
            return (string) $value;
        }
        return '';
    }
    
    // Handle boolean values
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    
    // For everything else, convert to string
    return (string) $value;
}

/**
 * Extract text from complex ACF array structures
 */
private function mxchat_extract_text_from_acf_array($array) {
    if (!is_array($array)) {
        return '';
    }
    
    $text_parts = array();
    
    foreach ($array as $key => $value) {
        if (is_string($value) && !empty(trim($value))) {
            // Skip keys that are likely to be IDs or technical values
            if (!is_numeric($value) || strlen($value) > 10) {
                $text_parts[] = trim($value);
            }
        } elseif (is_array($value)) {
            if (isset($value['post_title'])) {
                $text_parts[] = $value['post_title'];
            } elseif (isset($value['name'])) {
                $text_parts[] = $value['name'];
            } elseif (isset($value['label'])) {
                $text_parts[] = $value['label'];
            }
        }
    }
    
    return implode(', ', array_filter($text_parts));
}



public function mxchat_handle_post_update($post_id, $post, $update) {
    // Basic validation checks
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Only process published content
    if ($post->post_status !== 'publish') {
        return;
    }
    
    $post_type = $post->post_type;
    
    // Check if sync is enabled for this post type
    $should_sync = false;
    
    // Check built-in post types first
    if ($post_type === 'post' && get_option('mxchat_auto_sync_posts') === '1') {
        $should_sync = true;
    } else if ($post_type === 'page' && get_option('mxchat_auto_sync_pages') === '1') {
        $should_sync = true;
    } else {
        // Check custom post types
        $option_name = 'mxchat_auto_sync_' . $post_type;
        if (get_option($option_name) === '1') {
            $should_sync = true;
        }
    }
    
    if (!$should_sync) {
        return;
    }
    
    // Get content with proper formatting (matching ajax_mxchat_process_selected_content)
    $title = get_the_title($post_id);
    $content = get_post_field('post_content', $post_id);
    
    // Apply WordPress content filters to get properly formatted content
    $content = apply_filters('the_content', $content);
    
    // Strip tags but preserve structure
    $content = wp_strip_all_tags($content);
    
    // Combine title and content
    $final_content = $title . "\n\n" . $content;
    
    // For custom post types like job_listing, include additional fields
    if ($post_type === 'job_listing') {
        // Add job-specific meta if available
        $job_location = get_post_meta($post_id, '_job_location', true);
        if (!empty($job_location)) {
            $final_content .= "\n\nLocation: " . $job_location;
        }
        
        // Get job type terms
        $job_types = get_the_terms($post_id, 'job_listing_type');
        if (!empty($job_types) && !is_wp_error($job_types)) {
            $types = array();
            foreach ($job_types as $type) {
                $types[] = $type->name;
            }
            $final_content .= "\n\nJob Type: " . implode(', ', $types);
        }
        
        // Get company name if available
        $company_name = get_post_meta($post_id, '_company_name', true);
        if (!empty($company_name)) {
            $final_content .= "\n\nCompany: " . $company_name;
        }
    }
    
    // Get the source URL
    $source_url = get_permalink($post_id);
    
    // Get API key with proper model detection
    $options = get_option('mxchat_options');
    $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
    
    if (strpos($selected_model, 'voyage') === 0) {
        $api_key = $options['voyage_api_key'] ?? '';
    } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
        $api_key = $options['gemini_api_key'] ?? '';
    } else {
        $api_key = $options['api_key'] ?? '';
    }
    
    if (empty($api_key)) {
        //error_log('MxChat Auto-sync: No API key configured for embedding model');
        return;
    }
    
    // Use the centralized utility function for storage
    $result = MxChat_Utils::submit_content_to_db(
        $final_content, 
        $source_url, 
        $api_key,
        md5($source_url) // Vector ID for Pinecone
    );
    
    if (is_wp_error($result)) {
        //error_log('MxChat Auto-sync failed for post ' . $post_id . ': ' . $result->get_error_message());
    }
}


public function mxchat_handle_post_delete($post_id) {
    // Get post data before it's deleted
    $post = get_post($post_id);

    // Basic validation
    if (!$post || wp_is_post_revision($post_id)) {
        return;
    }

    $post_type = $post->post_type;
    
    // Check if sync is enabled for this post type
    $should_sync = false;
    
    // Check built-in post types first
    if ($post_type === 'post' && get_option('mxchat_auto_sync_posts') === '1') {
        $should_sync = true;
    } else if ($post_type === 'page' && get_option('mxchat_auto_sync_pages') === '1') {
        $should_sync = true;
    } else {
        // Check custom post types
        $option_name = 'mxchat_auto_sync_' . $post_type;
        if (get_option($option_name) === '1') {
            $should_sync = true;
        }
    }
    
    if (!$should_sync) {
        return;
    }

    // Get the URL before post is deleted
    $source_url = get_permalink($post_id);
    if (!$source_url) {
        //error_log('MXChat: Failed to get permalink for post ' . $post_id);
        return;
    }

    // Check if Pinecone is enabled
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';

    if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
        // Delete from Pinecone
        $this->mxchat_delete_from_pinecone_by_url($source_url, $pinecone_options);
    } else {
        // Delete from WordPress DB
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
        
        $result = $wpdb->delete(
            $table_name,
            array('source_url' => $source_url),
            array('%s')
        );
        
        if ($result === false) {
            //error_log('MXChat: WordPress DB deletion failed for URL: ' . $source_url);
        }
    }
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



public function mxchat_handle_product_change($post_id, $post, $update) {
    if ($post->post_type !== 'product') {
        return;
    }

    if ($post->post_status === 'publish') {
        add_action('shutdown', function() use ($post_id) {
            $product = wc_get_product($post_id);
            if ($product) {
                $this->mxchat_store_product_embedding($product);
            }
        });
    }
}

/**
 * Store WooCommerce product embeddings
 */
private function mxchat_store_product_embedding($product) {
    if (!isset($this->options['enable_woocommerce_integration']) ||
        !in_array($this->options['enable_woocommerce_integration'], ['1', 'on'])) {
        return;
    }
    
    $source_url = get_permalink($product->get_id());
    
    // Build product content
    $title = $product->get_name();
    $description = $product->get_description();
    $short_description = $product->get_short_description();
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $sku = $product->get_sku();
    
    // Format content consistently
    $content = $title . "\n\n";
    
    if (!empty($description)) {
        $content .= wp_strip_all_tags($description) . "\n\n";
    }
    
    if (!empty($short_description)) {
        $content .= "Short Description: " . wp_strip_all_tags($short_description) . "\n\n";
    }
    
    $content .= "Price: $" . $regular_price . "\n";
    
    if (!empty($sale_price)) {
        $content .= "Sale Price: $" . $sale_price . "\n";
    }
    
    if (!empty($sku)) {
        $content .= "SKU: " . $sku . "\n";
    }
    
    // Get API key with proper model detection
    $options = get_option('mxchat_options');
    $selected_model = $options['embedding_model'] ?? 'text-embedding-ada-002';
    
    if (strpos($selected_model, 'voyage') === 0) {
        $api_key = $options['voyage_api_key'] ?? '';
    } elseif (strpos($selected_model, 'gemini-embedding') === 0) {
        $api_key = $options['gemini_api_key'] ?? '';
    } else {
        $api_key = $options['api_key'] ?? '';
    }
    
    if (empty($api_key)) {
        //error_log('MxChat Auto-sync: No API key configured for embedding model');
        return;
    }
    
    // Use the centralized utility function for storage
    $result = MxChat_Utils::submit_content_to_db(
        $content,
        $source_url,
        $api_key,
        md5($source_url) // Vector ID for Pinecone
    );
    
    if (is_wp_error($result)) {
        //error_log('MxChat WooCommerce sync failed for product ' . $product->get_id() . ': ' . $result->get_error_message());
    }
}

public function mxchat_handle_product_delete($post_id) {
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    $source_url = get_permalink($post_id);

    // Check if Pinecone is enabled
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';

    if ($use_pinecone && !empty($pinecone_options['mxchat_pinecone_api_key'])) {
        // Delete from Pinecone
        $this->mxchat_delete_from_pinecone_by_url($source_url, $pinecone_options);
    } else {
        // Delete from WordPress DB
        global $wpdb;
        $table_name = $wpdb->prefix . 'mxchat_system_prompt_content';
        
        $wpdb->delete(
            $table_name,
            array('source_url' => $source_url),
            array('%s')
        );
    }
}

/**
 * Handle individual Pinecone content deletion
 */
public function mxchat_handle_pinecone_prompt_delete() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions.', 'mxchat'));
    }
    
    // Verify nonce
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'mxchat_delete_pinecone_prompt_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mxchat'));
    }
    
    $vector_id = isset($_GET['vector_id']) ? sanitize_text_field($_GET['vector_id']) : '';
    
    if (empty($vector_id)) {
        set_transient('mxchat_admin_notice_error', 
            esc_html__('Invalid vector ID.', 'mxchat'), 
            30
        );
        wp_safe_redirect(admin_url('admin.php?page=mxchat-prompts'));
        exit;
    }
    
    // Get Pinecone settings
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';
    
    if (!$use_pinecone || empty($pinecone_options['mxchat_pinecone_api_key'])) {
        set_transient('mxchat_admin_notice_error', 
            esc_html__('Pinecone is not properly configured.', 'mxchat'), 
            30
        );
        wp_safe_redirect(admin_url('admin.php?page=mxchat-prompts'));
        exit;
    }
    
    // Delete from Pinecone
    $pinecone_manager = MxChat_Pinecone_Manager::get_instance();
    $result = $pinecone_manager->mxchat_delete_from_pinecone_by_vector_id(
        $vector_id, 
        $pinecone_options['mxchat_pinecone_api_key'], 
        $pinecone_options['mxchat_pinecone_host']
    );
    
    if ($result['success']) {
        // Remove from ALL caches
        $pinecone_manager->mxchat_remove_from_pinecone_vector_cache($vector_id);
        $pinecone_manager->mxchat_remove_from_processed_content_caches($vector_id);
        
        // CLEAR ALL RELEVANT CACHES
        delete_transient('mxchat_pinecone_recent_1k_cache');
        delete_option('mxchat_pinecone_vector_ids_cache');
        delete_option('mxchat_pinecone_processed_cache');
        delete_option('mxchat_processed_content_cache');
        
        // Also force refresh for next page load
        $pinecone_manager->mxchat_refresh_after_new_content($pinecone_options);
        
        set_transient('mxchat_admin_notice_success', 
            esc_html__('Entry deleted successfully from Pinecone.', 'mxchat'), 
            30
        );
    } else {
        set_transient('mxchat_admin_notice_error', 
            esc_html__('Failed to delete entry: ', 'mxchat') . $result['message'], 
            30
        );
    }
    
    wp_safe_redirect(admin_url('admin.php?page=mxchat-prompts'));
    exit;
}

public function ajax_mxchat_delete_pinecone_prompt() {
    // Verify nonce and permissions
    if (!check_ajax_referer('mxchat_delete_pinecone_prompt_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        exit;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
        exit;
    }
    
    $vector_id = isset($_POST['vector_id']) ? sanitize_text_field($_POST['vector_id']) : '';
    
    if (empty($vector_id)) {
        wp_send_json_error('Missing vector ID');
        exit;
    }
    
    // Get Pinecone settings
    $pinecone_options = get_option('mxchat_pinecone_addon_options', array());
    $use_pinecone = ($pinecone_options['mxchat_use_pinecone'] ?? '0') === '1';
    
    if (!$use_pinecone || empty($pinecone_options['mxchat_pinecone_api_key'])) {
        wp_send_json_error('Pinecone is not properly configured');
        exit;
    }
    
    // Delete from Pinecone
    $pinecone_manager = MxChat_Pinecone_Manager::get_instance();
    $result = $pinecone_manager->mxchat_delete_from_pinecone_by_vector_id(
        $vector_id, 
        $pinecone_options['mxchat_pinecone_api_key'], 
        $pinecone_options['mxchat_pinecone_host']
    );
    
    if ($result['success']) {
        // Remove from ALL caches
        $pinecone_manager->mxchat_remove_from_pinecone_vector_cache($vector_id);
        $pinecone_manager->mxchat_remove_from_processed_content_caches($vector_id);
        
        // CLEAR ALL RELEVANT CACHES (ADD THESE LINES)
        delete_transient('mxchat_pinecone_recent_1k_cache');
        delete_option('mxchat_pinecone_vector_ids_cache');
        delete_option('mxchat_pinecone_processed_cache');
        delete_option('mxchat_processed_content_cache');
        
        // Also force refresh for next page load
        $pinecone_manager->mxchat_refresh_after_new_content($pinecone_options);
        
        wp_send_json_success(array(
            'message' => 'Entry deleted successfully from Pinecone',
            'vector_id' => $vector_id
        ));
    } else {
        wp_send_json_error('Failed to delete from Pinecone: ' . $result['message']);
    }
    
    exit;
}
    
 // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Check if user has required permissions for content processing
     */
    private function mxchat_check_user_permissions() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'mxchat'));
        }
    }
    
    /**
     * Validate nonce for security
     */
    private function mxchat_validate_nonce($nonce_name, $nonce_action) {
        if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action)) {
            wp_die(esc_html__('Security check failed.', 'mxchat'));
        }
    }
    
    /**
     * Get embedding API credentials
     */
    private function mxchat_get_embedding_credentials() {
        $embedding_model = $this->options['embedding_model'] ?? 'text-embedding-ada-002';
        
        if (strpos($embedding_model, 'text-embedding-') !== false) {
            return array(
                'type' => 'openai',
                'api_key' => $this->options['api_key'] ?? ''
            );
        } elseif (strpos($embedding_model, 'voyage-') !== false) {
            return array(
                'type' => 'voyage',
                'api_key' => $this->options['voyage_api_key'] ?? ''
            );
        } elseif (strpos($embedding_model, 'gemini-embedding-') !== false) {
            return array(
                'type' => 'gemini',
                'api_key' => $this->options['gemini_api_key'] ?? ''
            );
        }
        
        return array('type' => 'unknown', 'api_key' => '');
    }
    
    /**
     * Log processing errors
     */
    private function mxchat_log_processing_error($operation, $error_message) {
        //error_log("MxChat Knowledge Processing {$operation} Error: " . $error_message);
    }
    
    /**
     * Set admin notice transient
     */
    private function mxchat_set_admin_notice($type, $message) {
        set_transient("mxchat_admin_notice_{$type}", $message, 30);
    }
    
    /**
     * Get Pinecone manager instance for vector operations
     */
    private function mxchat_get_pinecone_manager() {
        return MxChat_Pinecone_Manager::get_instance();
    }
    
    // ========================================
    // STATIC ACCESS METHODS
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

// Initialize the Knowledge manager
$mxchat_knowledge_manager = MxChat_Knowledge_Manager::get_instance();