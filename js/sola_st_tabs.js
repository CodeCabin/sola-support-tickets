jQuery("document").ready(function()
	{

		jQuery("#sola_tabs").tabs();
		jQuery('#automatic_ticket_closure').click(function()
			{
				auto_close.determine_action();
			});
		
		jQuery('#sola_st_settings').on('submit',function()
		{
		    
			if(jQuery('#automatic_ticket_closure').is(":checked")&&jQuery.trim(jQuery("#sb_amount_of_days_till_auto_close").val())==='')
			{
				alert("You have ticked the checkbox to edit the settings for automatically closing tickets at a set interval. You have not selected the interval.");
				return false;
			}
		});
		
		
		php_mailer_settings_outgoing_mail.determine();
    
    	jQuery('input[name="rb_sola_mailing_system_selection"]').unbind('click').on('click',function()
    	{
    			php_mailer_settings_outgoing_mail.determine();
    	});
    
	});

var auto_close =
{
    determine_action : function()
	{
	    if (jQuery('#automatic_ticket_closure').is(':checked'))
	    {
		    this.show();
	    }
	    else
	    {
		    this.hide();
	    }
	},
    show : function()
    {
    	jQuery('#display_hide_auto_ticket_closure_settings').show();
    	jQuery('#display_no_setting_for_autoclose').hide();
    },
    hide : function()
    {
    	jQuery('#display_hide_auto_ticket_closure_settings').hide();
    	jQuery('#display_no_setting_for_autoclose').show();
    	jQuery('#display_hide_auto_ticket_closure_settings :input').attr('value',"");
    }
};




var php_mailer_settings_outgoing_mail={
		determine:function()
		{
			var checked_radio=jQuery('input[name="rb_sola_mailing_system_selection"]').filter(':checked').attr('id');
			if(checked_radio==='rb_sola_mailing_system_selection_smtp')
			{
				this.show();
			}
			else
			{
				this.hide();
			}
		},
		show:function()
		{
			jQuery('#sola_st_hidden_php_mailer_smtp_settings').show();
		},
		hide:function()
		{
			jQuery('#sola_st_hidden_php_mailer_smtp_settings').hide();
		}
};





