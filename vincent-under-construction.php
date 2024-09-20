<?php
/*
Plugin Name: Vincent's Under Construction
Description: Displays an under construction page when activated.
Version: 1.1
Author: Vincent Rozenberg
Author URI: https://vincentrozenberg.com
*/

// Add admin menu
function suc_add_admin_menu() {
    add_menu_page('Under Construction', 'Under Construction', 'manage_options', 'simple-under-construction', 'suc_admin_page');
}
add_action('admin_menu', 'suc_add_admin_menu');

// Create admin page
function suc_admin_page() {
    $options = get_option('suc_settings');
    ?>
    <div class="wrap">
        <h1>Under Construction Settings</h1>
        <?php
        // Display settings updated message
        if (isset($_GET['settings-updated'])) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Settings saved.</strong></p>
            </div>
            <?php
        }
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('suc_settings_group');
            do_settings_sections('simple-under-construction');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function suc_register_settings() {
    register_setting('suc_settings_group', 'suc_settings', 'suc_sanitize_settings');
    add_settings_section('suc_main_section', 'Main Settings', null, 'simple-under-construction');
    
    add_settings_field('suc_enabled', 'Enable Under Construction', 'suc_enabled_callback', 'simple-under-construction', 'suc_main_section');
    add_settings_field('suc_page', 'Under Construction Page', 'suc_page_callback', 'simple-under-construction', 'suc_main_section');
}
add_action('admin_init', 'suc_register_settings');

// Sanitize and validate input
function suc_sanitize_settings($input) {
    $new_input = array();
    
    $new_input['enabled'] = isset($input['enabled']) ? 1 : 0;
    $new_input['page'] = isset($input['page']) ? absint($input['page']) : 0;

    return $new_input;
}

// Callback for enabled setting
function suc_enabled_callback() {
    $options = get_option('suc_settings');
    $checked = isset($options['enabled']) ? checked($options['enabled'], 1, false) : '';
    echo '<input type="checkbox" name="suc_settings[enabled]" value="1"' . $checked . '>';
}

// Callback for page selection
function suc_page_callback() {
    $options = get_option('suc_settings');
    $pages = get_pages();
    echo '<select name="suc_settings[page]">';
    foreach ($pages as $page) {
        $selected = (isset($options['page']) && $options['page'] == $page->ID) ? 'selected' : '';
        echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
    }
    echo '</select>';
}

// Redirect to under construction page
function suc_redirect() {
    $options = get_option('suc_settings');
    
    if (isset($options['enabled']) && $options['enabled'] == 1 && !is_user_logged_in()) {
        $under_construction_page = isset($options['page']) ? get_permalink($options['page']) : home_url();
        
        if (!is_page($options['page'])) {
            wp_redirect($under_construction_page);
            exit;
        }
    }
}
add_action('template_redirect', 'suc_redirect');

// Add settings link on plugin page
function suc_settings_link($links) {
    $settings_link = '<a href="admin.php?page=simple-under-construction">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'suc_settings_link');
