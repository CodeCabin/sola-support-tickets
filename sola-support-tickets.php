<?php
/*
  Plugin Name: Sola Support Tickets
  Plugin URI: http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/
  Description: Create a support centre within your WordPress admin. No need for third party systems!
  Version: 3.07
  Author: SolaPlugins
  Author URI: http://www.solaplugins.com
 */

/* 3.07 2015-04-14
 * Bug fix: Localization bug fixed. Some strings were not being translated.
 * Enhancement: French translation has been updated to support the Customer Satisfaction Surveys add-on (Etienne Couturier)
 * 
 * 3.06 2015-04-13
 * Fixed bug where CAPTCHA setting was not utilized in front-end along with PHP notice (code refactored)
 *
 * 3.05 2015-04-12
 * Fixed character-set encoding (SMTP e-mail)
 * Updated French translation file (Etienne Couturier)
 *
 * 3.04 2015-04-08
 * Made the warning to update both the premium and basic plugin dynamic
 * Fixed a compatibility issue with the Customer Satisfaction Survey add-on
 * 
 * 3.03 2015-03-30
 * Enhancement: Can select to use the Wordpress e-mail function or SMTP (valid SMTP settings are required). Headers set (From Name and From E-mail - available when using the premium version) are incorporated.
 * Enhancement: Response text given for a ticket is now included in the automated notification e-mail.
 * Enhancement: A single function has been created to send out all automated e-mails. This function takes into account settings such as custom From Email and From Name headers (can be set when using premium version) and whether to use SMTP settings or the Wordpress e-mail function.
 * Enhancement: All automated e-mails are now HTML with UTF-8 character set.
 * Enhancement: The responder of a support ticket no longer receives a notification e-mail stating that there was a response. Notifications to other recipients are still sent.
 * Enhancement: Added translation - Portuguese - Portugal (Miguel Madeira Rodrigues)
 * Bug fix: Default priority setting in the front-end was not being used.
 * Bug fix: Fixed the support ticket counters at the top of the Support Tickets Dashboard.
 * 
 * 3.02 2015-03-17 
 * Enhancement: Added From e-mail headers to automated notification e-mail - A user can now enter a From E-mail that will be used when sending notification e-mails. (Premium)
 * Ehancement: When a file upload is done in the ticket editor view and no response text is given, a message is added as a response stating that a file upload was done. (Premium)
 * Enhancement: More strings are now translateable (Premium and Basic)
 * Bug Fix: Replaced deprecated function. Replaced get_settings() with get_option() (Premium and Basic)
 * Bug Fix: White space in ticket status removed (Premium and Basic)
 * Bug Fix: User roles weren't showing in ticket responses 
 * Bug Fix: Encoding has been fixed for ticket responses and notes - encoding between javascript and PHP was incorrect with AJAX requests (Premium and Basic)
 * Bug Fix: Font Awesome and Bootstrap disable functionality PHP notices fixed (Premium)
 * Bug Fix: Fixed Departments setting PHP notices
 * Bug Fix: Fixed generic naming of classes and IDs in the plugin stylesheets to ensure compatability with themes. (Premium and Basic) 
 * 
 * 3.01
 * Files can now be uploaded and linked to support tickets (For browsers that support the HTML 5 file API only) (Premium)
 * Sola Support Tickets Languages added: Bengali, Croatian
 * New feature: Automatic support ticket closure after x days (Premium)
 * New feature: Notification e-mails now contain the ticket content
 * New feature: The e-mail address of the author is now visible within the ticket editor
 * You can now disable/enable the use of bootstrap on the support desk page (Premium)
 * You can now disable/enable the use of fontawesome on the support desk page (Premium)
 * Bug fix: incorrect db prefix was used previously
 *
 * 3.0 2015-01-16
 * Fixed submit support ticket page bug
 * Fixed a bug that may have shown support tickets on the front end even when marked as private
 * Support ticket status change notifications (new feature)
 * Bug fixes
 * Help desk improvements
 * Menu changes
 *
 * 2.9 2014-11-11
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
 
 


@session_start();
ob_start();

global $sola_st_version;
global $sola_st_pro_version;

define("SOLA_ST_PLUGIN_NAME", "Sola Support Tickets");

global $sola_st_version;
global $sola_st_version_string;
$sola_st_version = "3.07";
$sola_st_version_string = "basic";



include_once "modules/metaboxes.php";

global $wpdb;


$plugin_url = ABSPATH . 'wp-content/plugins';

define("SOLA_ST_SITE_URL", get_bloginfo('url'));
define("SOLA_ST_PLUGIN_URL", $plugin_url . '/sola-support-tickets');
define("SOLA_ST_PLUGIN_DIR", plugins_url() . '/sola-support-tickets');


add_action('init', 'sola_st_init');
add_action('admin_menu', 'sola_st_admin_menu' ,1);

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
    if (!isset($sola_st_settings['sola_st_settings_notify_status_change'])) {
        $sola_st_settings['sola_st_settings_notify_status_change'] = 0;
    }





    update_option("sola_st_settings", $sola_st_settings);
    /* version control */
    global $sola_st_version;
    if (floatval($sola_st_version) > floatval(get_option("sola_st_current_version"))) {
        /* new version update functionality here */

        update_option("sola_st_current_version", $sola_st_version);
    }
	
	
	sola_st_warn_update_pro();
	
	
	
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
        'menu_name' => __('Help Desk', 'sola_st')
    );
    $args = array(
        'labels' => $labels,
        'description' => __('Support tickets', 'sola_st'),
        'public' => true,
        'menu_position' => 50,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'support-tickets'),
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'query_var' => true,
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
        'show_in_menu' => false,
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
        'show_in_menu' => false,
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
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Responses', 'sola_st'), __('Responses', 'sola_st'), 'manage_options', 'edit.php?post_type=sola_st_responses');
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
        wp_register_style('sola_st_admin_styles', plugins_url('/css/sola-support-admin.css', __FILE__));
        wp_enqueue_style('sola_st_admin_styles', get_stylesheet_uri());

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
        if(is_object($user))
        {
            add_option('sola_st_default_assigned_to', $user->ID);
        }
    }


    if (isset($_POST['sola_st_save_settings'])) {
    	
		$data=get_option('sola_st_settings');
		
		
		
        $sola_st_settings = array();

        if (isset($_POST['sola_st_settings_notify_new_tickets'])) {
            $sola_st_settings['sola_st_settings_notify_new_tickets'] = esc_attr($_POST['sola_st_settings_notify_new_tickets']);
        } else {
            $sola_st_settings['sola_st_settings_notify_new_tickets'] = 0;
        }
        if (isset($_POST['sola_st_settings_notify_new_responses'])) {
            $sola_st_settings['sola_st_settings_notify_new_responses'] = esc_attr($_POST['sola_st_settings_notify_new_responses']);
        } else {
            $sola_st_settings['sola_st_settings_notify_new_responses'] = 0;
        }
        if (isset($_POST['sola_st_settings_allow_html'])) {
            $sola_st_settings['sola_st_settings_allow_html'] = esc_attr($_POST['sola_st_settings_allow_html']);
        } else {
            $sola_st_settings['sola_st_settings_allow_html'] = 0;
        }
        if (isset($_POST['sola_st_settings_thank_you_text'])) {
            $sola_st_settings['sola_st_settings_thank_you_text'] = esc_attr($_POST['sola_st_settings_thank_you_text']);
        } else {
            $sola_st_settings['sola_st_settings_thank_you_text'] = __('Thank you for submitting your support ticket. One of our agents will respond as soon as possible.', 'sola_st');
        }
        if (isset($_POST['sola_st_settings_allow_priority'])) {
            $sola_st_settings['sola_st_settings_allow_priority'] = esc_attr($_POST['sola_st_settings_allow_priority']);
        } else {
            $sola_st_settings['sola_st_settings_allow_priority'] = 0;
        }
        
        if (isset($_POST['sola_st_settings_default_priority'])) 
        {
            $sola_st_settings['sola_st_settings_default_priority'] = $_POST['sola_st_settings_default_priority'];
        } 
        else 
        {
            $sola_st_settings['sola_st_settings_default_priority'] = 0;
        }
		
        if (isset($_POST['sola_st_settings_notify_status_change'])) {
            $sola_st_settings['sola_st_settings_notify_status_change'] = esc_attr($_POST['sola_st_settings_notify_status_change']);
        } else {
            $sola_st_settings['sola_st_settings_notify_status_change'] = 0;
        }

        if(isset($_POST['cb_settings_enable_file_uploads']))
        {
            //true;
            $sola_st_settings['enable_file_uploads']=1;
        }
        else
        {
            //false
            $sola_st_settings['enable_file_uploads']=0;
        }
		
		
		if(isset($_REQUEST['rb_sola_mailing_system_selection']))
		{
			$rb_sola_mailing_system_selection=$_REQUEST['rb_sola_mailing_system_selection'];
			if($rb_sola_mailing_system_selection==='smtp')
			{
				if(isset($_REQUEST['sola_st_smtp_host_setting_php_mailer'])&&isset($_REQUEST['sola_st_smtp_username_setting_php_mailer'])&&isset($_REQUEST['sola_st_smtp_password_setting_php_mailer'])&&isset($_REQUEST['sola_st_smtp_port_setting_php_mailer'])&&isset($_REQUEST['sola_st_smtp_encryption_setting_php_mailer']))
				{
					$sola_st_settings['sola_st_smtp_host_setting_php_mailer'] = $_REQUEST['sola_st_smtp_host_setting_php_mailer'];
					$sola_st_settings['sola_st_smtp_username_setting_php_mailer'] = $_REQUEST['sola_st_smtp_username_setting_php_mailer'];
					$sola_st_settings['sola_st_smtp_password_setting_php_mailer'] = $_REQUEST['sola_st_smtp_password_setting_php_mailer'];
					$sola_st_settings['sola_st_smtp_port_setting_php_mailer'] = $_REQUEST['sola_st_smtp_port_setting_php_mailer'];
					$sola_st_settings['sola_st_smtp_encryption_setting_php_mailer'] = $_REQUEST['sola_st_smtp_encryption_setting_php_mailer'];
					$sola_st_settings['rb_sola_mailing_system_selection']='smtp';
				}
				else
				{
					$sola_st_settings['rb_sola_mailing_system_selection']='wp_mail';	
				}
			}
			else
			{
				$sola_st_settings['rb_sola_mailing_system_selection']='wp_mail';	
			}
		}
		else
		{
			$sola_st_settings['rb_sola_mailing_system_selection']='wp_mail';	
		}	


		
		if(isset($data['sola_st_automated_emails_from_name']))
		{
			$sola_st_settings['sola_st_automated_emails_from_name']=$data['sola_st_automated_emails_from_name'];
		}
		
		if(isset($data['sola_st_mailbox_cron_frequency']))
		{
			$sola_st_settings['sola_st_mailbox_cron_frequency']=$data['sola_st_mailbox_cron_frequency'];
		}
		
		if(isset($data['sola_st_automated_emails_from']))
		{
			$sola_st_settings['sola_st_automated_emails_from']=$data['sola_st_automated_emails_from'];
		}





        update_option('sola_st_settings', $sola_st_settings);
        echo "<div class='updated'>";
        _e("Your settings have been saved.", "sola_st");
        echo "</div>";
    }


    if (isset($_POST['sola_st_send_feedback'])) {
    	
		
		
		if(function_exists('send_automated_emails'))
		{
			$mail_result=send_automated_emails("support@solaplugins.com", "Support Tickets Plugin feedback", "Name: " . $_POST['sola_st_feedback_name'] . " <br/><br/> " . "Email: " . $_POST['sola_st_feedback_email'] . " <br/><br/> " . "Website: " . $_POST['sola_st_feedback_website'] . " <br/><br/> " . "Feedback:" . $_POST['sola_st_feedback_feedback']);
		}
		else
		{
			$mail_result=false;		
		}
		
		
        if ($mail_result===true) {
            echo "<div id=\"message\" class=\"updated\"><p>" . __("Thank you for your feedback. We will be in touch soon", "sola_st") . ".</p></div>";
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

    /* encoding error fixed 3 march 2015 - albert */
    /* url_decode() shouldn't be used */
    
    
    
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


           
            /*base 64 file upload*/


            if(isset($_POST['base_64_data'])&&isset($_POST['file_name'])&&isset($_POST['file_mime_type']))
            {
                
                if(trim($content_current)==='')
                {
                    $content_current=' <span style="font-style:italic;"> - '.__(' File uploaded ','sola_st').' - </span>';
                }
                
                
                
                
                
                
                
                $posted_full_base_64 = $_POST['base_64_data'];
                $posted_mime_type = $_POST['file_mime_type'];
                $posted_file_name = $_POST['file_name'];

                if( !function_exists( 'wp_handle_sideload' ) )
                {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }

                if( !function_exists( 'wp_get_current_user' ))
                {
                    require_once( ABSPATH . 'wp-includes/pluggable.php' );
                }


                $upload_dir = wp_upload_dir();
                $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
                $base64_array=explode(';base64,',$posted_full_base_64);
                $file_data_as_string=$base64_array[1];

                if($posted_mime_type==='application/pdf'||$posted_mime_type==='application/zip'||$posted_mime_type==='application/x-zip-compressed'||$posted_mime_type==='image/tiff'||$posted_mime_type==='image/png'||$posted_mime_type==='image/x-png'||$posted_mime_type==='image/jpeg'||$posted_mime_type==='image/gif')
                {
                    $decoded_base64_string = base64_decode($file_data_as_string);
                    $hashed_filename = md5( $posted_file_name . microtime() ) . '_' . $posted_file_name;
                    $image_upload = file_put_contents( $upload_path . $hashed_filename, $decoded_base64_string );

                    /* create my own fake php $_FILES array*/
                    $overload_php_files_array = array();
                    $overload_php_files_array['error'] = '';
                    $overload_php_files_array['tmp_name'] = $upload_path . $hashed_filename;
                    $overload_php_files_array['name'] = $hashed_filename;
                    $overload_php_files_array['type'] = $posted_mime_type;
                    $overload_php_files_array['size'] = filesize( $upload_path . $hashed_filename );

                    /*pass the fake $_FILES array to the wp_handle_sideload function as the first parameter - this is the format this wp function expects*/

                    $file_save_result = wp_handle_sideload($overload_php_files_array, array( 'test_form' => false));


                    $file_name_path=$file_save_result['file'];
                    $file_name_url=$file_save_result['url'];

                    $wp_filetype = wp_check_filetype(basename($file_name_url), null );
                    $post_mime_type=$wp_filetype['type'];
                    $post_title=preg_replace('/\.[^.]+$/', '', basename($file_name_url));



                    $attachment = array(
                        'post_mime_type' => $post_mime_type,
                        'post_title' => $post_title,
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'guid'=>$file_name_url,
                        'post_parent'=>$parent_id
                    );

                    wp_insert_attachment( $attachment, $file_name_path );


                }

            }











            /* check if we allow for HTML or not */
            $content = sola_st_check_for_html($content_current);

            $data = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => $title,
                'post_type' => 'sola_st_responses',
                'post_author' => $author,
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            );
            $post_id = wp_insert_post($data);

            update_post_meta($post_id, '_response_parent_id', $parent_id);
            sola_st_notification_control('response', $parent_id, get_current_user_id(),false,false,$content);
            
        } else if ($_POST['action'] == "sola_st_save_note") {
            if (!isset($_POST['parent'])) {
                return false;
            }

            
             /* encoding error fixed 3 march 2015 - albert */
             /* url_decode() shouldn't be used */
            
            
            $parent_id = $_POST['parent'];
            $content_current = $_POST['content'];
            $title = $_POST['title'];
            $author = $_POST['author'];


            /* check if we allow for HTML or not */
            $content = sola_st_check_for_html($content_current);

            $data = array(
                'post_content' => $content,
                'post_status' => 'publish',
                'post_title' => $title,
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



function sola_st_notification_control($type, $post_id, $userid, $email = false, $password = false,$response_text='') {
    
	global $wpdb;
    $sola_st_settings = get_option("sola_st_settings");
    
    


    $post_data=get_post( $post_id, ARRAY_A);
    $ticket_content=$post_data['post_content'];
    $ticket_content=str_replace('<br/>',"\r",$ticket_content);
    $ticket_content=str_replace('<br>',"\r",$ticket_content);
    $ticket_content=strip_tags($ticket_content);
    $ticket_content=html_entity_decode($ticket_content,ENT_QUOTES);
    
    $response_text=str_replace('<br/>',"\r",$response_text);
    $response_text=str_replace('<br>',"\r",$response_text);
    $response_text=strip_tags($response_text);
    $response_text=html_entity_decode($response_text,ENT_QUOTES);

	
	
	
	
    //echo "notification control".$type.$post_id;
    if ($type == 'response') {
    	
		
		/*add the latest response to the notification e-mail*/
		
		
		
		
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


            foreach ($notification_array as $email_item) 
            {
                
                if($email_item!==get_userdata($response_user)->user_email)
                {
                	
					if(isset($response_text)&&$response_text!=="")
					{
						$message_text= __("There is a new response to the support ticket titled", "sola_st") ." <br/><br/> ". $post_data->post_title . " <br/><br/> ".__("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> ".__("Response text","sola_st").": <br/><br/> ".$response_text." <br/><br/> ". __("Follow this link to view the reply", "sola_st") . " " . get_permalink($post_id);
					}
					else 
					{
						$message_text= __("There is a new response to the support ticket titled", "sola_st") ." <br/><br/> ". $post_data->post_title . " <br/><br/> ".__("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> ". __("Follow this link to view the reply", "sola_st") . " " . get_permalink($post_id);
					}
					
					
					if(function_exists('send_automated_emails'))
					{
						send_automated_emails($email_item,  __("New response", "sola_st") . " (" . $post_data->post_title . ")", $message_text);	
					}
					
                }
                
                
            }
        }
    } else if ($type == 'ticket') {


        /* new ticket */
        extract($_POST);

        /* send an email to the owner of the ticket */
        if(is_object(get_userdata($userid)))
        {
            $user_email = get_userdata($userid)->user_email;
        }

        $post = get_post($post_id);

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
                $admin_address = get_option('admin_email');
            }

            
			$headers_array['from']['name']=get_bloginfo('name');
			$headers_array['from']['address']=$admin_address;
			$headers_array['reply_to']['name']=get_bloginfo('name');
			$headers_array['reply_to']['address']=$admin_address;
			


            $additional_response = $sola_st_settings['sola_st_settings_thank_you_text'];

            if ($email && $password) 
            {
                $username = str_replace('+', '', $email);
				
				if(function_exists('send_automated_emails'))
				{
					send_automated_emails($user_email, $post->post_title . " [$ticket_reference]", $additional_response . "<br/><br/>" .
                  		__("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> ".
                        __("Please use the following credentials to access and respond to your ticket: ", "sola_st") . " <br/><br/> " .
                        __("Username: ", "sola_st") . $username . "<br/><br/>" .
                        __("Password: ", "sola_st") . $password . "<br/><br/>" .
                        __("To login, please follow this link: ", "sola_st") . wp_login_url(get_permalink($post_id)) . "<br/><br/>" .
                        __("To view your ticket, please follow this link:", "sola_st") . " " . get_permalink($post_id) . "<br/><br/>",$headers_array);
					
				}
            } 
            else 
            {
            	if(function_exists('send_automated_emails'))
				{
					send_automated_emails($user_email, $post->post_title . " [$ticket_reference]", $additional_response . " <br/><br/> ".__("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> " . __("To access your ticket, please follow this link:", "sola_st") . " " . get_permalink($post_id),$headers_array);	
				}
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
                if (isset($user_email)) 
                {
					if(function_exists('send_automated_emails'))
					{
						send_automated_emails($user_email, __("New support ticket:" ,"sola_st") . " " . $post->post_title . " <br/><br/> ".__("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> ", __("A new support ticket has been received. To access this ticket, please follow this link:", "sola_st") . " " . get_permalink($post_id));
						
					}
                	
                }
            }
        }

    } else if ($type == 'agent_change') {

        if(isset($sola_st_settings['sola_st_settings_notify_agent_change']) && $sola_st_settings['sola_st_settings_notify_agent_change'] == "1"){
            $post_data = get_post($post_id);
            $user_details = get_user_by('id', $userid);
            $user_email = $user_details->user_email;
			
			if(function_exists('send_automated_emails'))
			{
				send_automated_emails($user_email, __("New Ticket Assigned", "sola_st") . " (" . $post_data->post_title . ")", __("A new ticket has been assigned to you. ", "sola_st") . " \"" . $post_data->post_title . "\" <br/><br/> ".__("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> ". __("Follow this link to view the ticket", "sola_st") . " " . get_page_link($post_id));	
			}
			            
       }

    } else if ($type == 'status_change') {

        if(isset($sola_st_settings['sola_st_settings_notify_status_change']) && $sola_st_settings['sola_st_settings_notify_status_change'] == "1")
        {

            $post_data = get_post($post_id);
            $post_status = get_post_meta($post_id, 'ticket_status', true);

            if($post_status == 0){
                /* Open */
                $stat = __('Open', 'sola_st');
            } else if ($post_status == 1) {
                /* Solved */
                $stat = __('Solved', 'sola_st');
            } else if ($post_status == 9) {
                /* Pending */
                $stat = __('Pending Approval', 'sola_st');
            } else {
                /* Unknown */
                $stat = __('Unknown', 'sola_st');
            }

            $user_details = get_user_by('id', $userid);
            $user_email = $user_details->user_email;

			if(function_exists('send_automated_emails'))
			{
				send_automated_emails($user_email, __("Support Ticket Status Changed", "sola_st") . " (" . $post_data->post_title . ")",
                    __("Your Support Ticket ", "sola_st") . " \"" .
                    $post_data->post_title . " ".__("has been marked as $stat")."\" <br/><br/> " .
                      __("Ticket content","sola_st").": <br/><br/> ".$ticket_content." <br/><br/> ".
                    __("Follow this link to view the ticket", "sola_st") . " " .
                    get_page_link($post_id));
			}
			
        }

    }
    elseif($type==='customer_satisfaction_survey_send_out')
    {

        $post_status = get_post_meta($post_id, 'ticket_status', true);
        $user_details = get_user_by('id', $userid);
        $user_email = $user_details->user_email;




        if($post_status == 1 ||$post_status==2)
        {

            if((isset($sola_st_settings['enable_sending_of_customer_satisfaction_surveys'])&&$sola_st_settings['enable_sending_of_customer_satisfaction_surveys']==='true')&&(function_exists('sola_st_pro_activate')&&defined('SOLA_ST_CSS_CUSTOMER_SATISFACTION_SURVEY_ACTIVE')))
            {
                $query_customer_satisfaction_survey='SELECT * FROM '.SOLA_ST_CSS_CUSTOMER_SATISFACTION_SURVEYS.';';
                $available_surveys=$wpdb->get_results($query_customer_satisfaction_survey);
                if(is_array($available_surveys)&&!empty($available_surveys))
                {
                    $survey_listing='';
                    $survey_listing.=__('Your ticket has been marked as closed. Please answer the following survey(s):','sola_st').'<br/>';
                    $survey_listing.='<ul>';
                    foreach($available_surveys as $survey)
                    {
                        $survey_id=$survey->id;
                        $survey_name=$survey->survey_name;
                        $ticket_id=$post_id;
                        $uid=$userid;
                        $permalink=get_permalink((integer)get_option('sola_st_survey_page'));
                        global $wp_rewrite;
                        if ($wp_rewrite->permalink_structure == '')
                            $survey_listing.='<li><a href="'.$permalink.'&survey_id='.$survey_id.'&uid='.$uid.'&ticket_id='.$ticket_id.'">'.$survey_name.'</a></li>';
                        else
                            $survey_listing.='<li><a href="'.$permalink.'?survey_id='.$survey_id.'&uid='.$uid.'&ticket_id='.$ticket_id.'">'.$survey_name.'</a></li>';


                    }

                    $survey_listing.='</ul>';
                }


                if(isset($survey_listing))
                {

                    if(function_exists('send_automated_emails'))
                    {
                        send_automated_emails($user_email, __('Customer satisfaction survey','sola_st'), $survey_listing);
                    }
                }
            }
        }
    }
    else
    {
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

        if (!is_single() && !is_admin() && !is_archive()) {
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
                $post_details = get_post($post->ID);

                $author_id = $post_details->post_author;
                $author_details = get_user_by('id', $author_id);
                /* come here */

                if(function_exists('sola_st_display_linked_files_metabox'))
                {
                	$sola_st_attached_files = sola_st_display_linked_files_metabox();
                }
                else
                {
                	$sola_st_attached_files = '';
                }




                if($current_user->ID == 0){
                    if($is_public){
                        $show_ticket = true;
                    } else {
                        $show_ticket = false;
                    }
                    $messgae = "";
                } else if ($current_user->ID == $author_details->ID || current_user_can('edit_sola_st_tickets', array(null))){
                    $show_ticket = true;
                } else {
                    if($is_public){
                        $show_ticket = true;
                    } else {
                        $show_ticket = false;
                    }
                }
				
                if ($show_ticket) {
                    $content = $content;
					if(!is_search()){
                    	$content = $content .$sola_st_attached_files. sola_st_append_responses_to_ticket(get_the_ID());
					} 
                } else {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket has been marked as private.", "sola_st") . "</span>";
                    $content = $sola_content;
                }
            } else if ($ticket_status == '1') {
                /* Solved Ticket */
                $current_user = wp_get_current_user();
                $post_details = get_post($post->ID);

                $author_id = $post_details->post_author;
                $author_details = get_user_by('id', $author_id);

                if($current_user->ID == 0){
                    if($is_public){
                        $show_ticket = true;
                    } else {
                        $show_ticket = false;
                    }
                    $messgae = "";
                } else if ($current_user->ID == $author_details->ID || current_user_can('edit_sola_st_tickets', array(null))){
                    $show_ticket = true;
                } else {
                    if($is_public){
                        $show_ticket = true;
                    } else {
                        $show_ticket = false;
                    }
                }

                if ($show_ticket) {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket has been marked as solved.", "sola_st") . "</span>";
                    $content = $sola_content . $content;
					if(!is_search()){
                    	$content = $content . sola_st_append_responses_to_ticket(get_the_ID());
					}
                } else {
                    $sola_content .= "<span class='sola_st_pending_approval_span'>" . __("This support ticket has been marked as solved.", "sola_st") . "</span>";
                    $content = $sola_content;
                }
            } else if ($ticket_status == '9') {
                /* Pending Ticket */
                $current_user = wp_get_current_user();
                $post_details = get_post($post->ID);

                $author_id = $post_details->post_author;
                $author_details = get_user_by('id', $author_id);

                if($current_user->ID == 0){
                    if($is_public){
                        $show_ticket = true;
                    } else {
                        $show_ticket = false;
                    }
                    $messgae = "";
                } else if ($current_user->ID == $author_details->ID || current_user_can('edit_sola_st_tickets', array(null))){
                    $show_ticket = true;
                } else {
                    if($is_public){
                        $show_ticket = true;
                    } else {
                        $show_ticket = false;
                    }
                }





                if(isset($_SESSION['file_upload_failed']))
                {
                    echo $_SESSION['file_upload_failed'].'<br/>';
                    unset($_SESSION['file_upload_failed']);
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
        if (isset($author_data->roles[1])) { 
            $role = $author_data->roles[1]; 
        } else { 
            $role = ""; 
        }
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

        if(isset($_POST['sola_st_submit_department']))
        {
            $tax_input = array(
                'sola_st_deparments' => wp_strip_all_tags($_POST['sola_st_submit_department'])
            );
        }

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
                if(is_object($user))
                {
                    add_option('sola_st_default_assigned_to', $user->ID);
                }
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
        sola_st_notification_control('response', $parent_id, get_current_user_id(),false,false,$content);
    }
}

function sola_st_tickets_cpt_columns($columns) {

    if (defined('SOLA_ST_CSS_CUSTOMER_SATISFACTION_SURVEY_ACTIVE')&&function_exists('sola_st_pro_activate')) {
        /* we're using customer satisfaction surveys */
        $new_columns = array(
            'ticket_priority_column' => __('Priority', 'sola_st'),
            'ticket_responses_column' => __('Responses', 'sola_st'),
            'ticket_last_responded_column' => __('Last Response By', 'sola_st'),
            'ticket_status' => __('Status', 'sola_st'),
            'satisfaction_rating' => __('Rating', 'sola_st')
        );

    } else {
        $new_columns = array(
            'ticket_priority_column' => __('Priority', 'sola_st'),
            'ticket_responses_column' => __('Responses', 'sola_st'),
            'ticket_last_responded_column' => __('Last Response By', 'sola_st'),
            'ticket_status' => __('Status', 'sola_st')
        );
    }
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
        case 'satisfaction_rating':
			
			if(defined('SOLA_ST_CSS_CUSTOMER_SATISFACTION_SURVEY_ACTIVE'))
			{
				$rating_data = sola_st_model::retrieve_average_rating_by_ticket_id_model($post_id);
				$rating=$rating_data[0]->rating;
				if($rating===null)
				{
					$rating=0;
				}	
				
				
				$stars = sola_st_view::return_survey_stars($rating);
            	$view_button='<input style="margin-top:10px;" type="button" class="btn btn-default btn-xs" name="sola_st_css_get_ticket_survey_results_'.$post_id.'" id="sola_st_css_get_ticket_survey_results_'.$post_id.'" value="View results"/>';
            	echo $stars.'<br/>'.$view_button;	
			}
			else
			{
				echo '';		
			}
			
			
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
    if (function_exists('sola_st_pro_activate')) {
    	
				
		
        if (isset($sola_st_settings['sola_st_settings_require_login']) && $sola_st_settings['sola_st_settings_require_login'] == 1) {
            if (is_user_logged_in()) {
                return sola_st_submission_form();
            } else {
                $content = "
                    <a href=\"" . wp_login_url(get_permalink()) . "\">" . __("Log in", "sola_st") . "</a> " . __("or", "sola_st") . " <a href=\"" . wp_registration_url() . "\">" . __("register", "sola_st") . "</a> " . __("to submit a support ticket.", "sola_st") . "
                    <br /><br />";
                return $content;
            }
        } else {
            return sola_st_submission_form();
        }
    } else {
        if (is_user_logged_in()) {
            return sola_st_submission_form();
        } else {
            $content = "
                <a href=\"" . wp_login_url(get_permalink()) . "\">" . __("Log in", "sola_st") . "</a> " . __("or", "sola_st") . " <a href=\"" . wp_registration_url() . "\">" . __("register", "sola_st") . "</a> " . __("to submit a support ticket.", "sola_st") . "
                <br /><br />";
            return $content;
        }
    }
}

add_filter('views_edit-sola_st_tickets', 'meta_views_sola_st_tickets', 10, 1);

function meta_views_sola_st_tickets($views) {
    
	if(defined('SOLA_ST_CSS_CUSTOMER_SATISFACTION_SURVEY_ACTIVE'))
	{
		echo sola_st_view::enter_survey_preview_modal();
    	echo sola_st_view::ajax_loader_display();	
	}
	
    	
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
                    "SELECT count(meta_id) as total FROM ".$wpdb->prefix."postmeta pm INNER JOIN ".$wpdb->prefix."posts as p
                    ON pm.post_id =p.ID  
                    WHERE pm.meta_key = %s AND pm.meta_value = %d
                    AND p.post_status='publish' AND p.post_type='sola_st_tickets'
                    "  , 'ticket_status', 0
            )
    );
    $total = $row->total;
    return $total;
}

function sola_st_return_pending_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(meta_id) as total FROM ".$wpdb->prefix."postmeta pm INNER JOIN ".$wpdb->prefix."posts as p
                    ON pm.post_id =p.ID  
                    WHERE pm.meta_key = %s AND pm.meta_value = %d
                    AND p.post_status='publish' AND p.post_type='sola_st_tickets'
                    "  , 'ticket_status', 9
            )
    );
    $total = $row->total;
    return $total;
}

function sola_st_return_closed_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(meta_id) as total FROM ".$wpdb->prefix."postmeta pm INNER JOIN ".$wpdb->prefix."posts as p
                    ON pm.post_id =p.ID  
                    WHERE pm.meta_key = %s AND pm.meta_value = %d
                    AND p.post_status='publish' AND p.post_type='sola_st_tickets'
                    "  , 'ticket_status', 2
            )
    );
    $total = $row->total;
    return $total;
}

