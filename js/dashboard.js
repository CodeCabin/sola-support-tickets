jQuery(document).ready(function(){

	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_db_request_tickets_from_control',
		'required_action': 0
	}

	jQuery.post( ajaxurl, data, function(response){

		jQuery(".sola_st_db_ticket_container tbody").html(response);

	});


});

jQuery("body").on("click", ".sola_st_db_control", function(){

	jQuery(".sola_st_modern_ticket_actions").css('display', 'block');

	var action = jQuery(this).attr('action');

	jQuery(".sola_st_db_ticket_meta_info").css('display', 'none');
	jQuery(".sola_st_db_ticket_meta_info").html("");

	jQuery(".sola_st_db_single_ticket_handle").html("");	

	jQuery(".sola_st_db_ticket_container").css("display", "table");

	jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");

	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_db_request_tickets_from_control',
		'required_action': action
	}

	jQuery.post( ajaxurl, data, function(response){

		jQuery(".sola_st_db_ticket_container tbody").html(response);

	});

});

jQuery("body").on("click", ".sola_st_db_priority_control", function(){

	var action = jQuery(this).attr('action');

	jQuery(".sola_st_db_ticket_meta_info").css('display', 'none');
	jQuery(".sola_st_db_ticket_meta_info").html("");

	jQuery(".sola_st_db_single_ticket_handle").html("");

	jQuery(".sola_st_db_ticket_container").css("display", "table");

	jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");

	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_db_request_tickets_from_control_priority',
		'required_action': action
	}

	jQuery.post( ajaxurl, data, function(response){

		jQuery(".sola_st_db_ticket_container tbody").html(response);

	});

});

jQuery("body").on("click", ".sola_st_private_note", function(){

	jQuery(this).css('text-decoration', 'underline');
	jQuery(".sola_st_standard_response").css('text-decoration', 'none');
	jQuery(".sola_st_response_textarea").css("background-color", "#FFF6D9");
	jQuery(".sola_st_response_textarea").focus();
	jQuery("#submit_ticket_response").hide();
	jQuery("#submit_ticket_internal_note").show();		

});

jQuery("body").on("click", ".sola_st_standard_response", function(){

	jQuery(this).css('text-decoration', 'underline');
	jQuery(".sola_st_private_note").css('text-decoration', 'none');
	jQuery(".sola_st_response_textarea").css("background-color", "#FFF");
	jQuery(".sola_st_response_textarea").focus();
	jQuery("#submit_ticket_response").show();
	jQuery("#submit_ticket_internal_note").hide();		

});	

jQuery('#sola_st_db_check_all').click(function(event) {   
    if(this.checked) {
        jQuery('.sola_st_checkbox').each(function() {
            this.checked = true;                        
        });
    } else {
        jQuery('.sola_st_checkbox').each(function() {
            this.checked = false;                        
        });
    }
});

jQuery("body").on("click", "#sola_st_modern_bulk_delete", function(){

	var ticket_ids = jQuery(".sola_st_checkbox:checked").map(function(){

	  	return jQuery(this).val();

	}).get();

	var proceed_delete = confirm('Are you sure you want to delete the selected tickets?');

	if( proceed_delete ){

		var data = {
			'sola_st_db_security': sola_st_dashboard_security,
			'action': 'sola_st_db_bulk_delete_tickets',
			'ticket_ids': JSON.stringify( ticket_ids )
		}

		jQuery.post( ajaxurl, data, function(response){

			if( response ){

				jQuery(".sola_st_modern_ticket_actions").append("<div class='updated'><p style='text-align: left;'>"+response+"</p></div>");

				jQuery.each( ticket_ids, function( index, value ){
					jQuery("#sola_st_modern_ticket_row_"+value).fadeOut();
				});
				

			}

		});

	}

});


jQuery("body").on("click", ".sola_st_db_single_ticket", function(){

	jQuery(".sola_st_modern_ticket_actions").css('display', 'none');

	jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");

	var ticket_id = jQuery(this).attr('ticket_id');

	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_db_request_ticket_from_content_list',
		'ticket_id': ticket_id
	}

	jQuery.post( ajaxurl, data, function(response){

		jQuery(".sola_st_db_ticket_container").css("display", "none");

		response = JSON.parse( response );

		jQuery(".sola_st_db_single_ticket_handle").html(response.ticket);
		jQuery(".sola_st_db_ticket_meta_info").html(response.meta);

		jQuery(".sola_st_db_ticket_meta_info").css('display', 'block');

		jQuery("textarea.sola_st_response_textarea").focus();

		jQuery('#sola_st_ticket_status').change(function( e ){

			jQuery(this).attr('disabled', 'disabled');

			var new_ticket_status = jQuery(this).val();

			var data = {
				'sola_st_db_security': sola_st_dashboard_security,
				'action': 'sola_st_db_update_ticket_status',
				'ticket_id': ticket_id,
				'ticket_status': new_ticket_status
			}

			jQuery.post( ajaxurl, data, function(response){

				if( response ){

					setTimeout(function(){

						jQuery("#sola_st_ticket_status").removeAttr('disabled');

					}, 300);					

				}				

			});
			
	    });

	    jQuery('#sola_st_ticket_priority').change(function( e ){

	    	jQuery(this).attr('disabled', 'disabled');

			var new_ticket_priority = jQuery(this).val();

	        var data = {
				'sola_st_db_security': sola_st_dashboard_security,
				'action': 'sola_st_db_update_ticket_priority',
				'ticket_id': ticket_id,
				'ticket_priority': new_ticket_priority
			}

			jQuery.post( ajaxurl, data, function(response){

				if( response ){

					setTimeout(function(){

						jQuery("#sola_st_ticket_priority").removeAttr('disabled');

					}, 300);					

				}

			});
	    });

	    if( typeof sola_st_single_response_success == 'function' ){

	    	sola_st_single_response_success( ticket_id );

	    }

	});



});

