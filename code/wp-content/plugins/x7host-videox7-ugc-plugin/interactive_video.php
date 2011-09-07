<?php
/*
Plugin Name: x7Host's Videox7 UGC Plugin
Plugin URI: http://public.exseven.com/wordpress/
Description: Leverage the power of open source video to add user generated content to your blog.
Version: 2.5.3.4
Author: rtcwp07
Author URI: http://www.x7host.com
*/

define("KALTURA_PLUGIN_FILE", __FILE__);
define("KALTURA_WIDGET_TABLE", "kaltura_widgets");

define("KALTURA_WIDGET_STATUS_UNPUBLISHED", 0);
define("KALTURA_WIDGET_STATUS_PUBLISHED", 1);

define("KALTURA_WIDGET_TYPE_COMMENT", "comment");
define("KALTURA_WIDGET_TYPE_POST", "post");

// backward compatibility for user who have the kalture-interactive-video installed and now trying to activate the new all-in-one-video-pack
if (WP_ADMIN) { // this check should run on admin pages only
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if (is_plugin_active("kaltura-interactive-video/interactive_video.php")) {
		function kaltura_warning_new() {
			echo "
			<div class='updated fade'><p><strong>".__("We're happy to see you've been using the Interactive Video plugin, but in order to upgrade to our new plugin, you'll need to deactivate the old one first. Don't worry, your Interactive Videos will be transferred to the new plugin automatically. Thanks and enjoy!")."</strong></p></div>
			";
		}
		add_action('admin_notices', 'kaltura_warning_new');
	}
	else 
	{
		require_once('all_in_one_video_pack.php');
	}
}
else
{
	require_once('all_in_one_video_pack.php');
}


?>