function sola_st_return_solved_ticket_qty() {
    global $wpdb;
    $row = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT count(meta_id) as total FROM ".$wpdb->prefix."postmeta pm INNER JOIN ".$wpdb->prefix."posts as p
                    ON pm.post_id =p.ID  
                    WHERE pm.meta_key = %s AND pm.meta_value = %d
                    AND p.post_status='publish' AND p.post_type='sola_st_tickets'
                    "  , 'ticket_status', 1
            )
    );
    $total = $row->total;
    return $total;
}

if (!function_exists("sola_st_pro_activate")) {
    add_filter('pre_get_posts', 'sola_st_loop_control');
}

function sola_st_loop_control($query) {

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
    $out='';
	
	
    $out.="
        <div class='sola_st_author_box'>
            <img src='" . sola_st_get_gravatar($user_data->user_email, '50') . "' class='alignleft sola_st_author_image' />
            " . __("Submitted by ", "sola_st") . " <span class='sola_st_author_box_name'>" . $user_data->display_name . "</span><br />
            " . __("on ", "sola_st") . " <span class='sola_st_author_box_date'>" . $date . " " . $time . "</span>

        </div>";
	
	
	/*
	$out.="<div class='sola_st_author_box'>
            <span class='sola_st_author_box_text'>
            	<span class='alignleft sola_st_author_image'>" . get_avatar($user_data->user_email, '50') . "</span>
            	" . __("Submitted by ", "sola_st") . " <span class='sola_st_author_box_name'>" . $user_data->display_name . "</span><br />
            	" . __("on ", "sola_st") . " <span class='sola_st_author_box_date'>" . $date . " at " . $time . "</span>
        	</span>
        	</div>";
	
	
	 */
	 
	return $out;
		
}

