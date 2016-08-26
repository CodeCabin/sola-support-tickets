<?php
/*Handles Reporting Ajax Calls*/
add_action("wp_ajax_sola_st_rep_update_stats", "sola_st_rep_ajax_callback");
function sola_st_rep_ajax_callback(){
	if( isset( $_POST['action'] ) ){
		if ($_POST['action'] == 'sola_st_rep_update_stats') {
		 	if(isset($_POST['payload']['sola_rep_period'])){
		 		$return_data = sola_st_get_tickets_for_period(intval($_POST['payload']['sola_rep_period']), $_POST['payload']);
		 		echo json_encode($return_data);
		 		wp_die();
		 	}
		}
	}
}


function sola_st_get_tickets_for_period($period_value, $payload){
	$return_data = array();
	$period_in_days = 30;
	if(isset($period_value)){
		$sola_args = false;
		switch($period_value){
			case 0:
				$sola_args = array(
				    'posts_per_page'  => -1,
				    'date_query' => array(
				    	'after' => array(
				    		'year'     => date('Y'),
				    		'month'    => date('m'),
				    		'day' 	   => date('d') - 1
				    	)
				    ),
				    'post_type'       => 'sola_st_tickets'
				);
				$period_in_days = 1;
				break;
			case 1:
				$sola_args = array(
				    'posts_per_page'  => -1,
				    'date_query' => array(
				    	'after' => array(
				    		'year'     => date('Y'),
				    		'month'    => date('m'),
				    		'day' 	   => date('d') - 7
				    	)
				    ),
				    'post_type'       => 'sola_st_tickets'
				);
				$period_in_days = 7;
				break;
			case 2:
				$sola_args = array(
				    'posts_per_page'  => -1,
				    'date_query' => array(
				    	'after' => array(
				    		'year'     => date('Y'),
				    		'month'    => date('m') - 1,
				    		'day' 	   => date('d')
				    	)
				    ),
				    'post_type'       => 'sola_st_tickets'
				);
				$period_in_days = 30;
				break;
			case 3:
				$sola_args = array(
				    'posts_per_page'  => -1,
				    'date_query' => array(
				    	'after' => array(
				    		'year'     => date('Y'),
				    		'month'    => date('m') - 2,
				    		'day' 	   => date('d')
				    	)
				    ),
				    'post_type'       => 'sola_st_tickets'
				);
				$period_in_days = 60;
				break;
			default:
				$sola_args = apply_filters("sola_st_get_ticket_query_args_hook", $period_value, $payload); //Pass it on to the next function if does not match original case
				$period_in_days = intval($sola_args['temp_data']['days']);
				unset($sola_args['temp_data']); //remove temp data
				break;
		}


		$return_count_new = 0;
		$return_count_solved = 0;
		
		$total_time =0;

		if($sola_args !== false){
			$all_sola_tickets = get_posts($sola_args);
			$statuses = array(
    			'0' => __('Open', 'sola_st'),
    			'3' => __('Pending', 'sola_st'),
    			'1' => __('Solved', 'sola_st'),
    			'2' => __('Closed', 'sola_st'),
    			'9' => __('New', 'sola_st')
			);

			foreach ($all_sola_tickets as $key => $value) {
				$ticket_meta_data = get_post_meta($value->ID);
				$ticket_status = $ticket_meta_data['ticket_status'][0];
				if(isset($statuses[$ticket_status])){
					//This is a valid index
					if(intval($ticket_status) === 1 || intval($ticket_status) === 2){
						//solved ticket - or closed that is
						$return_count_solved ++;
					} 
				}
				//Just a ticket
				$return_count_new ++;

				//Now the response time stuff
				$req_date = strtotime($value->post_date);
				$current_date = strtotime(date("Y-m-d H:i:s"));
				$diff_interval = false;
				$ticket_responses = sola_st_get_post_meta_all($value->ID);
				if(is_array($ticket_responses) && count($ticket_responses) > 0){
					//Got responses	
					$first_response = $ticket_responses[0];
					if(is_object($first_response) && $first_response !== false){
						$first_response_time = strtotime(sola_st_get_response_data($first_response->post_id)->post_date);
						$diff_interval = $first_response_time - $req_date;
					} else {
						$diff_interval = $current_date - $req_date;
					}
				} else {
					$diff_interval = $current_date - $req_date; //Get diff in seconds
				}
				$total_time += intval($diff_interval);
			}
		}
		$total_diff = intval($total_time / $return_count_new);
		$avg_hour = intval($total_diff / 60 / 60);

		$total_diff = $total_diff - ($avg_hour * 60 * 60);
		$avg_minutes = intval($total_diff / 60);

		$total_diff = $total_diff - ($avg_minutes * 60);
		$avg_seconds = intval($total_diff);

		$return_array['data']['success_action'] = 'update_sola_st_rep_heading';
		$return_array['data']['sola_new_count'] = $return_count_new;
		$return_array['data']['sola_solved_count'] = $return_count_solved;
		$return_array['data']['sola_first_response'] = $avg_hour . ":" . ($avg_minutes > 9 ? $avg_minutes :  "0" . $avg_minutes) . ":" . ($avg_seconds > 9 ? $avg_seconds :  "0" . $avg_seconds);
		$return_array['data']['selected_period_days'] = $period_in_days;

		$return_array = apply_filters("sola_st_get_tickets_for_period_hook", $return_array, $all_sola_tickets);
	}else{
		$return_array['error'] = "No value passed through";
	}
	return $return_array;
}