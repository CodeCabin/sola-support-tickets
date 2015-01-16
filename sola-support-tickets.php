<?php
/*
  Plugin Name: Sola Support Tickets
  Plugin URI: http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/
  Description: Create a support centre within your WordPress admin. No need for third party systems!
  Version: 2.9
  Author: SolaPlugins
  Author URI: http://www.solaplugins.com
 */


/* 2.9
 * Bug Fixes:
 *  - Fixed PHP Errors
 *  - Tickets do not show in normal site search
 *  - Private tickets are not displayed to other users except the author and agents. 
 * 
 * 2.8
 * Bug Fixes:
 *  - Fixed PHP Errors
 * 
 * 2.7
 * New Features: 
 *  - Show or hide the departments dropdown.
 *  - Choose a default department.
 *  - Allow guests to submit a ticket
 *  - You can now notify the default agent or all agents when a new ticket is received.
 *  - Choose a default ticket status
 *  - Enable CAPTCHA on your ticket submission form
 * 
 * Improvements:
 *  - Output Buffering enabled.
 *  - Uses user's Display name instead of login name
 * 
 * 2.6 
 * New Features:
 *  - Internal Notes can be created
 *  - Departments are now available
 *  - Assign a ticket to an agent
 *  - Force the collection of new mails
 * 
 * Languages Added:
 *  - German (Thank you Michael Schulz)
 *  - French (Thank you Raymond Radet)
 *  - Spanish (Thank you Io)
 * 
 * 2.5 2014-08-01
 * Fixed a bug that stopped showing the responses in the front end
 * Code improvements (PHP Warnings)
 * 
 */

ob_start();

global $sola_st_version;
global $sola_st_p_version;

define("SOLA_ST_PLUGIN_NAME", "Sola Support Tickets");

global $sola_st_version;
global $sola_st_version_string;
$sola_st_version = "2.9";
$sola_st_version_string = "beta";


include "modules/metaboxes.php";


global $wpdb;


$plugin_url = ABSPATH . 'wp-content/plugins';

define("SOLA_ST_SITE_URL", get_bloginfo('url'));
define("SOLA_ST_PLUGIN_URL", $plugin_url . '/sola-support-tickets');
define("SOLA_ST_PLUGIN_DIR", plugins_url() . '/sola-support-tickets');


add_action('init', 'sola_st_init');
add_action('admin_menu', 'sola_st_admin_menu');

if (function_exists("sola_st_pro_wp_head")) {
    add_action('admin_head', 'sola_st_pro_wp_head');
} else {
    add_action('admin_head', 'sola_st_wp_head');
}

if (function_exists("sola_st_pro_user_head")) {
    add_action('wp_head', 'sola_st_pro_user_head');
} else {
    add_action('wp_head', 'sola_st_user_head');
}
if (function_exists("sola_st_pro_admin_head")) {
    add_action("admin_head", "sola_st_pro_admin_head");
}


add_shortcode("sola_st_submit_ticket", "sola_st_shortcode_submit_ticket_page");

register_activation_hook(__FILE__, 'sola_st_activate');
register_deactivation_hook(__FILE__, 'sola_st_deactivate');

function sola_st_init() {
    if (isset($_POST['action']) && $_POST['action'] == 'sola_submit_find_us') {
        sola_st_feedback_head();
        wp_redirect("./edit.php?post_type=sola_st_tickets&page=sola-st-settings", 302);
        exit();
    }
    if (isset($_POST['action']) && $_POST['action'] == 'sola_skip_find_us') {
        wp_redirect("./edit.php?post_type=sola_st_tickets&page=sola-st-settings", 302);
        exit();
    }

    if (isset($_GET['post_type']) && $_GET['post_type'] == "sola_st_tickets") {
        if (get_option('sola_st_first_time') == false) {
            update_option('sola_st_first_time', true);
            wp_redirect('edit.php?post_type=sola_st_tickets&page=sola-st-settings&action=welcome_page', 302);
            exit();
        }
    }


    $plugin_dir = basename(dirname(__FILE__)) . "/languages/";
    load_plugin_textdomain('sola_st', false, $plugin_dir);

    /* allow admins to create and edit tickets */
    $admins = get_role('administrator');
    $admins->add_cap('edit_sola_st_ticket');
    $admins->add_cap('edit_sola_st_tickets');
    $admins->add_cap('edit_other_sola_st_tickets');
    $admins->add_cap('publish_sola_st_tickets');
    $admins->add_cap('read_sola_st_ticket');
    $admins->add_cap('read_private_sola_st_tickets');
    $admins->add_cap('delete_sola_st_tickets');


    if (!get_option("sola_st_submit_ticket_page")) {
        $content = "[sola_st_submit_ticket]";
        $page_id = sola_st_create_page('submit-ticket', __("Submit a ticket", "sola_st"), $content);
        add_option("sola_st_submit_ticket_page", "$page_id");
    }

    /* check if options are correct */
    $sola_st_settings = get_option("sola_st_settings");

    if (!isset($sola_st_settings['sola_st_settings_default_priority'])) {
        $sola_st_settings['sola_st_settings_default_priority'] = 1;
    }
    if (!isset($sola_st_settings['sola_st_settings_allow_priority'])) {
        $sola_st_settings['sola_st_settings_allow_priority'] = 0;
    }
    if (!isset($sola_st_settings['sola_st_settings_notify_new_tickets'])) {
        $sola_st_settings['sola_st_settings_notify_new_tickets'] = 0;
    }
    if (!isset($sola_st_settings['sola_st_settings_notify_new_responses'])) {
        $sola_st_settings['sola_st_settings_notify_new_responses'] = 0;
    }
    if (!isset($sola_st_settings['sola_st_settings_allow_html'])) {
        $sola_st_settings['sola_st_settings_allow_html'] = 0;
    }
    if (!isset($sola_st_settings['sola_st_settings_thank_you_text'])) {
        $sola_st_settings['sola_st_settings_thank_you_text'] = __("Thank you for submitting your support ticket. One of our agents will respond as soon as possible.", "sola_st");
    }
    if (!isset($sola_st_settings['sola_st_settings_notify_agent_change'])) {
        $sola_st_settings['sola_st_settings_notify_agent_change'] = 0;
    }

    update_option("sola_st_settings", $sola_st_settings);
    /* version control */
    global $sola_st_version;
    if (floatval($sola_st_version) > floatval(get_option("sola_st_current_version"))) {
        /* new version update functionality here */

        update_option("sola_st_current_version", $sola_st_version);
    }
}

add_action('init', 'sola_st_create_ticket_post_type', 0);
add_action('init', 'sola_st_create_response_post_type', 0);
add_action('init', 'sola_st_create_internal_notes', 0);

add_action('wp_ajax_sola_st_save_response', 'sola_st_action_callback');
add_action('wp_ajax_sola_st_save_note', 'sola_st_action_callback');

function sola_st_create_ticket_post_type() {

    $labels = array(
        'name' => __('Tickets', 'sola_st'),
        'singular_name' => __('Ticket', 'sola_st'),
        'add_new' => __('New Ticket', 'sola_st'),
        'add_new_item' => __('Add New Ticket', 'sola_st'),
        'edit_item' => __('Edit Ticket', 'sola_st'),
        'new_item' => __('New Ticket', 'sola_st'),
        'all_items' => __('All Tickets', 'sola_st'),
        'view_item' => __('View Ticket', 'sola_st'),
        'search_items' => __('Search Tickets', 'sola_st'),
        'not_found' => __('No tickets found', 'sola_st'),
        'not_found_in_trash' => __('No tickets found in the Trash', 'sola_st'),
        'menu_name' => __('Support Tickets', 'sola_st')
    );
    $args = array(
        'labels' => $labels,
        'description' => __('Support tickets', 'sola_st'),
        'public' => true,
        'menu_position' => 50,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'support-tickets'),
        'publicly_queryable' => true,
        'supports' => array('title', 'editor', 'custom-fields', 'revisions', 'page-attributes', 'author'),
        'has_archive' => true,
        'capabilities' => array(
            'edit_post' => 'edit_sola_st_ticket',
            'edit_posts' => 'edit_sola_st_tickets',
            'edit_others_posts' => 'edit_other_sola_st_tickets',
            'publish_posts' => 'publish_sola_st_tickets',
            'read_post' => 'read_sola_st_ticket',
            'read_private_posts' => 'read_private_sola_st_tickets',
            'delete_post' => 'delete_sola_st_ticket'
        ),
        'map_meta_cap' => true
    );
    if (post_type_exists('sola_st_tickets')) {
        
    } else {
        register_post_type('sola_st_tickets', $args);
    }
}

