var tid;
var sola_st_file = [];
var dashboard_obj = new Object();
var num_tabs;
var ticket_id;
var view_type;
var sola_limit = 20;
var sola_rotate_degree = 0, rotate_timer, sola_st_is_refreshing;

jQuery(document).ready(function(){

	/* set timer ... */

	var sola_st_main_timer = setInterval(function() {
		jQuery(".sola_st_refresh").click();
	},600000);

    jQuery("#sola_tabs").tabs({});
            


	/* check if we are looking at a ticket */
	var items = location.search.substr(1).split("&");
	tid = false;
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        console.log(tmp);
        if (tmp[0] == "tid") {
	        if (typeof tmp[1] !== "undefined") {
	        	tid = tmp[1];
	        } else {
	        	tid = false;
	        }
	    } else {
	    	tid = false;
	    }
    }


    if (tid) {
    	sola_st_view_ticket(parseInt(tid));

    	sola_st_fetch_tickets_by_view(1,sola_limit,0,0);
    }
    else { 
    	view_type = 1;
		sola_st_fetch_tickets_by_view(view_type,sola_limit,0,0);
	}


});

function sola_st_add_tab(tid,callback) {
	console.log("adding "+tid);

	if (jQuery("#sola_tab_"+tid).length > 0) {
		/* do nothing */
	} else {

	    jQuery("#sola_tabs_ul").append(
	        "<li id='sola_tab_"+tid+"'><a ticketid='"+tid+"' class='sola_st_tabselector' id='tickettaba_"+tid+"' href='#tab" + tid + "'>#" + tid + "</a> <a href='javascript:sola_st_remove_tab("+tid+");' class='tabclose' tabid='"+tid+"'>x</a></li>"
	    );
		jQuery("#sola_tabs").append(
	        "<div id='tab" + tid + "'></div>"
	    );
	    jQuery("#sola_tabs").tabs("refresh");
	}
    return callback("tab" + tid);

}
function sola_st_remove_tab(tid) {
	console.log("removing "+tid);
    jQuery("#sola_tab_"+tid).remove();
    jQuery("#tab"+tid).remove();
    jQuery("#sola_tabs").tabs("refresh");

}
function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');   
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {    
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                pars.splice(i, 1);
            }
        }

        url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
        return url;
    } else {
        return url;
    }
}


function st_add_to_object(obj_name,key,val,callback) {

	console.log("obj_name "+obj_name);
	console.log("key "+key);
	console.log("val "+val);
	console.log("callback");

	if (val === false) { callback(obj_name); return; }

	console.log(obj_name);

	if (typeof obj_name[key] === "undefined") {
		obj_name[key] = new Object();
	}

	if (typeof obj_name[key][val] === "undefined") {
		console.log("trying to add "+val + " to " + key);
		obj_name[key][val] = true;
	} else {
		console.log(val + " exists");
	}
	
	callback(obj_name);
}





function sola_st_fetch_tickets(status,limit,offset,priority) {
	console.log("status "+status);


	console.log(dashboard_obj);
	st_add_to_object(dashboard_obj,"status",status, function(dashboard_obj2) {
		console.log(dashboard_obj2);

		st_add_to_object(dashboard_obj2,"priority",priority, function(dashboard_obj) {



			console.log("Now getting tickets");

			url = document.URL ;
			newurl = removeURLParameter(url,"tid");
			
			window.history.pushState(
		        {
		            "html":"",
		            "pageTitle":""
		        },
		        "",
		        newurl
		   );

			console.log("fetching");
			console.log(status);
			console.log(limit);
			console.log(offset);
			console.log(priority);

			jQuery(".sola_st_modern_ticket_actions").css('display', 'block');
			jQuery(".sola_st_db_single_ticket_handle").html("");	
			//jQuery(".sola_st_db_ticket_container").css("display", "table");
			//jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");
			
			var main_action = 'sola_st_db_request_tickets_from_control';
			/*if (query_type === 'priority') {
				main_action = 'sola_st_db_request_tickets_from_control_priority';
			}*/


			var data = {
				'sola_st_db_security': sola_st_dashboard_security,
				'action': main_action,
				'offset' : offset,
				'limit' : limit,
				'ticket_status': status,
				'priority': priority
			}

			console.log(data);

			jQuery.post( ajaxurl, data, function(response){
				response = JSON.parse(response);
				console.log(response);

				if (typeof response.is_more !== "undefined" && response.is_more !== true) {
					jQuery("#sola_st_modern_pagination_next").attr('disabled', true);
				} else if (typeof response.is_more !== "undefined" && response.is_more === true) {
					jQuery("#sola_st_modern_pagination_next").removeAttr('disabled');
				} else {
					jQuery("#sola_st_modern_pagination_next").removeAttr('disabled');
				}

				if (typeof response.is_less !== "undefined" && response.is_less !== true) {
					jQuery("#sola_st_modern_pagination_previous").attr('disabled', true);
				} else if (typeof response.is_less !== "undefined" && response.is_less === true) {
					jQuery("#sola_st_modern_pagination_previous").removeAttr('disabled');
				} else {
					jQuery("#sola_st_modern_pagination_previous").removeAttr('disabled');
				}



				jQuery(".sola_st_db_ticket_container tbody").html(response.ticket_html);
				jQuery("#sola_st_modern_pagination_controls").attr('ticket_status',status);
			});



		});
	});
	
}

