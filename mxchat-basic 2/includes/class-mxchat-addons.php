<?php
/**
 * The admin-specific functionality for the Add Ons page.
 *
 * @package    MxChat
 * @subpackage MxChat/admin
 */
class MxChat_Addons {
    /**
     * Store add-on configuration data
     *
     * @var array
     */
    private $addons_config;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->addons_config = array(

            'mxchat-theme' => array(
                'title' => __('MxChat Theme Customizer', 'mxchat'),
                'description' => __('Make your chatbot uniquely yours. Customize colors, styles, and appearance with live previews—zero coding required. Perfect for matching your brand identity.', 'mxchat'),
                'key_benefits' => array(
                    __('Live preview customizer', 'mxchat'),
                    __('Point-and-click simplicity', 'mxchat'),
                    __('Brand-perfect styling', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-theme/',
                'plugin_file' => 'mxchat-theme/mxchat-theme.php',
                'config_page' => 'mxchat-theme-settings'
            ),

            'mxchat-admin-assistant' => array(
                'title' => __('MxChat Admin Assistant', 'mxchat'),
                'description' => __('Your AI powerhouse inside WordPress. Chat with multiple AI models, generate images, research the web, and boost productivity—all without leaving your dashboard.', 'mxchat'),
                'key_benefits' => array(
                    __('ChatGPT-like admin interface', 'mxchat'),
                    __('Generate images & research web', 'mxchat'),
                    __('Searchable chat history', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-admin-assistant/',
                'plugin_file' => 'mxchat-admin-chat/mxchat-admin-chat.php',
                'config_page' => 'mxchat-admin-chat'
            ),
            'mxchat-forms' => array(
                'title' => __('MxChat Forms', 'mxchat'),
                'description' => __('Convert conversations into data collection. Create smart forms that trigger during chats, collect user information, and turn casual visitors into qualified leads.', 'mxchat'),
                'key_benefits' => array(
                    __('No-code form builder', 'mxchat'),
                    __('Intent-triggered activation', 'mxchat'),
                    __('Export lead data easily', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-forms/',
                'plugin_file' => 'mxchat-forms/mxchat-forms.php',
                'config_page' => 'mxchat-forms'
            ),
            'mxchat-smart-recommender' => array(
                'title' => __('MxChat Smart Recommender', 'mxchat'),
                'description' => __('Turn your chatbot into a sales machine. Build personalized recommendation flows that understand user preferences and suggest perfect products or services.', 'mxchat'),
                'key_benefits' => array(
                    __('Increase conversion rates', 'mxchat'),
                    __('Custom recommendation flows', 'mxchat'),
                    __('No coding required', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-smart-recommender/',
                'plugin_file' => 'mxchat-smart-recommender/mxchat-smart-recommender.php',
                'config_page' => 'mxchat-smart-recommender'
            ),
            'mxchat-woo' => array(
                'title' => __('MxChat WooCommerce', 'mxchat'),
                'description' => __('Boost sales with AI-powered shopping assistance. Help customers find products, manage carts, and complete purchases—all through natural conversation.', 'mxchat'),
                'key_benefits' => array(
                    __('Smart product recommendations', 'mxchat'),
                    __('Cart & checkout assistance', 'mxchat'),
                    __('Order history access', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-woo/',
                'plugin_file' => 'mxchat-woo/mxchat-woo.php',
                'config_page' => 'mxchat-woo'
            ),
            'mxchat-perplexity' => array(
                'title' => __('MxChat Perplexity', 'mxchat'),
                'description' => __('Give your chatbot real-time knowledge. Add powerful web search capabilities so your bot can answer questions about current events and time-sensitive information.', 'mxchat'),
                'key_benefits' => array(
                    __('Real-time web search', 'mxchat'),
                    __('Intent-triggered research', 'mxchat'),
                    __('Up-to-date information', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-perplexity/',
                'plugin_file' => 'mxchat-perplexity/mxchat-perplexity.php',
                'config_page' => 'mxchat-perplexity'
            ),
            'mxchat-moderation' => array(
                'title' => __('MxChat Moderation', 'mxchat'),
                'description' => __('Keep your chat clean and professional. Block unwanted users, filter inappropriate content, and ensure your chatbot represents your brand properly.', 'mxchat'),
                'key_benefits' => array(
                    __('IP & email-based blocking', 'mxchat'),
                    __('Content filtering', 'mxchat'),
                    __('Spam protection', 'mxchat')
                ),
                'license' => 'MxChat PRO',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-moderation/',
                'plugin_file' => 'mxchat-moderation/mx-chat-moderation.php',
                'config_page' => 'mx-chat-moderation'
            ),
            
                'mxchat-intent-tester' => array(
                'title' => __('MxChat Similarity Tester', 'mxchat'),
                'description' => __('See exactly how your chatbot thinks. Visualize intent matches and similarity scores to optimize accuracy and understand AI decision-making. Essential for fine-tuning your responses.', 'mxchat'),
                'key_benefits' => array(
                    __('Reveal top 10 knowledge matches', 'mxchat'),
                    __('Optimize response accuracy', 'mxchat'),
                    __('Debug AI decision patterns', 'mxchat')
                ),
                'license' => 'free',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-intent-tester/',
                'plugin_file' => 'mxchat-intent-tester/mxchat-intent-tester.php',
                'config_page' => 'mxchat-intent-tester'
            ),
            
                        'mxchat-pinecone' => array(
                'title' => __('Pinecone DB Manager (Deprecated)', 'mxchat'),
                'description' => __('This add-on has been sunsetted and is now included directly in the core MxChat plugin. Find Pinecone settings under the Knowledge tab in Pinecone Settings.', 'mxchat'),
                'key_benefits' => array(
                    __('Now integrated into core plugin', 'mxchat'),
                    __('Access via Knowledge tab', 'mxchat'),
                    __('No separate installation needed', 'mxchat')
                ),
                'license' => 'free',
                'accent' => '#fa73e6',
                'url' => 'https://quickdeploywp.com/plugin/mxchat-pinecone/',
                'plugin_file' => 'mxchat-pinecone/pinecone-manager.php',
                'config_page' => 'mxchat-pinecone',
                'status' => 'deprecated'
            ),
        );
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        $plugin_version = '2.2.8';

        wp_enqueue_style(
            'mxchat-addons',
            plugin_dir_url(__FILE__) . '../css/admin-add-ons.css',
            array(),
            $plugin_version,
            'all'
        );
    }

    /**
     * Check if an addon is installed and active
     *
     * @param string $plugin_file The plugin's main file path
     * @return array Status information
     */
    private function get_addon_status($plugin_file) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());

        // Find the addon by iterating through configs
        $config_page = '';
        foreach ($this->addons_config as $slug => $addon) {
            if ($addon['plugin_file'] === $plugin_file) {
                $config_page = $addon['config_page'];
                break;
            }
        }

        if (isset($all_plugins[$plugin_file])) {
            if (in_array($plugin_file, $active_plugins)) {
                return array(
                    'status' => 'active',
                    'action_url' => admin_url('admin.php?page=' . $config_page),
                    'action_text' => __('Configure', 'mxchat')
                );
            } else {
                return array(
                    'status' => 'inactive',
                    'action_url' => wp_nonce_url(
                        admin_url('plugins.php?action=activate&plugin=' . $plugin_file),
                        'activate-plugin_' . $plugin_file
                    ),
                    'action_text' => __('Activate', 'mxchat')
                );
            }
        }

        return array(
            'status' => 'not-installed',
            'action_url' => '',
            'action_text' => __('Get Extension', 'mxchat')
        );
    }

    /**
     * Render the Add Ons page content.
     */
public function render_page() {
    $this->enqueue_styles();
    // Remove the sorting logic and just use the original order
    $sorted_addons = $this->addons_config;
    
    ?>
    <div class="wrap mxchat-addons-wrapper">
            <div class="mxchat-addons-hero">
                <h1 class="mxchat-main-title">
                    <span class="mxchat-gradient-text">Power Up</span> Your Chatbot
                </h1>
                <p class="mxchat-hero-subtitle">
                    <?php esc_html_e('Supercharge your WordPress AI chatbot with these powerful extensions. Start with our free add-ons or upgrade for premium features.', 'mxchat'); ?>
                </p>
            </div>
            
            <div class="mxchat-addons-section">
                <div class="mxchat-addons-grid">
                    <?php foreach ($sorted_addons as $slug => $addon): ?>
                        <?php $this->render_addon_card($slug, $addon); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mxchat-cta-section">
                <h2><?php esc_html_e('Ready to take your chatbot to the next level?', 'mxchat'); ?></h2>
                <p><?php esc_html_e('Get MxChat PRO today and access all premium extensions at one low price.', 'mxchat'); ?></p>
                <a href="https://mxchat.ai/" class="mxchat-cta-button" target="_blank"><?php esc_html_e('Learn More', 'mxchat'); ?></a>
            </div>
        </div>
        <?php
    }

/**
 * Render individual add-on card
 *
 * @param string $slug The addon slug
 * @param array  $addon The addon configuration array
 */
private function render_addon_card($slug, $addon) {
    $status_info = $this->get_addon_status($addon['plugin_file']);
    $button_url = $status_info['status'] === 'not-installed' ? $addon['url'] : $status_info['action_url'];
    $button_target = $status_info['status'] === 'not-installed' ? '_blank' : '_self';
    
    // Check if addon is deprecated
    $is_deprecated = isset($addon['status']) && $addon['status'] === 'deprecated';
    ?>
    <div class="mxchat-addon-card <?php echo $is_deprecated ? 'deprecated' : ''; ?>" style="--card-accent: <?php echo esc_attr($addon['accent']); ?>">
        <div class="mxchat-addon-badge">
            <?php if ($is_deprecated): ?>
                <?php esc_html_e('Deprecated', 'mxchat'); ?>
            <?php else: ?>
                <?php echo esc_html(ucfirst($addon['license'])); ?>
            <?php endif; ?>
        </div>
        <div class="mxchat-addon-content">
            <h3 class="mxchat-addon-title"><?php echo esc_html($addon['title']); ?></h3>
            <p class="mxchat-addon-description"><?php echo esc_html($addon['description']); ?></p>
            
            <?php if (!empty($addon['key_benefits'])): ?>
            <div class="mxchat-benefits-list">
                <?php foreach ($addon['key_benefits'] as $benefit): ?>
                    <div class="mxchat-benefit-item">
                        <span class="mxchat-benefit-icon">✓</span>
                        <?php echo esc_html($benefit); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="mxchat-addon-footer">
                <?php if ($is_deprecated): ?>
                    <div class="mxchat-status-indicator deprecated">
                        <?php esc_html_e('Now Built-in', 'mxchat'); ?>
                    </div>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mxchat-prompts')); ?>"
                       class="mxchat-action-button deprecated"
                       target="_self">
                        <?php esc_html_e('Go to Knowledge Tab', 'mxchat'); ?>
                    </a>
                <?php else: ?>
                    <div class="mxchat-status-indicator <?php echo esc_attr($status_info['status']); ?>">
                        <?php echo esc_html(ucfirst(str_replace('-', ' ', $status_info['status']))); ?>
                    </div>
                    <a href="<?php echo esc_url($button_url); ?>"
                       class="mxchat-action-button"
                       target="<?php echo esc_attr($button_target); ?>"
                       data-action="<?php echo esc_attr($status_info['status']); ?>">
                        <?php echo esc_html($status_info['action_text']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

}