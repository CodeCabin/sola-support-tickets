<?php
/* Handles all sola related API init*/

if(class_exists("WP_REST_Request")){
	//The request class was found, move one
	include_once "/sola-api-routes.php";
	include_once "/sola-api-functions.php";
	
}else{
	//No Rest Request class
}


/*
 * Checks if a secret key has been created. 
 * If not create one for use in the API
*/
add_action("sola_st_activate_hook", "sola_support_tickets_api_s_key_check", 10);
add_action("sola_st_update_hook", "sola_support_tickets_api_s_key_check", 10);
function sola_support_tickets_api_s_key_check(){
	if (!get_option("sola_st_api_secret_token")) {
		$user_token = sola_support_tickets_api_s_key_create();
        add_option("sola_st_api_secret_token", $user_token);
    }
}

/*
 * Generates a new Secret Token
*/
function sola_support_tickets_api_s_key_create(){
	$the_code = rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000) . rand(0, 1000);
	$the_time = time();
	$token = md5($the_code . $the_time);
	return $token;
}

/*
 * Creates new settings tab
*/
add_action("sola_st_settings_tabs", "sola_support_tickets_api_settings_tab", 10);
function sola_support_tickets_api_settings_tab(){
	?>
		 <li><a href="#tabs-api"><?php _e("REST API","sola_st") ?></a></li>
	<?php
}

/*
 * Creates new settings content
*/
add_action("sola_st_settings_content", "sola_support_tickets_api_settings_content", 10);
function sola_support_tickets_api_settings_content(){
	sola_support_tickets_api_settings_head();
	?>
		<div id="tabs-api">
	<?php

	if(!class_exists("WP_REST_Request")){
		?>
		 	<div class="update-nag">
		 		<?php _e("To make use of the REST API, please ensure you are using a version of WordPress with the REST API included.", "sola_st");?>
		 		<br><br>
		 		<?php _e("Alternatively, please install the official Rest API plugin from WordPress.", "sola_st");?>
		 	</div>
		<?php
	}

	$secret_token = get_option("sola_st_api_secret_token"); //Checks for token
	?>
			<h3><?php _e("REST API", "sola_st") ?></h3>
			<table class="wp-list-table widefat fixed striped pages">
				<thead>
					<tr>
						<th><?php _e("Option", "sola_st") ?></th>
						<th><?php _e("Value", "sola_st") ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php _e("Secret Token", "sola_st") ?>
						</td>
						<td>
							<input style="max-width:60%; width:100%" type="text" value="<?php echo ($secret_token === false ? __('No secret token found', 'sola_st') : $secret_token) ?>" readonly>
							<a class="button-secondary" href="?page=sola-st-settings&sola_action=new_secret_key"><?php _e("Generate New", "sola_st") ?></a>
						</td>
					</tr>
					<tr>
						<td>
							<?php _e("Supported API Calls", "sola_st") ?>:
						</td>
						<td>
							<code>/wp-json/sola_support_tickets/v1/create_ticket</code> <code>GET, POST</code> 
							<code><a href="#" class="rest_test_button" solaRest="/wp-json/sola_support_tickets/v1/create_ticket" solaTerms="email,subject,message,name" solaVals="test@user.com,Rest Test,This is a test for REST with Sola,Sola Support Tickets"><?php _e("Try", "sola_st") ?></a></code>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<code>/wp-json/sola_support_tickets/v1/view_ticket</code> <code>GET, POST</code>
							<code><a href="#" class="rest_test_button" solaRest="/wp-json/sola_support_tickets/v1/view_ticket" solaTerms="ticket_id" solaVals="1"><?php _e("Try", "sola_st") ?></a></code>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<code>/wp-json/sola_support_tickets/v1/delete_ticket</code> <code>GET, POST</code>
							<code><a href="#" class="rest_test_button" solaRest="/wp-json/sola_support_tickets/v1/delete_ticket" solaTerms="ticket_id" solaVals="1"><?php _e("Try", "sola_st") ?></a></code>
						</td>
					</tr>

					<?php do_action("sola_support_tickets_api_reference_hook"); ?>

					<tr>
						<td>
							<?php _e("API Response Codes", "sola_st") ?>:
						</td>
						<td>
							<code>200</code> <code>Success</code>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<code>400</code> <code>Bad Request</code>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<code>401</code> <code>Unauthorized</code>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<code>403</code> <code>Forbidden</code>
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<code>404</code> <code>Content Not Found</code>
						</td>
					</tr>

					<?php do_action("sola_support_tickets_api_response_ref_hook"); ?>
				</tbody>
			</table>
			<br>

			<?php do_action("sola_support_tickets_api_below_table_hook"); ?>

		</div>
		
	<?php
}