jQuery("body").on("click",".sola_st_tabselector", function() {
	if (jQuery(this).attr('ticketid').length > 0) {
		ticket_id = jQuery(this).attr('ticketid');	
		console.log(ticket_id);
	}
	
	

});

jQuery("body").on("click", "#sola_st_modern_pagination_next", function(){
	offset = jQuery("#sola_st_modern_pagination_controls").attr('offset');
	limit = jQuery("#sola_st_modern_pagination_controls").attr('limit');
	offset = parseInt(offset)+parseInt(limit);
	jQuery("#sola_st_modern_pagination_controls").attr('offset',offset);


	query = jQuery("#sola_st_modern_pagination_controls").attr('query');
	if (query === "search") {
		s = jQuery("#sola_st_modern_pagination_controls").attr('s');
		sola_st_fetch_tickets_by_search(s,limit,offset);
	} else {

		console.log("view_type: " +view_type);
		sola_st_fetch_tickets_by_view(view_type,limit,offset,0);

	}




});
jQuery("body").on("click", "#sola_st_modern_pagination_previous", function(){
	offset = jQuery("#sola_st_modern_pagination_controls").attr('offset');

	limit = jQuery("#sola_st_modern_pagination_controls").attr('limit');

	offset = parseInt(offset) - parseInt(limit);
	if (offset < 0) { offset = 0; }
	console.log("view_type: " +view_type);
	jQuery("#sola_st_modern_pagination_controls").attr('offset',offset);
	//sola_st_fetch_tickets(ticket_status,query_type,limit,offset,priority);
	sola_st_fetch_tickets_by_view(view_type,limit,offset,0);

});



jQuery("body").on("click", ".sola_st_view_control", function(){
	
	var ids = jQuery('.sola_st_view_control').map(function(index) {
	    jQuery("#"+this.id).removeClass("sola_st_view_active");

	});
	jQuery(this).addClass("sola_st_view_active");
	    

	/* set current view type */
	view_type = jQuery(this).attr('view');

	console.log("here"+view_type);
	var offset = 0;
	var limit = sola_limit;
	jQuery("#sola_st_modern_pagination_controls").attr('offset',offset);
	jQuery("#sola_st_modern_pagination_controls").attr('limit',limit);

	jQuery("#sola_st_modern_pagination_controls").attr('view',view_type);
	jQuery("#sola_st_modern_pagination_controls").attr('query',"view");

	sola_st_fetch_tickets_by_view(view_type,limit,offset,0);

	

});

jQuery("body").on("click", ".sola_st_db_control", function(){
	
	var query_type = jQuery(this).attr('query');

	var ticket_status = jQuery(this).attr('ticket_status');
	var offset = 0;
	var limit = sola_limit;
	jQuery("#sola_st_modern_pagination_controls").attr('ticket_status',ticket_status);
	jQuery("#sola_st_modern_pagination_controls").attr('query',"default");

	sola_st_fetch_tickets(ticket_status,query_type,limit,offset,false);

	

});