function sola_st_create_response_post_type() {

    $labels = array(
        'name' => __('Responses', 'sola_st'),
        'singular_name' => __('Response', 'sola_st'),
        'add_new' => __('New Response', 'sola_st'),
        'add_new_item' => __('Add New Response', 'sola_st'),
        'edit_item' => __('Edit Response', 'sola_st'),
        'new_item' => __('New Response', 'sola_st'),
        'all_items' => __('All Responses', 'sola_st'),
        'view_item' => __('View Response', 'sola_st'),
        'search_items' => __('Search Responses', 'sola_st'),
        'not_found' => __('No responses found', 'sola_st'),
        'not_found_in_trash' => __('No responses found in the Trash', 'sola_st'),
        'menu_name' => __('Ticket Responses', 'sola_st')
    );
    $args = array(
        'labels' => $labels,
        'description' => __('Responses to support tickets', 'sola_st'),
        'public' => true,
        'menu_position' => 51,
        'hierarchical' => true,
        'rewrite' => array('slug' => 'ticket-response'),
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'publicly_queryable' => true,
        'supports' => array('title', 'editor', 'custom-fields', 'revisions', 'page-attributes', 'author'),
        'has_archive' => true,
        'capabilities' => array(
            'edit_post' => 'edit_sola_st_ticket',
            'edit_posts' => 'edit_sola_st_tickets',
            'edit_others_posts' => 'edit_other_sola_st_tickets',
            'publish_posts' => 'publish_sola_st_tickets',
            'read_post' => 'read_sola_st_ticket',
            'read_private_posts' => 'read_private_sola_st_tickets',
            'delete_post' => 'delete_sola_st_ticket'
        ),
        'map_meta_cap' => true
    );
    if (post_type_exists('sola_st_responses')) {
        
    } else {
        register_post_type('sola_st_responses', $args);
    }
}

function sola_st_create_internal_notes() {

    $labels = array(
        'name' => __('Notes', 'sola_st'),
        'singular_name' => __('Note', 'sola_st'),
        'add_new' => __('New Note', 'sola_st'),
        'add_new_item' => __('Add New Note', 'sola_st'),
        'edit_item' => __('Edit Note', 'sola_st'),
        'new_item' => __('New Note', 'sola_st'),
        'all_items' => __('All Notes', 'sola_st'),
        'view_item' => __('View Note', 'sola_st'),
        'search_items' => __('Search Notes', 'sola_st'),
        'not_found' => __('No notes found', 'sola_st'),
        'not_found_in_trash' => __('No notes found in the Trash', 'sola_st'),
        'menu_name' => __('Internal Notes', 'sola_st')
    );
    $args = array(
        'labels' => $labels,
        'description' => __('Internal Notes for support tickets', 'sola_st'),
        'public' => true,
        'menu_position' => 52,
        'hierarchical' => true,
        'rewrite' => array('slug' => 'ticket-note'),
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'publicly_queryable' => false,
        'supports' => array('title', 'editor'),
        'has_archive' => true
    );
    if (post_type_exists('sola_st_notes')) {
        
    } else {
        register_post_type('sola_st_notes', $args);
    }
}

function sola_st_activate() {
    //sola_st_handle_db();
    if (!get_option("sola_st_email_to_ticket")) {
        add_option("sola_st_email_to_ticket", "0");
    }
    if (!get_option("sola_st_host")) {
        add_option("sola_st_host", "");
    }
    if (!get_option("sola_st_port")) {
        add_option("sola_st_port", "");
    }
    if (!get_option("sola_st_username")) {
        add_option("sola_st_username", "");
    }
    if (!get_option("sola_st_password")) {
        add_option("sola_st_password", "");
    }
    if (!get_option("sola_st_encryption")) {
        add_option("sola_st_encryption", "");
    }

    $sola_st_settings = get_option("sola_st_settings");
    if (!isset($sola_st_settings['sola_st_settings_thank_you_text'])) {
        $sola_st_settings['sola_st_settings_thank_you_text'] = __('Thank you for submitting your support ticket. One of our agents will respond as soon as possible.', 'sola_st');
    }

    update_option("sola_st_settings", $sola_st_settings);

    if (!get_option("sola_st_current_version")) {
        global $sola_st_version;
        add_option("sola_st_current_version", $sola_st_version);
    }


    flush_rewrite_rules();
}

function sola_st_deactivate() {
    
}

function sola_st_admin_menu() {
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Settings', 'sola_st'), __('Settings', 'sola_st'), 'manage_options', 'sola-st-settings', 'sola_st_settings_page');
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Feedback', 'sola'), __('Feedback', 'sola_st'), 'manage_options', 'sola-st-menu-feedback-page', 'sola_st_admin_feedback_layout');
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Log', 'sola'), __('System Log', 'sola_st'), 'manage_options', 'sola-st-menu-error-log', 'sola_st_admin_error_log_layout');
}

function sola_st_settings_page() {
    if (isset($_GET['page']) && $_GET['page'] == "sola-st-settings" && isset($_GET['action']) && $_GET['action'] == "welcome_page") {
        include('includes/welcome-page.php');
    } else {
        include('includes/settings-page.php');
    }
}

function sola_st_admin_error_log_layout() {
    include('includes/error-log-page.php');
}

function sola_st_admin_feedback_layout() {
    include('includes/feedback-page.php');
}

add_action('admin_print_scripts', 'sola_st_admin_scripts_basic');

function sola_st_admin_scripts_basic() {
    wp_enqueue_script('jquery');

    if (isset($_GET['post_type']) && isset($_GET['page']) && $_GET['page'] == "sola-st-settings") {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_register_style('sola_st_jquery_ui_theme_css', plugins_url('/css/jquery-ui-theme/jquery-ui.css', __FILE__));
        wp_enqueue_style('sola_st_jquery_ui_theme_css');


        wp_register_script('sola-st-tabs', plugins_url('js/sola_st_tabs.js', __FILE__), array('jquery-ui-core'), '', true);
        wp_enqueue_script('sola-st-tabs');
    }

    if (isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
        wp_register_script('sola-st', plugins_url('js/sola_st.js', __FILE__), array('jquery'), '', true);
        wp_enqueue_script('sola-st');
        wp_register_style('sola_st_user_styles', plugins_url('/css/sola-support-tickets.css', __FILE__));
        wp_enqueue_style('sola_st_user_styles', get_stylesheet_uri());
    }
}

function sola_st_user_styles() {
    wp_register_style('sola_st_user_styles', plugins_url('/css/sola-support-tickets.css', __FILE__));
    wp_enqueue_style('sola_st_user_styles', get_stylesheet_uri());

    wp_register_script('sola-st-form-validation', plugins_url('js/jquery.form-validator.min.js', __FILE__), array('jquery'), '', true);
    wp_enqueue_script('sola-st-form-validation');
    wp_register_script('sola-st-js', plugins_url('js/sola_st_frontend.js', __FILE__), array('jquery'), '', true);
    wp_enqueue_script('sola-st-js');
}

add_action('wp_enqueue_scripts', 'sola_st_user_styles');

