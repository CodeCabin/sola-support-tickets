<?php

add_action("wp_ajax_sola_st_db_request_tickets_from_control", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_request_tickets_from_control_by_view", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_fetch_channels", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_request_ticket_from_content_list", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_update_ticket_status", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_update_ticket_priority", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_submit_response", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_request_tickets_from_control_priority", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_delete_ticket", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_modern_submit_internal_note", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_bulk_delete_tickets", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_delete_channel", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_db_search_ticets", "sola_st_db_ajax_callback");
add_action("wp_ajax_sola_st_resend_notification", "sola_st_db_ajax_callback");

function sola_st_db_ajax_callback(){

	if( isset( $_POST['action'] ) ){

		if ($_POST['action'] == 'sola_st_resend_notification') {

			$nid = intval($_POST['post_id']);
			$not_array = get_post_meta($nid, 'sola_st_notification_issue', true);
			if ($not_array) {
				$email = $not_array['email'];
				$subject = $not_array['subject'];
				$message = $not_array['message'];
				$in_reply_to = $not_array['in_reply_to'];
				$post_id = $not_array['post_id'];
				$channel = $not_array['channel'];
				$headers = $not_array['headers'];

				$checker = send_automated_emails($email,$subject,$message,$headers,$in_reply_to,$channel,$post_id);
				if ($checker) {
					/* success.. */
					echo "1";
					wp_die();
				} else {
					echo json_encode(array('errormsg' => 'Failed'));
					wp_die();
				}

			}


			die();
		}

		if ($_POST['action'] == 'sola_st_fetch_channels') {
			do_action("sola_st_fetch_channels");
			echo "2";
			die();
		}

		if ($_POST['action'] == 'sola_st_delete_channel') {
			$cid = intval(sanitize_text_field($_POST['cid']));

			$sola_channels = get_option("sola_st_channels");

			if (count($sola_channel) == 1) {
				delete_option("sola_st_channels");
			} else {
				unset($sola_channels[$cid]);
				update_option("sola_st_channels",$sola_channels);
			}
			echo "1";
			die();
		}


		if( $_POST['action'] == 'sola_st_db_request_tickets_from_control_by_view'){

			

			if (isset($_POST['offset'])) { $offset = intval($_POST['offset']); } else { $offset = 0; }
			if (isset($_POST['limit'])) { $limit = intval($_POST['limit']); } else { $limit = 20; }
			if (isset($_POST['view'])) { $view = intval($_POST['view']); } else { echo "error"; wp_die(); }
			if (isset($_POST['return_counts'])) { $return_counts = $_POST['return_counts']; } else { $return_counts = 0; }
			$tickets = sola_st_get_tickets_by_view($view,$offset,$limit,$return_counts);


			echo $tickets;

			

			wp_reset_postdata();

			update_option("posts_per_page",$posts_per_page_default);
	

			wp_die();

		}

		if( $_POST['action'] == 'sola_st_db_request_tickets_from_control' ){

			$status = explode(",",$_POST['ticket_status']);

			$include_priority = false;

			$orderby = 'title';
			$order = 'DESC';
			

			if (isset($_POST['offset'])) { $offset = intval($_POST['offset']); } else { $offset = 0; }
			if (isset($_POST['limit'])) { $limit = intval($_POST['limit']); } else { $limit = 20; }


			if (isset($_POST['priority'])) { $priority = intval($_POST['priority']); } else { $priority = false; }

			$and_relation = array("relation" => "AND");
			$or_relation = array("relation" => "OR");


			$meta_query = array();
			
			if (count($status) > 1) {
				$status_meta_query = array();
				array_push($status_meta_query,$or_relation);

				/* multiple status values requested */
				$cnter = 0;
				foreach ($status as $status_val) {
					$status_meta_query_sub = array(
						'key'     => 'ticket_status',
						'value'   => $status_val,
						'compare' => '=',
					);
					array_push($status_meta_query,$status_meta_query_sub);
				}
				
				
			} else {
				/* single status value requested */
				$status_meta_query = array(
					'key'     => 'ticket_status',
					'value'   => $status[0],
					'compare' => '=',
				);
				
			}
			


			if ($priority > 0) {
				$priority_meta_query = array(
							'key'     => 'ticket_priority',
							'value'   => $priority,
							'compare' => '=',
						);
				$include_priority = true;
				
			}


			if ($priority > 0) {
				array_push($meta_query,$and_relation);
				array_push($meta_query,$status_meta_query);
				array_push($meta_query,$priority_meta_query);

			} else {
				/* one line query, do not use AND or OR */
				array_push($meta_query,$status_meta_query);
			}

			//var_dump($meta_query);

			//echo $required_action;

			$ret = "";


			/* limit is set to $limit+1 here because we are actively seeking if there are more posts than what has been asked for, if true, then we can let the JS know that it can keep the "next" button active. */

			$posts_per_page_default = get_option("posts_per_page");

			update_option("posts_per_page",$limit+1);



			$args = array(
				'post_type' => 'sola_st_tickets',
				'posts_per_page ' => $limit+1,
				'offset' => 0,
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
					$is_public = get_post_meta( get_the_ID(), 'ticket_public', true );
					$response_count = sola_st_cnt_responses( get_the_ID() );
					$priority = sola_st_return_ticket_priority_returns( get_the_ID() );

					$last_responder = "";
					$data = sola_st_get_last_response( get_the_ID() );
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
						$ret .= "<tr id='sola_st_modern_ticket_row_".get_the_ID()."'>";
						$ret .= "<td><input type='checkbox' class='sola_st_checkbox' value='".get_the_ID()."' /></td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_title' ticket_id='".get_the_ID()."' >" . get_the_title() . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_author' ticket_id='".get_the_ID()."'>" . get_the_author() . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_date' ticket_id='".get_the_ID()."'>" . get_the_date() . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_priority' ticket_id='".get_the_ID()."'>" . $priority . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_responses' ticket_id='".get_the_ID()."'>" . $response_count . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_responser' ticket_id='".get_the_ID()."'>" . $last_responder . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_status' ticket_id='".get_the_ID()."'>" . $post_status . "</td>";
						$ret .= "</tr>";
					} else {
						$is_more = true;
					}


				}
			} else {
				$ret .= "<tr><td colspan='8' style='padding: 10px 0;'>".__('No tickets found', 'sola_st')."</td></tr>";
			}

			echo json_encode(array(
				'ticket_cnt' => $ticket_counter,
				'ticket_html' => $ret,
				'is_more' => $is_more,
				'is_less' => $is_less,
				'orderby' => $orderby,
				'order' => $order,
				'priority' => $priority,
				'ticket_status' => $status
				)
			);
			

			wp_reset_postdata();

			update_option("posts_per_page",$posts_per_page_default);
	

			wp_die();

		}

		if( $_POST['action'] == 'sola_st_db_request_tickets_from_control_priority' ){

			$required_action = $_POST['required_action'];
			if (isset($_POST['offset'])) { $offset = intval($_POST['offset']); } else { $offset = 0; }
			if (isset($_POST['limit'])) { $limit = intval($_POST['limit']); } else { $limit = 20; }

			//echo $required_action;

			$ret = "";

			$args = array(
				'post_type' => 'sola_st_tickets',
				'posts_per_page ' => $limit+1,
				'offset' => $offset,
				'meta_query' => array(
					array(
						'key'     => 'ticket_priority',
						'value'   => $required_action,
						'compare' => '=',
					),
				),
			);

			$my_query = new WP_Query( $args );

			$ticket_counter = 0;
			$is_more = false;
			$is_less = false;

			if ($offset > 0) { $is_less = true; } /* if we've offset anything, logically there would be previous items so set is_less to true */


			if ( $my_query->have_posts() ) {
		
				while ( $my_query->have_posts() ) {
					$my_query->the_post();

					$post_status = sola_st_return_ticket_status_returns( get_the_ID() );

					$response_count = sola_st_cnt_responses( get_the_ID() );

					$priority = sola_st_return_ticket_priority_returns( get_the_ID() );

					$last_responder = "";
					$data = sola_st_get_last_response( get_the_ID() );
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
						$ret .= "<tr id='sola_st_modern_ticket_row_".get_the_ID()."'>";
						$ret .= "<td><input type='checkbox' class='sola_st_checkbox' value='".get_the_ID()."' /></td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_title' ticket_id='".get_the_ID()."' >" . get_the_title() . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_author' ticket_id='".get_the_ID()."'>" . get_the_author() . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_date' ticket_id='".get_the_ID()."'>" . get_the_date() . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_priority' ticket_id='".get_the_ID()."'>" . $priority . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_responses' ticket_id='".get_the_ID()."'>" . $response_count . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_responser' ticket_id='".get_the_ID()."'>" . $last_responder . "</td>";
						$ret .= "<td class='sola_st_db_single_ticket ticket_status' ticket_id='".get_the_ID()."'>" . $post_status . "</td>";
						$ret .= "</tr>";

					} else {
						$is_more = true;
					}


				}
			} else {
				$ret .= "<tr><td colspan='8' style='padding: 10px 0;'>".__('No tickets found', 'sola_st')."</td></tr>";
			}

			echo json_encode(array(
				'ticket_cnt' => $ticket_counter,
				'ticket_html' => $ret,
				'is_more' => $is_more,
				'is_less' => $is_less
				)
			);
			

			wp_reset_postdata();

			update_option("posts_per_page",$posts_per_page_default);
	

			wp_die();
		}

		if( $_POST['action'] == 'sola_st_db_request_ticket_from_content_list' ){
			$debug_start = (float) array_sum(explode(' ',microtime()));

			$ticket_id = sanitize_text_field( $_POST['ticket_id'] );

			$post = get_post( $ticket_id );
	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret = "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";


			$ticket_subject = $post->post_title;
			$ticket_content = $post->post_content;			
			
			$ticket_request_date = sola_st_parse_date(strtotime($post->post_date));

			$ticket_requester_id = $post->post_author;

			$ticket_author_data = get_userdata($post->post_author);
			$ticket_author_name = $ticket_author_data->display_name;
			$ticket_author_email = $ticket_author_data->user_email;
			$ticket_author_image = get_avatar( $ticket_author_data->ID, '40' );

			$meta_data = sola_st_get_post_meta_all($post->ID);
			
			$ticket_status = get_post_meta($post->ID, 'ticket_status', true);

			$note_data = sola_st_get_note_meta_all($post->ID);

			$response_contents = "";
			$note_contents = "";


	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret .= "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";


			if( $note_data ){

				krsort( $note_data );

				foreach( $note_data as $meta ){

					$note_data = sola_st_get_response_data($meta->post_id);

					$author_data = get_userdata($note_data->post_author);

				    if (isset($author_data->roles[0])) {
		        		$role = $author_data->roles[0];
		    		} else {
		        		if (isset($author_data->roles[1])) { 
		            		$role = $author_data->roles[1]; 
		        		} else { 
		            		$role = ""; 
		        		}
		    		}

		    		$response_image = get_avatar($author_data->user_email, '40');

		    		$response_display_name = $author_data->display_name;
		    		$response_post_date = $note_data->post_date;
		    		$response_post_time = sola_st_time_elapsed_string(strtotime($note_data->post_date));
		    		$response_title = $note_data->post_title;
		    		$response_content = nl2br($note_data->post_content);

		    		$note_contents .= "<div class='ticket_author_meta_note'>";

					$note_contents .= "	<div class='ticket_author_image ticket_responder_gravatar'>$response_image</div>";

					$note_contents .= "	<div class='ticket_author_details'>";

					$label = "<div class='ticket_response_label'>".__('Internal Note', 'sola_st')."</div>";

					$note_contents .= "		<div class='ticket_author'>$response_display_name | <span>$response_post_date</span> $label</div>";

					$note_contents .= " </div>";				

					$note_contents .= "		<div class='ticket_contents ticket_contents_response'>$response_content</div>";

					$note_contents .= "	</div>";

				}

			}

			if( $meta_data ){

				//krsort( $meta_data );

				foreach( $meta_data as $meta ){

					$response_data = sola_st_get_response_data($meta->post_id);

					$author_data = get_userdata($response_data->post_author);

				    if (isset($author_data->roles[0])) {
		        		$role = $author_data->roles[0];
		    		} else {
		        		if (isset($author_data->roles[1])) { 
		            		$role = $author_data->roles[1]; 
		        		} else { 
		            		$role = ""; 
		        		}
		    		}

		    		$response_image = get_avatar($author_data->user_email, '40');

		    		$response_display_name = $author_data->display_name;
		    		$response_post_date = $response_data->post_date;
		    		//$response_post_time = sola_st_time_elapsed_string(strtotime($response_data->post_date));
		    		$response_post_time = sola_st_parse_date(strtotime($response_data->post_date));
		    		$response_title = $response_data->post_title;
		    		$response_content = nl2br($response_data->post_content);

		    		$response_contents .= "<div class='ticket_author_meta_response'>";

					$response_contents .= "	<div class='ticket_author_image ticket_responder_gravatar'>$response_image</div>";

					$response_contents .= "	<div class='ticket_author_details'>";

					if( $ticket_requester_id == $author_data->ID ) {
						$label = "<div class='ticket_author_label'>".__('Ticket Author', 'sola_st')."</div>";
					} else {
						$label = "";
					}

					$response_contents .= "		<div class='ticket_author'><span class='author_name'>$response_display_name</span> | <span>$response_post_time</span> $label</div>";

					$response_contents .= " </div>";				

					$response_contents .= "		<div class='ticket_contents ticket_contents_response'>";

					/* check if we had issues notifying this user of their ticket */
					$notification_issue = get_post_meta($meta->post_id,'sola_st_notification_issue',true);
					if ($notification_issue) {
						
						$response_contents .= sola_st_build_notification_error_html('response',$meta->post_id);
					}

					$response_contents .= $response_content;
					$response_contents .= "</div>";

					$response_contents .= "	</div>";

				}

			}
	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret .= "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";


			$agent_id = get_current_user_id();

			$current_agent_data = get_userdata($agent_id);

			$current_agent_name = $current_agent_data->display_name;
			$current_agent_image = get_avatar($current_agent_data->user_email, '40');

			$text_box_response = "";

			$text_box_response .= "<div class='ticket_author_meta_response'>";

			$text_box_response .= "	<div class='ticket_author_image ticket_responder_gravatar'>$current_agent_image</div>";

			$text_box_response .= "	<div class='ticket_author_details'>";

			$text_box_response .= "		<div class='ticket_author'>$current_agent_name";

			$text_box_response = 			apply_filters('sola_st_current_agent_meta', $text_box_response, $ticket_id );

			$text_box_response .= "		</div>";

			$text_box_response .= " </div>";

			$text_box_response .= "<div class='ticket_response_fields'>";

			$text_box_response .= "<textarea id='sola_st_db_response_textarea' class='sola_st_response_textarea' rows='5'></textarea>";			

			$text_box_response .= "<input type='hidden' id='sola_st_response_title' value='".__('Reply to ', 'sola_st').$ticket_subject."' />";
			$text_box_response .= "<input type='hidden' id='sola_st_agent_id' value='$agent_id' />";
			$text_box_response .= "<input type='hidden' id='sola_st_parent_id' value='$ticket_id' />";			

			$text_box_response .= "<div class='sola_st_db_before_button'>";

			$text_box_response = apply_filters('sola_st_text_response_before', $text_box_response, $ticket_id );

			$text_box_response .= "</div>";

			/* check if we had issues notifying this user of their ticket */
			$notification_issue = get_post_meta($ticket_id,'sola_st_notification_issue',true);
			if ($notification_issue) {
				$text_box_response .= sola_st_build_notification_error_html('ticket',$ticket_id);
			}

			$text_box_response .= "<button type='button' class='button' id='submit_ticket_response'>".__('Submit Response', 'sola_st')."</button>";			
			$text_box_response = apply_filters('sola_st_text_response_after', $text_box_response, $ticket_id );

	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret .= "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";



			$text_box_response .= "</div>";			

			$ret = "";

			$ret .= "<div class='ticket_container'>";
			if( current_user_can('manage_options' ) ){
				$delete_button = "	<span style='float: right;'><button style='font-weight: normal;' class='button' id='sola_st_delete_ticket' ticket_id='$ticket_id'>".__('Delete Ticket', 'sola_st')."</button></span>";
			} else {
				$delete_button = "";
			}

			$ret .= $delete_button;

			$ret .= "	<div class='ticket_author_meta'>";

			$ret .= "		<div class='ticket_author_image ticket_responder_gravatar'>$ticket_author_image</div>";

			$ret .= "			<div class='ticket_author_details'>";


			$ret .= "			<div class='ticket_subject'>$ticket_subject </div>";

			$ret .= "			<div class='ticket_author'><span class='author_name'>$ticket_author_name (<a href='mailto:".$ticket_author_email."'>".$ticket_author_email."</a>)</span> | <span>$ticket_request_date</span> | <div class='ticket_id_label'>".__('Ticket #', 'sola_st')."$ticket_id</div></div>";

			$ret .= "		</div>";

			$ret .= "	</div>";			

			$ret .= 	$text_box_response;

			$ret .= "	<div id='ticket_response_content_holder'></div>";

			$ret .= 	$note_contents;

			$ret .= 	$response_contents;

			$ret .= "	<div class='ticket_author_meta_response'>";

			$ret .= "		<div class='ticket_author_image ticket_responder_gravatar'>$ticket_author_image</div>";

			$ret .= "		<div class='ticket_author_details'>";

			$ret .= "		<div class='ticket_author'><span class='author_name'>$ticket_author_name</span> | <span>$ticket_request_date</span></div>";

			$ret .= " 	</div>";

			$ret .= "	<div class='ticket_contents'>";
			


			

			$sola_tags = sola_st_get_allowed_tags();
			$ret .= wp_kses(utf8_decode($ticket_content),$sola_tags); 


			$ticket_attachments = maybe_unserialize(get_post_custom_values('ticket_attachments', $ticket_id));
			$upload_dir = wp_upload_dir();
			$udir = $upload_dir['baseurl'].'/sola-uploads/'.$ticket_id."/";
			if ($ticket_attachments) {
				$ret .= "<ul>";
				foreach ($ticket_attachments as $key => $att) {
					$att = maybe_unserialize($att);
					foreach ($att as $att_for_realz) {
					
						$ret .= "<li class='sola_st_attachment'><a class='' target='_BLANK' href='".$udir.$att_for_realz."'>".$att_for_realz."</li>";
					}
				}
				$ret .= "</ul>";
			}
			$ret .= "	</div>";

			$ret .= "</div>";

			$ticket_meta = "";

			$stored_ticket_status = get_post_custom_values('ticket_status', $ticket_id);

		    $stored_ticket_priority = get_post_custom_values('ticket_priority', $ticket_id);

	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret .= "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";


		    $ticket_statuses = array(
		    	'9' => __('New', 'sola_st'),
		    	'0' => __('Open', 'sola_st'),
		    	'3' => __('Pending', 'sola_st'),
		    	'2' => __('Closed', 'sola_st'),
		    	'1' => __('Solved', 'sola_st')
	    	);

	    	$ticket_priorities = array(
	    		'1' => __('Low', 'sola_st'),
	    		'2' => __('High', 'sola_st'),
	    		'3' => __('Urgent', 'sola_st'),
	    		'4' => __('Critical', 'sola_st')
    		);

		    $ticket_author_meta .= "<p>";

		    $ticket_author_meta .= "<label>".__('Ticket Status', 'sola_st')."</label>";

		    $ticket_author_meta .= "<select id='sola_st_ticket_status_".$ticket_id."' tid='".$ticket_id."' class='sola_st_ticket_status sola_st_ticket_meta_input'>";
		    
		    foreach( $ticket_statuses as $key => $val ){

		    	if( $stored_ticket_status[0] == $key ) { $sel = 'selected'; } else { $sel = ''; }

		    	$ticket_author_meta .= "<option value='$key' $sel >$val</option>";

		    }
		    
		    $ticket_author_meta .= "</select>";

		    $ticket_author_meta .= "</p>";

		    $ticket_author_meta .= "<p>";

		    $ticket_author_meta .= "<label>".__('Ticket Priority', 'sola_st')."</label>";

		    $ticket_author_meta .= "<select id='sola_st_ticket_priority_".$ticket_id."' tid='".$ticket_id."' class='sola_st_ticket_priority sola_st_ticket_meta_input'>";
		    
		    foreach( $ticket_priorities as $key => $val ){

		    	if( $stored_ticket_priority[0] == $key ) { $sel = 'selected'; } else { $sel = ''; }

		    	$ticket_author_meta .= "<option value='$key' $sel >$val</option>";

		    }
		    
		    $ticket_author_meta .= "</select>";

		    $ticket_author_meta .= "</p>";

	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret .= "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";

		    $ticket_author_meta = apply_filters('sola_st_author_meta', $ticket_author_meta, $ticket_id );

	        $end = (float) array_sum(explode(' ',microtime()));
//	        $ret .= "processing time: ". sprintf("%.4f", ($end-$debug_start))." seconds\n";


			echo json_encode( array( 'ticket' => $ret, 'meta' => $ticket_author_meta, 'ticket_title' => $ticket_subject ) );

			wp_die();

		}

		if( $_POST['action'] == 'sola_st_db_update_ticket_status' ){
			@ob_start();			
			$post_id = intval(sanitize_text_field( $_POST['ticket_id'] ));
			echo update_post_meta($post_id, 'ticket_status', sanitize_text_field( $_POST['ticket_status'] ) );
			$post_details = get_post($post_id);
			$author_id = $post_details->post_author;
			@ob_flush();
			@flush();
			@ob_end_flush();

			$sola_st_settings = get_option("sola_st_settings");
            if(isset($sola_st_settings['sola_st_settings_notify_status_change'])&&$sola_st_settings['sola_st_settings_notify_status_change'] == 1){
            	sola_st_notification_control('status_change', $post_id, $author_id);
            }

			wp_die();

		}

		if( $_POST['action'] == 'sola_st_db_update_ticket_priority' ){

			echo update_post_meta( sanitize_text_field( $_POST['ticket_id'] ), 'ticket_priority', sanitize_text_field( $_POST['ticket_priority'] ) );

			wp_die();

		}		

		if( $_POST['action'] == 'sola_st_submit_response' ){

			$parent_id = $_POST['parent'];

            if (isset($_POST['content'])) { 
            	$content_current = $_POST['content'];
            } else {
            	$content_current = false; 
            }
            

            $title = $_POST['title'];
            $author = $_POST['author'];
            if (!isset($_POST['status'])) {
            	$status = '0';
            } else { 
            	$status = $_POST['status'];
            }


            if ($content_current) {

            	/* only add if there is content */
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

	            $ticket_channel = get_post_meta($parent_id, 'ticket_channel_id', true );

	            update_post_meta($post_id, '_response_parent_id', $parent_id);
	            update_post_meta($parent_id, 'ticket_status', $status);

	            $checker = sola_st_notification_control('response', $parent_id, get_current_user_id(),false,false,$content,$ticket_channel,$post_id);
	            if (!$checker) {
	            	/* email settings not working or what?! */
	            	echo json_encode( array( 'errormsg' => __("There was a problem trying to send the email notification for this response. Please check your WordPress email settings and/or host to ensure that your settings are correct and no email ports are blocked.","sola_st") ) );
	            	wp_die();
	            }
	            $post = get_post( $post_id );

				$ticket_request_date = sola_st_parse_date(strtotime($post->post_date));

				$ticket_author_data = get_userdata($author);
				$ticket_author_name = $ticket_author_data->display_name;
	            $response_contents = "";
				$response_contents .= "<div class='ticket_author_meta_response'>";
				$response_contents .= "	<div class='ticket_author_image ticket_responder_gravatar'>".get_avatar(get_current_user_id(), '40')."</div>";
				$response_contents .= "	<div class='ticket_author_details'>";
				$response_contents .= "		<div class='ticket_author'><span class='author_name'>$ticket_author_name</span> | <span>$ticket_request_date</span></div>";
				$response_contents .= " </div>";				
				$response_contents .= "		<div class='ticket_contents ticket_contents_response'>".nl2br($content_current)."</div>";
				$response_contents .= "	</div>";
				echo json_encode( array( 'content' => $response_contents, 'status_string' => $status, 'message' => __('Your ticket has been successfully submitted', 'sola_st' ) ) );
	            wp_die();
	        } else {
	        	/* just update the status */
	        	update_post_meta($parent_id, 'ticket_status', $status);
	        	echo json_encode( array( 'content' => '', 'status_string' => $status, 'message' => __('Your ticket has been successfully submitted', 'sola_st' ) ) );
	            wp_die();
	        }



            

            


            

		}

		if( $_POST['action'] == 'sola_st_modern_submit_internal_note' ){

            $parent_id = $_POST['parent'];
            $content_current = $_POST['content'];
            $title = $_POST['title'];
            $author = $_POST['author'];

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

            $post = get_post( $post_id );

            $ticket_request_date = sola_st_parse_date(strtotime($post->post_date));

            $ticket_author_data = get_userdata($author);
            $ticket_author_name = $ticket_author_data->display_name;

            $note_contents = "";

            $note_contents .= "<div class='ticket_author_meta_note'>";

            $note_contents .= " <div class='ticket_author_image ticket_responder_gravatar'>".get_avatar(get_current_user_id(), '40')."</div>";

            $note_contents .= " <div class='ticket_author_details'>";

            $note_contents .= "     <div class='ticket_author'><span class='author_name'>$ticket_author_name</span> | <span>$ticket_request_date</span></div>";

            $note_contents .= " </div>";                

            $note_contents .= "     <div class='ticket_contents ticket_contents_response'>".nl2br($content_current)."</div>";

            $note_contents .= " </div>";

            echo json_encode( array( 'content' => $note_contents, 'message' => __('Your note has been successfully saved', 'sola_st' ) ) );

            wp_die();

        }

		if( $_POST['action'] == 'sola_st_delete_ticket' ){

			$ticket_id = intval(sanitize_text_field($_POST['ticket_id']));
			if ($ticket_id) {
				

				$failed = sola_st_delete_ticket($ticket_id);
				if( $failed == FALSE ){
					echo 0;				
				} else {
					echo 1;
				}
			}
			wp_die();

		}

		if( $_POST['action'] == 'sola_st_db_bulk_delete_tickets' ){

			if( isset( $_POST['ticket_ids'] ) ){

				$failed_count = 0;

				$ticket_ids = json_decode(stripslashes($_POST['ticket_ids']));

				foreach( $ticket_ids as $ticket ){

					$ticket = intval( $ticket );

					$failed = sola_st_delete_ticket($ticket);
					//$failed = wp_delete_post( $ticket, true);

					if( $failed == FALSE ){
						$failed_count++;			
					} 

				}

				if( $failed_count > 0 ){
					echo 0;
				} else {
					echo __('The selected tickets have been successfully deleted', 'sola_st');
				}

			}

			wp_die();

		}

		if( $_POST['action'] == 'sola_st_db_search_ticets' ){

			$query = sanitize_text_field($_POST['search']);
			if (isset($_POST['offset'])) { $offset = intval($_POST['offset']); } else { $offset = 0; }
			if (isset($_POST['limit'])) { $limit = intval($_POST['limit']); } else { $limit = 2; }

			$orderby = "date";

			if ($orderby == "date") { $orderby = "post_date"; }
			$order = "DESC";

			global $wpdb;
			$is_more = false;
			$is_less = false;
			if ($offset > 0) { $is_less = true; } /* if we've offset anything, logically there would be previous items so set is_less to true */

			/* search by ticket ID */
			$search_id = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID = '$query' AND post_type = 'sola_st_tickets' AND post_status = 'publish' ");

			$ret = "";

			if( $search_id ){

				/**
				 * Do something with the response
				 */
				$query_count = count($search_id);

				foreach( $search_id as $search ){

					$post_status = sola_st_return_ticket_status_returns( $search->ID );

					$response_count = sola_st_cnt_responses( $search->ID );

					$priority = sola_st_return_ticket_priority_returns( $search->ID );

					$last_responder = "";
					$data = sola_st_get_last_response( intval( $search->ID ) );

		            if (isset($data->post_author)) {
		                $author = $data->post_author;
		                if ($author) {
		                    $author_data = get_userdata( intval( $author ) );
		                    $author_name = $author_data->display_name;
		                    $last_responder .= $author_data->display_name;

		                    $last_responder .= "<br /><small>" . sola_st_time_elapsed_string(strtotime($data->post_date)) . "</small>";
		                } else {
		                    $last_responder .= "-";
		                }
		            } else {
		                $last_responder .= "-";
		            }
					$ret .= "<tr id='sola_st_modern_ticket_row_".$search->ID."'>";
					$ret .= "<td><input type='checkbox' class='sola_st_checkbox' value='".$search->ID."' /></td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_title' ticket_id='".$search->ID."' >" . $search->post_title . "</td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_author' ticket_id='".$search->ID."'>" . $author_name . "</td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_date' ticket_id='".$search->ID."'>" . date('M d, Y', strtotime($search->post_date) ) . "</td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_priority' ticket_id='".$search->ID."'>" . $priority . "</td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_responses' ticket_id='".$search->ID."'>" . $response_count . "</td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_responser' ticket_id='".$search->ID."'>" . $last_responder . "</td>";
					$ret .= "<td class='sola_st_db_single_ticket ticket_status' ticket_id='".$search->ID."'>" . $post_status . "</td>";
					$ret .= "</tr>";			

				}

			} else {

				/* search content */
				$limit = $limit;
				$ticket_counter = 0;
				$sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'sola_st_tickets' AND ( post_content LIKE '%$query%' OR post_title LIKE '%$query%' ) AND post_status = 'publish' ORDER BY `".$orderby."` $order LIMIT ".($limit+1)." OFFSET $offset";
				$query_arr = $wpdb->get_results($sql);
				$query_count = count($query_arr);

				if( $query_arr ){

					/**
					 * Do something with the response
					 */
					foreach( $query_arr as $ticket ){


						$post_status = sola_st_return_ticket_status_returns( intval( $ticket->ID ) );

						$response_count = sola_st_cnt_responses( intval( $ticket->ID ) );

						$priority = sola_st_return_ticket_priority_returns( intval( $ticket->ID ) );

						$last_responder = "";
						$data = sola_st_get_last_response( intval( $ticket->ID ) );
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

							$ret .= "<tr id='sola_st_modern_ticket_row_".$ticket->ID."'>";
							$ret .= "<td><input type='checkbox' class='sola_st_checkbox' value='".$ticket->ID."' /></td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_title' ticket_id='".$ticket->ID."' >" . $ticket->post_title . "</td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_author' ticket_id='".$ticket->ID."'>" . $author_name . "</td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_date' ticket_id='".$ticket->ID."'>" . sola_st_parse_date($ticket->post_date) . "</td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_priority' ticket_id='".$ticket->ID."'>" . $priority . "</td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_responses' ticket_id='".$ticket->ID."'>" . $response_count . "</td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_responser' ticket_id='".$ticket->ID."'>" . $last_responder . "</td>";
							$ret .= "<td class='sola_st_db_single_ticket ticket_status' ticket_id='".$ticket->ID."'>" . $post_status . "</td>";
							$ret .= "</tr>";
						} else {
							$is_more = true;
						}

					}

				} else {

					/**
					 * NO TICKETS FOUND
					 */

					$ret .= "<tr><td colspan='9' style='padding: 10px 0;'>".__('No tickets found', 'sola_st')."</td></tr>";

				}

			}

			$js = json_encode(array(
				'ticket_cnt' => $ticket_counter,
				'ticket_html' => $ret,
				'is_more' => $is_more,
				'is_less' => $is_less,
				'orderby' => $orderby,
				'order' => $order,
				'cnt' => $query_count,
				'limit' => $limit,
				'offset' => $offset
				)
			);
			//var_dump($js);

			echo $js;

			wp_die();
		}

	}

}