jQuery("body").on("click", "#submit_ticket_response", function(){

	jQuery('.updated').remove();

	if( jQuery("#sola_st_db_response_textarea").val() !== '' ){

		jQuery("#submit_ticket_response").attr('disabled', 'disabled');

	    var data = {
	        'sola_st_db_security': sola_st_dashboard_security,
			'action': 'sola_st_submit_response',
			'parent': jQuery("#sola_st_parent_id").val(),
	        'content': jQuery("#sola_st_db_response_textarea").val(),
	        'title': jQuery("#sola_st_response_title").val(),
	        'author': jQuery("#sola_st_agent_id").val()
	    };

	    jQuery.post( ajaxurl, data, function(response){

	    	response = JSON.parse( response );

	    	jQuery("#sola_st_db_response_textarea").val('');

	    	jQuery("#ticket_response_content_holder").prepend( response.content );

	    	jQuery("#submit_ticket_response").removeAttr('disabled');

	    	jQuery(".ticket_author_meta").append("<div class='updated'><p>"+response.message+"</p></div>");

	    });

	} 

});

jQuery("body").on("click", "#submit_ticket_internal_note", function(){

	var ticket_id = jQuery("#sola_st_parent_id").val();

	jQuery('.updated').remove();

	if( jQuery("#sola_st_db_response_textarea").val() !== '' ){

		jQuery("#submit_ticket_internal_note").attr('disabled', 'disabled');

	    var data = {
	        sola_st_db_security: sola_st_dashboard_security,
			action: 'sola_st_modern_submit_internal_note',
			parent: jQuery("#sola_st_parent_id").val(),
	        content: jQuery("#sola_st_db_response_textarea").val(),
	        title: jQuery("#sola_st_response_title").val(),
	        author: jQuery("#sola_st_agent_id").val()
	    };
	    
	    jQuery.post( ajaxurl, data, function(response){

	    	response = JSON.parse( response );

	    	jQuery("#sola_st_db_response_textarea").val('');

	    	jQuery("#ticket_response_content_holder").prepend( response.content );

	    	jQuery("#submit_ticket_internal_note").removeAttr('disabled');

	    	jQuery(".ticket_author_meta").append("<div class='updated'><p>"+response.message+"</p></div>");

	    });

	} 

});

jQuery("body").on("click", "#sola_st_delete_ticket", function(){

	var can_continue = confirm("Are you sure you want to delete this ticket?");

	if( can_continue ){

		var ticket_id = jQuery(this).attr('ticket_id');

		var data = {
	        'sola_st_db_security': sola_st_dashboard_security,
			'action': 'sola_st_delete_ticket',
			'ticket_id': ticket_id
	    };

	    jQuery.post( ajaxurl, data, function(response){

	    	if( response ){

	    		jQuery(".sola_st_db_ticket_meta_info").css('display', 'none');
				jQuery(".sola_st_db_ticket_meta_info").html("");

				jQuery(".sola_st_db_single_ticket_handle").html("");

				jQuery(".sola_st_db_ticket_container").css("display", "table");

				jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");

				var data = {
					'sola_st_db_security': sola_st_dashboard_security,
					'action': 'sola_st_db_request_tickets_from_control',
					'required_action': 0
				}

				jQuery.post( ajaxurl, data, function(response){

					jQuery(".sola_st_db_ticket_container tbody").html(response);

				});

	    	}

	    });

	}

});

jQuery(document).keypress(function(e) {

    if(e.which == 13) {

        if( jQuery("#sola_st_modern_search").is(":focus") ){

        	jQuery(".sola_st_modern_ticket_actions").css('display', 'block');

			jQuery(".sola_st_db_ticket_meta_info").css('display', 'none');
			jQuery(".sola_st_db_ticket_meta_info").html("");

			jQuery(".sola_st_db_single_ticket_handle").html("");	

			jQuery(".sola_st_db_ticket_container").css("display", "table");

			jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");

        	var data = {
				'sola_st_db_security': sola_st_dashboard_security,
				'action': 'sola_st_db_search_ticets',
				'search': jQuery("#sola_st_modern_search").val()
			}

			jQuery.post( ajaxurl, data, function(response){

				jQuery(".sola_st_db_ticket_container tbody").html(response);
				jQuery("#sola_st_modern_search").val("");
				
			});

        }
    }

});