<?php
/* Handles reporting functionality */
include("/reporting-ajax.php");

/*
 * Register the dashboard widget
*/
add_action('wp_dashboard_setup', 'sola_st_register_reporting_dash_components');
function sola_st_register_reporting_dash_components(){
	wp_add_dashboard_widget("sola_st_reporting_dashboard_widget", __("Sola Support Tickets - Reporting", "sola_st"), "sola_st_reporting_widget");
}

/*
 * Creates the reporting window (Minimal)
 * Used in widget view
*/
function sola_st_reporting_widget(){
	sola_st_reporting_enqueue_styles();
	sola_st_reporting_enqueue_scripts();

	do_action("sola_st_reporting_widget_head_hook"); //For adding widget exclusive code of styles

	sola_st_reporting_action_bar();
	sola_st_reporting_separate();

	sola_st_reporting_primary_stats();
	sola_st_reporting_separate();

	sola_st_draw_primary_stats_chart();
	sola_st_all_stats_button();
}

/*
 * Register the reporting page
*/
add_action('admin_menu', 'sola_st_admin_menu_reporting' ,10);
function sola_st_admin_menu_reporting(){
	add_submenu_page('support-tickets', __('Reporting', 'sola_st'), __('Reporting', 'sola_st'), 'manage_options', 'sola-st-menu-reporting', 'sola_st_reporting_page');
}
/*
 * Creates the reporting window
 * Used in page view
*/
function sola_st_reporting_page(){
	?>
		<h1><?php _e("Sola Support Tickets - Reporting", "sola_st"); ?></h1>
	<?php
	sola_st_reporting_enqueue_styles();
	sola_st_reporting_enqueue_scripts();

	sola_st_open_container();
	sola_st_reporting_action_bar();
	sola_st_reporting_separate();
	sola_st_reporting_primary_stats();
	sola_st_reporting_separate();

	?>
	<div class="sola_st_reporting_row">
		<div class="sola_st_reporting_col_30">
	<?php
	sola_st_draw_primary_stats_chart();
	?>
		</div>
	<?php

	?>
		<div class="sola_st_reporting_col_70">
	<?php
		do_action("sola_st_reporting_page_grid_area_hook");
	?>
		</div>
	</div>
	<?php	
	sola_st_close_container();
	?>
	<div class="sola_st_container_clear">
		<div class="sola_st_reporting_row" style="text-align:left">
	<?php
	do_action("sola_st_reporting_page_after_hook");
	?>
		</div>
	</div>
	<?php
	
}

/*
 * Loads stylesheet
*/
function sola_st_reporting_enqueue_styles(){
	wp_register_style('sola_st_reporting_css', plugins_url('/css/', dirname(__FILE__)).'reporting.css');
	wp_enqueue_style("sola_st_reporting_css");	
}

/*
 * Loads scripts
*/
function sola_st_reporting_enqueue_scripts(){
	wp_register_script('sola-st-reporting', plugins_url('/js/', dirname(__FILE__)).'reporting.js', array('jquery'), '', true);

    $sola_st_dashboard_nonce = wp_create_nonce("sola_st_reporting");

    wp_localize_script('sola-st-reporting', 'sola_st_rep_security', $sola_st_dashboard_nonce);
    wp_localize_script('sola-st-reporting', 'sola_st_rep_ajax_icon_url', plugins_url('/images/', dirname(__FILE__)).'ajax-loader.gif');

    wp_localize_script('sola-st-reporting', 'sola_st_rep_total', __("Total Tickets", "sola_st"));
    wp_localize_script('sola-st-reporting', 'sola_st_rep_solved', __("Solved Tickets", "sola_st"));

    wp_enqueue_script('sola-st-reporting');

    do_action("sola_st_reporting_js_hook");
}

/*
 * Creates the actions
*/
function sola_st_reporting_action_bar(){
	?>
		<div class="sola_st_reporting_actions">
			<?php do_action("sola_st_reporting_action_bar_before_hook"); ?>
			<select class="sola_st_reporting_action_dropdown" id="sola_st_rep_action_period">
				<option value="0" autoFire><?php _e("Last 24 hours", "sola_st"); ?></option>
				<option value="1" autoFire><?php _e("Last 7 days", "sola_st"); ?></option>
				<option value="2" autoFire selected><?php _e("Last 30 days", "sola_st"); ?></option>
				<option value="3" autoFire><?php _e("Last 60 days", "sola_st"); ?></option>
				<?php do_action("sola_st_reporting_period_action_hook"); ?>
			</select>
			<?php do_action("sola_st_reporting_action_bar_after_hook"); ?>
		</div>
	<?php
}

/*
 * Creates the primary stats (counts etc)
*/
function sola_st_reporting_primary_stats(){
	?>
		<div class="sola_st_reporting_row">
			<div class="sola_st_reporting_col_30 sola_stat_heading">
				<span id="ticket_count_current_new"><?php sola_st_show_ajax_loader("50"); ?></span>
				<span class="sola_stat_heading_sub"><?php _e("Total Tickets", "sola_st") ?></span>
			</div>
			<div class="sola_st_reporting_col_30 sola_stat_heading">
				<span id="ticket_count_current_closed"><?php sola_st_show_ajax_loader("50"); ?></span>
				<span class="sola_stat_heading_sub"><?php _e("Solved Tickets", "sola_st") ?></span>
			</div>
			<div class="sola_st_reporting_col_30 sola_stat_heading">
				<span id="ticket_average_res_time"><?php sola_st_show_ajax_loader("50"); ?></span>
				<span class="sola_stat_heading_sub"><?php _e("First Reply Time", "sola_st") ?></span>
			</div>
			<?php do_action("sola_st_reporting_primary_stats_hook"); ?>
		</div>
	<?php
}


function sola_st_draw_primary_stats_chart(){
	?>
		<div class="sola_st_rep_chart" id="sola_st_rep_chart_primary"></div>
	<?php
}

/*
 * Creates an HR tag
*/
function sola_st_reporting_separate(){
	?><hr><?php
}

function sola_st_show_ajax_loader($max_width_perc){
	?>
		<img style="max-width: <?php echo $max_width_perc;?>px;" src="<?php echo  plugins_url('/images/', dirname(__FILE__)).'ajax-loader.gif' ?>"/>
	<?php	
}

function sola_st_open_container(){
	?>
		<div class="sola_st_container sola_st_border sola_rounded">
	<?php
}

function sola_st_close_container(){
	?>
		</div>
	<?php
}

add_action("sola_st_reporting_page_grid_area_hook", "sola_st_reporting_page_grid_basic_upsell", 10);
function sola_st_reporting_page_grid_basic_upsell(){
	?>
		<div class="sola_st_border sola_st_rounded_container_grey">
			<div style="font-size:16px;margin-top: 87px;"> 
				<span style="display:block"><?php _e("Get Comparison Charts", "sola_st"); ?></span><br>
				<span style="display:block; font-size:20px"><strong><?php _e("Upgrade to the premium version", "sola_st"); ?></strong></span><br>
				<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_reporting" target="_BLANK" class="button button-primary">
					<?php _e("Upgrade now" ,"sola_st"); ?>
				</a>
			</div>
		</div>
	<?php
}

function sola_st_all_stats_button(){
	?>
		<a href="<?php echo admin_url('admin.php') ?>?page=sola-st-menu-reporting"  style="width: 100%;text-align: center;" class="button"><?php _e("View All Statistics"); ?></a>
	<?php
}