jQuery("body").on("click", ".sola_st_db_priority_control", function(){

	var priority = jQuery(this).attr('priority');
	var offset = 0;
	var limit = sola_limit;
	jQuery("#sola_st_modern_pagination_controls").attr('query',"priority");
	sola_st_fetch_tickets(ticket_status,limit,offset,priority);



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

function sola_rotate(element) {
    console.log("rotating");
    jQuery(element).css({ WebkitTransform: 'rotate(' + sola_rotate_degree + 'deg)'});  
    jQuery(element).css({ '-moz-transform': 'rotate(' + sola_rotate_degree + 'deg)'});                      
    rotate_timer = setTimeout(function() {
        ++sola_rotate_degree; sola_rotate(element);
    },5);
}

jQuery(function() {
    
    

}); 
jQuery("body").on("click", ".sola_st_refresh", function(){
	if (sola_st_is_refreshing) {
		return;
	}

	sola_st_is_refreshing = true;
	sola_rotate(jQuery(this));

	var wheeler = jQuery(this);
	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_fetch_channels'
	}

	var ids = jQuery('.sola_st_view_control_ticket_count').map(function(index) {
	    jQuery(this.id).html("...");
	});

	jQuery('.sola_st_view_control_ticket_count').each(function(i, obj) {
	    //test
	    jQuery("#"+obj.id).html("...");
	});
	    


	jQuery.post( ajaxurl, data, function(response){

		if( response ){



			/* refresh everything but stay on the current view */
			offset = jQuery("#sola_st_modern_pagination_controls").attr('offset');
			limit = jQuery("#sola_st_modern_pagination_controls").attr('limit');
			query = jQuery("#sola_st_modern_pagination_controls").attr('query');
			if (query === "search") {
				s = jQuery("#sola_st_modern_pagination_controls").attr('s');
				sola_st_fetch_tickets_by_search(s,limit,offset);
			} else {
				sola_st_fetch_tickets_by_view(view_type,limit,offset,1);

			}

		}
		sola_st_is_refreshing = false
		clearTimeout(rotate_timer);


	});
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

				jQuery(".sola_st_modern_ticket_actions").append("<div class='updated sola_st_fade_away'><p style='text-align: left;'>"+response+"</p></div>");

				jQuery.each( ticket_ids, function( index, value ){
					jQuery("#sola_st_modern_ticket_row_"+value).fadeOut();
				});
				var fadeaway = setTimeout(function() {
					jQuery(".sola_st_fade_away").fadeOut('slow');
				},1000);
				jQuery("#sola_st_db_check_all").attr('checked',false);
				

			}

		});

	}

});

jQuery("body").on("click", ".sola_st_resend_notification_button", function() {
	var nid = jQuery(this).attr('nid');
	var thiselem = jQuery(this);
	var origtext = jQuery("#sola_st_error_"+nid).html();
	jQuery(this).hide();
	jQuery("#sola_st_error_"+nid).html('Sending...');

	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_resend_notification',
		'post_id': nid
	}

	jQuery.post( ajaxurl, data, function(response){
		try{
		    response = JSON.parse( response );
		}catch(e){
		    alert(e); //error in the above string(in this case,yes)!
		}

		if (typeof e === "undefined") {
			if (typeof response.errormsg !== "undefined") {
				console.log(response.errormsg);
				jQuery("#sola_st_error_"+nid).html(origtext);
				jQuery(thiselem).show();

				

			} else {
				/* success */
				jQuery(thiselem).hide();
				jQuery("#sola_st_error_"+nid).html('The notification was sent successfully.');

			}
		} else {
			console.log(e);
			jQuery("#sola_st_error_"+nid).html(origtext);
			jQuery(thiselem).show();
			/* failed.. */
		}

	});



});
jQuery("body").on("click", ".sola_st_db_single_ticket", function(){


	//jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");
	ticket_id = jQuery(this).attr('ticket_id');
	sola_st_view_ticket(ticket_id);



});
/**
* Add a URL parameter (or changing it if it already exists)
* @param {search} string  this is typically document.location.search
* @param {key}    string  the key to set
* @param {val}    string  value 
*/
var addUrlParam = function(search, key, val){
  var newParam = key + '=' + val,
      params = '?' + newParam;

  // If the "search" string exists, then build params from it
  if (search) {
    // Try to replace an existance instance
    params = search.replace(new RegExp('([?&])' + key + '[^&]*'), '$1' + newParam);

    // If nothing was replaced, then add the new param to the end
    if (params === search) {
      params += '&' + newParam;
    }
  }


  return params;
};
function insertParam(key, value) {
    key = encodeURI(key); value = encodeURI(value);

    var kvp = document.location.search.substr(1).split('&');

    var i=kvp.length; var x; while(i--) 
    {
        x = kvp[i].split('=');

        if (x[0]==key)
        {
            x[1] = value;
            kvp[i] = x.join('=');
            break;
        }
    }

    if(i<0) {kvp[kvp.length] = [key,value].join('=');}

    //this will reload the page, it's likely better to store this until finished
    document.URL = kvp.join('&');
 
}