/*
 * Settings head
*/
function sola_support_tickets_api_settings_head(){
	if(isset($_GET)){
		if(isset($_GET["sola_action"])){
			if($_GET["sola_action"] === "new_secret_key"){
				$user_token = sola_support_tickets_api_s_key_create();
       			update_option("sola_st_api_secret_token", $user_token);
			}
		}
	}
}

add_action("sola_support_tickets_api_below_table_hook", "sola_support_tickets_api_test_component", 10);
function sola_support_tickets_api_test_component(){
	$site_url = home_url();
	$secret_token = get_option("sola_st_api_secret_token");

	?>
		<script>
			jQuery(function(){
				jQuery(document).ready(function(){
					jQuery("body").on("click", ".rest_test_button", function (){
						var route = jQuery(this).attr('solaRest');
						var terms = jQuery(this).attr('solaTerms').split(",");
						var vals = jQuery(this).attr('solaVals').split(",");
						sola_rest_console_setup(route,terms,vals);
						sola_rest_console_show();
					});

					jQuery("body").on("click", "#sola_rest_console_button", function (){
						sola_rest_ajax();
					});

					function sola_rest_console_setup(route,terms,values){
						var url = "<?php echo $site_url; ?>";

						url += route + "?token=" + "<?php echo $secret_token; ?>";

						for(var i = 0; i < terms.length; i++){
							url += "&" + terms[i] + "=" + values[ (i < values.length ? i : values.length-1) ]
						}

						jQuery("#sola_rest_console_input").val(encodeURI(url));
					}

					function sola_rest_console_show(){
						jQuery(".sola_rest_consol").fadeIn();
					}

					function sola_rest_ajax(){
						var url = jQuery("#sola_rest_console_input").val();

						jQuery.get(url, function(response){
							console.log(response);

							var returned_data = solaParseResponse(response);

							jQuery("#sola_rest_console_response").text("Success:\n--------\n" + returned_data);
						}).fail(function(e){
							//console.log("somin wrong ");
							var errors = "";

							errors = solaParseResponse(e.responseText);

							jQuery("#sola_rest_console_response").text("Error:\n--------\n" + errors);
						});
					}

					function solaParseResponse(content){
						try{
							if(typeof content !== "object"){
						    	content = JSON.parse(content);
						    }
						}catch(e){
						    content = e.toString();
						}
						if (typeof e === "undefined") {
							var new_content ="";
							jQuery.each(content, function(i, val) {
								if(typeof val === "object"){
									new_content += solaParseResponse(val);
								}else{
							  		new_content += "\n"+ i + ": "+ val;										
								}
							});
							content = new_content;
						}
						return content;
					}
				});
			});
			
		</script>
		<table class="wp-list-table widefat fixed striped pages sola_rest_consol" style="display:none">
			<thead>
				<tr>
					<th><?php _e("Rest Console ", "sola_st") ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="text" value="<?php echo $site_url ?>" style="max-width: 600px; width:80%" id="sola_rest_console_input">
						<a href="javascript:void(0)"  class="button" style="max-width:120px" id="sola_rest_console_button"><?php _e("Try it!", "sola_st"); ?></a>
					</td>
				</tr>
				<tr>
					<td>
						<textarea style="max-width: 600px; width:80%; min-height:250px" id="sola_rest_console_response">

						</textarea>
					</td>
				</tr>
			</tbody>
		</table>
	<?php
}
