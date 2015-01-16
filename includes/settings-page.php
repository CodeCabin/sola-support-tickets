<?php 
$st_notification = get_option("sola_st_notifications");
$sola_st_ajax_nonce = wp_create_nonce("sola_st");
$sola_st_settings = get_option("sola_st_settings");

if(function_exists('sola_st_pro_activate')){
    global $sola_st_pro_version;
    if($sola_st_pro_version <= '1.4'){
        $need_update = true;
    } else {
        $need_update = false;
    }
} else {
    $need_update = false;
}


 if (function_exists("sola_st_api_check")) {
    sola_st_api_check();
}


?>

<script language="javascript">
    var sola_st_nonce = '<?php echo $sola_st_ajax_nonce; ?>';
</script>
<style>
    label { font-weight: bolder; }    
</style>

<div class="wrap">
    <div id="icon-options-general" class="icon32 icon32-posts-post"><br></div><h2><?php _e("Sola Support Tickets Settings","sola_st") ?></h2>
    <?php if($need_update){ ?>
        <div id="" class="error">
            <p><?php _e('You are using an outdated version of Sola Support Tickets Pro. Please update the plugin to take advantage of new features.', 'sola_st'); ?></p>
        </div>
    <?php } ?>

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
         <table width='100%'>
                <?php if (function_exists("sola_st_pro_activate")) { ?>
                <tr>
                    <td width="250px" valign="top" >
                        <label><?php _e('API Key', 'sola'); ?></label>
                            <p class="description" style='padding:10px;'><?php _e('Enter your Sola Support Tickets Premium API key', 'sola'); ?></p>
                    </td>
                    <td>
                        <input type="text" class='sola-input' name="sola_st_api" value="<?php echo get_option("sola_st_api");?>"/>
                        <a href="http://solaplugins.com/documentation/sola-support-tickets-documentation/where-do-i-get-my-sola-support-tickets-pro-api-key/" title="Sola Support Tickets" target="_BLANK"><?php _e("Where do I get my API key?","sola"); ?></a>
                        <br />
                        <?php 
                        
                            $api_check = get_option("sola_st_api_status");
                            if (isset($api_check) && $api_check)    {
                                
                                $api_msg = get_option("sola_st_api_msg");
                                $api_msg2 = get_option("sola_st_api_msg2");
                                $api_domains = get_option("sola_st_api_domains");
                            ?>
                        <div class="api_box <?php  if (isset($api_check) && $api_check == 1) {?>green-border<?php } else { ?>red-border<?php } ?>">
                            <strong><?php echo $api_msg; ?></strong>
                            <p><?php echo $api_msg2; ?></p>
                            <p><ul><?php if($api_domains) { foreach($api_domains as $domain) { echo "<li>".$domain."</li>"; } }?></ul></p>
                        </div>
                        

                            <?php
                            }
                            else {
                                
                            
                            }
                        
                        ?>
                    </td>
                </tr>
                <tr style="height:20px;"><td></td><td></td></tr>
         </table>
                <?php } ?> 
          
          
        <h3><?php _e("Notification Settings","sola_st"); ?></h3>
        <table width='100%' class="form-table">
            <tr>
                <td width="250">
                    <label><?php _e("Notifications","sola_st"); ?></label>
                </td>
                <td>
                    <input type="checkbox" class='sola-input' name="sola_st_settings_notify_new_tickets" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_new_tickets']) && $sola_st_settings['sola_st_settings_notify_new_tickets'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification when a new support ticket is received","sola_st"); ?><br />
                    <input type="checkbox" class='sola-input' name="sola_st_settings_notify_new_responses" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_new_responses']) && $sola_st_settings['sola_st_settings_notify_new_responses'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification when a new response is received","sola_st"); ?><br />
                    <input type="checkbox" class='sola-input' name="sola_st_settings_notify_status_change" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_status_change']) && $sola_st_settings['sola_st_settings_notify_status_change'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification to the user whenever the status of a support ticket changes","sola_st"); ?><br />
                    <?php if (function_exists("sola_st_pro_activate")) { ?>
                        <input type="checkbox" class='sola-input' name="sola_st_settings_notify_agent_change" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_agent_change']) && $sola_st_settings['sola_st_settings_notify_agent_change'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification to the agent when a ticket is assigned to them","sola_st"); ?><br />
                    <?php } else { 
                        $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=notify_agent_change">'.__('Premium Version', 'sola_st').'</a>';
                    ?>                  
                        <input type="checkbox" class='sola-input' value="1" disabled readonly="readonly" /><?php _e("Send a notification to the agent when a ticket is assigned to them. Upgrade to the $pro_link to take advantage of this.","sola_st"); ?><br />
                    <?php } ?>
                    <?php if (function_exists("sola_st_pro_activate")) { ?>
                        <input type="checkbox" class='sola-input' name="sola_st_settings_notify_all_agents" value="1" <?php if (isset($sola_st_settings['sola_st_settings_notify_all_agents']) && $sola_st_settings['sola_st_settings_notify_all_agents'] == "1") echo 'checked="checked"'; ?> /><?php _e("Send a notification to all agents when a new ticket is received.","sola_st"); ?><br />
                    <?php } else { 
                        $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=notify_all_agents">'.__('Premium Version', 'sola_st').'</a>';
                    ?>                  
                        <input type="checkbox" class='sola-input' value="1" disabled readonly="readonly" /><?php _e("Send a notification to all agents when a new ticket is received. Upgrade to the $pro_link to take advantage of this.","sola_st"); ?><br />
                  <?php } ?>
                        
               </td>
            </tr>
            <tr>
                <td width="250">
                    <label><?php _e("Thank you text","sola_st"); ?></label>
                    <p class="description"><?php _e("This is sent when someone posts a new support ticket","sola_st"); ?></p>
                </td>
               <td>
                  <textarea cols="80" rows="6" name="sola_st_settings_thank_you_text"><?php if (isset($sola_st_settings['sola_st_settings_thank_you_text'])) { echo $sola_st_settings['sola_st_settings_thank_you_text']; } ?></textarea>
               </td>
            </tr>
            <tr>
                <td width="250">
                    <label><?php _e("Default Ticket Status","sola_st"); ?></label>
                </td>
               <td>
                   <?php if(function_exists('sola_st_pro_activate')){ ?>
                    <select name="sola_st_settings_default_status" id="sola_st_settings_default_status">
                        <option value="0" <?php if(isset($sola_st_settings['sola_st_settings_default_status']) && $sola_st_settings['sola_st_settings_default_status'] == '0'){ echo 'selected'; }?>><?php _e("Pending Review","sola_st"); ?></option>
                        <option value="1" <?php if(isset($sola_st_settings['sola_st_settings_default_status']) && $sola_st_settings['sola_st_settings_default_status'] == '1'){ echo 'selected'; }?>><?php _e("Open","sola_st"); ?></option>
                    </select>
                   <?php } else { ?>
                        <select name="sola_st_settings_default_status" id="sola_st_settings_default_status" disabled>
                            <option value="0" ><?php _e("Pending Review","sola_st"); ?></option>
                        </select>
                        <?php 
                            $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=default_status" target="_BLANK">'.__('Premium Version', 'sola_st').'</a>';
                            _e("Only available in the $pro_link", "sola_st");
                   }
                   ?>
               </td>
            </tr>
            <tr>
                <td width="250">
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
               </td>
            </tr>
            <tr>
                <td width="250">
                    
                </td>
               <td>
                  <input type="checkbox" class='sola-input' name="sola_st_settings_allow_priority" value="1" <?php if (isset($sola_st_settings['sola_st_settings_allow_priority']) && $sola_st_settings['sola_st_settings_allow_priority'] == "1") echo 'checked="checked"'; ?> /><?php _e("Allow users to select a priority when submitting a ticket","sola_st"); ?><br />
               </td>
            </tr>
            <tr>
                <td width="250">
                        <label><?php _e("Departments ","sola_st"); ?></label>
                </td>
                <td>
                    <?php if(function_exists('sola_st_pro_activate')){ ?>
                        <input type="checkbox" class='sola-input' name="sola_st_settings_allow_department" id="sola_st_settings_allow_department" value="1" <?php if(isset($sola_st_settings['sola_st_settings_allow_department']) && $sola_st_settings['sola_st_settings_allow_department'] == 1) { echo 'checked'; } ?>/><?php _e("Allow users to select a department when submitting a ticket","sola_st"); ?><br />
                    <?php
                    } else {
                        $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=select_departments" target="_BLANK">'.__('Premium Version', 'sola_st').'</a>';
                    ?>
                        <input type="checkbox" value="1" disabled="disabled" /><?php _e("Allow users to select a department when submitting a ticket. $pro_link Only","sola_st"); ?><br />
                    <?php
                    }
                    ?>
                    
                </td>
            </tr>
            <tr id="sola_st_departments_row">
                <td width="250">
                </td>
                <td>                    
                    <?php
                    if(function_exists('sola_st_pro_activate')){
                        if(function_exists('sola_st_get_all_departments')){
                            echo sola_st_get_all_departments();
                            _e(" Select a default department your support tickets will be added to. ","sola_st"); 
                        }                        
                    } else {
                        $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=default_departments" target="_BLANK">'.__('Premium Version', 'sola_st').'</a>';
                        echo '<select disabled><option>'.__('None', 'sola_st').'</option></select>';
                        _e(" Select a default department your support tickets will be added to. Only available in the $pro_link","sola_st"); 
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td width="250">
                     <label><?php _e("Require Login?","sola_st"); ?></label>
                </td>
                <td>   
                    <?php 
                    if(function_exists('sola_st_pro_activate')){
                    ?>
                        <input type="checkbox" class='sola-input' name="sola_st_settings_require_login" value="1" <?php if (isset($sola_st_settings['sola_st_settings_require_login']) && $sola_st_settings['sola_st_settings_require_login'] == "1") { echo 'checked="checked"'; } ?> /><?php _e("Require users to login when submitting a support ticket?","sola_st"); ?><br />
                    <?php } else {
                        $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=allow_guest_tickets" target="_BLANK">'.__('Premium Version', 'sola_st').'</a>';
                    ?>
                        <input type="checkbox" class='sola-input' disabled="disabled" checked><?php _e("Require users to login when submitting a support ticket? Only available in the $pro_link","sola_st"); ?><br />
                    <?php } ?>
                    
                </td>
            </tr>
            <tr>
                <td width="250">
                     <label><?php _e("Enable CAPTCHA?","sola_st"); ?></label>
                </td>
                <td>   
                    <?php 
                    if(function_exists('sola_st_pro_activate')){
                        /* Allow them to enable the captcha */
                        if(class_exists('ReallySimpleCaptcha')){
                            ?><input type="checkbox" class='sola-input' name="sola_st_settings_enable_captcha" value="1" <?php if (isset($sola_st_settings['sola_st_settings_enable_captcha']) && $sola_st_settings['sola_st_settings_enable_captcha'] == "1") { echo 'checked="checked"'; } ?> /><?php _e("Enable CAPTCHA verification for users who are not logged in when submitting a ticket?","sola_st"); ?><br /><?php
                        } else {
                            $captcha_link = '<a href="https://wordpress.org/plugins/really-simple-captcha/" target="_BLANK">'.__('Really Simple CAPTCHA', 'sola_st').'</a>';
                            ?><input type="checkbox" class='sola-input' disabled value="1" /><span style="color: red;"><?php _e("$captcha_link is required to be installed and activated on your website to enable CAPTCHA verification","sola_st"); ?></span><br /><?php
                        }
                    } else {
                        /* Disabled the checkbox */
                        $pro_link = '<a href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=enable_captcha" target="_BLANK">'.__('Premium Version', 'sola_st').'</a>';
                        ?><input type="checkbox" class='sola-input' disabled="disabled"><?php _e("Enable CAPTCHA verification for users who are not logged in when submitting a ticket? Only available in the $pro_link","sola_st"); ?><br /><?php
                    }
                    ?>
                </td>
            </tr>  
            <tr>
                <td width="250">
                    <label><?php _e("General Settings","sola_st"); ?></label>
                </td>
               <td>
                  <input type="checkbox" class='sola-input' name="sola_st_settings_allow_html" value="1" <?php if (isset($sola_st_settings['sola_st_settings_allow_html']) && $sola_st_settings['sola_st_settings_allow_html'] == "1") { echo 'checked="checked"'; } ?> /><?php _e("Allow users to post HTML in support tickets and responses?","sola_st"); ?><br />
               </td>
            </tr>                      
          </table>
        <p>&nbsp;</p>
        <p><?php echo __("Need more options?","sola_st"). " <a href='./edit.php?post_type=sola_st_tickets&page=sola-st-menu-feedback-page'>".__("Let us know!","sola_st")."</a> ".__("and we'll add it in!","sola_st"); ?></p>
        
        

      </div>
      <div id="tabs-2">
        <h3><?php _e("Email Settings",'sola_st'); ?></h3>
        <?php 
            if (function_exists("sola_st_pro_activate")) { 
                sola_st_pro_settings('email_settings'); 
            } else { 
        ?>
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
              <h2><?php _e("only","sola_st"); ?> $29.99 <?php _e("/ year","sola_st"); ?></h2>
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