function sola_st_wp_head() {
    @session_start();
    // post data handling

    global $sola_st_success;
    global $sola_st_error;

    /* move to activation hook */
    if (!get_option("sola_st_default_assigned_to")) {
        $super_admins = get_super_admins();
        $user = get_user_by('slug', $super_admins[0]);
        add_option('sola_st_default_assigned_to', $user->ID);
    }

    if (isset($_POST['sola_st_save_settings'])) {

        $sola_st_settings = array();
        if (isset($_POST['sola_st_settings_notify_new_tickets'])) {
            $sola_st_settings['sola_st_settings_notify_new_tickets'] = esc_attr($_POST['sola_st_settings_notify_new_tickets']);
        } else {
            $sola_st_settings['sola_st_settings_notify_new_tickets'] = 0;
        }
        if (isset($sola_st_settings['sola_st_settings_notify_new_responses'])) {
            $sola_st_settings['sola_st_settings_notify_new_responses'] = esc_attr($_POST['sola_st_settings_notify_new_responses']);
        } else {
            $sola_st_settings['sola_st_settings_notify_new_responses'] = 0;
        }
        if (isset($sola_st_settings['sola_st_settings_allow_html'])) {
            $sola_st_settings['sola_st_settings_allow_html'] = esc_attr($_POST['sola_st_settings_allow_html']);
        } else {
            $sola_st_settings['sola_st_settings_allow_html'] = 0;
        }
        if (isset($sola_st_settings['sola_st_settings_thank_you_text'])) {
            $sola_st_settings['sola_st_settings_thank_you_text'] = esc_attr($_POST['sola_st_settings_thank_you_text']);
        } else {
            $sola_st_settings['sola_st_settings_thank_you_text'] = __('Thank you for submitting your support ticket. One of our agents will respond as soon as possible.', 'sola_st');
        }
        if (isset($sola_st_settings['sola_st_settings_allow_priority'])) {
            $sola_st_settings['sola_st_settings_allow_priority'] = esc_attr($_POST['sola_st_settings_allow_priority']);
        } else {
            $sola_st_settings['sola_st_settings_allow_priority'] = 0;
        }
        if (isset($sola_st_settings['sola_st_settings_default_priority'])) {
            $sola_st_settings['sola_st_settings_default_priority'] = esc_attr($_POST['sola_st_settings_default_priority']);
        } else {
            $sola_st_settings['sola_st_settings_default_priority'] = 0;
        }

        update_option('sola_st_settings', $sola_st_settings);
        echo "<div class='updated'>";
        _e("Your settings have been saved.", "sola_st");
        echo "</div>";
    }


    if (isset($_POST['sola_st_send_feedback'])) {
        if (wp_mail("support@solaplugins.com", "Support Tickets Plugin feedback", "Name: " . $_POST['sola_st_feedback_name'] . "\n\r" . "Email: " . $_POST['sola_st_feedback_email'] . "\n\r" . "Website: " . $_POST['sola_st_feedback_website'] . "\n\r" . "Feedback:" . $_POST['sola_st_feedback_feedback'])) {
            echo "<div id=\"message\" class=\"updated\"><p>" . __("Thank you for your feedback. We will be in touch soon", "sola_st") . "</p></div>";
        } else {

            if (function_exists('curl_version')) {
                $request_url = "http://www.solaplugins.com/apif-support-tickets/rec_feedback.php";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $request_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
                curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);
                echo "<div id=\"message\" class=\"updated\"><p>" . __("Thank you for your feedback. We will be in touch soon", "sola_st") . "</p></div>";
            } else {
                echo "<div id=\"message\" class=\"error\">";
                echo "<p>" . __("There was a problem sending your feedback. Please log your feedback on ", "sola_st") . "<a href='http://solaplugins.com/support-desk' target='_BLANK'>http://solaplugins.com/support-desk</a></p>";
                echo "</div>";
            }
        }
    }
}

function sola_st_return_error_log() {
    $fh = @fopen(ABSPATH . 'wp-content/uploads/sola' . "/sola_st_log.txt", "r");
    $ret = "";
    if ($fh) {
        for ($i = 0; $i < 10; $i++) {
            $visits = fread($fh, 4096);
            $ret .= $visits;
        }
    } else {
        $ret .= __("No errors to report on", "sola_st");
    }
    return $ret;
}

function sola_st_return_error($data) {
    echo "<div id=\"message\" class=\"error\"><p><strong>" . $data->get_error_message() . "</strong><blockquote>" . $data->get_error_data() . "</blockquote></p></div>";
    sola_st_write_to_error_log($data);
}

function sola_st_write_to_error_log($data) {
    $upload_dir = wp_upload_dir();
    if (sola_st_error_directory()) {
        if (is_multisite()) {
            $content = "\r\n" . date("Y-m-d H:i:s", current_time('timestamp')) . ": " . $data->get_error_message() . " -> " . $data->get_error_data();
            $fp = fopen($upload_dir['basedir'] . '/sola' . "/sola_st_log.txt", "a+");
            fwrite($fp, $content);
        } else {
            $content = "\r\n" . date("Y-m-d H:i:s", current_time('timestamp')) . ": " . $data->get_error_message() . " -> " . $data->get_error_data();
            $fp = fopen(ABSPATH . 'wp-content/uploads/sola' . "/sola_st_log.txt", "a+");
            fwrite($fp, $content);
        }
    }

    error_log(date("Y-m-d H:i:s", current_time('timestamp')) . ": " . SOLA_ST_PLUGIN_NAME . ": " . $data->get_error_message() . "->" . $data->get_error_data());
}

function sola_st_error_directory() {
    $upload_dir = wp_upload_dir();

    if (is_multisite()) {
        if (!file_exists($upload_dir['basedir'] . '/sola')) {
            wp_mkdir_p($upload_dir['basedir'] . '/sola');
            $content = "Log created";
            $fp = fopen($upload_dir['basedir'] . '/sola' . "/sola_st_log.txt", "w+");
            fwrite($fp, $content);
        }
    } else {
        if (!file_exists(ABSPATH . 'wp-content/uploads/sola')) {
            wp_mkdir_p(ABSPATH . 'wp-content/uploads/sola');
            $content = "Log created";
            $fp = fopen(ABSPATH . 'wp-content/uploads/sola' . "/sola_st_log.txt", "w+");
            fwrite($fp, $content);
        }
    }
    return true;
}

function sola_st_get_response_data($post_id) {
    $data = get_post($post_id);
    if (isset($data) && $data) {
        return $data;
    } else {
        return false;
    }
}

function sola_st_action_callback() {

    global $wpdb;
    $check = check_ajax_referer('sola_st', 'security');



    if ($check == 1) {

        if ($_POST['action'] == "sola_st_save_response") {
            if (!isset($_POST['parent'])) {
                return false;
            }

            $parent_id = $_POST['parent'];
            $content_current = $_POST['content'];
            $title = $_POST['title'];
            $author = $_POST['author'];


            /* check if we allow for HTML or not */
            $content = sola_st_check_for_html(urldecode($content_current));

            $data = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => urldecode($title),
                'post_type' => 'sola_st_responses',
                'post_author' => $author,
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            );
            $post_id = wp_insert_post($data);


            update_post_meta($post_id, '_response_parent_id', $parent_id);

            sola_st_notification_control('response', $parent_id, get_current_user_id());
        } else if ($_POST['action'] == "sola_st_save_note") {
            if (!isset($_POST['parent'])) {
                return false;
            }

            $parent_id = $_POST['parent'];
            $content_current = $_POST['content'];
            $title = $_POST['title'];
            $author = $_POST['author'];


            /* check if we allow for HTML or not */
            $content = sola_st_check_for_html(urldecode($content_current));

            $data = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => urldecode($title),
                'post_type' => 'sola_st_notes',
                'post_author' => $author,
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            );
            $post_id = wp_insert_post($data);


            update_post_meta($post_id, '_note_parent_id', $parent_id);
        }
    }

    die(); // this is required to return a proper result
}

function sola_st_check_for_html($content) {


    if (current_user_can('edit_sola_st_ticket', array(null))) {
        /* they're a support agent, they can do what they want */
        return $content;
    } else {
        $sola_st_settings = get_option("sola_st_settings");
        if (isset($sola_st_settings['sola_st_settings_allow_html']) && $sola_st_settings['sola_st_settings_allow_html'] == 0) {
            return strip_tags($content);
        } else {
            return $content;
        }
    }
}

//remove_role('sola_st-ticket_author');

