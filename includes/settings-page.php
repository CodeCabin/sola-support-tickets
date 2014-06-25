<?php $st_notification = get_option("sola_st_notifications"); ?>
<?php
$sola_st_ajax_nonce = wp_create_nonce("sola_st");


$sola_st_settings = get_option("sola_st_settings");

?>

<script language="javascript">
    var sola_st_nonce = '<?php echo $sola_st_ajax_nonce; ?>';
</script>
<style>
 label { font-weight: bolder; }    
</style>
<div class="wrap">
   
    
    <div id="icon-options-general" class="icon32 icon32-posts-post"><br></div><h2><?php _e("Sola Support Tickets Settings","sola_st") ?></h2>


    <form action='' name='sola_st_settings' method='POST' id='sola_st_settings'>

    <div id="sola_tabs">
      <ul>
          <li><a href="#tabs-1"><?php _e("Main Settings","sola_st") ?></a></li>
          <li><a href="#tabs-2"><?php _e("Email","sola_st") ?></a></li>
          <li><a href="#tabs-3"><?php _e("Agents","sola_st") ?></a></li>
      </ul>
      <div id="tabs-1">
        <h3><?php _e("Notification Settings","sola_st"); ?></h3>
        <table width='100%'>
            <tr>
                <td width="250" valign="top">
                    <label><?php _e("Notifications","sola_st"); ?></label>
                </td>
               <td>
                  <input type="checkbox" class='sola-input' name="sola_st_settings_notify_new_tickets" value="1" <?php if ($sola_st_settings['sola_st_settings_notify_new_tickets'] == "1") echo 'checked="checked"'; ?> /><?php _e("Get an email every time a new ticket is submitted","sola_st"); ?><br />
                  <input type="checkbox" class='sola-input' name="sola_st_settings_notify_new_responses" value="1" <?php if ($sola_st_settings['sola_st_settings_notify_new_responses'] == "1") echo 'checked="checked"'; ?> /><?php _e("Get an email every time a new response is submitted","sola_st"); ?><br />
               </td>
            </tr>
          </table>
        <p>&nbsp;</p>
        <p><?php echo __("Need more options?","sola_st"). " <a href='./edit.php?post_type=sola_st_tickets&page=sola-st-menu-feedback-page'>".__("Let us know!","sola_st")."</a> ".__("and we'll add it in!","sola_st"); ?></p>
        
        

      </div>
      <div id="tabs-2">
          <h3><?php _e("Email Settings",'sola_st'); ?></h3>
          <?php if (function_exists("sola_st_pro_activate")) { ?>
                <?php 
                //$schedule = wp_get_schedule( 'sola_st_cron_imap' );
                //$timestamp = wp_next_scheduled('sola_st_cron_imap');
                //var_dump(date("Y-m-d H:i:s"));
                //var_dump(date("Y-m-d H:i:s",$timestamp));
                sola_st_pro_settings('email_settings'); 
                ?>
          <?php } else { ?>
          <p><?php echo __("Upgrade to the","sola_st")." <a href='http://solaplugins.com/plugins/sola-support-tickets/?utm_source=plugin&utm_medium=link&utm_campaign=st_email' title='Premium Version' target='_BLANK'>".__("Premium version","sola_st")."</a> ". __("of Sola Support Tickets and automatically convert received emails to support tickets and responses","sola_st"); ?></p>
          <?php } ?>
      </div>
      <div id="tabs-3">
          <h3><?php _e("Agents",'sola_st'); ?></h3>
          <?php if (function_exists("sola_st_pro_activate")) { ?>
                <?php sola_st_pro_settings('agents'); ?>
          <?php } else { ?>
          <p><?php echo __("Upgrade to the","sola_st")." <a href='http://solaplugins.com/plugins/sola-support-tickets/?utm_source=plugin&utm_medium=link&utm_campaign=st_agents' title='Premium Version' target='_BLANK'>".__("Premium version","sola_st")."</a> ". __("of Sola Support Tickets and assign more than one support agent.","sola_st"); ?></p>
          <?php } ?>
      </div>

    <p class='submit' style="margin-left:15px;"><input type='submit' name='sola_st_save_settings' class='button-primary' value='<?php _e("Save Settings","sola_st") ?>' /></p>
    </form>
    
    <p style="margin-left:15px;"><?php echo __("Need help?","sola_st"). " <a href='http://solaplugins.com/documentation/sola-support-tickets-documentation/?utm_source=plugin&utm_medium=link&utm_campaign=st_documentation' target='_BLANK'>".__("Read the documentation","sola_st")."</a>"; ?></p>
    </div>
<?php include 'footer.php'; ?>