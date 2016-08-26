var SOLA_REP_ACTION = {
    UPDATE_STATS : "sola_st_rep_update_stats"
}

var sola_st_charts_loaded = false;

var sola_ticket_data = [[sola_st_rep_total, 0],
          		        [sola_st_rep_solved, 0]];

jQuery(function(){
	jQuery(document).ready(function(){
		sola_st_rep_charts_check();

		//Make initial call for data
		sola_st_rep_get_data(SOLA_REP_ACTION.UPDATE_STATS, {'sola_rep_period' : 2});

		jQuery("#sola_st_rep_action_period").on("change", function(){
			var sola_period_sel_index = parseInt(jQuery(this).val());
			var payload = {
				'sola_rep_period' : sola_period_sel_index
			}

			var additional_auto_fire = jQuery(this).find(":selected").attr('autoFire'); //Should we fire this ajax request automatically?
			if(typeof additional_auto_fire !== 'undefined' && additional_auto_fire !== false){
				var additional_payload_ids = jQuery(this).find(":selected").attr('payloadAddition');
				if(typeof additional_payload_ids !== 'undefined' && additional_payload_ids !== false){
					//this element wants us to send additional data through
					var check_ids = additional_payload_ids.split(","); //Get comma sepereateds id's
					if(Array.isArray(check_ids)){
						for(var i = 0; i < check_ids.length; i++){
							payload[check_ids[i]] = jQuery("#" + check_ids[i]).val();
						}
					} else {
						console.log("Additional payload found - cannot convert to array");
					}
					
				}

				sola_st_rep_get_data(SOLA_REP_ACTION.UPDATE_STATS, payload);

				jQuery("#ticket_count_current_new").html("<img src='"+sola_st_rep_ajax_icon_url+"' style='max-width:50px'/>");
				jQuery("#ticket_count_current_closed").html("<img src='"+sola_st_rep_ajax_icon_url+"' style='max-width:50px'/>");
				jQuery("#ticket_average_res_time").html("<img src='"+sola_st_rep_ajax_icon_url+"' style='max-width:50px'/>");
			}
			
		});

		jQuery(window).on("resize", function(){
			sola_st_rep_draw_chart_primary();
		});
	});
	
});

function sola_st_rep_draw_chart_primary(){
		if(sola_st_charts_loaded){
			 var data = new google.visualization.DataTable();
	        data.addColumn('string', 'Content');
	        data.addColumn('number', 'Value');
	        data.addRows(sola_ticket_data);

	         var options = {pieHole: 0.4,
	         				legend: 'none',
	         				colors: ['#0073AA','#757575']
	         				};

	        var chart = new google.visualization.PieChart(document.getElementById('sola_st_rep_chart_primary'));
	        chart.draw(data, options);
		}
	}

	function sola_st_rep_charts_check(){
		if(typeof google === "undefined" && sola_st_charts_loaded !== true){
			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = '//www.gstatic.com/charts/loader.js';    

			document.getElementsByTagName('head')[0].appendChild(script);
			setTimeout(function(){
				sola_st_rep_charts_check();
			}, 1000); //Try again in a second
		} else{
			sola_st_charts_loaded = true;
			google.charts.load('current', {'packages':['corechart']});
      		google.charts.setOnLoadCallback(sola_st_rep_draw_chart_primary);

		}
	}

function sola_st_rep_get_data(action, payload){

		var data = {
			'sola_st_rep_security': sola_st_rep_security,
			'action': action,
			'payload': payload
		}

		jQuery.post( ajaxurl, data, function(response){
			if(response){
				response = JSON.parse(response);
				//console.log(response);
				if (response.data.success_action !== "undefined" && response.data.success_action === "update_sola_st_rep_heading") {
					if(typeof response.data.sola_new_count !== "undefined" && typeof response.data.sola_solved_count !== "undefined" && typeof response.data.sola_first_response !== "undefined"){
						//Got data
						jQuery("#ticket_count_current_new").text(parseInt(response.data.sola_new_count));
						jQuery("#ticket_count_current_closed").text(parseInt(response.data.sola_solved_count));
						jQuery("#ticket_average_res_time").text(response.data.sola_first_response);

						//No chart update
						sola_ticket_data = [[sola_st_rep_total, parseInt(response.data.sola_new_count)],
          		        	[sola_st_rep_solved, parseInt(response.data.sola_solved_count)]];

          		        sola_st_rep_draw_chart_primary();

          		        jQuery.event.trigger({type: "sola_st_update_charts", responseData: response}); //Notify all scripts of a change in selection

					}
				}
			}
		});
	}