function sola_st_notification_control($type, $post_id, $userid, $email = false, $password = false) {
    $sola_st_settings = get_option("sola_st_settings");

    //echo "notification control".$type.$post_id;
    if ($type == 'response') {
        /* response */
        if ($sola_st_settings['sola_st_settings_notify_new_responses'] == "1") {
            /* get user who the post is assigned to */
            $meta_data = get_post_custom_values('ticket_assigned_to', $post_id);
            $user_details = get_user_by('id', $meta_data[0]);

            /* first figure out who sent the response */

            $post_data = get_post($post_id);

            $post_user = $post_data->post_author;



            /* get a list of everyone involved in this ticket */
            $meta_data = sola_st_get_post_meta_all($post_id);
            $notification_array = array();
            $notification_array[$post_user] = get_userdata($post_user)->user_email;
            foreach ($meta_data as $response) {
                $response_data = get_post($response->post_id);
                $response_user = $response_data->post_author;
                if (isset($notification_array[$response_user])) {
                    
                } else {
                    $notification_array[$response_user] = get_userdata($response_user)->user_email;
                }
            }
            $notification_array = array_unique($notification_array);


            foreach ($notification_array as $email_item) {
                wp_mail($email_item, __("New response", "sola_st") . " (" . $post_data->post_title . ")", __("There is a new response to the support ticket titled", "sola_st") . " \"" . $post_data->post_title . "\"\n\r" . __("Follow this link to view the reply", "sola_st") . " " . get_permalink($post_id));
            }
        }
    } else if ($type == 'ticket') {


        /* new ticket */
        extract($_POST);

        /* send an email to the owner of the ticket */
        $user_email = get_userdata($userid)->user_email;
        $post = get_post($post_id);

//        var_dump($user_email);
//        exit();
        if ($user_email == null && isset($sola_st_user_email_address)) {
            $user_email = $sola_st_user_email_address;
        }
        if (isset($user_email)) {
            $custom_fields = get_post_custom($post_id);
            if (!isset($custom_fields['ticket_reference'])) {
                $ticket_reference = md5($post_id . $userid);
                add_post_meta($post_id, 'ticket_reference', $ticket_reference, true);
            } else {
                $ticket_reference = $custom_fields['ticket_reference'][0];
            }

            if ($sola_st_settings['sola_st_username'] && function_exists('sola_st_pro_init')) {
                $admin_address = $sola_st_settings['sola_st_username'];
            } else {
                $admin_address = get_settings('admin_email');
            }

            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $admin_address . '>';
            $headers[] = 'Reply-To: ' . get_bloginfo('name') . ' <' . $admin_address . '>';


            $additional_response = $sola_st_settings['sola_st_settings_thank_you_text'];

            if ($email && $password) {
                $username = str_replace('+', '', $email);
                wp_mail($user_email, $post->post_title . " [$ticket_reference]", $additional_response . "\n\r\n\r" .
                        __("Please use the following credentials to access and respond to your ticket: ", "sola_st") . "\n\r\n\r" .
                        __("Username: ", "sola_st") . $username . "\n\r" .
                        __("Password: ", "sola_st") . $password . "\n\r" .
                        __("To login, please follow this link: ", "sola_st") . wp_login_url(get_permalink($post_id)) . "\n\r\n\r" .
                        __("To view your ticket, please follow this link:", "sola_st") . " " . get_permalink($post_id) . "\n\r\n\r", $headers);
            } else {
                wp_mail($user_email, $post->post_title . " [$ticket_reference]", $additional_response . "\n\r\n\r" . __("To access your ticket, please follow this link:", "sola_st") . " " . get_permalink($post_id), $headers);
            }
        }

        if (isset($sola_st_settings['sola_st_settings_notify_all_agents']) && $sola_st_settings['sola_st_settings_notify_all_agents'] == "1") {
            /* Notify all agents function must go here */
            if (function_exists('sola_st_pro_activate')) {
                sola_st_notify_all_agents($post_id);
            }
        } else {
            /* send an email to the auto assigned support member */
            if (isset($sola_st_settings['sola_st_settings_notify_new_tickets']) && $sola_st_settings['sola_st_settings_notify_new_tickets'] == "1") {
                $meta_data = get_post_custom_values('ticket_assigned_to', $post_id);
                $user_details = get_user_by('id', $meta_data[0]);
                $user_email = $user_details->user_email;
                if (isset($user_email)) {
                    wp_mail($user_email, __("New support ticket:", "sola_st") . " " . $post->post_title . "", __("A new support ticket has been received. To access this ticket, please follow this link:", "sola_st") . " " . get_permalink($post_id));
                }
            }
        }
    } else if ('agent_change') {

        $sola_st_settings['sola_st_settings_notify_agent_change'];

        $post_data = get_post($post_id);
        $user_details = get_user_by('id', $userid);
        $user_email = $user_details->user_email;
        wp_mail($user_email, __("New Ticket Assigned", "sola_st") . " (" . $post_data->post_title . ")", __("A new ticket has been assigned to you. ", "sola_st") . " \"" . $post_data->post_title . "\"\n\r" . __("Follow this link to view the ticket", "sola_st") . " " . get_page_link($post_id));
    } else {
        return;
    }
}

function sola_st_append_responses_to_ticket($post_id) {
    $ticket_id = $post_id;
    $meta_data = sola_st_get_post_meta_all($ticket_id);
    //$sola_content = '<hr />';
    $post_data = get_post($post_id);
    $custom = get_post_custom($post_id);

    if ($meta_data) {
        $sola_content = "<h3>" . __("Replies", "sola_st") . "</h3>";
    } else {
        $sola_content = "";
    }



    if ($custom['ticket_status'][0] == "1") {
        $add_a_response = "";
    } else {
        if (is_user_logged_in()) {

            if (function_exists("sola_st_pro_metabox_addin_macros") && current_user_can('edit_sola_st_ticket', $post_id)) {
                $macro = sola_st_pro_metabox_addin_macros(1);
            } else {
                $macro = "";
            }



            $add_a_response = '
            <h2 class="sola_st_response_title">' . __('Add a Response', 'sola_st') . '</h2>
                <div class="sola_st_response_div">
                    <form name="sola_st_add_response" method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" value="' . $post_id . '" name="sola_st_response_id" id="sola_st_response_id" />
                        <table width="100%">
                        <tr>
                           <td>
                              <input style="width:50%; min-width:200px; margin-bottom:5px; font-weight:bold;" type="text" value="Reply to ' . get_the_title() . '" name="sola_st_response_title" id="sola_st_response_title" />
                              <textarea style="width:100%; height:120px;" name="sola_st_response_text" id="sola_st_response_text"></textarea>
                              ' . $macro . '
                           </td>
                        </tr>
                        <tr>
                           <td align="right">
                                <input type="submit" value="' . __("Send", "sola_st") . '" class="sola_st_button_send_reponse" />
                           </td>
                        </tr>
                        </table>
                    </form>

                </div>
            ';
        } else {
            $add_a_response = "
               <br />
            <p><strong><a href=\"" . wp_login_url(get_permalink()) . "\">" . __("Log in", "sola_st") . "</a> " . __("or", "sola_st") . " <a href=\"" . wp_registration_url() . "\">" . __("register", "sola_st") . "</a> " . __("to submit a response.", "sola_st") . "
            <br /><br /> </strong></p>
            ";
        }
    }


    foreach ($meta_data as $response) {
        $sola_content .= sola_st_draw_response_box($response->post_id);
    }



    return $sola_content . $add_a_response;
}

add_filter('the_content', 'sola_st_content_control');


add_filter('next_post_link', 'sola_st_next_previous_fix');
add_filter('previous_post_link', 'sola_st_next_previous_fix');

function sola_st_next_previous_fix($url) {
    if (get_post_type() == "sola_st_tickets") {
        return "";
    }
}

