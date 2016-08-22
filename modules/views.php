<?php

add_action("sola_st_view_control","sola_st_hook_control_view_control",10);
function sola_st_hook_control_view_control() {
	$current_views = get_option("sola_st_views");
	echo '<ul class="sola_st_db_controls">';
	foreach ($current_views as $key => $view) {
		echo '<li><a href="javascript:void(0)" class="sola_st_view_control" id="sola_st_view_control_'.$key.'" view="'.$key.'"><span class="sola_st_view_control_ticket_name">'.$view['title'].'</span><span class="sola_st_view_control_ticket_count" id="sola_st_view_count_'.$key.'"">'.sola_st_ticket_count_by_view($key).'</span></a></li>';
	}
	echo '</ul>';
}

function sola_st_ticket_count_by_view($key) {

	$check = json_decode(sola_st_get_tickets_by_view($key,0,1,false));
	return $check->cnt;
}


function sola_st_get_tickets_by_view($view,$offset = 0,$limit = 20,$return_all_counts = 0) {
	$return_all_counts = intval($return_all_counts);

	$current_views = get_option("sola_st_views");

	if (isset($current_views[$view])) { } else {  echo "error2"; wp_die(); }

	$orderby = $current_views[$view]['data']['orderby'];
	if (!isset($current_views[$view]['data']['channel'])) {
		$channel = false;
	} else {
		$channel = $current_views[$view]['data']['channel'];
	}
	
	
	$order = $current_views[$view]['data']['order'];
	$priority = $current_views[$view]['data']['priority'];
	if (!isset($current_views[$view]['data']['status'])) {
		/* if a user created a view and selected no statuses, select all by default now  */
		$status = array(
			0 => true,
			1 => true,
			2 => true,
			3 => true,
			9 => true
			);
	} else {
		$status = $current_views[$view]['data']['status'];
	}
	$agents = $current_views[$view]['data']['agents'];
	
	



	$and_relation = array("relation" => "AND");


	$meta_query = array();
	
	if (count($status) > 1) {
		$status_meta_query = array("relation" => "OR");

		/* multiple status values requested */
		$cnter = 0;
		foreach ($status as $key => $status_val) {

			if ((string)$key == "-1") {
				/* Unassigned statuses */
				$status_meta_query_sub = array(
					'key'     => 'ticket_status',
					'compare' => 'NOT EXISTS',
				);

			} else {
				$status_meta_query_sub = array(
					'key'     => 'ticket_status',
					'value'   => (string)$key,
					'compare' => '=',
				);
			}
			array_push($status_meta_query,$status_meta_query_sub);
		}
		
		
	} else {
		/* single status value requested */
		if ((string)key($status) == "-1") {
			/* Unassigned statuses */
			$status_meta_query = array(
				'key'     => 'ticket_status',
				'compare' => 'NOT EXISTS',
			);

		} else {
			$status_meta_query = array(
				'key'     => 'ticket_status',
				'value'   => (string)key($status),
				'compare' => '=',
			);
		}
		
	}
	

	if ($priority > 0) {

		if (count($priority) > 1) {
			$priority_meta_query = array("relation" => "OR");

			/* multiple priority values requested */
			$cnter = 0;
			foreach ($priority as $key => $priority_val) {
				if ((string)$key == "0") {
					/* all priorities, so just ignore this meta query build subset */
					/* set $priority to zero so that we do not include this meta query sub build */
					$priority = 0;
					break;


				} else if ((string)$key == "-1") {
					/* Unassigned tickets */
					$priority_meta_query_sub = array(
						'key'     => 'ticket_priority',
						'compare' => 'NOT EXISTS',
					);

				} else {
					$priority_meta_query_sub = array(
						'key'     => 'ticket_priority',
						'value'   => (string)$key,
						'compare' => '=',
					);
				}
				array_push($priority_meta_query,$priority_meta_query_sub);
			}
			
			
		} else {
			/* single priority value requested */
			if ((string)key($priority) == "0") {
				/* all agents, so just ignore this meta query build subset */
			} else if ((string)key($priority) == "-1") {
				/* Unassigned tickets */
				$priority_meta_query = array(
					'key'     => 'ticket_priority',
					'compare' => 'NOT EXISTS',
				);

			} else {

				$priority_meta_query = array(
							'key'     => 'ticket_priority',
							'value'   => (string)key($priority),
							'compare' => '=',
						);
			}
			$include_priority = true;
		}
	}

	if ($agents > 0) {

		if (count($agents) > 1) {

			$agent_meta_query = array("relation" => "OR");

			/* multiple agent values requested */
			$cnter = 0;
			foreach ($agents as $key => $agents_val) {
				if ((string)$key == "0") {
					/* all agents, so just ignore this meta query build subset */
					/* set $agents to zero so that we do not include this meta query sub build */
					$agents = 0;
					break;


				} else if ((string)$key == "-1") {
					/* Unassigned tickets */
					$agent_meta_query_sub = array(
						'key'     => 'ticket_assigned_to',
						'compare' => 'NOT EXISTS',
					);

				} else if ((string)$key == "current_agent") {
					/* Tickets for this current user only */
					$agent_meta_query_sub = array(
						'key'     => 'ticket_assigned_to',
						'value'	  => get_current_user_id(),
						'compare' => '=',

					);


				} else {
					$agent_meta_query_sub = array(
						'key'     => 'ticket_assigned_to',
						'value'   => (string)$key,
						'compare' => '=',
					);
				}
				array_push($agent_meta_query,$agent_meta_query_sub);
			}
			
			
		} else {

			/* single priority value requested */
			if ((string)key($agents) == "0") {
				/* all agents, so just ignore this meta query build subset */
			} else if ((string)key($agents) == "-1") {
				/* Unassigned tickets */
				$agent_meta_query = array(
					'key'     => 'ticket_assigned_to',
					'compare' => 'NOT EXISTS',
				);

			} else if ((string)key($agents) == "current_agent") {

				/* Tickets for this current user only */
				$agent_meta_query = array(
					'key'     => 'ticket_assigned_to',
					'value'	  => get_current_user_id(),
					'compare' => '=',

				);


			} 
		 	else {
				$agent_meta_query = array(
							'key'     => 'ticket_assigned_to',
							'value'   => (string)key($agents),
							'compare' => '=',
						);
				$include_agent = true;
			}
		}
	}
	

	if ($priority > 0 || $agents > 0) {

		$meta_query = array("relation" => "AND");
		array_push($meta_query,$status_meta_query);
		
		if ($priority) {
			array_push($meta_query,$priority_meta_query);
		}
		if ($agents) {
			array_push($meta_query,$agent_meta_query);
		}

	} else {
		/* one line query, do not use AND or OR */
		array_push($meta_query,$status_meta_query);
	}
	global $wpdb;
	$meta_sql = get_meta_sql( $meta_query, 'post', $wpdb->posts, 'ID' );


	//echo $required_action;

	$ret = "";


	/* limit is set to $limit+1 here because we are actively seeking if there are more posts than what has been asked for, if true, then we can let the JS know that it can keep the "next" button active. */

	$posts_per_page_default = get_option("posts_per_page");

	update_option("posts_per_page",$limit+1);



	$args = array(
		'post_type' => 'sola_st_tickets',
		'posts_per_page ' => $limit+1,
		'offset' => $offset,
		'orderby' => $orderby,
		'order' => $order,
		'meta_query' => array($meta_query)
	);
	//var_dump($args);




	$my_query = new WP_Query( $args );

	$ticket_counter = 0;
	$is_more = false;
	$is_less = false;

	if ($offset > 0) { $is_less = true; } /* if we've offset anything, logically there would be previous items so set is_less to true */

	if ( $my_query->have_posts() ) {

		while ( $my_query->have_posts() ) {
			$my_query->the_post();

			$post_status = sola_st_return_ticket_status_returns( get_the_ID() );
			$ticket_id = get_the_ID();

			$ticket_channel = get_post_meta( $ticket_id, 'ticket_channel_id', true );
			if (function_exists('sola_st_get_ticket_channel_name')) {
				$channel_name = sola_st_get_ticket_channel_name($ticket_channel);
			} else {
				$channel_name = __('Default','sola_st');
			}

			$is_public = get_post_meta( $ticket_id, 'ticket_public', true );
			$assigned_to = get_post_meta( $ticket_id, 'ticket_assigned_to', true );
			$user_data = get_user_by('id', $assigned_to);
			if (!$user_data) { $user_data = (object)[]; $user_data->display_name = "Not assigned"; }

			$response_count = sola_st_cnt_responses( $ticket_id );
			$priority = sola_st_return_ticket_priority_returns( $ticket_id );

			$last_responder = "";
			$data = sola_st_get_last_response( $ticket_id );
            if (isset($data->post_author)) {
                $author = $data->post_author;
                if ($author) {
                    $author_data = get_userdata($author);
                    $last_responder .= $author_data->display_name;

                    $last_responder .= "<br /><small>" . sola_st_time_elapsed_string(strtotime($data->post_date)) . "</small>";
                } else {
                    $last_responder .= "-";
                }
            } else {
                $last_responder .= "-";
            }
            $ticket_counter++;
            if ($ticket_counter <= $limit) {
				$ret .= "<tr id='sola_st_modern_ticket_row_".$ticket_id."'>";
				$ret .= "<td><input type='checkbox' class='sola_st_checkbox' value='".$ticket_id."' /></td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_title' ticket_id='".$ticket_id."' >" . get_the_title() . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_author' ticket_id='".$ticket_id."'>" . get_the_author() . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_date' ticket_id='".$ticket_id."'>".sola_st_parse_date(get_the_time('U')) . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_priority' ticket_id='".$ticket_id."'>" . $priority . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_responses' ticket_id='".$ticket_id."'>" . $response_count . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_responser' ticket_id='".$ticket_id."'>" . $last_responder . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_owner' ticket_id='".$ticket_id."'>" . $user_data->display_name . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_status' ticket_id='".$ticket_id."'>" . $post_status . "</td>";
				$ret .= "<td class='sola_st_db_single_ticket ticket_channel' ticket_id='".$ticket_id."'>" . $channel_name . "</td>";
				$ret .= "</tr>";
			} else {
				$is_more = true;
			}


		}
	} else {
		$ret .= "<tr><td colspan='9' style='padding: 10px 0;'>".__('No tickets found', 'sola_st')."</td></tr>";
	}
	if ($return_all_counts) {
		$js = json_encode(array(
			'ticket_cnt' => $ticket_counter,
			'ticket_html' => $ret,
			'is_more' => $is_more,
			'is_less' => $is_less,
			'orderby' => $orderby,
			'order' => $order,
			'priority' => $priority,
			'ticket_status' => $status,
			'cnt' => $my_query->found_posts,
			'limit' => $limit,
			'offset' => $offset,
			'counts' => sola_st_return_ticket_count_array()
			)
		);
	} else {
		$js = json_encode(array(
			'ticket_cnt' => $ticket_counter,
			'ticket_html' => $ret,
			'is_more' => $is_more,
			'is_less' => $is_less,
			'orderby' => $orderby,
			'order' => $order,
			'priority' => $priority,
			'ticket_status' => $status,
			'cnt' => $my_query->found_posts,
			'limit' => $limit,
			'offset' => $offset
			)
		);

	}
	//var_dump($js);
	return $js;
}

