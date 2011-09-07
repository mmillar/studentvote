<?php
    global $user_login, $user_email,$admin_email;
    get_currentuserinfo();
    $reg_url = "http://bubble-cast.com/register.html?userName=".$user_email."&email=".$user_email."&siteURL=".get_option('siteurl');
    $action = "options.php";
    
    $building_sitemap = false;
    if ($_REQUEST['action'] == 'generate-sitemap') {
    	bubblecast_build_sitemap_from_ui();
    	$building_sitemap = true;
    }
    
    $updated = $_GET['updated'];
    if ($updated) {
        require_once("bubblecast_utils.php");
        $bubblecast_username = get_option("bubblecast_username");
        $bubblecast_password = get_option("bubblecast_password");
        $siteId = bubblecast_remote_login($bubblecast_username, $bubblecast_password);
?>
<div id="message" class="updated fade"><p><strong>
<?php
        if (!$siteId) {
            if (is_wpmu()) {
                update_site_option('bubblecast_site_id',"");
            } else{
                update_option('bubblecast_site_id',"");
            }
            echo "<span style='background-color: red;padding:5px;'>" . __('Login to bubblecast failed.', 'bubblecast')."</span>";
        } else {
            if (is_wpmu()) {
                update_site_option('bubblecast_username', $bubblecast_username);
                update_site_option('bubblecast_password', $bubblecast_password);
                update_site_option('bubblecast_language', get_option('bubblecast_language'));
                update_site_option('bubblecast_site_id', $siteId);
            } else {
                update_option('bubblecast_site_id', $siteId);
            }
            _e('Login successful', 'bubblecast');
        }
        $auto_sitemap = get_option('bubblecast_auto_build_sitemap');
        if ($auto_sitemap == 'yes') {
        	wp_schedule_event(time() + BUBBLECAST_SITEMAP_REBUILD_PERIOD_SECONDS, BUBBLECAST_SITEMAP_REBUILD_PERIOD_SECONDS, 'bubblecast_build_sitemap_cron');
        } else {
        	wp_clear_scheduled_hook('bubblecast_auto_build_sitemap');
        }
?>
</strong></p></div>
<?php
    } else {
    	if ($building_sitemap) {
?>
<div id="message" class="updated fade"><p><strong>
<?php
			_e('Sitemap will start building in a couple of seconds. This may take some time.', 'bubblecast');
?>
</strong></p></div>
<?php
    	}
    }
?>
<div class="wrap" style="padding-top:5px">
<h2 style="height:50px;background-repeat:no-repeat;background-image:url('<?php echo bubblecast_get_plugin_base_dir().'/i/bubble-big.gif'; ?>');vertical-align:middle;padding-left:65px;">Bubblecast</h2>

<h3><?php _e('Bubblecast plugin for Wordpress brings users\' video to your blog.', 'bubblecast');?></h3>
<div id="trackbacksdiv" class="postbox " >
<div class="inside" style="padding: 25px;">
<ul style="list-style:circle;">
      <li><?php _e('Add video to the post when you\'re writing it', 'bubblecast');?></li>
      <li><?php _e('Add video to your comments', 'bubblecast');?></li>
      <li><?php _e('The tag <b>[bubblecast id=123]</b> is pasted from the widget', 'bubblecast');?></li>

</ul>
<p>
<?php _e('Type in your Bubblecast login and password below and log in.', 'bubblecast');?>
<?php _e('It should be done only once, after successful logon the plugin will remember the credentials.', 'bubblecast');?>
<?php _e('If you still don\'t have Bubblecast account, please,', 'bubblecast');?> <a href="<?php echo $reg_url;?>"><b><?php _e('register here', 'bubblecast');?></b></a>
</p>
</div></div>
<form method="post" action="<?php echo $action; ?>">
<?php
    wp_nonce_field('update-options');
?>
<table id="postcustomstuff" style="width:600px">
<tr valign="top">
<th scope="row">
<?php _e('User name', 'bubblecast') ?>
</th>
<td><input type="text" name="bubblecast_username" value="<?php echo get_bubblecast_option('bubblecast_username'); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Password', 'bubblecast') ?></th>
<td><input type="password" name="bubblecast_password" value="<?php echo get_bubblecast_option('bubblecast_password'); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Language', 'bubblecast') ?></th>
<td>
    <select name="bubblecast_language" value="<?php echo get_option('bubblecast_language'); ?>">
        <option value="en"<?php if (get_bubblecast_option('bubblecast_language') == 'en') { ?> selected="selected"<?php } ?>><?php _e('English', 'bubblecast') ?></option>
        <option value="ru"<?php if (get_bubblecast_option('bubblecast_language') == 'ru') { ?> selected="selected"<?php } ?>><?php _e('Russian', 'bubblecast') ?></option>
        <option value="it"<?php if (get_bubblecast_option('bubblecast_language') == 'it') { ?> selected="selected"<?php } ?>><?php _e('Italian', 'bubblecast') ?></option>
        <option value="nl"<?php if (get_bubblecast_option('bubblecast_language') == 'nl') { ?> selected="selected"<?php } ?>><?php _e('Dutch', 'bubblecast') ?></option>
    </select>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Auto-generate video sitemap', 'bubblecast') ?></th>
<td><input type="checkbox" name="bubblecast_auto_build_sitemap" <?php if (get_bubblecast_option('bubblecast_auto_build_sitemap') == 'yes') echo 'checked="checked"'; ?> value="yes" />
</td>
</tr>
<tr valign="top" align="center"><td colspan="2"> <p style="font-size: 11px; text-align: left;">Bubblecast Video plugin supports video sitemap generation to rank your blog higher in Google. See more details on <a href="http://bubble-cast.com/video-seo-tools.html">Bubblecast Video SEO tools</a>

</p>
</td>
</tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="bubblecast_username,bubblecast_password,bubblecast_language,bubblecast_auto_build_sitemap" />
<?php
    settings_fields( 'bubblecast-group' );
?>
<p>
<input id="bubblecast_submit" name="bubblecast_submit" type="submit" class="button-primary" value="<?php _e('Save and Login', 'bubblecast') ?>" />
<a class="button-primary" style="background:#009900 none repeat scroll 0 0"  href="<?php echo $reg_url;?>"><?php _e('Get login here', 'bubblecast') ?></a>
<a style="background:#EE11AA none repeat scroll 0 0" href="options-general.php?action=generate-sitemap&page=bubblecast-video-plugin/bubblecast.php" class="button-primary"><?php _e('Rebuild sitemap now', 'bubblecast'); ?></a>
</p>
</form>
</div>
