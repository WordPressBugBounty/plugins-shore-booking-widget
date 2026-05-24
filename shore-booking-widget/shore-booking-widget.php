<?php
/**
 * Plugin Name: Shore Booking Widget
 * Plugin URI: https://shore.com/wordpress-booking-plugin/
 * Description: Shore Booking Widget brings your booking system right onto your WordPress site. Perfect for salons, beauty studios, and service businesses. Three display styles, easy setup, no coding needed.
 * Version: 1.0.5
 * Author: Shore GmbH
 * Author URI: https://shore.com/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: shore-booking-widget
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// PLUGIN CONSTANTS
// =============================================================================

define('SHBW_VERSION', '1.0.1');
define('SHBW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHBW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Shore URLs
define('SHBW_SHORE_WIDGET_LOADING_JS', 'https://connect.shore.com/widget/loading.js');
define('SHBW_SHORE_WIDGET_BOOKING_JS', 'https://connect.shore.com/widget/booking.js');
define('SHBW_SHORE_WIDGET_URL', 'https://connect.shore.com/widget/');
define('SHBW_SHORE_BOOKINGS_URL', 'https://connect.shore.com/bookings/');
define('SHBW_SHORE_ORIGIN', 'https://connect.shore.com');

// Defaults
define('SHBW_DEFAULT_DISPLAY_TYPE', 'embedded');
define('SHBW_DEFAULT_LOCALE', 'auto');
define('SHBW_DEFAULT_BG_COLOR', '#00d0be');
define('SHBW_DEFAULT_TEXT_COLOR', '#ffffff');

// =============================================================================
// INITIALIZATION
// =============================================================================

// Initialize plugin
add_action('plugins_loaded', function() {
    add_action('admin_menu', 'shbw_add_admin_menu');
    add_action('admin_init', 'shbw_register_settings');
    add_shortcode('shore_booking', 'shbw_render_widget');
});

// Activation
register_activation_hook(__FILE__, function() {
    add_option('shbw_config_token', '');
    add_option('shbw_locale', SHBW_DEFAULT_LOCALE);
    add_option('shbw_display_type', SHBW_DEFAULT_DISPLAY_TYPE);
    add_option('shbw_standard_bg_color', SHBW_DEFAULT_BG_COLOR);
    add_option('shbw_standard_text_color', SHBW_DEFAULT_TEXT_COLOR);
    add_option('shbw_floating_bg_color', SHBW_DEFAULT_BG_COLOR);
    add_option('shbw_floating_text_color', SHBW_DEFAULT_TEXT_COLOR);
    add_option('shbw_button_text', __('Book Now', 'shore-booking-widget'));
    add_option('shbw_floating_position', 'left');
});

// Uninstall (handled via uninstall.php if needed)
// Note: register_uninstall_hook cannot use anonymous functions

// =============================================================================
// SETTINGS REGISTRATION
// =============================================================================

function shbw_register_settings() {
    register_setting('shbw_settings', 'shbw_config_token', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('shbw_settings', 'shbw_locale', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('shbw_settings', 'shbw_display_type', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('shbw_settings', 'shbw_standard_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('shbw_settings', 'shbw_standard_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('shbw_settings', 'shbw_floating_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('shbw_settings', 'shbw_floating_text_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('shbw_settings', 'shbw_button_text', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('shbw_settings', 'shbw_floating_position', ['sanitize_callback' => 'sanitize_text_field']);
}

function shbw_add_admin_menu() {
    add_options_page(
        __('Shore Booking Settings', 'shore-booking-widget'),
        __('Shore Booking', 'shore-booking-widget'),
        'manage_options',
        'shore-booking-widget',
        'shbw_settings_page'
    );
}

// =============================================================================
// ADMIN STYLES
// =============================================================================

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'settings_page_shore-booking-widget') {
        return;
    }
    
    wp_enqueue_style('shbw-admin', SHBW_PLUGIN_URL . 'assets/admin.css', [], SHBW_VERSION);
    wp_enqueue_script('shbw-admin', SHBW_PLUGIN_URL . 'assets/admin.js', ['jquery'], SHBW_VERSION, true);
    
    wp_localize_script('shbw-admin', 'shbwSettings', [
        'defaultText' => __('Book Now', 'shore-booking-widget')
    ]);
});

// =============================================================================
// WIDGET RENDERING
// =============================================================================

function shbw_render_widget($atts = []) {
    $config_token = get_option('shbw_config_token', '');
    $display_type = get_option('shbw_display_type', SHBW_DEFAULT_DISPLAY_TYPE);
    
    if (empty($config_token)) {
        if (current_user_can('manage_options')) {
            return '<p style="color: red;">' . esc_html__('Shore Booking Widget: Please configure your token in Settings.', 'shore-booking-widget') . '</p>';
        }
        return '';
    }
    
    $config_token = preg_replace('/[^a-zA-Z0-9\-_]/', '', $config_token);
    $locale = shbw_get_locale();
    
    $atts = shortcode_atts(['height' => '100vh', 'locale' => $locale], $atts);
    
    switch ($display_type) {
        case 'standard_button':
            return shbw_render_standard_button($config_token, $atts['locale']);
        case 'floating_button':
            return shbw_render_floating_button($config_token, $atts['locale']);
        default:
            return shbw_render_embedded($config_token, $atts['locale'], $atts['height']);
    }
}

function shbw_get_locale() {
    $saved_locale = get_option('shbw_locale', SHBW_DEFAULT_LOCALE);
    
    if ($saved_locale !== 'auto') {
        return $saved_locale;
    }
    
    $wp_locale = get_locale();
    $lang_code = substr($wp_locale, 0, 2);
    
    $supported = ['en', 'de', 'fr', 'es'];
    return in_array($lang_code, $supported) ? $lang_code : 'en';
}

function shbw_render_standard_button($config_token, $locale) {
    $bg_color = get_option('shbw_standard_bg_color', SHBW_DEFAULT_BG_COLOR);
    $text_color = get_option('shbw_standard_text_color', SHBW_DEFAULT_TEXT_COLOR);
    $button_text = get_option('shbw_button_text', __('Book Now', 'shore-booking-widget'));
    $button_url = SHBW_SHORE_WIDGET_URL . $config_token . '?locale=' . $locale;
    
    wp_enqueue_script('shore-widget-loading', SHBW_SHORE_WIDGET_LOADING_JS, [], SHBW_VERSION, true);
    
    return sprintf(
        '<a class="termine24-widget termine24-widget-custom" style="background-color: %s; color: %s;" target="_blank" href="%s">%s</a>',
        esc_attr($bg_color),
        esc_attr($text_color),
        esc_url($button_url),
        esc_html($button_text)
    );
}

function shbw_render_floating_button($config_token, $locale) {
    $bg_color = get_option('shbw_floating_bg_color', SHBW_DEFAULT_BG_COLOR);
    $text_color = get_option('shbw_floating_text_color', SHBW_DEFAULT_TEXT_COLOR);
    $button_text = get_option('shbw_button_text', __('Book Now', 'shore-booking-widget'));
    $position = get_option('shbw_floating_position', 'left');
    
    wp_enqueue_script('shore-widget-booking', SHBW_SHORE_WIDGET_BOOKING_JS, [], SHBW_VERSION, true);
    
    $inline_script = sprintf(
        'window.shoreBookingSettings = {
            themeColor: %s,
            textColor: %s,
            text: %s,
            company: %s,
            locale: %s,
            position: %s,
            selectLocation: false
        };',
        wp_json_encode($bg_color),
        wp_json_encode($text_color),
        wp_json_encode($button_text),
        wp_json_encode($config_token),
        wp_json_encode($locale),
        wp_json_encode($position)
    );
    
    wp_add_inline_script('shore-widget-booking', $inline_script, 'before');
    
    return '<!-- Shore Floating Button loaded via script -->';
}

function shbw_render_embedded($config_token, $locale, $height) {
    $booking_url = SHBW_SHORE_BOOKINGS_URL . $config_token . '/services?locale=' . $locale;
    
    $tracking_script = sprintf(
        '(function() {
            const shoreOrigin = %s;
            window.addEventListener("load", function() {
                window.addEventListener("message", function(event) {
                    if (event.origin === shoreOrigin && window.dataLayer && typeof window.dataLayer.push === "function" && event.data && event.data.event) {
                        window.dataLayer.push({
                            event: event.data.event,
                            eventAction: event.data.eventAction,
                            eventCategory: event.data.eventCategory,
                            eventCustomer: event.data.eventCustomer,
                            eventLabel: event.data.eventLabel,
                            eventLocation: event.data.eventLocation,
                            eventValue: event.data.eventValue
                        });
                    }
                });
            });
        })();',
        wp_json_encode(SHBW_SHORE_ORIGIN)
    );
    
    wp_add_inline_script('jquery', $tracking_script);
    
    return sprintf(
        '<iframe src="%s" title="Shore booking" style="width: 100%%; height: %s; border: 0;"></iframe>',
        esc_url($booking_url),
        esc_attr($height)
    );
}

// =============================================================================
// SETTINGS PAGE
// =============================================================================

function shbw_settings_page() {
    // Initialize defaults if needed
    if (!get_option('shbw_standard_bg_color')) {
        update_option('shbw_standard_bg_color', SHBW_DEFAULT_BG_COLOR);
        update_option('shbw_standard_text_color', SHBW_DEFAULT_TEXT_COLOR);
        update_option('shbw_floating_bg_color', SHBW_DEFAULT_BG_COLOR);
        update_option('shbw_floating_text_color', SHBW_DEFAULT_TEXT_COLOR);
    }
    
    $config_token = get_option('shbw_config_token', '');
    $locale = get_option('shbw_locale', SHBW_DEFAULT_LOCALE);
    $display_type = get_option('shbw_display_type', SHBW_DEFAULT_DISPLAY_TYPE);
    $standard_bg = get_option('shbw_standard_bg_color', SHBW_DEFAULT_BG_COLOR);
    $standard_text = get_option('shbw_standard_text_color', SHBW_DEFAULT_TEXT_COLOR);
    $floating_bg = get_option('shbw_floating_bg_color', SHBW_DEFAULT_BG_COLOR);
    $floating_text = get_option('shbw_floating_text_color', SHBW_DEFAULT_TEXT_COLOR);
    $button_text = get_option('shbw_button_text', __('Book Now', 'shore-booking-widget'));
    $floating_position = get_option('shbw_floating_position', 'left');
    
    $bg_colors = [
        '#1a1a1a' => __('Black', 'shore-booking-widget'),
        '#6c757d' => __('Gray', 'shore-booking-widget'),
        '#adb5bd' => __('Light Gray', 'shore-booking-widget'),
        '#ff4d6d' => __('Pink', 'shore-booking-widget'),
        '#c9184a' => __('Red', 'shore-booking-widget'),
        '#ff6900' => __('Orange', 'shore-booking-widget'),
        '#ff9f1c' => __('Light Orange', 'shore-booking-widget'),
        '#ffbf00' => __('Yellow', 'shore-booking-widget'),
        '#6a4c93' => __('Purple', 'shore-booking-widget'),
        '#0466c8' => __('Blue', 'shore-booking-widget'),
        '#00b4d8' => __('Light Blue', 'shore-booking-widget'),
        '#06d6a0' => __('Green', 'shore-booking-widget'),
        '#94d82d' => __('Lime', 'shore-booking-widget'),
        '#00d0be' => __('Teal', 'shore-booking-widget')
    ];
    
    $text_colors = [
        '#1a1a1a' => __('Black', 'shore-booking-widget'),
        '#6c757d' => __('Gray', 'shore-booking-widget'),
        '#adb5bd' => __('Light Gray', 'shore-booking-widget'),
        '#ffffff' => __('White', 'shore-booking-widget')
    ];
    
    ?>
    <div class="wrap">
        <h1 class="screen-reader-text"><?php echo esc_html__('Shore Booking Widget', 'shore-booking-widget'); ?></h1>

        <div class="shbw-wrap">
            <div class="shbw-header">
                <img src="<?php echo esc_url(SHBW_PLUGIN_URL . 'assets/shore-logo.png'); ?>" alt="Shore Logo" class="shbw-logo">
                <div>
                    <h1><?php echo esc_html__('Shore Booking Widget', 'shore-booking-widget'); ?></h1>
                    <p><?php echo esc_html__('Configure your booking widget to match your brand', 'shore-booking-widget'); ?></p>
                </div>
            </div>
        
        <?php 
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress core sets this parameter
        if (isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated']))): 
        ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php echo esc_html__('Settings saved successfully!', 'shore-booking-widget'); ?></strong></p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($config_token)): ?>
        <div class="shbw-card shbw-onboarding-card">
            <div class="shbw-card-header">
                <h2>🚀 <?php echo esc_html__('Getting Started', 'shore-booking-widget'); ?></h2>
            </div>
            <div class="shbw-card-body">
                <div class="shbw-onboarding-steps">
                    <div class="shbw-onboarding-step">
                        <div class="shbw-onboarding-step-number">1</div>
                        <div>
                            <h3><?php echo esc_html__('Sign up with Shore', 'shore-booking-widget'); ?></h3>
                            <p><?php echo esc_html__("Don't have a Shore account yet? Create your free booking system in just a few minutes!", 'shore-booking-widget'); ?></p>
                            <a href="https://signup.shore.com/en/signup/booking?source=onecom_wordpress" target="_blank" rel="noopener noreferrer" class="button button-primary shbw-onboarding-btn">
                                <?php echo esc_html__("Sign Up, It's Free! →", 'shore-booking-widget'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="shbw-onboarding-step">
                        <div class="shbw-onboarding-step-number">2</div>
                        <div>
                            <h3><?php echo esc_html__('Get Your Token', 'shore-booking-widget'); ?></h3>
                            <p><?php
                                printf(
                                    /* translators: %s: Link to Shore dashboard */
                                    esc_html__('Find your merchant configuration token in your %s. It\'s usually your business name.', 'shore-booking-widget'),
                                    '<a href="https://my.shore.com/application-settings" target="_blank" rel="noopener noreferrer">' . esc_html__('Shore dashboard', 'shore-booking-widget') . '</a>'
                                );
                            ?></p>
                        </div>
                    </div>
                    <div class="shbw-onboarding-step">
                        <div class="shbw-onboarding-step-number">3</div>
                        <div>
                            <h3><?php echo esc_html__('Enter Your Token Below', 'shore-booking-widget'); ?></h3>
                            <p><?php echo esc_html__('Paste your configuration token into the field below to activate your booking widget.', 'shore-booking-widget'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('shbw_settings'); ?>

            <!-- Configuration -->
            <div class="shbw-card">
                <div class="shbw-card-header">
                    <h2><span class="step-badge">1</span> <?php echo esc_html__('Configuration', 'shore-booking-widget'); ?></h2>
                </div>
                <div class="shbw-card-body">
                    <div class="shbw-field">
                        <label><?php echo esc_html__('Configuration Token', 'shore-booking-widget'); ?></label>
                        <input type="text" name="shbw_config_token" value="<?php echo esc_attr($config_token); ?>" 
                               placeholder="test-shore-booking" required>
                        <p class="description"><?php echo esc_html__('Enter your Shore configuration token.', 'shore-booking-widget'); ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($config_token)): ?>
            
            <!-- Language Selection -->
            <div class="shbw-card">
                <div class="shbw-card-header">
                    <h2><span class="step-badge">2</span> <?php echo esc_html__('Language', 'shore-booking-widget'); ?></h2>
                </div>
                <div class="shbw-card-body">
                    <div class="shbw-field">
                        <label><?php echo esc_html__('Widget Language', 'shore-booking-widget'); ?></label>
                        <select name="shbw_locale">
                            <option value="auto" <?php selected($locale, 'auto'); ?>><?php echo esc_html__('Auto-detect from page language', 'shore-booking-widget'); ?></option>
                            <option value="en" <?php selected($locale, 'en'); ?>><?php echo esc_html__('English (en)', 'shore-booking-widget'); ?></option>
                            <option value="de" <?php selected($locale, 'de'); ?>><?php echo esc_html__('German (de)', 'shore-booking-widget'); ?></option>
                            <option value="fr" <?php selected($locale, 'fr'); ?>><?php echo esc_html__('French (fr)', 'shore-booking-widget'); ?></option>
                            <option value="es" <?php selected($locale, 'es'); ?>><?php echo esc_html__('Spanish (es)', 'shore-booking-widget'); ?></option>
                        </select>
                        <p class="description"><?php echo esc_html__('Choose the language for the booking widget.', 'shore-booking-widget'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Display Type -->
            <div class="shbw-card">
                <div class="shbw-card-header">
                    <h2><span class="step-badge">3</span> <?php echo esc_html__('Display Style', 'shore-booking-widget'); ?></h2>
                </div>
                <div class="shbw-card-body">
                    <div class="shbw-field">
                        <select name="shbw_display_type" id="shbw_display_type">
                            <option value="embedded" <?php selected($display_type, 'embedded'); ?>><?php echo esc_html__('Embedded Booking Page', 'shore-booking-widget'); ?></option>
                            <option value="standard_button" <?php selected($display_type, 'standard_button'); ?>><?php echo esc_html__('Standard Button', 'shore-booking-widget'); ?></option>
                            <option value="floating_button" <?php selected($display_type, 'floating_button'); ?>><?php echo esc_html__('Floating Button', 'shore-booking-widget'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Standard Button Settings -->
            <div class="shbw-card" id="standard_section" style="display: <?php echo $display_type === 'standard_button' ? 'block' : 'none'; ?>;">
                <div class="shbw-card-header">
                    <h2><span class="step-badge">4</span> <?php echo esc_html__('Button Customization', 'shore-booking-widget'); ?></h2>
                </div>
                <div class="shbw-card-body">
                    <div class="shbw-field">
                        <label><?php echo esc_html__('Button Text', 'shore-booking-widget'); ?></label>
                        <input type="text" name="shbw_button_text" value="<?php echo esc_attr($button_text); ?>">
                    </div>
                    
                    <div class="shbw-color-section">
                        <h4><?php echo esc_html__('Background Color', 'shore-booking-widget'); ?></h4>
                        <div class="shbw-color-palette">
                            <?php foreach ($bg_colors as $color => $label): ?>
                                <div class="shbw-color-option">
                                    <input type="radio" name="shbw_standard_bg_color" value="<?php echo esc_attr($color); ?>" 
                                           id="std_bg_<?php echo esc_attr($color); ?>" <?php checked($standard_bg, $color); ?>>
                                    <label for="std_bg_<?php echo esc_attr($color); ?>" style="background-color: <?php echo esc_attr($color); ?>;" 
                                           title="<?php echo esc_attr($label); ?>"></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="shbw-color-section">
                        <h4><?php echo esc_html__('Text Color', 'shore-booking-widget'); ?></h4>
                        <div class="shbw-color-palette">
                            <?php foreach ($text_colors as $color => $label): ?>
                                <div class="shbw-color-option">
                                    <input type="radio" name="shbw_standard_text_color" value="<?php echo esc_attr($color); ?>" 
                                           id="std_txt_<?php echo esc_attr($color); ?>" <?php checked($standard_text, $color); ?>>
                                    <label for="std_txt_<?php echo esc_attr($color); ?>" style="background-color: <?php echo esc_attr($color); ?>;" 
                                           title="<?php echo esc_attr($label); ?>"></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="shbw-preview">
                        <button type="button" id="standard_preview" style="background-color: <?php echo esc_attr($standard_bg); ?>; color: <?php echo esc_attr($standard_text); ?>;">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Floating Button Settings -->
            <div class="shbw-card" id="floating_section" style="display: <?php echo $display_type === 'floating_button' ? 'block' : 'none'; ?>;">
                <div class="shbw-card-header">
                    <h2><span class="step-badge">4</span> <?php echo esc_html__('Floating Button Customization', 'shore-booking-widget'); ?></h2>
                </div>
                <div class="shbw-card-body">
                    <div class="shbw-field">
                        <label><?php echo esc_html__('Button Text', 'shore-booking-widget'); ?></label>
                        <input type="text" name="shbw_button_text" value="<?php echo esc_attr($button_text); ?>">
                    </div>
                    
                    <div class="shbw-field">
                        <h4><?php echo esc_html__('Button Position', 'shore-booking-widget'); ?></h4>
                        <div class="shbw-position-options">
                            <label>
                                <input type="radio" name="shbw_floating_position" value="left" <?php checked($floating_position, 'left'); ?>>
                                <?php echo esc_html__('Left', 'shore-booking-widget'); ?>
                            </label>
                            <label>
                                <input type="radio" name="shbw_floating_position" value="right" <?php checked($floating_position, 'right'); ?>>
                                <?php echo esc_html__('Right', 'shore-booking-widget'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="shbw-color-section">
                        <h4><?php echo esc_html__('Background Color', 'shore-booking-widget'); ?></h4>
                        <div class="shbw-color-palette">
                            <?php foreach ($bg_colors as $color => $label): ?>
                                <div class="shbw-color-option">
                                    <input type="radio" name="shbw_floating_bg_color" value="<?php echo esc_attr($color); ?>" 
                                           id="flt_bg_<?php echo esc_attr($color); ?>" <?php checked($floating_bg, $color); ?>>
                                    <label for="flt_bg_<?php echo esc_attr($color); ?>" style="background-color: <?php echo esc_attr($color); ?>;" 
                                           title="<?php echo esc_attr($label); ?>"></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="shbw-color-section">
                        <h4><?php echo esc_html__('Text Color', 'shore-booking-widget'); ?></h4>
                        <div class="shbw-color-palette">
                            <?php foreach ($text_colors as $color => $label): ?>
                                <div class="shbw-color-option">
                                    <input type="radio" name="shbw_floating_text_color" value="<?php echo esc_attr($color); ?>" 
                                           id="flt_txt_<?php echo esc_attr($color); ?>" <?php checked($floating_text, $color); ?>>
                                    <label for="flt_txt_<?php echo esc_attr($color); ?>" style="background-color: <?php echo esc_attr($color); ?>;" 
                                           title="<?php echo esc_attr($label); ?>"></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="shbw-floating-preview">
                        <button type="button" id="floating_preview" class="position-<?php echo esc_attr($floating_position); ?>" 
                                style="background-color: <?php echo esc_attr($floating_bg); ?>; color: <?php echo esc_attr($floating_text); ?>;">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Embedded Preview -->
            <div class="shbw-card" id="embedded_section" style="display: <?php echo $display_type === 'embedded' ? 'block' : 'none'; ?>;">
                <div class="shbw-card-header">
                    <h2><span class="step-badge">4</span> <?php echo esc_html__('Embedded Preview', 'shore-booking-widget'); ?></h2>
                </div>
                <div class="shbw-card-body">
                    <div class="shbw-embedded-preview">
                        <div class="shbw-embedded-mock">
                            <div class="shbw-embedded-header">Shore Booking Widget</div>
                            <div class="shbw-embedded-content">
                                <div class="shbw-service-item">💈 Haircut & Styling</div>
                                <div class="shbw-service-item">💅 Nail Care Services</div>
                                <div class="shbw-service-item">🧴 Spa Treatments</div>
                                <div class="shbw-service-item">📅 Book Appointment</div>
                            </div>
                            <div class="shbw-embedded-footer">Powered by Shore</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="shbw-card shbw-save-card">
                <div class="shbw-card-body">
                    <div class="shbw-save-section">
                        <div>
                            <strong><?php echo esc_html__('Ready to save your changes?', 'shore-booking-widget'); ?></strong>
                            <p><?php echo esc_html__('Your booking widget will update immediately', 'shore-booking-widget'); ?></p>
                        </div>
                        <?php submit_button(__('Save Settings', 'shore-booking-widget'), 'primary', 'submit', false); ?>
                    </div>
                </div>
            </div>
            
            <?php endif; ?>
        </form>
        
        <!-- Usage Instructions -->
        <div class="shbw-usage-card">
            <h3>📖 <?php echo esc_html__('How to Use', 'shore-booking-widget'); ?></h3>
            <p><strong><?php echo esc_html__('Shortcode:', 'shore-booking-widget'); ?></strong> 
               <?php echo esc_html__('Add', 'shore-booking-widget'); ?> <code>[shore_booking]</code> 
               <?php echo esc_html__('to any page or post.', 'shore-booking-widget'); ?></p>
            <p><strong><?php echo esc_html__('PHP Template:', 'shore-booking-widget'); ?></strong> 
               <?php echo esc_html__('Use', 'shore-booking-widget'); ?> 
               <code>&lt;?php echo do_shortcode('[shore_booking]'); ?&gt;</code> 
               <?php echo esc_html__('in theme files.', 'shore-booking-widget'); ?></p>
        </div>
        </div><!-- .shbw-wrap -->
    </div><!-- .wrap -->
    <?php
}

// =============================================================================
// ONBOARDING & WELCOME BANNER
// =============================================================================

/**
 * Show welcome banner on first activation
 */
function shbw_show_welcome_banner() {
    // Only show if not dismissed and no token configured
    $is_dismissed = get_option('shbw_welcome_dismissed', false);
    $has_token = get_option('shbw_config_token', '');
    
    if ($is_dismissed || !empty($has_token)) {
        return;
    }
    
    // Only show on specific admin pages (dashboard and plugins page)
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }
    
    // Only show on dashboard and plugins page (settings page has its own permanent onboarding UI)
    $allowed_screens = ['dashboard', 'plugins'];
    if (!in_array($screen->id, $allowed_screens)) {
        return;
    }
    
    ?>
    <div class="notice notice-info shbw-welcome-banner" style="position: relative; padding: 0; border-left-color: #00D0BE; margin: 20px 0;">
        <button type="button" class="notice-dismiss shbw-dismiss-welcome" style="position: absolute; top: 10px; right: 10px;">
            <span class="screen-reader-text"><?php echo esc_html__('Dismiss this notice.', 'shore-booking-widget'); ?></span>
        </button>
        
        <div style="padding: 30px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
                <div style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; padding: 5px;">
                    <img src="<?php echo esc_url(SHBW_PLUGIN_URL . 'assets/shore-logo.png?v=' . SHBW_VERSION); ?>" 
                         alt="Shore Logo" 
                         style="width: 100%; height: 100%; object-fit: contain; border: none; outline: none; box-shadow: none;">
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 24px; color: #000000;"><?php echo esc_html__('Welcome to Shore Booking Widget!', 'shore-booking-widget'); ?></h2>
                    <p style="margin: 5px 0 0 0; color: #666;"><?php echo esc_html__('The easiest way to add booking functionality to your WordPress site.', 'shore-booking-widget'); ?></p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <!-- Step 1: Create Account -->
                <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #00D0BE;">
                                <span style="display: inline-block; width: 28px; height: 28px; background: #00D0BE; color: white; border-radius: 50%; text-align: center; line-height: 28px; font-size: 16px; font-weight: bold; margin-right: 10px;">1</span>
                                <?php echo esc_html__('Sign up with Shore', 'shore-booking-widget'); ?>
                            </h3>
                            <p style="margin: 0 0 15px 0; color: #666; font-size: 14px; line-height: 1.6;">
                                <?php echo esc_html__('Don\'t have a Shore account yet? Create your free booking system in just a few minutes!', 'shore-booking-widget'); ?>
                            </p>
                            <a href="https://signup.shore.com/en/signup/booking?source=onecom_wordpress" 
                               target="_blank" 
                               class="button button-primary" 
                               style="background: #00D0BE; border-color: #00D0BE; text-decoration: none; box-shadow: none;">
                                <?php echo esc_html__('Sign Up, It\'s Free! →', 'shore-booking-widget'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Get Token -->
                <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #00D0BE;">
                                <span style="display: inline-block; width: 28px; height: 28px; background: #00D0BE; color: white; border-radius: 50%; text-align: center; line-height: 28px; font-size: 16px; font-weight: bold; margin-right: 10px;">2</span>
                                <?php echo esc_html__('Get Your Token', 'shore-booking-widget'); ?>
                            </h3>
                            <p style="margin: 0 0 15px 0; color: #666; font-size: 14px; line-height: 1.6;">
                                <?php 
                                printf(
                                    /* translators: %s: Link to Shore dashboard */
                                    esc_html__('Find your merchant configuration token in your %s. It\'s usually your business name.', 'shore-booking-widget'),
                                    '<a href="https://my.shore.com/application-settings" target="_blank" rel="noopener noreferrer" style="color: #00D0BE; text-decoration: underline;">' . esc_html__('Shore dashboard', 'shore-booking-widget') . '</a>'
                                );
                                ?>
                            </p>
                            <button type="button" 
                                    class="button button-primary shbw-enter-token-btn" 
                                    style="background: #00D0BE; border-color: #00D0BE; text-decoration: none; box-shadow: none;">
                                <?php echo esc_html__('I Have My Token →', 'shore-booking-widget'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Configure -->
                <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; color: #00D0BE;">
                                <span style="display: inline-block; width: 28px; height: 28px; background: #00D0BE; color: white; border-radius: 50%; text-align: center; line-height: 28px; font-size: 16px; font-weight: bold; margin-right: 10px;">3</span>
                                <?php echo esc_html__('Configure Plugin', 'shore-booking-widget'); ?>
                            </h3>
                            <p style="margin: 0 0 15px 0; color: #666; font-size: 14px; line-height: 1.6;">
                                <?php echo esc_html__('You\'re almost done! Configure your widget display settings and start accepting bookings.', 'shore-booking-widget'); ?>
                            </p>
                            <a href="<?php echo esc_url(admin_url('options-general.php?page=shore-booking-widget')); ?>" 
                               class="button button-primary" 
                               style="background: #00D0BE; border-color: #00D0BE; text-decoration: none; box-shadow: none;">
                                <?php echo esc_html__('Go to Settings →', 'shore-booking-widget'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Token Input Modal -->
    <div id="shbw-token-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 999999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative;">
            <button type="button" class="shbw-close-modal" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999; line-height: 1;">×</button>
            
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="width: 60px; height: 60px; background: rgba(0,208,190,0.1); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <span style="font-size: 32px;">🔑</span>
                </div>
                <h2 style="margin: 0 0 10px 0; color: #00D0BE; font-size: 24px;"><?php echo esc_html__('Enter Your Configuration Token', 'shore-booking-widget'); ?></h2>
                <p style="margin: 0; color: #666; font-size: 14px;"><?php echo esc_html__('You can find this in your Shore dashboard', 'shore-booking-widget'); ?></p>
            </div>
            
            <form id="shbw-token-form" style="margin-bottom: 0;">
                <div style="margin-bottom: 20px;">
                    <label for="shbw_token_input" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;"><?php echo esc_html__('Token', 'shore-booking-widget'); ?></label>
                    <input type="text" 
                           id="shbw_token_input" 
                           name="token" 
                           placeholder="<?php echo esc_attr__('e.g., your-business-name', 'shore-booking-widget'); ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                           required>
                    <p style="margin: 8px 0 0 0; font-size: 13px; color: #666;"><?php echo esc_html__('Usually your business name or merchant ID', 'shore-booking-widget'); ?></p>
                </div>
                
                <div id="shbw-token-error" style="display: none; padding: 12px; background: #fee; border-left: 4px solid #c00; border-radius: 4px; margin-bottom: 15px; color: #c00; font-size: 14px;"></div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" 
                            class="button shbw-close-modal" 
                            style="padding: 10px 20px;">
                        <?php echo esc_html__('Cancel', 'shore-booking-widget'); ?>
                    </button>
                    <button type="submit" 
                            class="button button-primary" 
                            style="background: #00D0BE; border-color: #00D0BE; padding: 10px 20px; box-shadow: none;">
                        <?php echo esc_html__('Save Token', 'shore-booking-widget'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

// Enqueue welcome banner scripts
add_action('admin_enqueue_scripts', function() {
    // Only enqueue on pages where the banner might show
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }
    
    $allowed_screens = ['dashboard', 'plugins'];
    if (!in_array($screen->id, $allowed_screens)) {
        return;
    }

    // Check if banner would show
    $is_dismissed = get_option('shbw_welcome_dismissed', false);
    $has_token = get_option('shbw_config_token', '');
    
    if ($is_dismissed || !empty($has_token)) {
        return;
    }
    
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Add inline script for welcome banner functionality
    $welcome_script = "
    jQuery(document).ready(function($) {
        // Move welcome banner above the custom header on settings page
        var \$banner = $('.shbw-welcome-banner');
        var \$shbwWrap = $('.shbw-wrap');
        
        if (\$banner.length && \$shbwWrap.length) {
            \$banner.insertBefore(\$shbwWrap);
        }
        
        // Dismiss welcome banner
        $('.shbw-dismiss-welcome').on('click', function(e) {
            e.preventDefault();
            var \$banner = $(this).closest('.shbw-welcome-banner');
            $.post(ajaxurl, {
                action: 'shbw_dismiss_welcome',
                nonce: '" . wp_create_nonce('shbw_dismiss_welcome') . "'
            }, function(response) {
                if (response.success) {
                    \$banner.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
        });
        
        // Open token modal
        $(document).on('click', '.shbw-enter-token-btn', function(e) {
            e.preventDefault();
            $('#shbw-token-modal').css('display', 'flex').hide().fadeIn(200);
            setTimeout(function() {
                $('#shbw_token_input').focus();
            }, 250);
        });
        
        // Close token modal
        $(document).on('click', '.shbw-close-modal', function(e) {
            e.preventDefault();
            $('#shbw-token-modal').fadeOut(200);
            $('#shbw-token-error').hide();
        });
        
        // Close modal on background click
        $('#shbw-token-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).fadeOut(200);
                $('#shbw-token-error').hide();
            }
        });
        
        // Submit token
        $('#shbw-token-form').on('submit', function(e) {
            e.preventDefault();
            var token = $('#shbw_token_input').val().trim();
            var \$submitBtn = $(this).find('button[type=\"submit\"]');
            var \$error = $('#shbw-token-error');
            
            if (!token) {
                \$error.text('" . esc_js(__('Please enter a configuration token', 'shore-booking-widget')) . "').fadeIn(200);
                return;
            }
            
            \$submitBtn.prop('disabled', true).text('" . esc_js(__('Saving...', 'shore-booking-widget')) . "');
            \$error.hide();
            
            $.post(ajaxurl, {
                action: 'shbw_save_token',
                token: token,
                nonce: '" . wp_create_nonce('shbw_save_token') . "'
            }, function(response) {
                if (response.success) {
                    window.location.href = '" . esc_url(admin_url('options-general.php?page=shore-booking-widget')) . "';
                } else {
                    \$error.text(response.data || '" . esc_js(__('Failed to save token. Please try again.', 'shore-booking-widget')) . "').fadeIn(200);
                    \$submitBtn.prop('disabled', false).text('" . esc_js(__('Save Token', 'shore-booking-widget')) . "');
                }
            }).fail(function() {
                \$error.text('" . esc_js(__('An error occurred. Please try again.', 'shore-booking-widget')) . "').fadeIn(200);
                \$submitBtn.prop('disabled', false).text('" . esc_js(__('Save Token', 'shore-booking-widget')) . "');
            });
        });
    });
    ";
    wp_add_inline_script('jquery', $welcome_script);
});

add_action('admin_notices', 'shbw_show_welcome_banner');

/**
 * AJAX handler to dismiss welcome banner
 */
function shbw_dismiss_welcome_ajax() {
    check_ajax_referer('shbw_dismiss_welcome', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    update_option('shbw_welcome_dismissed', true);
    wp_send_json_success();
}
add_action('wp_ajax_shbw_dismiss_welcome', 'shbw_dismiss_welcome_ajax');

/**
 * AJAX handler to save configuration token
 */
function shbw_save_token_ajax() {
    check_ajax_referer('shbw_save_token', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
    
    if (empty($token)) {
        wp_send_json_error('Token cannot be empty');
    }
    
    // Sanitize token (only allow alphanumeric, hyphens, and underscores)
    $token = preg_replace('/[^a-zA-Z0-9\-_]/', '', $token);
    
    if (empty($token)) {
        wp_send_json_error('Invalid token format');
    }
    
    // Save the token
    update_option('shbw_config_token', $token);
    
    // Dismiss the welcome banner since token is now configured
    update_option('shbw_welcome_dismissed', true);
    
    wp_send_json_success([
        'message' => 'Token saved successfully',
        'token' => $token
    ]);
}
add_action('wp_ajax_shbw_save_token', 'shbw_save_token_ajax');

/**
 * Show banner again if token is removed
 */
function shbw_check_token_removal($old_value, $new_value) {
    // If token was removed, show banner again
    if (!empty($old_value) && empty($new_value)) {
        delete_option('shbw_welcome_dismissed');
    }
}
add_action('update_option_shbw_config_token', 'shbw_check_token_removal', 10, 2);