function sola_st_content_control($content) {
    global $post;
    $sola_content = "";

    if (!isset($post)) {
        return $content;
    }

    if (get_post_type($post) == "sola_st_tickets") {
        /* is single page? */

        if (!is_single() && !is_admin()) {
            return $content;
        } else {
            $is_public = get_post_meta($post->ID, 'ticket_public', true);

            $ticket_status = get_post_meta($post->ID, 'ticket_status', true);

            /*
             * 0 - Open 
             * 1 - Solved
             * 9 - Pending
             */

            if ($ticket_status == '0') {
                /* Open Ticket */
                $current_user = wp_get_current_user();
                if ((get_the_author_meta('ID') == $current_user->ID) || current_user_can('edit_sola_st_ticket', array(null))) {
                    /* This guy owns the ticket or it is an agent. Show it to him */
                    $show_ticket = true;
                    $message = "";
                } else {
                    /* The current user doesnt own the ticket. Check if the ticket is public */
                    if ($is_public) {
                        $show_ticket = true;
                        $message = "";
                    } else {
                        $show_ticket = false;
                        $message = __('This support ticket has been marked private.', 'sola_st');
                    }
                }

                if ($show_ticket) {
                    $content = $content;
                    $content = $content . sola_st_append_responses_to_ticket(get_the_ID());
                } else {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket has been  marked as private.", "sola_st") . "</span>";
                    $content = $sola_content;
                }
            } else if ($ticket_status == '1') {
                /* Solved Ticket */
                $current_user = wp_get_current_user();
                if ((get_the_author_meta('ID') == $current_user->ID) || current_user_can('edit_sola_st_ticket', array(null))) {
                    /* This guy owns the ticket or it is an agent. Show it to him */
                    $show_ticket = true;
                    $message = "";
                } else {
                    /* The current user doesnt own the ticket. Check if the ticket is public */
                    if ($is_public) {
                        $show_ticket = true;
                        $message = __('This support ticket has been marked as solved.', 'sola_st');
                    } else {
                        $show_ticket = false;
                        $message = __('This support ticket has been marked private.', 'sola_st');
                    }
                }

                if ($show_ticket) {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket has been  marked as solved.", "sola_st") . "</span>";
                    $content = $sola_content . $content;
                    $content = $content . sola_st_append_responses_to_ticket(get_the_ID());
                } else {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket has been  marked as solved.", "sola_st") . "</span>";
                    $content = $sola_content;
                }
            } else if ($ticket_status == '9') {
                /* Pending Ticket */
                $current_user = wp_get_current_user();
                if ((get_the_author_meta('ID') == $current_user->ID) || current_user_can('edit_sola_st_ticket', array(null))) {
                    /* This guy owns the ticket or it is an agent. Show it to him */
                    $show_ticket = true;
                    $message = "";
                } else {
                    /* Pending Approval. Dont show. */
                    $show_ticket = false;
                    $message = __('This support ticket is pending approval by an agent.', 'sola_st');
                }

                if ($show_ticket) {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket is pending approval.", "sola_st") . "</span>";
                    $content = $sola_content . $content;
                    $content = $content;
                } else {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket is pending approval.", "sola_st") . "</span>";
                    $content = $sola_content;
                }
            }
        }
    }

    return $content;
}

//    
//    if (get_post_type( $post ) == "sola_st_tickets") {
//        /* is single page? /*
//         * 
//         */
//        if (!is_single() && !is_admin()) {
//            return $content;
//        } else {
//            $post_id = get_the_ID();
//            $custom = get_post_custom($post_id);
//            if ($custom['ticket_status'][0] == "9") {
//
//                /* check if there is a user logged in */
//                $current_user = wp_get_current_user();
//                if (!$current_user->ID) {
//                    /* show 404 template as the user is not logged in and it is pending */
//                    return __("This support ticket is marked as private or is pending approval.","sola_st");
//                }
//                else {
//                /* check if it's the owner of the ticket */
//                    $show_content = false;
//                    if ((get_the_author_meta('ID') == $current_user->ID)) {
//                        /* this is the user that posted the ticket */
//                        $show_content = true;
//                    } else {
//                        /* let's check if the current user has capabilitie to see tickets */
//                        if (current_user_can('edit_sola_st_ticket')) {
//                            $show_content = true;
//                        } else {
//                            $show_content = false;
//                        }
//
//                    }
//
//                    if ($show_content) {
//                        $sola_content .= "<span class='sola_st_pending_approval_span'>".__("This support ticket is pending approval.","sola_st")."</span>";
//                        $content = $content.$sola_content;
//                    }
//                }
//            } else if ($custom['ticket_status'][0] == "0") {  
//                /* open ticket */
//
//                /* can others see the ticket or not? - pro version only */
//
//                if (function_exists("sola_st_check_if_public")) {
//                    $show_content = false;
//                } else { 
//
//                    
//                    $show_content = false;
//                    if ((get_the_author_meta('ID') == $current_user->ID)) {
//                        /* this is the user that posted the ticket */
//                        $show_content = true;
//                    } else {
//                        /* let's check if the current user has capabilitie to see tickets */
//                        if (current_user_can('edit_sola_st_ticket')) {
//                            $show_content = true;
//                        } else {
//                            $show_content = false;
//                        }
//
//                    }
//                }
//                if ($show_content) {
//                    $sola_content = "";
//                    $pre_content = "";
//                    $after_content = sola_st_show_author_box(get_the_author_meta('ID'),get_the_date(),get_the_time());
//
//                    $content = $pre_content.$content.$sola_content.$after_content;
//                }
//
//                $content = $content.sola_st_append_responses_to_ticket(get_the_ID());
//                
//            } else if ($custom['ticket_status'][0] == "1") {  
//                /* solved ticket */
//
//                /* can others see the ticket or not? - pro version only */
//                $current_user = wp_get_current_user();
//                if (!$current_user->ID) {
//                    return __("You cannot view this support ticket.","sola_st");
//                }
//                else {
//                /* check if it's the owner of the ticket */
//                    $show_content = false;
//                    if ((get_the_author_meta('ID') == $current_user->ID)) {
//                        /* this is the user that posted the ticket */
//                        $show_content = true;
//                    } else {
//                        /* let's check if the current user has capabilitie to see tickets */
//                        if (current_user_can('edit_sola_st_ticket')) {
//                            $show_content = true;
//                        } else {
//                            $show_content = false;
//                        }
//
//                    }
//
//                    if ($show_content) {
//                        $sola_content .= "<span class='sola_st_pending_approval_span'>".__("This support ticket is marked as solved.","sola_st")."</span>";
//                        $content = $content.$sola_content;
//                        $content = $content.sola_st_append_responses_to_ticket(get_the_ID());
//                    }
//                }
//
//
//
//                
//            }
//            $content = $content;
//        }
//    }
//    return $content;


function sola_st_draw_response_box($post_id) {
    $response_data = sola_st_get_response_data($post_id);

    if ($response_data->post_status != "publish") {
        return "";
    }

    $author_data = get_userdata($response_data->post_author);
    $sola_content = '<div class="sola_st_response" style="width:100%; display:block; overflow:auto;">';

    if (isset($author_data->roles[0])) {
        $role = $author_data->roles[0];
    } else {
        $role = $author_data->roles[1];
    }

    $sola_content .= "<div class='sola_st_post_author'>";
    $sola_content .= "<div class='sola_st_post_author_image'>" . get_avatar($author_data->user_email, '50') . "</div>";
    $sola_content .= "<div class='sola_st_post_author_meta'><span class=\"sola_response_user\">" . $author_data->display_name . "</span><br/><span class=\"sola_response_user_type\">" . $role . "</span><br /><span title=\"" . $response_data->post_date . "\" class=\"sola_response_time\">" . sola_st_time_elapsed_string(strtotime($response_data->post_date)) . "</span><br />";
    $sola_content .= "<span class=\"sola_st_post_title\">" . $response_data->post_title . "</span></div></div>";
    $sola_content .= "<div class=\"sola_st_post_response\"><p>" . nl2br($response_data->post_content) . "</p>";
    $sola_content .= "<div class='sola_st_post_box'>";
    if (sola_st_is_admin()) {
        $sola_content .= "<span class='sola_st_admin_edit_response_span'><a href='" . get_edit_post_link($post_id) . "' class='sola_button'>" . __("edit", "sola_st") . "</a></span> &nbsp;";
        $sola_content .= "<span class='sola_st_admin_delete_response_span'><a href='" . get_delete_post_link($post_id) . "' class='sola_button'>" . __("delete", "sola_st") . "</a></span>";
    }
    $sola_content .= '</div>';
    $sola_content .= '</div></div>';
    $sola_content .= '<hr />';
    return $sola_content;
}

function sola_st_is_admin() {
    /* build this up according to user roles in the near future */
    if (current_user_can('manage_options')) {
        return true;
    } else {
        return false;
    }
}

