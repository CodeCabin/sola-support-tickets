<div class="wrap">



	<?php 
sola_st_write_to_mail_log("test");
	?>


	<div class="sola_st_db_container">
			<?php do_action( 'sola_st_modern_tickets_tab_pane_before' ); ?>
			<!-- <div class="sola_st_db_full_column sola_st_db_column">
				Tabs
			</div> -->
			<?php do_action( 'sola_st_modern_tickets_tab_pane_after' ); ?>

		<div id='sola_tabs'>

		    <ul id='sola_tabs_ul'>
		        <li><a href='#tab1'><?php _e("Dashboard","sola_st"); ?></a></li>
		    </ul>
		    <div id='tab1'>
	  
				<div class="sola_st_db_inner_container">

					<div class="sola_st_db_left_column">
						<div class="sola_st_db_left_column_inner">
							<?php do_action( 'sola_st_modern_tickets_left_column_before' ); ?>
							<div class='sola_st_db_column sola_st_db_ticket_meta_info' style='display: none;'></div>
							<div class='sola_st_db_column sola_st_db_controls_search'>
								<h4><?php _e('Search for a Ticket', 'sola_st'); ?></h4>
								<input type='text' class='sola_st_ticket_meta_input' id='sola_st_modern_search' placeholder="<?php _e("Press 'Enter' to search", "sola_st"); ?>" />
							</div>

							<div class='sola_st_db_column sola_st_db_controls_primary'>
								<?php do_action("sola_st_view_control"); ?>
							</div>


							
							<?php do_action( 'sola_st_modern_tickets_left_column_after' ); ?>
							<!-- <div class='sola_st_db_column'>
								<h4><?php // _e('Feedback', 'sola_st'); ?> <span style='float: right;' id="sola_st_hide_feedback_form">x</span></h4>
								<p><?php //echo sprintf( __('Modern Ticket Views are still in %s. Should you experience any issues or have any feature suggestions, please %s', 'sola_st' ), 
								// '<span style="color: red;">'.__('beta', 'sola_st').'</span>', '<a href="javascript:void();" id="sola_st_modern_open_feedback_form">'.__('let us know', 'sola_st').'</a>' ) ; ?></p>
								<div id="sola_st_modern_feedback_form"></div>
							</div>	 -->		
						</div>	
					</div>
					<div class="sola_st_db_center_column">
						<div class="sola_st_db_center_column_inner">


									<div class='sola_st_dashboard_view_control'></div>
									<div class='sola_st_modern_ticket_actions'>
										<div class='sola_st_ticket_action_inner' style="display:none;">
											<select class="sola_st_dropdown" id="sola_st_modern_bulk_select_primary">
												<option value="sola_st_db_bulk_delete_tickets"><?php _e('Delete', 'sola_st'); ?></option>
												<?php do_action("sola_st_dashboard_actions_primary"); ?>
											</select>

											<?php do_action("sola_st_dashboard_actions_after_primary"); ?>

											<button class='button' id='sola_st_modern_bulk_action'><?php _e('Apply', 'sola_st'); ?></button>
										</div>
									</div>
									<?php do_action( 'sola_st_modern_tickets_right_column_before' ); ?>
									<table class="sola_st_db_ticket_container">
										<thead>
											<tr>
												<td class='ticket_checkbox'><input type="checkbox" id='sola_st_db_check_all' /></td>
												<td class='ticket_status'></td>
												<td class='ticket_id'><?php _e("ID","sola_st_"); ?></td>
												<td><?php _e('Subject', 'sola_st'); ?></td>
												<td><?php _e('Requester', 'sola_st'); ?></td>
												<td><?php _e('Requested', 'sola_st'); ?></td>
												<td><?php _e('Priority', 'sola_st'); ?></td>
												<td class='ticket_responses'><?php _e('Responses', 'sola_st'); ?></td>
												<td class='ticket_responser'><?php _e('Last Response', 'sola_st'); ?></td>
												<td><?php _e('Owner', 'sola_st'); ?></td>
												<td><?php _e('Channel', 'sola_st'); ?></td>
											</tr>
										</thead>
										<tbody></tbody>					
									</table>
									<div class='sola_st_modern_ticket_pagination'>
										<span style='display:none;' offset='0' limit='20' id='sola_st_modern_pagination_controls'>&nbsp;</span>
										<button class='button' id='sola_st_modern_pagination_previous'><?php _e('Previous', 'sola_st'); ?></button>
										<button class='button' id='sola_st_modern_pagination_next'><?php _e('Next', 'sola_st'); ?></button>
									</div>
								
							
							<div class="sola_st_db_single_ticket_handle"></div>
						</div>
					</div>
					<!-- Add when on a single ticket page -->
					<!-- <div class="sola_st_db_center_column sola_st_db_column sola_st_action_panel">
						<button class='button button-primary'><?php // _e( 'Submit', 'sola_st' ); ?></button>
					</div> -->
				</div>
	  		</div>
    	</div>
	</div>
</div>