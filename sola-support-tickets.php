<?php
/*
Plugin Name: Sola Support Tickets
Plugin URI: http://www.solaplugins.com/plugins/sola-support-tickets/
Description: Create a support centre within your WordPress admin. No need for third party systems!
Version: 1.1
Author: SolaPlugins
Author URI: http://www.solaplugins.com
*/

global $sola_st_version;
global $sola_st_p_version;
global $sola_st_tblprfx;

define("SOLA_ST_PLUGIN_NAME","Sola Support Tickets");

global $sola_st_version;
global $sola_st_version_string;
$sola_st_version = "1.1";
$sola_st_version_string = "beta";


include "modules/metaboxes.php";


global $wpdb;
$sola_st_tblprfx = $wpdb->prefix."sola_st_";


$plugin_url = ABSPATH.'wp-content/plugins';

define("SOLA_ST_SITE_URL", get_bloginfo('url'));
define("SOLA_ST_PLUGIN_URL", $plugin_url.'/sola-support-tickets');
define("SOLA_ST_PLUGIN_DIR", plugins_url().'/sola-support-tickets');


add_action('init','sola_st_init');
add_action('admin_menu', 'sola_st_admin_menu');

if (function_exists("sola_st_pro_wp_head")) { 
    add_action('admin_head','sola_st_pro_wp_head');
} else {
    add_action('admin_head','sola_st_wp_head');
}

if (function_exists("sola_st_pro_user_head")) { 
    add_action('wp_head','sola_st_pro_user_head');
} else {
    add_action('wp_head','sola_st_user_head');
}

add_shortcode("sola_st_submit_ticket", "sola_st_shortcode_submit_ticket_page");


register_activation_hook( __FILE__, 'sola_st_activate' );
register_deactivation_hook( __FILE__, 'sola_st_deactivate' );
function sola_st_init() {

   
   if (isset($_POST['action']) && $_POST['action'] == 'sola_submit_find_us') {
        sola_st_feedback_head();
        wp_redirect("./edit.php?post_type=sola_st_tickets&page=sola-st-settings",302);
        exit();
    }
   if (isset($_POST['action']) && $_POST['action'] == 'sola_skip_find_us') {
        wp_redirect("./edit.php?post_type=sola_st_tickets&page=sola-st-settings",302);
        exit();
    }
    
    if (isset($_GET['post_type']) && $_GET['post_type'] == "sola_st_tickets") { 
        if(get_option('sola_st_first_time') == false){
            update_option('sola_st_first_time', true);
            wp_redirect('edit.php?post_type=sola_st_tickets&page=sola-st-settings&action=welcome_page', 302);
            exit();
        }
    }
    

    
    
    $plugin_dir = basename(dirname(__FILE__))."/languages/";
    load_plugin_textdomain( 'sola_st', false, $plugin_dir );
    
    /* allow admins to create and edit tickets */
    $admins = get_role( 'administrator' );
    $admins->add_cap( 'edit_sola_st_ticket' ); 
    $admins->add_cap( 'edit_sola_st_tickets' ); 
    $admins->add_cap( 'edit_other_sola_st_tickets' ); 
    $admins->add_cap( 'publish_sola_st_tickets' ); 
    $admins->add_cap( 'read_sola_st_ticket' ); 
    $admins->add_cap( 'read_private_sola_st_tickets' ); 
    $admins->add_cap( 'delete_sola_st_tickets' ); 
    
    
    if (!get_option("sola_st_submit_ticket_page")) {
        $content = __("[sola_st_submit_ticket]","sola");
        $page_id = sola_st_create_page('submit-ticket',__("Submit a ticket","sola_st"),$content);
        add_option("sola_st_submit_ticket_page","$page_id");
    }
    
    
 

}
add_action( 'init', 'sola_st_create_ticket_post_type' );
add_action( 'init', 'sola_st_create_response_post_type' );

add_action('wp_ajax_sola_st_save_response', 'sola_st_action_callback');