function sola_st_user_head() {


    if (isset($_POST['sola_st_submit_ticket']) && $_POST['sola_st_ticket_title'] != "") {

        /* add a option to save as draft or live (settings) */

        $content = sola_st_check_for_html($_POST['sola_st_ticket_text']);

        $tax_input = array(
            'sola_st_deparments' => wp_strip_all_tags($_POST['sola_st_submit_department'])
        );

        $sola_st_settings = get_option("sola_st_settings");

        $data = array(
            'post_content' => $content,
            'post_status' => 'publish',
            'post_title' => esc_attr(wp_strip_all_tags($_POST['sola_st_ticket_title'])),
            'post_type' => 'sola_st_tickets',
            'post_author' => get_current_user_id(),
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        );
        $post_id = wp_insert_post($data);



        $custom_fields = get_post_custom($post_id);
        if (!isset($custom_fields['ticket_status'])) {
            add_post_meta($post_id, 'ticket_status', '9', true);
        }
        if (!isset($custom_fields['ticket_public'])) {
            add_post_meta($post_id, 'ticket_public', '0', true);
        }

        if (isset($_POST['sola_st_submit_priority'])) {
            add_post_meta($post_id, 'ticket_priority', $_POST['sola_st_submit_priority'], true);
        } else {
            /* get default */
            $sola_default_priority = $sola_st_settings['sola_st_settings_default_priority'];
            if (!$sola_default_priority) {
                $sola_default_priority = 0;
            }
            add_post_meta($post_id, 'ticket_priority', $sola_default_priority, true);
        }

        if (!isset($custom_fields['ticket_assigned_to'])) {
            if (!get_option("sola_st_default_assigned_to")) {
                $super_admins = get_super_admins();
                $user = get_user_by('slug', $super_admins[0]);
                add_option('sola_st_default_assigned_to', $user->ID);
            }
            $default_user = get_option("sola_st_default_assigned_to");
            add_post_meta($post_id, 'ticket_assigned_to', $default_user, true);
        }
        add_post_meta($post_id, 'ticket_reference', md5($post_id . get_current_user_id()), true);
        sola_st_notification_control('ticket', $post_id, get_current_user_id());
        wp_redirect(get_permalink($post_id));
    }

    if (isset($_POST['sola_st_response_id']) && $_POST['sola_st_response_id'] != "") {

        $parent_id = esc_attr($_POST['sola_st_response_id']);

        $content = sola_st_check_for_html($_POST['sola_st_response_text']);

        $data = array(
            'post_content' => $content,
            'post_status' => 'publish',
            'post_title' => esc_attr(wp_strip_all_tags($_POST['sola_st_response_title'])),
            'post_type' => 'sola_st_responses',
            'post_author' => get_current_user_id(),
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        );
        $post_id = wp_insert_post($data);
        update_post_meta($post_id, '_response_parent_id', $parent_id);
        sola_st_notification_control('response', $parent_id, get_current_user_id());
    }
}

function sola_st_tickets_cpt_columns($columns) {

    $new_columns = array(
        'ticket_priority_column' => __('Priority', 'sola_st'),
        'ticket_responses_column' => __('Responses', 'sola_st'),
        'ticket_last_responded_column' => __('Last Response By', 'sola_st'),
        'ticket_status' => __('Status', 'sola_st')
    );
    return array_merge($columns, $new_columns);
}

add_filter('manage_sola_st_tickets_posts_columns', 'sola_st_tickets_cpt_columns');

add_action('manage_sola_st_tickets_posts_custom_column', 'sola_st_manage_ticket_status_column', 10, 2);

function sola_st_manage_ticket_status_column($column_name, $post_id) {
    global $wpdb;
    switch ($column_name) {
        case 'ticket_responses_column':
            echo "<a href='" . get_edit_post_link($post_id) . "'>" . sola_st_cnt_responses($post_id) . "</a>";
            break;
        case 'ticket_priority_column':
            echo "<a href='" . get_edit_post_link($post_id) . "'>" . sola_st_return_ticket_priority($post_id) . "</a>";
            break;
        case 'ticket_last_responded_column':
            $data = sola_st_get_last_response($post_id);
            if (isset($data->post_author)) {
                $author = $data->post_author;
                //var_dump($author);

                if ($author) {
                    $author_data = get_userdata($author);
                    echo $author_data->display_name;

                    echo "<br /><small>" . sola_st_time_elapsed_string(strtotime($data->post_date)) . "</small>";
                } else {
                    echo "-";
                }
            } else {
                echo "-";
            }
            break;
        case 'ticket_status':
            echo sola_st_return_ticket_status($post_id);
            break;
        default:
            break;
    } // end switch
}

function sola_st_cnt_responses($id) {
    $meta_data = sola_st_get_post_meta_all($id);
    $cnt = count($meta_data);
    return $cnt;
}

function sola_st_get_last_response($id) {
    $meta_data = sola_st_get_post_meta_last($id);

    if (isset($meta_data[0])) {
        $post_id = $meta_data[0]->post_id;
        if ($meta_data) {
            $response_data = sola_st_get_response_data($post_id);
            return $response_data;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function sola_st_time_elapsed_string($ptime) {

    $etime = time() - $ptime;

    if ($etime < 1) {
        return 'Now';
    }

    $a = array(12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}

function sola_st_return_ticket_status($post_id) {
    $value = get_post_custom_values('ticket_status', $post_id);
    if ($value[0] == "0") {
        _e("Open", "sola_st");
    } else if ($value[0] == "1") {
        _e("Solved", "sola_st");
    } else if ($value[0] == "2") {
        _e("Closed", "sola_st");
    } else if ($value[0] == "9") {
        _e("Pending Approval", "sola_st");
    } else {
        _e("Unknown", "sola_st");
    }
}

function sola_st_return_ticket_status_from_meta_id($id) {
    if ($id == "0") {
        return __("Open", "sola_st");
    } else if ($id == "1") {
        return __("Solved", "sola_st");
    } else if ($id == "2") {
        return __("Closed", "sola_st");
    } else if ($id == "9") {
        return __("Pending Approval", "sola_st");
    } else {
        return __("Unknown", "sola_st");
    }
}

function sola_st_return_ticket_priority($post_id) {
    $value = get_post_custom_values('ticket_priority', $post_id);
    if ($value[0] == "1") {
        echo __("Low", "sola_st");
    } else if ($value[0] == "2") {
        echo __("High", "sola_st");
    } else if ($value[0] == "3") {
        echo "<span style='color:orange;'>" . __("Urgent", "sola_st") . "</span>";
    } else if ($value[0] == "4") {
        echo "<span style='color:red;'>" . __("Critical", "sola_st") . "</span>";
    } else {
        echo __("Low", "sola_st");
    }
}

function sola_st_return_ticket_priority_from_meta_id($id) {
    if ($id == "1") {
        return __("Low", "sola_st");
    } else if ($id == "2") {
        return __("High", "sola_st");
    } else if ($id == "3") {
        return __("Urgent", "sola_st");
    } else if ($id == "4") {
        return __("Critical", "sola_st");
    } else {
        return __("Low", "sola_st");
    }
}

function sola_st_create_page($slug, $title, $content) {
    // Initialize the post ID to -1. This indicates no action has been taken.
    $post_id = -1;

    // Setup the author, slug, and title for the post
    $author_id = 1;

    $post_type = "page";

    // If the page doesn't already exist, then create it
    $sola_check_page = get_page_by_title($title, '', $post_type);
    if ($sola_check_page == null) {

        // Set the page ID so that we know the page was created successfully
        $post_id = wp_insert_post(
                array(
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_author' => $author_id,
                    'post_name' => $slug,
                    'post_title' => $title,
                    'post_status' => 'publish',
                    'post_type' => $post_type,
                    'post_content' => $content
                )
        );
        return $post_id;

        // Otherwise, we'll stop and set a flag
    } else {

        // Arbitrarily use -2 to indicate that the page with the title already exists

        return $sola_check_page->ID;


        //$post_id = -2;
    } // end if
}

function sola_st_shortcode_submit_ticket_page($atr, $text = null) {

    $sola_st_settings = get_option("sola_st_settings");
//    var_dump($sola_st_settings);
    if (function_exists('sola_st_pro_activate')) {
        if (isset($sola_st_settings['sola_st_settings_require_login']) && $sola_st_settings['sola_st_settings_require_login'] == 1) {
            if (is_user_logged_in()) {
                echo sola_st_submission_form();
            } else {
                $content = "
                    <a href=\"" . wp_login_url(get_permalink()) . "\">" . __("Log in", "sola_st") . "</a> " . __("or", "sola_st") . " <a href=\"" . wp_registration_url() . "\">" . __("register", "sola_st") . "</a> " . __("to submit a support ticket.", "sola_st") . "
                    <br /><br />";
                echo $content;
            }
        } else {
            echo sola_st_submission_form();
        }
    } else {
        if (is_user_logged_in()) {
            echo sola_st_submission_form();
        } else {
            $content = "
                <a href=\"" . wp_login_url(get_permalink()) . "\">" . __("Log in", "sola_st") . "</a> " . __("or", "sola_st") . " <a href=\"" . wp_registration_url() . "\">" . __("register", "sola_st") . "</a> " . __("to submit a support ticket.", "sola_st") . "
                <br /><br />";
            echo $content;
        }
    }
}

add_filter('views_edit-sola_st_tickets', 'meta_views_sola_st_tickets', 10, 1);

function meta_views_sola_st_tickets($views) {
    //$views['separator'] = '&nbsp;';
    $views['metakey'] = '<a href="edit.php?meta_data=ticket_status&ticket_status=9&post_type=sola_st_tickets">' . __('Pending Approval', 'sola_st') . '</a> (' . sola_st_return_pending_ticket_qty() . ")";
    $views['metakey'] .= '| <a href="edit.php?meta_data=ticket_status&ticket_status=0&post_type=sola_st_tickets">' . __('Open Tickets', 'sola_st') . '</a> (' . sola_st_return_open_ticket_qty() . ")";
    $views['metakey'] .= '| <a href="edit.php?meta_data=ticket_status&ticket_status=1&post_type=sola_st_tickets">' . __('Solved Tickets', 'sola_st') . '</a> (' . sola_st_return_solved_ticket_qty() . ")";
    $views['metakey'] .= '| <a href="edit.php?meta_data=ticket_status&ticket_status=2&post_type=sola_st_tickets">' . __('Closed Tickets', 'sola_st') . '</a> (' . sola_st_return_closed_ticket_qty() . ")";
    return $views;
}

add_action('load-edit.php', 'load_sola_st_custom_filter');

function load_sola_st_custom_filter() {
    global $typenow;

    if ('sola_st_tickets' != $typenow)
        return;

    add_filter('posts_where', 'posts_where_sola_st_status');
}

function posts_where_sola_st_status($where) {
    global $wpdb;
    if (isset($_GET['meta_data']) && !empty($_GET['meta_data'])) {
        $meta = esc_sql($_GET['meta_data']);
        $meta_val = esc_sql($_GET['ticket_status']);
        $where .= " AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='$meta' AND meta_value='$meta_val' )";
    }
    return $where;
}

function sola_st_return_open_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d", 'ticket_status', 0
            )
    );
    $total = $row->total;
    return $total;
}

function sola_st_return_pending_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d", 'ticket_status', 9
            )
    );
    $total = $row->total;
    return $total;
}