/* thank you KooiInc
http://stackoverflow.com/questions/1199352/smart-way-to-shorten-long-strings-with-javascript
*/
String.prototype.trunc = String.prototype.trunc ||
      function(n){
          return (this.length > n) ? this.substr(0,n-1)+'&hellip;' : this;
      };


function sola_st_view_ticket(ticket_id) {

	var items = location.search.substr(1).split("&");
	tid = false;
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        console.log(tmp);
        if (tmp[0] == "tid") {
	        if (typeof tmp[1] !== "undefined") {
	        	tid = tmp[1];
	        } else {
	        	tid = false;
	        }
	    } else {
	    	tid = false;
	    }
    }

    if (tid) {} else {
		newurl = addUrlParam(document.URL,"tid",ticket_id);
		console.log(newurl);
		window.history.pushState(
	    {
	        "html":"",
	        "pageTitle":""
	    },
	    "",
	    newurl
		);
	}
	
	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_db_request_ticket_from_content_list',
		'ticket_id': ticket_id
	}
	check = sola_st_add_tab(ticket_id, function(tabdiv) {

		jQuery('a[href=#tab'+ticket_id+']').click();
		jQuery("#"+tabdiv).html("<table width='100%''><tr><td colspan='9'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' class='sola_st_loader' /></td></tr></table>");
		
		jQuery.post( ajaxurl, data, function(response){

			/*jQuery(".sola_st_db_ticket_container").css("display", "none");*/

			response = JSON.parse( response );

			
			jQuery("#"+tabdiv).html('<div class="sola_st_db_center_column"><div class="sola_st_db_center_column_inner">'+response.ticket+'</div></div>');
			jQuery("#"+tabdiv).prepend("<div class='sola_st_db_left_column'><div class='sola_st_db_left_column_inner'><div class='sola_st_db_ticket_meta_info' tid='"+ticket_id+"'>"+response.meta+"</div></div></div>");
			
			jQuery('a[href=#tab'+ticket_id+']').click();
			
			jQuery("#tickettaba_"+ticket_id).html(response.ticket_title.trunc(27));

			jQuery(".sola_st_db_ticket_meta_info").css('display', 'block');

			jQuery("textarea.sola_st_response_textarea").focus();

			jQuery('.sola_st_ticket_status').change(function( e ){

		    	var elem = jQuery(this);
				var ticket_id = jQuery(this).attr('tid');
				jQuery("#sola_st_ticket_status_"+ticket_id).attr('disabled', 'disabled');

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

							jQuery("#sola_st_ticket_status_"+ticket_id).removeAttr('disabled');

						}, 300);					

					}				

				});
				
		    });

		    jQuery('.sola_st_ticket_priority').change(function( e ){

				var new_ticket_priority = jQuery(this).val();
				var ticket_id = jQuery(this).attr('tid');
		    	jQuery("sola_st_ticket_priority_"+ticket_id).attr('disabled', 'disabled');
				console.log(ticket_id);

		        var data = {
					'sola_st_db_security': sola_st_dashboard_security,
					'action': 'sola_st_db_update_ticket_priority',
					'ticket_id': ticket_id,
					'ticket_priority': new_ticket_priority
				}

				jQuery.post( ajaxurl, data, function(response){

					if( response ){

						setTimeout(function(){

							jQuery("#sola_st_ticket_priority_"+ticket_id).removeAttr('disabled');

						}, 300);					

					}

				});
		    });

		    if( typeof sola_st_single_response_success == 'function' ){

		    	sola_st_single_response_success( ticket_id );

		    }

		});
	});
}

