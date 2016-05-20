jQuery("document").ready(function ()
{

    sola_st_display_file_input();

    jQuery(".sola_st_send_response_btn").click(function ()
    {
        try
        {
            // try to call the pro version for the upload - this helps with backwards compatibility and also , the functionality is in the pro files , so cannot be done without the pro files
            sola_st_save_response_pro_version();
        }
        catch (error)
        {
            // could not locate the pro version so the user is using the
            // normal version
            sola_st_save_response();
        }

    });

    jQuery(".sola_st_save_note_btn").click(function ()
    {

        /*encoding error fixed 3 march 2015 - albert*/
        /* wordpress already url encodes the items, do not use escape() or encodeURIcomponent() */



        var post_id = jQuery("#sola_st_note_id").val();
        var post_response = jQuery("#sola_st_note_text").val();
        var post_title = jQuery("#sola_st_note_title").val();
        var post_author = jQuery("#sola_st_note_author").val();
        var orig = jQuery("sola_st_note_div").html();
        jQuery(".sola_st_note_div").html("Saving...");
        var data =
                {
                    action: 'sola_st_save_note',
                    parent: post_id,
                    content: post_response,
                    title: post_title,
                    author: post_author,
                    security: sola_st_nonce
                };

        jQuery.post(ajaxurl, data, function (response)
        {
            location.reload();
        });

    });

    
});






function sola_st_save_response()
{

    /*encoding error fixed 3 march 2015 - albert*/
    /* wordpress already url encodes the items, do not use escape() or encodeURIcomponent() */



    var post_id = jQuery("#sola_st_response_id").val();
    var post_response = jQuery("#sola_st_response_text").val();
    var post_title = jQuery("#sola_st_response_title").val();
    var post_author = jQuery("#sola_st_response_author").val();

    var data =
            {
                action: 'sola_st_save_response',
                parent: post_id,
                content: post_response,
                title: post_title,
                author: post_author,
                security: sola_st_nonce
            };


    jQuery(".sola_st_response_div").html("Sending...");

    jQuery.post(ajaxurl, data, function (response)
    {
        window.location.reload();
    });

}

function sola_st_display_file_input()
{
    if (window.File && window.FileReader && window.FileList && window.Blob)
    {
        /* file api is supported so show the field */

        /*according to the php this div should only exist if the user is on pro, but remember, a user may try to hack the system.*/

        if (jQuery('#response_file_upload_field_container').length > 0)
        {

            jQuery('#response_file_upload_field_container').show('fast');
        }

    }
}

