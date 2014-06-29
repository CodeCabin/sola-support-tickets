<br /><br />
<hr />
<div class="footer" style="padding:15px 7px;">
    <div id=foot-contents>
        <div class="support">
            <em><?php _e("Sola Support Tickets is still in BETA. If you find any errors or if you have any suggestions","sola_st");?>, <a href="http://solaplugins.com/support-desk/" target="_BLANK"><?php _e("please get in touch with us","sola_st"); ?></a>.</em>
            
            <?php if (function_exists("sola_st_pro_activate")) { global $sola_st_pro_version; global $sola_st_pro_version_string; ?>
            
            <br />Sola Support Tickets Premium Version: <a target='_BLANK' href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_sversion_premium"><?php echo $sola_st_pro_version.$sola_st_pro_version_string; ?></a> |
            <a target="_blank" href="http://solaplugins.com/support-desk/">Support</a>
            <?php } else { global $sola_st_version; global $sola_st_version_string; ?>
            <br /><?php _e("Sola Support Tickets Version","sola_st"); ?>: <a target='_BLANK' href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_version_free"><?php echo $sola_st_version.$sola_st_version_string; ?></a> |
            <a target="_blank" href="http://solaplugins.com/support-desk/?utm_source=plugin&utm_medium=link&utm_campaign=st_support_footer"><?php _e("Support","sola_st"); ?></a> | 
            <a target="_blank" href="http://solaplugins.com/documentation/sola-support-tickets-documentation/?utm_source=plugin&utm_medium=link&utm_campaign=st_documentation_footer"><?php _e("Documentation","sola_st"); ?></a> | 
            <a target="_blank" id="uppgrade" href="http://solaplugins.com/plugins/sola-support-tickets-helpdesk-plugin/?utm_source=plugin&utm_medium=link&utm_campaign=st_footer" title="Premium Upgrade"><?php _e("Go Premium","sola_st"); ?></a>
            <?php } ?>
            
        </div>
    </div>
</div>
 