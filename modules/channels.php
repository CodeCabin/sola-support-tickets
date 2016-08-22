<?php

function sola_st_channel_html_output() {


	do_action("sola_st_channels_output_html");




}

add_action("sola_st_channels_output_html","sola_st_basic_channel_html_output",10);
function sola_st_basic_channel_html_output() {
	$current_channels = get_option("sola_st_channels");

	echo "<table class='wp-list-table widefat fixed striped pages'>";
	echo "<thead>";
	echo "<tr>";
	echo "<th>".__("Channel","sola_st")."</th>";
	echo "<th class='sola_st_table_action'>Action</th>";
	echo "</tr>";
	echo "</thead>";
	echo "<tbody>";
	if ($current_channels) {
		foreach ($current_channels as $key => $channel) {
			echo "<tr id='view_tr_'".$key.">";
			echo "<td>".$channel['title']."</td>";
			echo "<td>".apply_filters("sola_st_filter_channel_action_control","",$key)."</td>";
			echo "</tr>";


		}
	} else {
		echo "<tr><td colspan='2'>".__("No channels","sola_st")."</td></tr>";
	}
	echo "</tbody>";
	echo "</table>";
}