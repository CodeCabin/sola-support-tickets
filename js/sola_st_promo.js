jQuery(document).ready(function(){

	jQuery("body").on("click","#sola_st_close_review", function() {
        
        jQuery("#sola_st_review_div").fadeOut("fast");

        var data = { 

        	action: 'close_st_review',
        	security: sola_st_security 

        };

        jQuery.post( ajaxurl, data, function( response ) {

        	//console.log(response);
           
        });


    });


});