function sola_st_return_ticket_count_array() {
	$current_views = get_option("sola_st_views");


	$view_array = array();
	foreach ($current_views as $key => $view) {
		$view_array[$key] = intval(sola_st_ticket_count_by_view($key));

	}
	return $view_array;

	
}

function sola_st_views_html_output() {

	$current_views = get_option("sola_st_views");



	echo "<table class='wp-list-table widefat fixed striped pages'>";
	echo "<thead>";
	echo "<tr>";
	echo "<th>Name</th>";
	echo "<th class='sola_st_table_action'>Action</th>";
	echo "</tr>";
	echo "</thead>";
	echo "<tbody>";
	foreach ($current_views as $key => $view) {
		echo "<tr id='view_tr_'".$key.">";
		echo "<td>".$view['title']."</td>";
		echo "<td>".apply_filters("sola_st_filter_view_action_control","",$key)."</td>";
		echo "</tr>";


	}
	echo "</tbody>";
	echo "</table>";


}

function sola_st_set_default_views() {
	$current_views = array(
		1 => array(
			"readonly" => true,
			"active" => 1,
			"title" => __("Your unsolved tickets","sola_st"),
			"data" => array(
				"status" => array(
					"-1" => true,
					"0" => true,
					"3" => true,
					"9" => true
				),
				"priority" => false,
				"agents" => array(
					"current_agent" => true
				),
				"orderby" => "date",
				"order" => "asc",
				"department" => false
			)
		),
		2 => array(
			"readonly" => true,
			"active" => 1,
			"title" => __("All unsolved tickets","sola_st"),
			"data" => array(
				"status" => array(
					"0" => true,
					"3" => true,
					"9" => true
				),
				"priority" => false,
				"agents" => false,
				"orderby" => "date",
				"order" => "asc",
				"department" => false
			)
		),
		3 => array(
			"readonly" => true,
			"active" => 1,
			"title" => __("New tickets","sola_st"),
			"data" => array(
				"status" => array(
					"9" => true
				),
				"priority" => false,
				"agents" => false,
				"orderby" => "date",
				"order" => "asc",
				"department" => false
			)
		),
		4 => array(
			"readonly" => true,
			"active" => 1,
			"title" => __("Open tickets","sola_st"),
			"data" => array(
				"status" => array(
					"0" => true
				),
				"priority" => false,
				"agents" => false,
				"orderby" => "date",
				"order" => "asc",
				"department" => false
			)
		),
		5 => array(
			"readonly" => true,
			"active" => 1,
			"title" => __("Pending tickets","sola_st"),
			"data" => array(
				"status" => array(
					"3" => true
				),
				"priority" => false,
				"agents" => false,
				"orderby" => "date",
				"order" => "asc",
				"department" => false
			)
		),
		6 => array(
			"readonly" => true,
			"active" => 0,
			"title" => __("Closed Tickets","sola_st"),
			"data" => array(
				"status" => array(
					"1" => true,
					"2" => true
				),
				"priority" => false,
				"agents" => false,
				"orderby" => "date",
				"order" => "asc",
				"department" => false
			)
		)


	);

	update_option("sola_st_views",$current_views);
}