function sola_st_submission_form() {
    $sola_st_settings = get_option('sola_st_settings');	
	
    if (isset($sola_st_settings['sola_st_settings_allow_priority']) && $sola_st_settings['sola_st_settings_allow_priority'] == "1") {
				
	
			if(isset($sola_st_settings['sola_st_settings_default_priority']))
			{
				if($sola_st_settings['sola_st_settings_default_priority']=='0')
				{
					$sola_priority_text = "
		            <tr class=\"sola_st_st_tr sola_st_st_subject\">
		               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_label\">
		                  <strong>" . __("Priority", "sola_st") . "</strong>
		               </td>
		               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_input\">
		                  <select name=\"sola_st_submit_priority\" id=\"sola_st_submit_priority\">
		                  <option value='1' selected='selected'>" . __("Low", "sola_st") . "</option>
		                  <option value='2'>" . __("High", "sola_st") . "</option>
		                  <option value='3'>" . __("Urgent", "sola_st") . "</option>
		                  <option value='4'>" . __("Critical", "sola_st") . "</option>
		
		                    </select>
		               </td>
		            </tr>";		
				}
				else if($sola_st_settings['sola_st_settings_default_priority']=='1')
				{
					$sola_priority_text = "
		            <tr class=\"sola_st_st_tr sola_st_st_subject\">
		               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_label\">
		                  <strong>" . __("Priority", "sola_st") . "</strong>
		               </td>
		               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_input\">
		                  <select name=\"sola_st_submit_priority\" id=\"sola_st_submit_priority\">
		                  <option value='1'>" . __("Low", "sola_st") . "</option>
		                  <option value='2' selected='selected'>" . __("High", "sola_st") . "</option>
		                  <option value='3'>" . __("Urgent", "sola_st") . "</option>
		                  <option value='4'>" . __("Critical", "sola_st") . "</option>
		
		                    </select>
		               </td>
		            </tr>";		
				}
				else if($sola_st_settings['sola_st_settings_default_priority']=='2')
				{
					$sola_priority_text = "
		            <tr class=\"sola_st_st_tr sola_st_st_subject\">
		               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_label\">
		                  <strong>" . __("Priority", "sola_st") . "</strong>
		               </td>
		               <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_priority_input\">
		                  <select name=\"sola_st_submit_priority\" id=\"sola_st_submit_priority\">
		                  <option value='1'>" . __("Low", "sola_st") . "</option>
		                  <option value='2'>" . __("High", "sola_st") . "</option>
		                  <option value='3' selected='selected'>" . __("Urgent", "sola_st") . "</option>
		                  <option value='4'>" . __("Critical", "sola_st") . "</option>
		
		                    </select>
		               </td>
		            </tr>";		
				}
				else if($sola_st_settings['sola_st_settings_default_priority']=='3')
				{
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
		                  	<option value='4' selected='selected'>" . __("Critical", "sola_st") . "</option>
		                  </select>
		               </td>
		            </tr>";		
				}
				else
				{
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
				}
			}
			else
			{
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
			}
			
			
			
			
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
        
        if(isset($sola_st_settings['sola_st_settings_enable_captcha'])&&$sola_st_settings['sola_st_settings_enable_captcha']==1)
        {
            if(function_exists('sola_st_pro_captcha'))
            {
                $captcha = sola_st_pro_captcha();    
            }
            else
            {
                $captcha='';
            }
        }
        else
        {
            $captcha='';
        }
        
        
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
                $captcha";

                if(isset($sola_st_settings['enable_file_uploads'])&&$sola_st_settings['enable_file_uploads']===1&&function_exists('sola_st_pro_activate'))
                {
                    $content.='
                    <tr>
                        <td colspan="2">
                            '.__('Upload a file:', 'sola_st').'
                        </td>
                    </tr>
                    <tr>
                        <td>
                            '.__('Allowed formats: JPEG, PNG, GIF, TIFF, PDF, ZIP:', 'sola_st').'
                        </td>
                        <td>
                            <input type="file" name="fl_upload_ticket_file_public_section" id="fl_upload_ticket_file_public_section"/>
                        </td>
                    </tr>';
                }



                $content.="
                <tr class=\"sola_st_st_tr sola_st_st_submit\">
                   <td valign=\"top\"></td>
                   <td valign=\"top\" align=\"right\" class=\"sola_st_st_td sola_st_st_td_submit_button\">
                        <input type=\"submit\" name=\"sola_st_submit_ticket\" title=\"" . __("Submit", "sola_st") . "\" class=\"sola_st_button_send_reponse\" value=\"" . __("Submit", "sola_st") . "\" />
                   </td>
                </tr>

                </table>
            </form>
        </div>";
    return $content;
}


function sola_st_select_mailing_system_to_use()
{
		$out='';
	
		$data=get_option('sola_st_settings');
		$rb_sola_mailing_system_selection='';
		$sola_st_smtp_host_setting_php_mailer='';
		$sola_st_smtp_username_setting_php_mailer='';
		$sola_st_smtp_password_setting_php_mailer='';
		$sola_st_smtp_port_setting_php_mailer='';
		$sola_st_smtp_encryption_setting_php_mailer='';
		
		
		
		if(isset($data['rb_sola_mailing_system_selection']))
		{
			$rb_sola_mailing_system_selection=$data['rb_sola_mailing_system_selection'];	
		}
		
		if(isset($data['sola_st_smtp_host_setting_php_mailer']))
		{
			$sola_st_smtp_host_setting_php_mailer=$data['sola_st_smtp_host_setting_php_mailer'];
			
		}
		
		if(isset($data['sola_st_smtp_username_setting_php_mailer']))
		{
			
			$sola_st_smtp_username_setting_php_mailer=$data['sola_st_smtp_username_setting_php_mailer'];
		}
		
		if(isset($data['sola_st_smtp_password_setting_php_mailer']))
		{
			$sola_st_smtp_password_setting_php_mailer=$data['sola_st_smtp_password_setting_php_mailer'];
		}
		
		if(isset($data['sola_st_smtp_port_setting_php_mailer']))
		{
			$sola_st_smtp_port_setting_php_mailer=$data['sola_st_smtp_port_setting_php_mailer'];
		}
		
		if(isset($data['sola_st_smtp_encryption_setting_php_mailer']))
		{
			$sola_st_smtp_encryption_setting_php_mailer=$data['sola_st_smtp_encryption_setting_php_mailer'];	
		}
		
		
		
		
		
		
		
		
	
		$out.='<div class="sola_st_email_settings_seperator">';
			 $out.='<p> '.__("Please select the mailing type to use when sending any notification e-mails","sola_st").'</p>';
            
            
            
            
            
			if($rb_sola_mailing_system_selection==='wp_mail')
			{
				$out.='<input type="radio" name="rb_sola_mailing_system_selection" id="rb_sola_mailing_system_selection_wp_mail" value="wp_mail" checked="checked"/> <label> '.__('The Wordpress mailer - wp mail','sola_st').'</label> ';
				$out.='<br/>';
				$out.='<input type="radio" name="rb_sola_mailing_system_selection" id="rb_sola_mailing_system_selection_smtp" value="smtp"/> <label>  '.__('Custom SMTP settings','sola_st').' </label> ';
			
			}
			elseif ($rb_sola_mailing_system_selection==='smtp') 
			{
				$out.='<input type="radio" name="rb_sola_mailing_system_selection" id="rb_sola_mailing_system_selection_wp_mail" value="wp_mail"/> <label> '.__('The Wordpress mailer - wp mail','sola_st').'</label> ';
				$out.='<br/>';
				$out.='<input type="radio" name="rb_sola_mailing_system_selection" id="rb_sola_mailing_system_selection_smtp" value="smtp" checked="checked"/> <label>  '.__('Custom SMTP settings','sola_st').' </label> ';
			
			}
			else 
			{
				$out.='<input type="radio" name="rb_sola_mailing_system_selection" id="rb_sola_mailing_system_selection_wp_mail" value="wp_mail" checked="checked"/> <label> '.__('The Wordpress mailer - wp mail','sola_st').'</label> ';
				$out.='<br/>';
				$out.='<input type="radio" name="rb_sola_mailing_system_selection" id="rb_sola_mailing_system_selection_smtp" value="smtp"/> <label>  '.__('Custom SMTP settings','sola_st').' </label> ';
			
			}
			
			
			
			$out.='<div style="display:none;" class="sola_st_email_settings_seperator" id="sola_st_hidden_php_mailer_smtp_settings">
				<label for="sola_st_smtp_host_setting_php_mailer"> '.__('Enter your host URL (for gmail use imap.gmail.com)','sola_st').': </label> <br/>
				<input type="text" name="sola_st_smtp_host_setting_php_mailer" id="sola_st_smtp_host_setting_php_mailer" class="sola-input sola_st_smtp_settings" value="'.$sola_st_smtp_host_setting_php_mailer.'"/> <br/><br/>
				<label for="sola_st_smtp_username_setting_php_mailer"> '.__('The username for your Email Account','sola_st').': </label> <br/> <input type="text" name="sola_st_smtp_username_setting_php_mailer" id="sola_st_smtp_username_setting_php_mailer" class="sola-input sola_st_smtp_settings" value="'.$sola_st_smtp_username_setting_php_mailer.'" /> <br/><br/>
				<label for="sola_st_smtp_password_setting_php_mailer"> '.__('The password of your Email Account','sola_st').': </label> <br/> <input type="text" name="sola_st_smtp_password_setting_php_mailer" id="sola_st_smtp_password_setting_php_mailer" class="sola-input sola_st_smtp_settings" value="'.$sola_st_smtp_password_setting_php_mailer.'" /> <br/><br/>
				<label for="sola_st_smtp_port_setting_php_mailer"> '.__('And finally the port number','sola_st').': </label> <br/> <input type="text" name="sola_st_smtp_port_setting_php_mailer" id="sola_st_smtp_port_setting_php_mailer" class="sola-input sola_st_smtp_settings" value="'.$sola_st_smtp_port_setting_php_mailer.'" /> <br/><br/>
				<label>'.__('Encryption','sola_st').': </label>
				<br/>';
			
			
				
				if($sola_st_smtp_encryption_setting_php_mailer==='No Encryption')
				{
					$out.='<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_no_encryption" value="No Encryption" checked="checked"/> '.__('No Encryption','sola_st').' <br/>
					<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_ssl" value="SSL"/> '.__('SSL - (use SSL for gmail)','sola_st').' <br/>
					<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_tls" value="TLS"/> '.__('TLS - This is not the same as STARTTLS. For most servers SSL is the recommended option.','sola_st').' <br/>';
				
				}
				elseif($sola_st_smtp_encryption_setting_php_mailer==='SSL')
				{
					$out.='<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_no_encryption" value="No Encryption"/> '.__('No Encryption','sola_st').' <br/>
					<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_ssl" value="SSL" checked="checked"/> '.__('SSL - (use SSL for gmail)','sola_st').' <br/>
					<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_tls" value="TLS"/> '.__('TLS - This is not the same as STARTTLS. For most servers SSL is the recommended option.','sola_st').' <br/>';
				
				}
	            elseif($sola_st_smtp_encryption_setting_php_mailer==='TLS')
				{
					$out.='<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_no_encryption" value="No Encryption"/> '.__('No Encryption','sola_st').' <br/>
					<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_ssl" value="SSL"/> '.__('SSL - (use SSL for gmail)','sola_st').' <br/>
					<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_tls" value="TLS" checked="checked"/> '.__('TLS - This is not the same as STARTTLS. For most servers SSL is the recommended option.','sola_st').' <br/>';
				
				}
				else 
				{
		           $out.='<input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_no_encryption" value="No Encryption"/> '.__('No Encryption','sola_st').' <br/>
				  <input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_ssl" value="SSL"/> '.__('SSL - (use SSL for gmail)','sola_st').' <br/>
				  <input type="radio" name="sola_st_smtp_encryption_setting_php_mailer" id="sola_st_smtp_encryption_setting_php_mailer_tls" value="TLS"/> '.__('TLS - This is not the same as STARTTLS. For most servers SSL is the recommended option.','sola_st').' <br/>';
			
				}
	
				
				$out.='<div style="font-weight:bold;" class="sola_st_email_settings_seperator" >'.__('In the event that SMTP sending fails, the wordpress mailer will be used as the fallback.','sola_st').'</div>';
			
			
			
			
			
			$out.='</div>';
			
			
		$out.='</div>';
		
		
		echo $out;
		
		
		
}




function send_automated_emails($email,$subject,$message,$headers=null)
{
    include_once "includes/PHPMailer-master/PHPMailerAutoload.php";

    $php_mailer_object = new PHPMailer;
	$sola_st_smtp_host_setting_php_mailer='';
	$sola_st_smtp_username_setting_php_mailer='';
	$sola_st_smtp_password_setting_php_mailer='';
	$sola_st_smtp_port_setting_php_mailer='';
	$sola_st_smtp_encryption_setting_php_mailer='';
	$replyto_address='';
	$from_address='';
	$from_name='';
	$replyto_name='';
	$wp_mail_headers='';
	
	$result=false;
	
	$data=get_option('sola_st_settings');
	
	if($headers===null)
	{
		/*none passed to function - check if any were set for pro*/
		if(function_exists("sola_st_pro_activate"))
		{
			if((isset($data['sola_st_automated_emails_from'])&& filter_var($data['sola_st_automated_emails_from'], FILTER_VALIDATE_EMAIL) !== false)&&(isset($data['sola_st_automated_emails_from_name'])&&ctype_alnum($data['sola_st_automated_emails_from_name'])===true))
			{
				/*valid from and reply to headers*/
				$replyto_address=$data['sola_st_automated_emails_from'];
            	$from_address=$data['sola_st_automated_emails_from'];
            	$from_name=$data['sola_st_automated_emails_from_name'];
            	$replyto_name=$data['sola_st_automated_emails_from_name'];
				
				$wp_mail_headers['from_name']=$from_name;
				$wp_mail_headers['from_email']=$from_address;
				$wp_mail_headers['replyto_name']=$replyto_name;
				$wp_mail_headers['replyto_address']=$replyto_address;
				
			}
		}
	}
	elseif(is_array($headers))
	{
		 $replyto_address=$headers['reply_to']['address'];
		 $from_address=$headers['from']['address'];
		 $from_name=$headers['from']['name'];
		 $replyto_name=$headers['reply_to']['name'];
		 
		 $wp_mail_headers['from_name']=$from_name;
		 $wp_mail_headers['from_email']=$from_address;
		 $wp_mail_headers['replyto_name']=$replyto_name;
		 $wp_mail_headers['replyto_address']=$replyto_address;
	}
	
	
	if(isset($data['sola_st_smtp_host_setting_php_mailer']))
	{
		$sola_st_smtp_host_setting_php_mailer=$data['sola_st_smtp_host_setting_php_mailer'];
	}
	
	if(isset($data['sola_st_smtp_username_setting_php_mailer']))
	{
		$sola_st_smtp_username_setting_php_mailer=$data['sola_st_smtp_username_setting_php_mailer'];
	}
	
	if(isset($data['sola_st_smtp_password_setting_php_mailer']))
	{
		$sola_st_smtp_password_setting_php_mailer=$data['sola_st_smtp_password_setting_php_mailer'];
	}
	
	if(isset($data['sola_st_smtp_port_setting_php_mailer']))
	{
		$sola_st_smtp_port_setting_php_mailer=$data['sola_st_smtp_port_setting_php_mailer'];
	}
	
	if(isset($data['sola_st_smtp_encryption_setting_php_mailer']))
	{
		$sola_st_smtp_encryption_setting_php_mailer=$data['sola_st_smtp_encryption_setting_php_mailer'];
	}
	
	
	
	
	
	if(isset($data['rb_sola_mailing_system_selection'])&&$data['rb_sola_mailing_system_selection']==='smtp')
	{
			$php_mailer_object->isSMTP();
			$php_mailer_object->Host = $sola_st_smtp_host_setting_php_mailer;		
			$php_mailer_object->Port = $sola_st_smtp_port_setting_php_mailer;
			$php_mailer_object->SMTPAuth = true;
			$php_mailer_object->SMTPSecure = $sola_st_smtp_encryption_setting_php_mailer;
			$php_mailer_object->Username = $sola_st_smtp_username_setting_php_mailer;
			$php_mailer_object->Password = $sola_st_smtp_password_setting_php_mailer;
			$php_mailer_object->CharSet = "UTF-8"; 
			if($from_address!==''&&$from_name!=='')
			{
				$php_mailer_object->setFrom($from_address,$from_name);	
			}
			
			$php_mailer_object->addAddress($email,'User');
			
			if($replyto_address!==''&&$replyto_name!=='')
			{
				$php_mailer_object->addReplyTo($replyto_address,$replyto_name);	
			}
			
			
			$php_mailer_object->Subject = $subject;
			
			
			$php_mailer_object->msgHTML($message."<br/><br/> <b>Sent using SMTP settings</b>");
		
			if(!$php_mailer_object->send())
			{
				
				$result=use_wp_mail_as_default($email, $subject, $message, $wp_mail_headers);
			} 
			else 
			{
	   			$result=true;
			}
			
	}
	else
	{
		$result=use_wp_mail_as_default($email, $subject, $message, $wp_mail_headers);
	}
	
	
	return $result;
	
	
}

function use_wp_mail_as_default($email,$subject,$message,$wp_mail_headers)
{
   
	
    $result=false;
	
	if(is_array($wp_mail_headers))
	{
		$headers_mail = 'From: '.$wp_mail_headers['from_name'].' < '.$wp_mail_headers['from_email'].' >' ."\r\n";
		$headers_mail.= 'Reply-To: '.$wp_mail_headers['replyto_name'].' < '.$wp_mail_headers['replyto_address'].' >' ."\r\n";
        $headers_mail.= 'MIME-Version: 1.0' . "\r\n";
        $headers_mail.= 'Content-type: text/html; charset=utf-8' . "\r\n";
		
		$result=mail($email,$subject,$message."<br/><br/> <b>Sent using wordpress email</b> ",$headers_mail);
	}
	else
	{
        $headers_mail= 'MIME-Version: 1.0' . "\r\n";
        $headers_mail.= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$result=mail($email,$subject,$message."<br/><br/> <b>Sent using wordpress email </b> ",$headers_mail);
	}
	
	return $result;
	
	
}




function sola_st_warn_update_pro()
{
    if(function_exists('sola_st_pro_activate')&&(!isset($_REQUEST['mvc_action'])&&!isset($_REQUEST['mvc_function']))&&(isset($_GET['post_type'])&&$_GET['post_type']==='sola_st_tickets'))
    {
    	global $sola_st_pro_version;
		$sola_st_pro_version=floatval($sola_st_pro_version);
        if(is_admin())
        {
        	if($sola_st_pro_version<2.04)
			{
				 echo "<div class='error'><p>".__('Warning: Please update to the latest Sola Support Tickets Premium version. We have made many changes in both Sola Support Tickets Basic and Sola Support Tickets Premium. Both these plugins need to be updated to ensure that no compatibility issues arise and to ensure that our latest add-on Sola Support Tickets Customer Satisfaction Surveys is also supported. Please do the update through your WordPress dashboard, or by logging into your Sola Plugins account ', 'sola_st')."<a href='http://solaplugins.com/my-account' target='_BLANK'>".__('here', 'sola_st')."</a></p></div>";	
			}
		}
    }
}




