function sola_st_create_ticket_post_type() {
    
    $labels = array(
        'name'               => __( 'Tickets', 'sola_st' ),
        'singular_name'      => __( 'Ticket', 'sola_st' ),
        'add_new'            => __( 'New Ticket', 'sola_st' ),
        'add_new_item'       => __( 'Add New Ticket', 'sola_st' ),
        'edit_item'          => __( 'Edit Ticket', 'sola_st' ),
        'new_item'           => __( 'New Ticket', 'sola_st' ),
        'all_items'          => __( 'All Tickets', 'sola_st' ),
        'view_item'          => __( 'View Ticket', 'sola_st' ),
        'search_items'       => __( 'Search Tickets', 'sola_st' ),
        'not_found'          => __( 'No tickets found', 'sola_st' ),
        'not_found_in_trash' => __( 'No tickets found in the Trash', 'sola_st' ), 
        'menu_name'          => __('Support Tickets','sola_st')
      );
      $args = array(
        'labels'        => $labels,
        'description'   => __('Support tickets','sola_st'),
        'public'        => true,
        'menu_position' => 50,
        'hierarchical'  => false,
        'rewrite'            => array( 'slug' => 'support-tickets' ),
        'publicly_queryable' => true,          
        'supports'      => array( 'title', 'editor', 'custom-fields', 'revisions', 'page-attributes', 'author' ),
        'has_archive'   => true,
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
      
      if (post_type_exists('sola_st_tickets')) { } else {
          register_post_type( 'sola_st_tickets', $args ); 
          flush_rewrite_rules();
      }
    
    
       
}
function sola_st_create_response_post_type() {
    
    $labels = array(
        'name'               => __( 'Responses', 'sola_st' ),
        'singular_name'      => __( 'Response', 'sola_st' ),
        'add_new'            => __( 'New Response', 'sola_st' ),
        'add_new_item'       => __( 'Add New Response', 'sola_st' ),
        'edit_item'          => __( 'Edit Response', 'sola_st' ),
        'new_item'           => __( 'New Response', 'sola_st' ),
        'all_items'          => __( 'All Responses', 'sola_st' ),
        'view_item'          => __( 'View Response', 'sola_st' ),
        'search_items'       => __( 'Search Responses', 'sola_st' ),
        'not_found'          => __( 'No responses found', 'sola_st' ),
        'not_found_in_trash' => __( 'No responses found in the Trash', 'sola_st' ), 
        'menu_name'          => __('Ticket Responses','sola_st')
      );
      $args = array(
        'labels'        => $labels,
        'description'   => __('Responses to support tickets','sola_st'),
        'public'        => true,
        'menu_position' => 51,
        'hierarchical'  => true,
        'rewrite'            => array( 'slug' => 'ticket-response' ),
        'publicly_queryable' => true,          
        'supports'      => array( 'title', 'editor', 'custom-fields', 'revisions', 'page-attributes', 'author' ),
        'has_archive'   => true,
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
      
      if (post_type_exists('sola_st_responses')) { } else {
           register_post_type( 'sola_st_responses', $args );
           flush_rewrite_rules();
      }

     
    
    
       
}
    
function sola_st_activate() {
  //sola_st_handle_db();
  if (!get_option("sola_st_email_to_ticket")) { add_option("sola_st_email_to_ticket", "0"); }
  if (!get_option("sola_st_host")) { add_option("sola_st_host", ""); }
  if (!get_option("sola_st_port")) { add_option("sola_st_port", ""); }
  if (!get_option("sola_st_username")) { add_option("sola_st_username", ""); }
  if (!get_option("sola_st_password")) { add_option("sola_st_password", ""); }
  if (!get_option("sola_st_encryption")) { add_option("sola_st_encryption", ""); }

    
}

function sola_st_deactivate() {
    
}

function sola_st_admin_menu() {
    
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Settings','sola_st'), __('Settings','sola_st'), 'manage_options' , 'sola-st-settings', 'sola_st_settings_page');
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Feedback','sola'), __('Feedback','sola_st'), 'manage_options' , 'sola-st-menu-feedback-page', 'sola_st_admin_feedback_layout');
    add_submenu_page('edit.php?post_type=sola_st_tickets', __('Log','sola'), __('Error Log','sola_st'), 'manage_options' , 'sola-st-menu-error-log', 'sola_st_admin_error_log_layout');
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
   
    if(isset($_GET['post_type']) && isset($_GET['page']) && $_GET['page'] == "sola-st-settings"){
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script( 'jquery-ui-tabs');
        wp_enqueue_script( 'jquery-ui-datepicker');
        wp_register_style( 'sola_st_jquery_ui_theme_css', plugins_url('/css/jquery-ui-theme/jquery-ui.css', __FILE__) );
        wp_enqueue_style( 'sola_st_jquery_ui_theme_css' );


        wp_register_script('sola-st-tabs', plugins_url('js/sola_st_tabs.js',__FILE__), array('jquery-ui-core'), '', true);
        wp_enqueue_script('sola-st-tabs');
    }
    
    if (isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit') {
        wp_register_script('sola-st', plugins_url('js/sola_st.js',__FILE__), array('jquery'), '', true);
        wp_enqueue_script('sola-st');
        wp_register_style( 'sola_st_user_styles', plugins_url('/css/sola-support-tickets.css', __FILE__) );
	wp_enqueue_style( 'sola_st_user_styles', get_stylesheet_uri() );    }
    
}
function sola_st_user_styles() {
        wp_register_style( 'sola_st_user_styles', plugins_url('/css/sola-support-tickets.css', __FILE__) );
	wp_enqueue_style( 'sola_st_user_styles', get_stylesheet_uri() );
}

add_action( 'wp_enqueue_scripts', 'sola_st_user_styles' );


function sola_st_wp_head() {
    @session_start();
    // post data handling
    
   global $sola_st_success;
   global $sola_st_error;
   
   

   
   /* move to activation hook */
   if (!get_option("sola_st_default_assigned_to")) {
       $super_admins = get_super_admins();
       $user = get_user_by( 'slug', $super_admins[0] );
       add_option('sola_st_default_assigned_to',$user->ID);
   }
   
   if (isset($_POST['sola_st_save_settings'])) {
        $sola_st_settings = array();
        $sola_st_settings['sola_st_settings_notify_new_tickets'] = esc_attr($_POST['sola_st_settings_notify_new_tickets']);
        $sola_st_settings['sola_st_settings_notify_new_responses'] = esc_attr($_POST['sola_st_settings_notify_new_responses']);
        update_option('sola_st_settings', $sola_st_settings);
        echo "<div class='updated'>";
        _e("Your settings have been saved.","sola_st");
        echo "</div>";
   }
   
   
    if (isset($_POST['sola_st_send_feedback'])) {
        if(wp_mail("support@solaplugins.com", "Support Tickets Plugin feedback", "Name: ".$_POST['sola_st_feedback_name']."\n\r"."Email: ".$_POST['sola_st_feedback_email']."\n\r"."Website: ".$_POST['sola_st_feedback_website']."\n\r"."Feedback:".$_POST['sola_st_feedback_feedback'] )){
            echo "<div id=\"message\" class=\"updated\"><p>".__("Thank you for your feedback. We will be in touch soon","sola_st")."</p></div>";
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
                echo "<div id=\"message\" class=\"updated\"><p>".__("Thank you for your feedback. We will be in touch soon","sola_st")."</p></div>";
            } 
            else {
                echo "<div id=\"message\" class=\"error\">";
                echo "<p>".__("There was a problem sending your feedback. Please log your feedback on ","sola_st")."<a href='http://support.solaplugins.com' target='_BLANK'>http://support.solaplugins.com</a></p>";
                echo "</div>";
            }
        }
        
    }

}

function sola_st_return_error_log() {
    $fh = @fopen(ABSPATH.'wp-content/uploads/sola'."/sola_st_log.txt","r");
    $ret = "";
    if ($fh) {
        for ($i=0;$i<10;$i++) {
            $visits = fread($fh,4096);
            $ret .= $visits;
        }
    } else {
            $ret .= __("No errors to report on","sola_st");
    }
    return $ret;
    
}
function sola_st_return_error($data) {
    echo "<div id=\"message\" class=\"error\"><p><strong>".$data->get_error_message()."</strong><blockquote>".$data->get_error_data()."</blockquote></p></div>";
    sola_st_write_to_error_log($data);
}
function sola_st_write_to_error_log($data) {
    $upload_dir = wp_upload_dir();
    if (sola_st_error_directory()) {
        if (is_multisite()) {
            $content = "\r\n".date("Y-m-d H:i:s",current_time( 'timestamp' )).": ".$data->get_error_message() . " -> ". $data->get_error_data();
            $fp = fopen($upload_dir['basedir'].'/sola'."/sola_st_log.txt","a+");
            fwrite($fp,$content);
        } else {
            $content = "\r\n".date("Y-m-d H:i:s",current_time( 'timestamp' )).": ".$data->get_error_message() . " -> ". $data->get_error_data();
            $fp = fopen(ABSPATH.'wp-content/uploads/sola'."/sola_st_log.txt","a+");
            fwrite($fp,$content);
        }
    }
    
    error_log(date("Y-m-d H:i:s",current_time( 'timestamp' )). ": ".SOLA_ST_PLUGIN_NAME . ": " . $data->get_error_message() . "->" . $data->get_error_data());
    
}
function sola_st_error_directory() {
    $upload_dir = wp_upload_dir();
    
    if (is_multisite()) {
        if (!file_exists($upload_dir['basedir'].'/sola')) {
            wp_mkdir_p($upload_dir['basedir'].'/sola');
$content = "Error log created";
            $fp = fopen($upload_dir['basedir'].'/sola'."/sola_st_log.txt","w+");
            fwrite($fp,$content);
        }
    } else {
        if (!file_exists(ABSPATH.'wp-content/uploads/sola')) {
            wp_mkdir_p(ABSPATH.'wp-content/uploads/sola');
$content = "Error log created";
            $fp = fopen(ABSPATH.'wp-content/uploads/sola'."/sola_st_log.txt","w+");
            fwrite($fp,$content);
        }
        
    }
    return true;
    
}


function sola_st_get_response_data($post_id) {
    $data = get_post($post_id);
    if (isset($data) && $data) { return $data; }
    else { return false; }
}




function sola_st_action_callback() {

    global $wpdb;
    $check = check_ajax_referer( 'sola_st', 'security' );
    
    
    
    if ($check == 1) {
        
        if ($_POST['action'] == "sola_st_save_response") {
            if (!isset($_POST['parent'])) { return false; }
            
            $parent_id = $_POST['parent'];
            $content = $_POST['content'];
            $title = $_POST['title'];
            $author = $_POST['author'];
            $data = array(
                'post_content' => urldecode($content),
                'post_status' => 'publish', 
                'post_title' => urldecode($title),
                'post_type' => 'sola_st_responses', 
                'post_author' => $author,
                'comment_status' => 'closed',
                'ping_status' => 'closed'
                
            );  
            $post_id = wp_insert_post( $data );
            
            
            update_post_meta( $post_id, '_response_parent_id', $parent_id );
           
            sola_st_notification_control('response',$parent_id,get_current_user_id());
            
            
            
        }
    } 
    
    
    
    
    die(); // this is required to return a proper result
}

function sola_st_notification_control($type,$post_id,$userid) {
    $sola_st_settings = get_option("sola_st_settings");
    //var_dump($sola_st_settings);
    //echo "notification control".$type.$post_id;
    if ($type == 'response') {
        /* response */
        if ($sola_st_settings['sola_st_settings_notify_new_responses'] == "1") {
            /* get user who the post is assigned to */
            $meta_data = get_post_custom_values( 'ticket_assigned_to', $post_id );
            $user_details = get_user_by( 'id', $meta_data[0] );
            
            /* first figure out who sent the response */
            
            $post_data = get_post($post_id);
            
            $post_user = $post_data->post_author;
            
           
            
            /* get a list of everyone involved in this ticket */
            $meta_data = sola_st_get_post_meta_all($post_id);
            $notification_array = array();
            $notification_array[$post_user] = get_userdata($post_user)->user_email;
            foreach ($meta_data as $response){
                $response_data = get_post($response->post_id);
                $response_user = $response_data->post_author;
                if (isset($notification_array[$response_user])) {
                    
                } else {
                    $notification_array[$response_user] = get_userdata($response_user)->user_email;
                }
            }
            $notification_array = array_unique($notification_array);
            
            
            foreach ($notification_array as $email_item) {
                wp_mail($email_item,__("New response","sola_st")." (".$post_data->post_title.")",__("There is a new response to the support ticket titled","sola_st")." \"".$post_data->post_title."\"\n\r".__("Follow this link to view the reply","sola_st")." ".get_permalink($post_id));
            }
            

        }
    }
    else if ($type == 'ticket') {
        /* new ticket */
        
        /* send an email to the owner of the ticket */
        $user_email = get_userdata($userid)->user_email;
        $post = get_post($post_id);
        if (isset($user_email)) {
            $custom_fields = get_post_custom($post_id);
            if (!isset($custom_fields['ticket_reference'])) {
                $ticket_reference = md5($post_id.$userid);
                add_post_meta( $post_id, 'ticket_reference', $ticket_reference, true ); 
            } else {
                $ticket_reference = $custom_fields['ticket_reference'][0];
            }
            
            $headers[] = 'From: '.get_bloginfo('name').' <'.get_settings('admin_email').'>';
            $headers[] = 'Content-type: text/html';
            $headers[] = 'Reply-To: '.get_bloginfo('name').' <'.get_settings('admin_email').'>';
            wp_mail($user_email,$post->post_title." [$ticket_reference]",__("Your support ticket has been received. To access your ticket, please follow this link:","sola_st"). " ". get_permalink($post_id),$headers);
        }
        
        
        /* send an email to the auto assigned support member */
        $meta_data = get_post_custom_values( 'ticket_assigned_to', $post_id );
        $user_details = get_user_by( 'id', $meta_data[0] );
        $user_email = $user_details->user_email;
        if (isset($user_email)) {
            wp_mail($user_email,__("New support ticket:","sola_st")." ".$post->post_title."",__("A new support ticket has been received. To access this ticket, please follow this link:","sola_st"). " ". get_permalink($post_id));
        }
        
    }
    else {
        return;
    }
    
    
    
}

function sola_st_append_responses_to_ticket($post_id) {
    $ticket_id = $post_id;
    $meta_data = sola_st_get_post_meta_all($ticket_id);
    $sola_content = '<hr />';
    $post_data = get_post($post_id);
    $custom = get_post_custom($post_id);
    
    $sola_content .= '
    <h2 class="sola_st_response_title">'.__('Add a Response','sola_st').'</h2>
        <div class="sola_st_response_div">
            <form name="sola_st_add_response" method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" value="'.$post_id.'" name="sola_st_response_id" id="sola_st_response_id" />
                <table width="100%">
                <tr>
                   <td>
                      <input style="width:50%; min-width:200px; margin-bottom:5px; font-weight:bold;" type="text" value="Reply to '.get_the_title().'" name="sola_st_response_title" id="sola_st_response_title" />
                      <textarea style="width:100%; height:120px;" name="sola_st_response_text" id="sola_st_response_text"></textarea>
                   </td>
                </tr>
                <tr>
                   <td align="right">
                        <input type="submit" value="'.__("Send","sola_st").'" class="sola_st_button_send_reponse" />
                   </td>
                </tr>
                </table>
            </form>

        </div>
    ';
    
    
    foreach ($meta_data as $response) {
        $sola_content .= sola_st_draw_response_box($response->post_id);
    }
    
    return $sola_content;
}
add_filter('the_content', 'sola_st_content_control');


add_filter('next_post_link', 'sola_st_next_previous_fix');
add_filter('previous_post_link', 'sola_st_next_previous_fix');

function sola_st_next_previous_fix($url) {
    if (get_post_type() == "sola_st_tickets") {
        return "";
    }
}
add_action( 'views_edit-post',  'remove_views' );
function remove_views( $views ) {
    return "hello";
}

function sola_st_content_control($content) {
        
	if (get_post_type( $post ) == "sola_st_tickets") {
            
            
            /* is single page? /*
             * 
             */
            if (!is_single() && !is_admin()) {
                return $content;
            } else {
            
                $post_id = get_the_ID();
                $custom = get_post_custom($post_id);
                if ($custom['ticket_status'][0] == "9") {

                    /* check if there is a user logged in */
                    $current_user = wp_get_current_user();
                    if (!$current_user->ID) {
                        /* show 404 template as the user is not logged in and it is pending */
                        return __("This support ticket is marked as private or is pending approval.","sola_st");
                    }
                    else {
                    /* check if it's the owner of the ticket */
                        $show_content = false;
                        if ((get_the_author_meta('ID') == $current_user->ID)) {
                            /* this is the user that posted the ticket */
                            $show_content = true;
                        } else {
                            /* let's check if the current user has capabilitie to see tickets */
                            if (current_user_can('edit_sola_st_ticket')) {
                                $show_content = true;
                            } else {
                                $show_content = false;
                            }
                            
                        }
                    
                        if ($show_content) {
                            $sola_content .= "<span class='sola_st_pending_approval_span'>".__("This support ticket is pending approval.","sola_st")."</span>";
                            $content = $content.$sola_content;
                        }
                    }
                } else if ($custom['ticket_status'][0] == "0") {  
                    /* open ticket */
                    
                    /* can others see the ticket or not? - pro version only */
                    $current_user = wp_get_current_user();
                    if (!$current_user->ID) {
                        return __("You cannot view this support ticket","sola_st");
                    }
                    else {
                    /* check if it's the owner of the ticket */
                        $show_content = false;
                        if ((get_the_author_meta('ID') == $current_user->ID)) {
                            /* this is the user that posted the ticket */
                            $show_content = true;
                        } else {
                            /* let's check if the current user has capabilitie to see tickets */
                            if (current_user_can('edit_sola_st_ticket')) {
                                $show_content = true;
                            } else {
                                $show_content = false;
                            }
                            
                        }
                    
                        if ($show_content) {
                            $sola_content .= "";
                            $content = $content.$sola_content;
                        }
                    }
                    
                    
                    
                    $content = $content.sola_st_append_responses_to_ticket(get_the_ID());
                } else if ($custom['ticket_status'][0] == "1") {  
                    /* solved ticket */
                    
                    /* can others see the ticket or not? - pro version only */
                    $current_user = wp_get_current_user();
                    if (!$current_user->ID) {
                        return __("You cannot view this support ticket.","sola_st");
                    }
                    else {
                    /* check if it's the owner of the ticket */
                        $show_content = false;
                        if ((get_the_author_meta('ID') == $current_user->ID)) {
                            /* this is the user that posted the ticket */
                            $show_content = true;
                        } else {
                            /* let's check if the current user has capabilitie to see tickets */
                            if (current_user_can('edit_sola_st_ticket')) {
                                $show_content = true;
                            } else {
                                $show_content = false;
                            }
                            
                        }
                    
                        if ($show_content) {
                            $sola_content .= "<span class='sola_st_pending_approval_span'>".__("This support ticket is marked as solved.","sola_st")."</span>";
                            $content = $content.$sola_content;
                        }
                    }
                    
                    
                    
                    $content = $content;
                }
        }
	}
	return $content;
}

function sola_st_draw_response_box($post_id) {
    $response_data = sola_st_get_response_data($post_id);
    
    if ($response_data->post_status != "publish") { return ""; }
    
    $author_data = get_userdata($response_data->post_author);
    $sola_content = '<div class="sola_st_response" style="width:100%; display:block; overflow:auto;">';
    
    
    $sola_content .= "<div class='sola_st_post_author_image'>";
    $sola_content .= get_avatar( $author_data->user_email, '50')."</div>";
    $sola_content .= "<div class='sola_st_post_box'>";
    $sola_content .= '<span class="sola_response_user">'.$author_data->user_login.'</span> <span class="sola_response_time">'.$response_data->post_date.'</span><br />';
    $sola_content .= '<span class="sola_st_post_title">'.$response_data->post_title.'</span><p>'.nl2br($response_data->post_content).'</p>';
    if (sola_st_is_admin()) {
        $sola_content .= "<span class='sola_st_admin_edit_response_span'><a href='".get_edit_post_link( $post_id )."' class='sola_button'>".__("edit","sola_st")."</a></span> &nbsp;";
        $sola_content .= "<span class='sola_st_admin_delete_response_span'><a href='".get_delete_post_link( $post_id )."' class='sola_button'>".__("delete","sola_st")."</a></span>";
    }
    $sola_content .= '</div>';
    $sola_content .= '</div>';
    $sola_content .= '<hr />';
    return $sola_content;
    
}
function sola_st_is_admin() {
    /* build this up according to user roles in the near future */
    if (current_user_can( 'manage_options' )) { return true; }
    else { return false; }
}

function sola_st_user_head() {
    
    
    if (isset($_POST['sola_st_submit_ticket']) && $_POST['sola_st_ticket_title'] != "") {
    
        /* add a option to save as draft or live (settings) */
        
        $data = array(
            'post_content' => esc_attr($_POST['sola_st_ticket_text']),
            'post_status' => 'publish', 
            'post_title' => esc_attr($_POST['sola_st_ticket_title']),
            'post_type' => 'sola_st_tickets', 
            'post_author' => get_current_user_id(),
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        );  
        $post_id = wp_insert_post( $data );
        $custom_fields = get_post_custom($post_id);
        if (!isset($custom_fields['ticket_status'])) {
            add_post_meta( $post_id, 'ticket_status', '9', true ); 
        }
        if (!isset($custom_fields['ticket_public'])) {
            add_post_meta( $post_id, 'ticket_public', '0', true ); 
        }
        if (!isset($custom_fields['ticket_assigned_to'])) {
            if (!get_option("sola_st_default_assigned_to")) {
                $super_admins = get_super_admins();
                $user = get_user_by( 'slug', $super_admins[0] );
                add_option('sola_st_default_assigned_to',$user->ID);
            }
            $default_user = get_option("sola_st_default_assigned_to");
            add_post_meta( $post_id, 'ticket_assigned_to', $default_user, true ); 
        }
        add_post_meta( $post_id, 'ticket_reference', md5($post_id.get_current_user_id()), true ); 
        sola_st_notification_control('ticket',$post_id,get_current_user_id());
        wp_redirect(get_permalink($post_id));
    }
    
    if (isset($_POST['sola_st_response_id']) && $_POST['sola_st_response_id'] != "") {
    
        $parent_id = esc_attr($_POST['sola_st_response_id']);
        $data = array(
            'post_content' => esc_attr($_POST['sola_st_response_text']),
            'post_status' => 'publish', 
            'post_title' => esc_attr($_POST['sola_st_response_title']),
            'post_type' => 'sola_st_responses', 
            'post_author' => get_current_user_id(),
            'comment_status' => 'closed',
            'ping_status' => 'closed'
            
        );  
        $post_id = wp_insert_post( $data );
        update_post_meta( $post_id, '_response_parent_id', $parent_id );
        sola_st_notification_control('response',$parent_id,get_current_user_id());
    }
}

function sola_st_tickets_cpt_columns($columns) {

	$new_columns = array(
		'ticket_responses_column' => __('Responses', 'sola_st'),
		'ticket_last_responded_column' => __('Last Response By', 'sola_st'),
		'ticket_status' => __('Status', 'sola_st'),
	);
    return array_merge($columns, $new_columns);
}
add_filter('manage_sola_st_tickets_posts_columns' , 'sola_st_tickets_cpt_columns');

add_action('manage_sola_st_tickets_posts_custom_column', 'sola_st_manage_ticket_status_column', 10, 2);
 
function sola_st_manage_ticket_status_column($column_name, $post_id) {
    global $wpdb;
    switch ($column_name) {
    case 'ticket_responses_column':
        echo "<a href='".get_edit_post_link($post_id)."'>".sola_st_cnt_responses($post_id)."</a>";
        break;
    case 'ticket_last_responded_column':
        $data = sola_st_get_last_response($post_id);
        $author = $data->post_author;
        
        if ($author) {
            $author_data = get_userdata($author);
            echo $author_data->user_login;
            
            echo "<br /><small>".sola_st_time_elapsed_string(strtotime($data->post_date))."</small>";
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
    if ($meta_data) {
        $response_data = sola_st_get_response_data($post_id);
        return $response_data;
    }
    else {
        return false;
    } 
        
}
function sola_st_time_elapsed_string($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}
function sola_st_return_ticket_status($post_id) {
    $value = get_post_custom_values( 'ticket_status', $post_id );
    if ($value[0] == "0") { _e("Open","sola_st"); } 
    else if ($value[0] == "1") { _e("Solved","sola_st"); }
    else if ($value[0] == "2") { _e("Closed","sola_st"); }
    else if ($value[0] == "9") { _e("Pending Approval","sola_st"); }
    else { _e("Unknown","sola_st"); }

}



function sola_st_create_page($slug , $title, $content){
    // Initialize the post ID to -1. This indicates no action has been taken.
    $post_id = -1;

    // Setup the author, slug, and title for the post
    $author_id = 1;
    
    $post_type = "page";

    // If the page doesn't already exist, then create it
    $sola_check_page = get_page_by_title( $title ,'',$post_type);
    if( $sola_check_page == null ) {

            // Set the page ID so that we know the page was created successfully
            $post_id = wp_insert_post(
                array(
                    'comment_status'	=>	'closed',
                    'ping_status'	=>	'closed',
                    'post_author'	=>	$author_id,
                    'post_name'		=>	$slug,
                    'post_title'	=>	$title,
                    'post_status'	=>	'publish',
                    'post_type'		=>	$post_type,
                    'post_content'      =>      $content
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
function sola_st_shortcode_submit_ticket_page($atr , $text = null){

    if (is_user_logged_in()) {
    
    $content = "
        <div class=\"sola_st_response_div\">
            <form name=\"sola_st_add_ticket\" method=\"POST\" action=\"\" enctype=\"multipart/form-data\">
                <table width=\"100%\" border=\"0\">
                <tr class=\"sola_st_st_tr sola_st_st_subject\">
                   <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_subject_label\">
                      <strong>".__("Subject","sola_st")."</strong>
                   </td>
                   <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_subject_input\">
                      <input type=\"text\" value=\"\" name=\"sola_st_ticket_title\" id=\"sola_st_ticket_title\" /><br />
                   </td>
                </tr>
                <tr class=\"sola_st_st_tr sola_st_st_desc\">
                    <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_desc_label\">
                      <strong>".__("Description","sola_st")."</strong>
                    </td>
                    <td valign=\"top\" class=\"sola_st_st_td sola_st_st_td_desc_textare\">
                      <textarea style=\"width:100%; height:120px;\" name=\"sola_st_ticket_text\" id=\"sola_st_ticket_text\"></textarea>
                   </td>
                </tr>
                <tr class=\"sola_st_st_tr sola_st_st_submit\">
                   <td valign=\"top\"></td>
                   <td valign=\"top\" align=\"right\" class=\"sola_st_st_td sola_st_st_td_submit_button\">
                        <input type=\"submit\" name=\"sola_st_submit_ticket\" title=\"".__("Send","sola_st")."\" class=\"sola_st_button_send_reponse\" />
                   </td>
                </tr>
                </table>
            </form>
            
        </div>
";    


    } else {
        $content = "
        <a href=\"".wp_login_url()."\">".__("Log in","sola_st")."</a> ".__("or","sola_st")." <a href=\"".wp_registration_url()."\">".__("register","sola_st")."</a> ".__("to submit a support ticket.","sola_st")."
        <br /><br /> 
        ";
    }
    return $content;
}
add_filter( 'views_edit-sola_st_tickets', 'meta_views_sola_st_tickets', 10, 1 );

function meta_views_sola_st_tickets( $views ) 
{
    //$views['separator'] = '&nbsp;';
    $views['metakey'] = '<a href="edit.php?meta_data=ticket_status&ticket_status=9&post_type=sola_st_tickets">'.__('Pending Approval','sola_st').'</a> ('.sola_st_return_pending_ticket_qty().")";
    $views['metakey'] .= '| <a href="edit.php?meta_data=ticket_status&ticket_status=0&post_type=sola_st_tickets">'.__('Open Tickets','sola_st').'</a> ('.sola_st_return_open_ticket_qty().")";
    $views['metakey'] .= '| <a href="edit.php?meta_data=ticket_status&ticket_status=1&post_type=sola_st_tickets">'.__('Solved Tickets','sola_st').'</a> ('.sola_st_return_solved_ticket_qty().")";
    $views['metakey'] .= '| <a href="edit.php?meta_data=ticket_status&ticket_status=2&post_type=sola_st_tickets">'.__('Closed Tickets','sola_st').'</a> ('.sola_st_return_closed_ticket_qty().")";
    return $views;
}

add_action( 'load-edit.php', 'load_sola_st_custom_filter' );

function load_sola_st_custom_filter() {
    global $typenow;

    if( 'sola_st_tickets' != $typenow )
        return;

    add_filter( 'posts_where' , 'posts_where_sola_st_status' );
}

function posts_where_sola_st_status($where) {
    global $wpdb;       
    if ( isset( $_GET[ 'meta_data' ] ) && !empty( $_GET[ 'meta_data' ] ) ) {
        $meta = esc_sql( $_GET['meta_data'] );
        $meta_val = esc_sql( $_GET['ticket_status'] );
        $where .= " AND ID IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='$meta' AND meta_value='$meta_val' )";
       
        
    }
    return $where;
}

function sola_st_return_open_ticket_qty() {
   global $wpdb;
   $row = $wpdb->get_row( 
        $wpdb->prepare( 
            "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d",
            'ticket_status', 0
        )
    );
    $total = $row->total;
    return $total;
       
}
function sola_st_return_pending_ticket_qty() {
   global $wpdb;
   $row = $wpdb->get_row( 
        $wpdb->prepare( 
            "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d",
            'ticket_status', 9
        )
    );
    $total = $row->total;
    return $total;
       
}
function sola_st_return_closed_ticket_qty() {
   global $wpdb;
   $row = $wpdb->get_row( 
        $wpdb->prepare( 
            "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d",
            'ticket_status', 2
        )
    );
    $total = $row->total;
    return $total;
       
}
function sola_st_return_solved_ticket_qty() {
   global $wpdb;
   $row = $wpdb->get_row( 
        $wpdb->prepare( 
            "SELECT count(`meta_id`) as `total` FROM `wp_postmeta` WHERE `meta_key` = %s AND `meta_value` = %d",
            'ticket_status', 1
        )
    );
    $total = $row->total;
    return $total;
    
}

if (!function_exists("sola_st_pro_activate")) { 
    add_filter( 'pre_get_posts', 'sola_st_loop_control' );
}
function sola_st_loop_control( $query ) {
    
    if (!is_admin() && !is_single() && !is_page()) {
        if ($query->query['post_type'] == "sola_st_tickets" || $query->query['post_type'] == "sola_st_responses" ) {
            $query->set('post_type', 'sola_st_x'); /* 4 0 4 */
            $query->parse_query();
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