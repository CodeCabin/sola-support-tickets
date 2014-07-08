jQuery("document").ready(function() {
    
   jQuery(".sola_st_send_response_btn").click(function (){
        var post_id = jQuery("#sola_st_response_id").val();
        var post_response = escape(jQuery("#sola_st_response_text").val());
        var post_title = escape(jQuery("#sola_st_response_title").val());
        var post_author = escape(jQuery("#sola_st_response_author").val());
        var orig = jQuery("sola_st_response_div").html();
        jQuery(".sola_st_response_div").html("Sending...");
        var data = {
            action: 'sola_st_save_response',
            parent: post_id,
            content: post_response,
            title: post_title,
            author: post_author,
            security: sola_st_nonce
        };
        
        jQuery.post(ajaxurl, data, function(response) {
            console.log(response);
            
            location.reload();
        });
        
        
    });
    
});