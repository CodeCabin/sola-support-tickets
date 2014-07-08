
<center>
    <h1 style="font-weight: 300; font-size: 50px; line-height: 50px;">
        <?php _e("Welcome to ",'sola'); ?> 
        <strong style='color: #ec6851;'>Sola Support Tickets</strong> 
        <small>Beta</small>
    </h1>
    <div class="about-text" style="margin: 0;"><?php _e("Create your own support desk in minutes","sola"); ?></div>
    <img src="<?php echo SOLA_ST_PLUGIN_DIR; ?>/images/sola-support-tickets-welcome.png" width="20%"/>

    <h2 style="font-size: 25px;"><?php _e("How did you find us?","sola"); ?></h2>
    <form method="post" name="sola_find_us_form" action="" style="font-size: 16px;">
        <div  style="text-align: left; width:275px;">
            <input type="radio" name="sola_find_us" id="wordpress" value='repository'>
            <label for="wordpress">
                <?php _e('WordPress.org plugin repository ', 'sola'); ?>
            </label>
            <br/>
            <input type='text' placeholder="<?php _e('Search Term', 'sola'); ?>" name='sola_st_search_term' style='margin-top:5px; margin-left: 23px; width: 100%  '>
            <br/>
            <input type="radio" name="sola_find_us" id="search_engine" value='search_engine'>
            <label for="search_engine">
                <?php _e('Google or other search Engine', 'sola'); ?>
            </label>
            <br/>
            <input type="radio" name="sola_find_us" id="friend" value='friend'>
            
            <label for='friend'>
                <?php _e('Friend recommendation', 'sola'); ?>
            </label>
            <br/>   
            <input type="radio" name="sola_find_us" id='other' value='other'>
            
            <label for='other'>
                <?php _e('Other', 'sola'); ?>
            </label>
            <br/>
            
            <textarea placeholder="<?php _e('Please Explain', 'sola'); ?>" style='margin-top:5px; margin-left: 23px; width: 100%' name='sola_st_findus_other_url'></textarea>
        </div>
        <div>
            
        </div>
        <div>
            
        </div>
        <div style='margin-top: 20px;'>
            <button name='action' value='sola_submit_find_us' class="button-primary" style="font-size: 30px; line-height: 60px; height: 60px; margin-bottom: 10px;"><?php _e('Submit', 'sola'); ?></button>
            <br/>
            <button name='action' value="sola_skip_find_us" class="button"><?php _e('Skip', 'sola'); ?></button>
        </div>
    </form> 
</center>