jQuery("body").on("click", "#submit_ticket_response", function(){

	jQuery('.updated').remove();



	jQuery("#submit_ticket_response").attr('disabled', 'disabled');

	formData = new FormData();
	
	formData.append('sola_st_db_security', sola_st_dashboard_security);
	formData.append('action', 'sola_st_submit_response');
	formData.append('status', jQuery("#submit_ticket_status_on_response").val());
	formData.append('parent', jQuery("#sola_st_parent_id").val());
	formData.append('content', jQuery("#sola_st_db_response_textarea").val());
	formData.append('title', jQuery("#sola_st_response_title").val());
	formData.append('author', jQuery("#sola_st_agent_id").val());
	if (typeof sola_st_file !== "undefined") {

		jQuery.each( sola_st_file, function( index, value ){
			formData.append('file[]', sola_st_file[index]);
		});
		//formData.append('file', sola_st_file);
		formData.append('ticket_id', jQuery("#sola_st_parent_id").val());
	}
	formData.append('timestamp', Date.now());
	console.log(formData);
	/*
    var data = {
        'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_submit_response',
		'status' : jQuery("#submit_ticket_status_on_response").val(),
		'parent': jQuery("#sola_st_parent_id").val(),
        'content': jQuery("#sola_st_db_response_textarea").val(),
        'title': jQuery("#sola_st_response_title").val(),
        'author': jQuery("#sola_st_agent_id").val(),
        'extra' : formData
    };
    console.log(data);
	*/

	jQuery.ajax({
		url : ajaxurl,
		type : 'POST',
		data : formData,
		cache: false,
		processData: false, 
		contentType: false, 
		success : function(response) {

			if(parseInt(response) !== 0){

				try{
				    response = JSON.parse( response );
				}catch(e){
				    console.log(e); //error in the above string(in this case,yes)!
				}

				if (typeof e === "undefined") {
					if (typeof response.errormsg !== "undefined") {
						alert(response.errormsg);

						jQuery("#submit_ticket_response").removeAttr('disabled');

					} else {
				    	if (response.content !== '') {
				    		jQuery("#sola_st_db_response_textarea").val('');
					    	jQuery("#ticket_response_content_holder").prepend( response.content );
					    	jQuery("#submit_ticket_response").removeAttr('disabled');
					    	jQuery(".ticket_author_meta").append("<div class='updated'><p>"+response.message+"</p></div>");
					    	jQuery("#sola_st_ticket_status").val(response.status_string);
					    } else {
					    	jQuery("#submit_ticket_response").removeAttr('disabled');
					    	jQuery(".ticket_author_meta").append("<div class='updated'><p>"+response.message+"</p></div>");
					    	jQuery("#sola_st_ticket_status").val(response.status_string);
					    }
					}
				} else {
				    	jQuery("#submit_ticket_response").removeAttr('disabled');
				}

			}

		},
		error : function (){
			jQuery("#submit_ticket_response").removeAttr('disabled');
   		 	jQuery(".ticket_author_meta").append("<div class='updated'><p>"+"There was an error. Please try again."+"</p></div>");
		}
	});
   



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

jQuery("body").on("click", ".sola_st_private_note", function(){
	console.log("yes");
	jQuery(this).addClass('sola_st_button_active');
	jQuery(".sola_st_standard_response").removeClass('sola_st_button_active');

});
jQuery("body").on("click", ".sola_st_standard_response", function(){
	jQuery(this).addClass('sola_st_button_active');
	jQuery(".sola_st_private_note").removeClass('sola_st_button_active');

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

				sola_st_fetch_tickets(0,sola_limit,0,false);	
				sola_st_remove_tab(ticket_id);
	    		
	    	}

	    });

	}

});

