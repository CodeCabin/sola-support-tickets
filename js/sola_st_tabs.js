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