function sola_st_return_closed_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d", 'ticket_status', 2
            )
    );
    $total = $row->total;
    return $total;
}

function sola_st_return_solved_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d", 'ticket_status', 1
            )
    );
    $total = $row->total;
    return $total;
}

if (!function_exists("sola_st_pro_activate")) {
    add_filter('pre_get_posts', 'sola_st_loop_control');
}

function sola_st_loop_control($query) {

//    if (!is_admin() && !is_single() && !is_page()) {
//        if (isset($query->query['post_type']) && $query->query['post_type'] == "sola_st_tickets" || isset($query->query['post_type']) && $query->query['post_type'] == "sola_st_responses") {
//            $query->set('post_type', 'sola_st_x'); /* 4 0 4 */
//            $query->parse_query();
//        }
//    }

    $current_user = wp_get_current_user();
    if ((get_the_author_meta('ID') == $current_user->ID) || current_user_can('edit_sola_st_ticket', array(null)) || current_user_can('read', array(null))) {
        if (!is_single() && !is_admin() && !is_page()) {
            if ($query->is_search) {
                if (isset($query->query['post_type']) && $query->query['post_type'] == "sola_st_tickets") {
                    if (current_user_can('edit_sola_st_tickets', array(null))) {
                        /* Agent is searching */
                        $query->set('meta_query', array(
                            'relation' => 'AND',
                            array(
                                'relation' => 'OR',
                                array(
                                    'key' => 'ticket_status',
                                    'value' => 0
                                ),
                                array(
                                    'key' => 'ticket_status',
                                    'value' => 1
                                ),
                                array(
                                    'key' => 'ticket_status',
                                    'value' => 9
                                )
                            ),
                            array(
                                'relation' => 'OR',
                                array(
                                    'key' => 'ticket_public',
                                    'value' => 1
                                ),
                                array(
                                    'key' => 'ticket_public',
                                    'value' => 0
                                )
                            )
                        ));
                    } else {
                        $query->set('meta_query', array(
                            'relation' => 'AND',
                            array(
                                'relation' => 'OR',
                                array(
                                    'key' => 'ticket_status',
                                    'value' => 0
                                ),
                                array(
                                    'key' => 'ticket_status',
                                    'value' => 1
                                )
                            ),
                            array(
                                'key' => 'ticket_public',
                                'value' => 1
                            )
                        ));
                    }
                } else {
                    /* Exclude support tickets from normal search */

                    $query->set('meta_query', array(
                        'relation' => 'AND',
                        array(
                            'key' => 'ticket_status',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => '_response_parent_id',
                            'compare' => 'NOT EXISTS'
                        )
                    ));
                }
                return $query;
            }
        }
    }
}

function sola_st_feedback_head() {
    if (function_exists('curl_version')) {

        $request_url = "http://www.solaplugins.com/apif-support-tickets/rec.php";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
    }
    return;
}

add_action('restrict_manage_posts', 'sola_st_add_priority_filter');
add_action('restrict_manage_posts', 'sola_st_add_agent_filter');
add_action('restrict_manage_posts', 'sola_st_add_status_filter');

function sola_st_add_priority_filter() {
    global $typenow;
    if ($typenow != "sola_st_tickets") {
        return;
    }
    ?>
    <select name="sola_st_priority_mv">
        <option value=""><?php _e('All Priorities', 'sola_st_tickets'); ?></option>
        <option value="1" <?php
        if (isset($_GET['sola_st_priority_mv']) && $_GET['sola_st_priority_mv'] == "1") {
            echo "selected='selected'";
        }
        ?>><?php echo esc_attr(sola_st_return_ticket_priority_from_meta_id(1)); ?></option>
        <option value="2" <?php
    if (isset($_GET['sola_st_priority_mv']) && $_GET['sola_st_priority_mv'] == "2") {
        echo "selected='selected'";
    }
        ?>><?php echo esc_attr(sola_st_return_ticket_priority_from_meta_id(2)); ?></option>
        <option value="3" <?php
                if (isset($_GET['sola_st_priority_mv']) && $_GET['sola_st_priority_mv'] == "3") {
                    echo "selected='selected'";
                }
                ?>><?php echo esc_attr(sola_st_return_ticket_priority_from_meta_id(3)); ?></option>
        <option value="4" <?php
    if (isset($_GET['sola_st_priority_mv']) && $_GET['sola_st_priority_mv'] == "4") {
        echo "selected='selected'";
    }
    ?>><?php echo esc_attr(sola_st_return_ticket_priority_from_meta_id(4)); ?></option>
    </select><input type="hidden" size="16" value="ticket_priority" name="sola_st_priority_mk" />
    <?php
}

