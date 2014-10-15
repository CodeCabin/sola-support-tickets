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
          <?php if (!function_exists("sola_st_pro_activate")) { ?>
          <li><a href="#tabs-4"><?php _e("Upgrade","sola_st") ?></a></li>
          <?php } ?>
      </ul>
      <div id="tabs-1">
        <h3><?php _e("Notification Settings","sola_st"); ?></h3>
        <table width='100%'>
            <tr>
                <td width="250" valign="top">
                    <label><?php _e("Notifications","sola_st"); ?></label>
                </td>
               <td>
                  <input type="checkbox" class='sola-input' name="sola_st_settings_notify_new_tickets" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_new_tickets']) && $sola_st_settings['sola_st_settings_notify_new_tickets'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification when a new support ticket is received","sola_st"); ?><br />
                  <input type="checkbox" class='sola-input' name="sola_st_settings_notify_new_responses" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_new_responses']) && $sola_st_settings['sola_st_settings_notify_new_responses'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification when a new response is received","sola_st"); ?><br />
               </td>
            </tr>
            <tr style="height:25px;"><td></td><td></td></tr>
            <tr>
                <td width="250" valign="top">
                    <label><?php _e("Thank you text","sola_st"); ?></label>
                    <p class="description"><?php _e("This is sent when someone posts a new support ticket","sola_st"); ?></p>
                </td>
               <td>
                  <textarea cols="80" rows="6" name="sola_st_settings_thank_you_text"><?php if (isset($sola_st_settings['sola_st_settings_thank_you_text'])) { echo $sola_st_settings['sola_st_settings_thank_you_text']; } ?></textarea>
               </td>
            </tr>
            <tr style="height:25px;"><td></td><td></td></tr>
            <tr>
                <td width="250" valign="top">
                    <label><?php _e("Priorities","sola_st"); ?></label>
                </td>
               <td>
                  <?php _e("Default ticket priority:","sola_st"); ?>
                  <select name="sola_st_settings_default_priority" id="sola_st_settings_default_priority">
                      <option value="0" <?php if (isset($sola_st_settings['sola_st_settings_default_priority']) && $sola_st_settings['sola_st_settings_default_priority'] == 0) { echo "selected='selected'"; } ?>><?php _e("Low","sola_st"); ?></option>
                      <option value="1" <?php if (isset($sola_st_settings['sola_st_settings_default_priority']) && $sola_st_settings['sola_st_settings_default_priority'] == 1) { echo "selected='selected'"; } ?>><?php _e("High","sola_st"); ?></option>
                      <option value="2" <?php if (isset($sola_st_settings['sola_st_settings_default_priority']) && $sola_st_settings['sola_st_settings_default_priority'] == 2) { echo "selected='selected'"; } ?>><?php _e("Urgent","sola_st"); ?></option>
                      <option value="3" <?php if (isset($sola_st_settings['sola_st_settings_default_priority']) && $sola_st_settings['sola_st_settings_default_priority'] == 3) { echo "selected='selected'"; } ?>><?php _e("Critical","sola_st"); ?></option>
                  </select>
                  <br />
                  <br />
                  <input type="checkbox" class='sola-input' name="sola_st_settings_allow_priority" value="1" <?php if (isset($sola_st_settings['sola_st_settings_allow_priority']) && $sola_st_settings['sola_st_settings_allow_priority'] == "1") echo 'checked="checked"'; ?> /><?php _e("Allow users to select a priority when submitting a ticket","sola_st"); ?><br />
               </td>
            </tr>
            <tr style="height:25px;"><td></td><td></td></tr>
            <tr>
                <td width="250" valign="top">
                    <label><?php _e("General Settings","sola_st"); ?></label>
                </td>
               <td>
                  <input type="checkbox" class='sola-input' name="sola_st_settings_allow_html" value="1" <?php if (isset($sola_st_settings['sola_st_settings_allow_html']) && $sola_st_settings['sola_st_settings_allow_html'] == "1") echo 'checked="checked"'; ?> /><?php _e("Allow users to post HTML in support tickets and responses?","sola_st"); ?><br />
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
                sola_st_pro_settings('email_settings'); 
                ?>
          <?php } else { ?>
          <p><?php echo __("Upgrade to the","sola_st")." <a href='http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_email' title='Premium Version' target='_BLANK'>".__("Premium version","sola_st")."</a> ". __("of Sola Support Tickets and automatically convert received emails to support tickets and responses","sola_st"); ?></p>
          <?php } ?>
      </div>
      <div id="tabs-3">
          <h3><?php _e("Agents",'sola_st'); ?></h3>
          <?php if (function_exists("sola_st_pro_activate")) { ?>
                <?php sola_st_pro_settings('agents'); ?>
          <?php } else { ?>
          <p><?php echo __("Upgrade to the","sola_st")." <a href='http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_agents' title='Premium Version' target='_BLANK'>".__("Premium version","sola_st")."</a> ". __("of Sola Support Tickets and assign more than one support agent.","sola_st"); ?></p>
          <?php } ?>
      </div>
      <?php if (!function_exists("sola_st_pro_activate")) { ?>
      <div id="tabs-4">
          <center>
              <h1><?php _e("Upgrade to the premium version",'sola_st'); ?></h1>
              <h2><?php _e("only","sola_st"); ?> $9.99 <?php _e("once off","sola_st"); ?></h2>
              <br />
              <a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_agents" target="_BLANK" class="button button-primary">Upgrade now</a>
          
          </center><br /><br />
          <div style="display:block; clear:both; width:100%; overflow:auto;">
              <div style="width:33%; overflow:auto; float:left; text-align:center;">
                  <img src="<?php echo SOLA_ST_PLUGIN_DIR; ?>/images/pro1.png" style="width:75%;" /><br />
                  <strong style='color: #ec6851; font-weight: 300; font-size: 30px; line-height: 36px;'>Support Desk</strong>
                  <p style="font-size:18px;"><?php _e("Add a support desk to the front end of your website","sola_st"); ?></p>
              </div>
              <div style="width:33%; overflow:auto; float:left; clear:right; text-align:center;">
                  <img src="<?php echo SOLA_ST_PLUGIN_DIR; ?>/images/pro3.png" style="width:75%;" /><br />
                  <strong style='color: #ec6851; font-weight: 300; font-size: 30px; line-height: 36px;'>Multiple Agents</strong>
                  <p style="font-size:18px;"><?php _e("Add as many support agents as you need","sola_st"); ?></p>
              </div>
              <div style="width:33%; overflow:auto; float:left; clear:right; text-align:center;">
                  <img src="<?php echo SOLA_ST_PLUGIN_DIR; ?>/images/pro2.png" style="width:75%;" /><br />
                  <strong style='color: #ec6851; font-weight: 300; font-size: 30px; line-height: 36px;'>Email Conversion</strong>
                  <p style="font-size:18px;"><?php _e("Convert emails to support tickets","sola_st"); ?></p>
              </div>
              
          </div>
          
      <?php } ?>
      </div>
    <p class='submit' style="margin-left:15px;"><input type='submit' name='sola_st_save_settings' class='button-primary' value='<?php _e("Save Settings","sola_st") ?>' /></p>
    </form>
    
    <p style="margin-left:15px;"><?php echo __("Need help?","sola_st"). " <a href='http://solaplugins.com/documentation/sola-support-tickets-documentation/?utm_source=plugin&utm_medium=link&utm_campaign=st_documentation' target='_BLANK'>".__("Read the documentation","sola_st")."</a>"; ?></p>
    </div>
<?php include 'footer.php'; ?>