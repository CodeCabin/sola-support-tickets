<?php
/* Handles all routes related to the Sola API */

/*
 * Add the following routes:
 * - '/sola_support_tickets/v1/create_ticket' 
 * - '/sola_support_tickets/v1/view_ticket' 
 * - '/sola_support_tickets/v1/delete_ticket' 
 */
add_action('rest_api_init', function(){
	register_rest_route('sola_support_tickets/v1','/create_ticket', array(
						'methods' => 'GET, POST',
						'callback' => 'sola_support_tickets_api_create_ticket'
	));

	register_rest_route('sola_support_tickets/v1','/view_ticket', array(
						'methods' => 'GET, POST',
						'callback' => 'sola_support_tickets_api_view_ticket'
	));

	register_rest_route('sola_support_tickets/v1','/delete_ticket', array(
						'methods' => 'GET, POST',
						'callback' => 'sola_support_tickets_api_delete_ticket'
	));

	do_action("sola_support_tickets_api_route_hook");
});