jQuery(document).keypress(function(e) {

    if(e.which == 13) {

        if( jQuery("#sola_st_modern_search").is(":focus") ){

			jQuery(".sola_st_db_ticket_container").css("display", "table");
			jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='9'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' class='sola_st_loader' /></td></tr>");




			var s = jQuery("#sola_st_modern_search").val();

        	sola_st_fetch_tickets_by_search(s,sola_limit,0);

        }
    }

});


function sola_st_fetch_tickets_by_search(s,limit,offset) {
	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': 'sola_st_db_search_ticets',
		'limit' : limit,
		'offset' : offset,
		'search': s
	}

	jQuery("#sola_st_modern_pagination_controls").attr('query',"search");
	jQuery("#sola_st_modern_pagination_controls").attr('s',s);

	jQuery.post( ajaxurl, data, function(response){
		response = JSON.parse(response);

		console.log(response);
		jQuery(".sola_st_db_ticket_container tbody").html(response.ticket_html);

		jQuery("#sola_st_modern_search").val("");

		if (typeof response.is_more !== "undefined" && response.is_more !== true) {
			jQuery("#sola_st_modern_pagination_next").attr('disabled', true);
		} else if (typeof response.is_more !== "undefined" && response.is_more === true) {
			jQuery("#sola_st_modern_pagination_next").removeAttr('disabled');
		} else {
			jQuery("#sola_st_modern_pagination_next").removeAttr('disabled');
		}

		if (typeof response.is_less !== "undefined" && response.is_less !== true) {
			jQuery("#sola_st_modern_pagination_previous").attr('disabled', true);
		} else if (typeof response.is_less !== "undefined" && response.is_less === true) {
			jQuery("#sola_st_modern_pagination_previous").removeAttr('disabled');
		} else {
			jQuery("#sola_st_modern_pagination_previous").removeAttr('disabled');
		}
		
	});
}




function sola_st_fetch_tickets_by_view(view,limit,offset,return_counts) {
	jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='10'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' class='sola_st_loader' /></td></tr>");

	url = document.URL ;
	newurl = removeURLParameter(url,"tid");
	
	window.history.pushState(
        {
            "html":"",
            "pageTitle":""
        },
        "",
        newurl
   );

	jQuery(".sola_st_modern_ticket_actions").css('display', 'block');
	jQuery(".sola_st_db_single_ticket_handle").html("");	
	jQuery(".sola_st_db_ticket_container").css("display", "table");
	jQuery("#sola_st_modern_pagination_controls").attr('query',"view");
	//jQuery(".sola_st_db_ticket_container tbody").html("<tr><td colspan='8'><img src='"+sola_st_db_plugins_url+"/sola-support-tickets/images/ajax-loader.gif' style='display: block; margin: 0 auto;' /></td></tr>");
	
	var main_action = 'sola_st_db_request_tickets_from_control_by_view';

	var data = {
		'sola_st_db_security': sola_st_dashboard_security,
		'action': main_action,
		'offset' : offset,
		'return_counts': return_counts,
		'limit' : limit,
		'view': view
	}

	jQuery.post( ajaxurl, data, function(response){
		response = JSON.parse(response);

		if (typeof response.counts !== "undefined") {
			/* update ticket counts.. */
			jQuery.each( response.counts, function( index, value ){
				jQuery("#sola_st_view_count_"+index).html(value);
			});
		}

		if (typeof response.is_more !== "undefined" && response.is_more !== true) { jQuery("#sola_st_modern_pagination_next").attr('disabled', true); }
		else if (typeof response.is_more !== "undefined" && response.is_more === true) { jQuery("#sola_st_modern_pagination_next").removeAttr('disabled'); } 
		else { jQuery("#sola_st_modern_pagination_next").removeAttr('disabled'); }

		if (typeof response.is_less !== "undefined" && response.is_less !== true) { jQuery("#sola_st_modern_pagination_previous").attr('disabled', true); }
		else if (typeof response.is_less !== "undefined" && response.is_less === true) { jQuery("#sola_st_modern_pagination_previous").removeAttr('disabled'); }
		else { jQuery("#sola_st_modern_pagination_previous").removeAttr('disabled'); }

		jQuery(".sola_st_db_ticket_container tbody").html(response.ticket_html);
		jQuery("#sola_st_view_count_"+view).html(response.cnt);
		
	});

	
}