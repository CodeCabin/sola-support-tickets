<div class="wrap">
	<div class="sola_st_db_container">
			<?php do_action( 'sola_st_modern_tickets_tab_pane_before' ); ?>
			<!-- <div class="sola_st_db_full_column sola_st_db_column">
				Tabs
			</div> -->
			<?php do_action( 'sola_st_modern_tickets_tab_pane_after' ); ?>
		<div class="sola_st_db_inner_container">

			<div class="sola_st_db_left_column">
				<?php do_action( 'sola_st_modern_tickets_left_column_before' ); ?>
				<div class='sola_st_db_column sola_st_db_ticket_meta_info' style='display: none;'></div>
				<div class='sola_st_db_column'>
					<h4><?php _e('Search for a Ticket', 'sola_st'); ?></h4>
					<input type='text' class='sola_st_ticket_meta_input' id='sola_st_modern_search' placeholder="<?php _e("Press 'Enter' to search", "sola_st"); ?>" />
				</div>
				<div class='sola_st_db_column'>
					<h4><?php _e('Filter By Ticket Status', 'sola_st'); ?></h4>
					<ul class="sola_st_db_controls_primary sola_st_db_controls">
						<?php 

							$ticket_statuses = array(
						    	'0' => array( 'title' => __('Open', 'sola_st'), 'count' => sola_st_return_open_ticket_qty() ),
						    	'1' => array( 'title' => __('Solved', 'sola_st'), 'count' => sola_st_return_solved_ticket_qty() ),
						    	'2' => array( 'title' => __('Closed', 'sola_st'), 'count' => sola_st_return_closed_ticket_qty() ),
						    	'9' => array( 'title' => __('Pending Review', 'sola_st'), 'count' => sola_st_return_pending_ticket_qty() )
					    	);					    

							foreach( $ticket_statuses as $key => $val ){

								echo "<li><a href='javascript:void(0)' class='sola_st_db_control' action='$key' ><span>".$val['title']."</span> ".$val['count']."</a></li>";

							}

						?>
					</ul>
				</div>
				<div class='sola_st_db_column'>
					<h4><?php _e('Filter By Ticket Priority', 'sola_st'); ?></h4>
					<ul class="sola_st_db_controls_priorities sola_st_db_controls">
							
						<?php

							$ticket_priorities = array(
					    		'1' => __('Low', 'sola_st'),
					    		'2' => __('High', 'sola_st'),
					    		'3' => __('Urgent', 'sola_st'),
					    		'4' => __('Critical', 'sola_st')
				    		);

							foreach( $ticket_priorities as $key => $val ){

								echo "<li><a href='javascript:void(0)' class='sola_st_db_priority_control' action='$key' ><span>$val</span></a></li>";

							}

						?>

					</ul>
				</div>
				<?php do_action( 'sola_st_modern_tickets_left_column_after' ); ?>
				<!-- <div class='sola_st_db_column'>
					<h4><?php // _e('Feedback', 'sola_st'); ?> <span style='float: right;' id="sola_st_hide_feedback_form">x</span></h4>
					<p><?php //echo sprintf( __('Modern Ticket Views are still in %s. Should you experience any issues or have any feature suggestions, please %s', 'sola_st' ), 
					// '<span style="color: red;">'.__('beta', 'sola_st').'</span>', '<a href="javascript:void();" id="sola_st_modern_open_feedback_form">'.__('let us know', 'sola_st').'</a>' ) ; ?></p>
					<div id="sola_st_modern_feedback_form"></div>
				</div>	 -->			
			</div>
			<div class="sola_st_db_center_column">
				<div class='sola_st_modern_ticket_actions'>
					<button class='button' id='sola_st_modern_bulk_delete'><?php _e('Delete Tickets', 'sola_st'); ?></button>
				</div>
				<?php do_action( 'sola_st_modern_tickets_right_column_before' ); ?>
				<table class="sola_st_db_ticket_container">
					<thead>
					<?php 
						$menu_items = array(
							'subject' => __('Subject', 'sola_st'),
							'requester' => __('Requester', 'sola_st'),
							'requested' => __('Requested', 'sola_st'),
							'priority' => __('Priority', 'sola_st'),
							'responses' => __('Responses', 'sola_st'),
							'last_response' => __('Last Response', 'sola_st'),
							'status' => __('Status', 'sola_st')
						);

						$dashboard_menu = apply_filters('sola_st_dashboard_headings', $menu_items ); 
						
						echo "<tr>";

						echo "<td><input type='checkbox' id='sola_st_db_check_all' /></td>";

						if( is_array( $dashboard_menu ) ){
							foreach( $dashboard_menu as $menu ){
								echo "<td>$menu</td>";
							}
						}

						echo "</tr>";
					?>
					</thead>
					<tbody></tbody>					
				</table>
				<?php do_action( 'sola_st_modern_tickets_right_column_before' ); ?>
				<div class="sola_st_db_single_ticket_handle"></div>
			</div>
			<!-- Add when on a single ticket page -->
			<!-- <div class="sola_st_db_center_column sola_st_db_column sola_st_action_panel">
				<button class='button button-primary'><?php // _e( 'Submit', 'sola_st' ); ?></button>
			</div> -->
		</div>
	</div>
</div>