function sola_st_add_status_filter() {
    global $typenow;
    if ($typenow != "sola_st_tickets") {
        return;
    }
    ?>
    <select name="sola_st_status_mv">
        <option value=""><?php _e('All Statuses', 'sola_st_tickets'); ?></option>
        <option value="99" <?php
    if (isset($_GET['sola_st_status_mv']) && $_GET['sola_st_status_mv'] == "99") {
        echo "selected='selected'";
    }
    ?>><?php echo esc_attr(sola_st_return_ticket_status_from_meta_id(0)); ?></option>
        <option value="1" <?php
    if (isset($_GET['sola_st_status_mv']) && $_GET['sola_st_status_mv'] == "1") {
        echo "selected='selected'";
    }
    ?>><?php echo esc_attr(sola_st_return_ticket_status_from_meta_id(1)); ?></option>
        <option value="2" <?php
    if (isset($_GET['sola_st_status_mv']) && $_GET['sola_st_status_mv'] == "2") {
        echo "selected='selected'";
    }
    ?>><?php echo esc_attr(sola_st_return_ticket_status_from_meta_id(2)); ?></option>
        <option value="9" <?php
    if (isset($_GET['sola_st_status_mv']) && $_GET['sola_st_status_mv'] == "9") {
        echo "selected='selected'";
    }
    ?>><?php echo esc_attr(sola_st_return_ticket_status_from_meta_id(9)); ?></option>
    </select><input type="hidden" size="16" value="ticket_status" name="sola_st_status_mk" />
        <?php
    }

    function sola_st_add_agent_filter() {

        global $typenow;
        if ($typenow != "sola_st_tickets") {
            return;
        }
        ?>
    <select name="sola_st_agent_mv">
        <option value=""><?php _e('All Agents', 'sola_st_tickets'); ?></option>
        <?php
        /* add superadmin */
        $super_admins = get_super_admins();
        $suser = get_user_by('slug', $super_admins[0]);
        ?>
        <option value="<?php echo $suser->ID; ?>" <?php
                if (isset($_GET['sola_st_agent_mv']) && $_GET['sola_st_agent_mv'] == $suser->ID) {
                    echo "selected='selected'";
                }
                ?>><?php echo $suser->display_name; ?></option>

    <?php
    $users = get_users(array(
        'meta_key' => 'sola_st_agent',
        'meta_value' => '1',
        'meta_compare' => '-',
    ));
    foreach ($users as $user) {
        ?>        
            <option value="<?php echo $user->ID; ?>" <?php
        if (isset($_GET['sola_st_agent_mv']) && $_GET['sola_st_agent_mv'] == $user->ID) {
            echo "selected='selected'";
        }
        ?>><?php echo $user->display_name; ?></option>
        <?php
    }
    ?>
    </select><input type="hidden" size="16" value="ticket_assigned_to" name="sola_st_agent_mk" />
    <?php
}

add_filter('pre_get_posts', 'sola_st_admin_loop_control');

function sola_st_admin_loop_control($query) {


    if (is_admin()) {
        if (isset($query->query['post_type']) && $query->query['post_type'] == "sola_st_tickets") {

            $agent = false;
            $status = false;
            $priority = false;

            if (isset($_GET['sola_st_agent_mk']) and isset($_GET['sola_st_agent_mv']) and ( $_GET['sola_st_agent_mv'] != '')) {
                $agent = true;
                $agent_array = array(
                    'key' => 'ticket_assigned_to',
                    'value' => $_GET['sola_st_agent_mv']
                );
            } else {
                $agent_array = array('' => '');
            }
            if (isset($_GET['sola_st_priority_mk']) and isset($_GET['sola_st_priority_mv']) and ( $_GET['sola_st_priority_mv']) != '') {
                $priority = true;
                $priority_array = array(
                    'key' => 'ticket_priority',
                    'value' => $_GET['sola_st_priority_mv']
                );
            } else {
                $priority_array = array('' => '');
            }
            if (isset($_GET['sola_st_status_mk']) and isset($_GET['sola_st_status_mv']) and ( $_GET['sola_st_status_mv']) != '') {
                $status = true;
                if ($_GET['sola_st_status_mv'] == "99") {
                    $status_code = 0;
                } else {
                    $status_code = $_GET['sola_st_status_mv'];
                }
                $status_array = array(
                    'key' => 'ticket_status',
                    'value' => "$status_code"
                );
            } else {
                $status_array = array('' => '');
            }


            if ($agent || $priority || $status) {
                $query->set('meta_query', array(
                    'relation' => 'AND',
                    $agent_array,
                    $status_array,
                    $priority_array
                        )
                );
            }
        }
    }
}

function sola_st_get_gravatar($email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array()) {
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}

function sola_st_show_author_box($id, $date, $time) {
    $user_data = get_user_by('id', $id);
    return "
        <div class='sola_st_author_box'>
            <img src='" . sola_st_get_gravatar($user_data->user_email, '50') . "' class='alignleft sola_st_author_image' />
            " . __("Submitted by ", "sola_st") . " <span class='sola_st_author_box_name'>" . $user_data->display_name . "</span><br />
            " . __("on ", "sola_st") . " <span class='sola_st_author_box_date'>" . $date . " " . $time . "</span>
                
        </div>";
}

function sola_st_submission_form() {
    $sola_st_settings = get_option('sola_st_settings');
    if (isset($sola_st_settings['sola_st_settings_allow_priority']) && $sola_st_settings['sola_st_settings_allow_priority'] == "1") {

        $sola_priority_text = "
            <tr class=\"sola_st_st_tr sola_st_st_subject\">
               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_label\">
                  <strong>" . __("Priority", "sola_st") . "</strong>
               </td>
               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_input\">
                  <select name=\"sola_st_submit_priority\" id=\"sola_st_submit_priority\">
                  <option value='1'>" . __("Low", "sola_st") . "</option>
                  <option value='2'>" . __("High", "sola_st") . "</option>
                  <option value='3'>" . __("Urgent", "sola_st") . "</option>
                  <option value='4'>" . __("Critical", "sola_st") . "</option>

                    </select>
               </td>
            </tr>";
    } else {
        $sola_priority_text = "";
    }

    $validation_text = __('You have not completed all required fields', 'sola_st');
    if (function_exists('sola_st_pro_activate')) {
        if (function_exists('sola_st_pro_departments')) {
            $sola_st_departments_row = sola_st_pro_departments();
        } else {
            $sola_st_departments_row = "";
        }
        if (!is_user_logged_in()) {
            if (function_exists('sola_st_pro_email_field')) {
                $sola_st_email_row = sola_st_pro_email_field();
            } else {
                $sola_st_email_row = "";
            }
        } else {
            $sola_st_email_row = "";
        }
        $captcha = sola_st_pro_captcha();
    } else {
        $sola_st_departments_row = "";
        $sola_st_email_row = "";
        $captcha = "";
    }

    $content = "
        <div class=\"sola_st_response_div\">
            <form name=\"sola_st_add_ticket\" method=\"POST\" action=\"\" id=\"sola_st_add_ticket\" enctype=\"multipart/form-data\">
                <table width=\"100%\" border=\"0\">
                $sola_st_email_row
                <tr class=\"sola_st_st_tr sola_st_st_subject\">
                   <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_subject_label\">
                      <strong>" . __("Subject", "sola_st") . "</strong>
                   </td>
                   <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_subject_input\">
                      <input type=\"text\" value=\"\" name=\"sola_st_ticket_title\" id=\"sola_st_ticket_title\" data-validation=\"required\" data-validation-error-msg=\"$validation_text\"/><br />
                   </td>
                </tr>
                <tr class=\"sola_st_st_tr sola_st_st_desc\">
                    <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_desc_label\">
                      <strong>" . __("Description", "sola_st") . "</strong>
                    </td>
                    <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_desc_textare\">
                      <textarea style=\"width:100%; height:120px;\" name=\"sola_st_ticket_text\" id=\"sola_st_ticket_text\" data-validation=\"required\" data-validation-error-msg=\"$validation_text\"></textarea><br />
                   </td>
                </tr>
                $sola_priority_text
                $sola_st_departments_row
                $captcha
                <tr class=\"sola_st_st_tr sola_st_st_submit\">
                   <td valign=\"top\"></td>
                   <td valign=\"top\" align=\"right\" class=\"sola_st_st_td sola_st_st_td_submit_button\">
                        <input type=\"submit\" name=\"sola_st_submit_ticket\" title=\"" . __("Send", "sola_st") . "\" class=\"sola_st_button_send_reponse\" />
                   </td>
                </tr>
                </table>
            </form>
        </div>";
    return $content;
}
